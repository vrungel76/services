<?php

/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 30.11.18
 * Time: 14:23
 */

namespace app\services\exchanger;

use app\models\Currency;
use app\models\CurrencyExchange;
use app\models\ExchangeProvider;
use app\models\Provider;
use app\services\exchanger\providers\Creator;
use app\services\exchanger\providers\Exchanger;
use app\services\exchanger\providers\objects\ExchangeCurrency;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class ExchangeService extends Component
{

    /**
     * Valid period time for rates in DB
     * @var int
     */
    private $timeExpired = 24*60*60; // hours * minute * second

    /**
     * Returns array of exchange providers names
     * @return array
     * @throws ExchangeException
     */
    public function getExchangeProviders(): array
    {

        $all = $this->getActiveExchangeProviders();
        $result = ArrayHelper::getColumn($all, function ($element) {
            return $element->name;
        });
        return $result;

    }


    /**
     * Refresh all rates for provider
     * @param Exchanger $provider Interface of Exchange provider
     * @return bool
     */
    public function refreshProviderRates(Exchanger $provider): bool
    {
        return $provider->refreshRatesToDb();

    }

    /**
     * Gets rate from Db else from provider
     * @param int $providerId Settings of Provider model
     * @param string $from
     * @param string $to
     * @return ExchangeCurrency|null
     * @throws ExchangeException
     */
    public function getExchangeRate(int $providerId, string $from, string $to): ?ExchangeCurrency
    {
        $exchangers = $this->getWeightedExchangers($providerId);

        $result = null;
        foreach ($exchangers as $exchanger => $weight) {

            /** @var Exchanger $exchangeProvider */
            $exchangeProvider = $this->createExchanger($exchanger);

            $rate = $exchangeProvider->getRateDb($from, $to, $this->timeExpired);

            if ($rate instanceof ExchangeCurrency) {
                $result = $rate;
                break;
            } else {
                /** @var ExchangeCurrency[] $rate */
                $rate = $exchangeProvider->getRateOnline($from, [$to]);

                if (!empty($rate)) {

                    if ($exchangeProvider->setRateDb($from, $to, $rate[0]->rate)) {
                        $result = $rate[0];
                    }
                    break;
                }
                continue;
            }
        }
        return $result;
    }


    /**
     * @param string $name
     * @return Exchanger
     * @throws ExchangeException
     */
    protected function createExchanger(string $name): Exchanger
    {
        return (new Creator())->createExchanger($name);
    }


    /**
     * @param int $providerId Settings of Provider model
     * @param float $amount
     * @param string $currency
     * @param int $baseCurrency
     * @return float Converted amount of money
     * @throws ExchangeException
     */
    public function getCalculateAmount($providerId, $amount, $currency, $baseCurrency): float
    {
        /** @var Currency $taskCurrencyModel */
        $taskCurrencyModel = Currency::find()->where(['code' => $currency])->one();
        if ($taskCurrencyModel === null ) {
            throw new ExchangeException('Currency is not supported');
        }

        if ($baseCurrency == $taskCurrencyModel->id) {
            return $amount;
        }

        /** @var CurrencyExchange $exchange */
        $exchange = CurrencyExchange::find()->where([
            'provider_id' => $providerId,
            'currency_id' => $taskCurrencyModel->id
        ])->one();

        if ($exchange === null) {
            $defaultCurrency = Currency::findOne($baseCurrency);
            $rate = $this->getExchangeRate($providerId, $defaultCurrency->code, $currency);
            if ($rate === null) {
                throw new ExchangeException('Exchange currency is not available for: ' . $taskCurrencyModel->code);
            }
            $exchange = $rate;
        }

        $amount = $amount/$exchange->rate;

        return  round($amount, 2);

    }

    /**
     * @param array $param
     * @return bool
     * @throws ExchangeException
     */
    public function checkProvidersName(array $param): bool
    {
        $exchangers = $this->getExchangeProviders();

        if (count(array_intersect($exchangers, $param)) != count($param)) {
            return false;
        }
        if (key_exists(0, $param)) {
            return false;
        }

        return true;
    }


    /**
     * Returns sorted exchange providers
     *
     * @param int $providerId  ID of Provider Settings model
     * @return array
     * [
     *      2 => classNameProvider1,
     *      10 => classNameProvider2,
     *      ...
     * ]
     * @throws ExchangeException
     */
    private function getWeightedExchangers($providerId): array
    {
        $weights = $this->getWeightsFromProviderSettings($providerId);

        $exchangeProviders = $this->getActiveExchangeProviders();
        $defaultWeight = ArrayHelper::map($exchangeProviders, 'name', 'weight');
        asort($defaultWeight);

        if (empty($weights)) {
           return $defaultWeight;
        }

        return $weights;
    }


    /**
     * Return weights of exchangers from Provider settings
     * @param $providerId
     * @return array exchanger_weight from Provider settings
     * @throws ExchangeException
     */
    protected function getWeightsFromProviderSettings($providerId): array
    {
        $provider = Provider::findOne($providerId);
        if ($provider === null) {
            throw new ExchangeException('There is no provider settings with ID: ' . $providerId);
        }

        $weights = $provider->exchanger_weight;
        // sort by weight
        ksort($weights);
        return  array_flip($weights) ?? [];


    }

    /**
     * @return ExchangeProvider[] array
     * @throws ExchangeException
     */
    protected function getActiveExchangeProviders(): array
    {
        $providers = ExchangeProvider::findAll(['is_active' => true]);
        if ($providers === null) {
            throw new ExchangeException('There is no active providers');
        }
        return $providers;

    }


}