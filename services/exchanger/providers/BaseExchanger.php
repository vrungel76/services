<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 11.02.19
 * Time: 16:44
 */

namespace app\services\exchanger\providers;


use app\models\Currency;
use app\models\Exchanger as ExchangerModel;
use app\services\exchanger\providers\objects\ExchangeCurrency;
use yii\helpers\ArrayHelper;


abstract class BaseExchanger implements Exchanger
{

    /**
     * @var
     */
    protected $exchangerId;
    /**
     * @var ExchangerClient
     */
    protected $client;

    public function __construct(int $id)
    {
        $this->exchangerId = $id;
        $this->client = $this->getClient();
    }


    /**
     * Fabric method
     * @return ExchangerClient
     */
    abstract public function getClient(): ExchangerClient;

    /**
     * Get rate from DB of provider ant return array of Object
     * @param string $from
     * @param string $to
     * @param int $diffTime
     * @return ExchangeCurrency|null
     */
    public function getRateDb(string $from, string $to, int $diffTime): ?ExchangeCurrency
    {

        /** @var ExchangerModel $rate */
        $rate = ExchangerModel::find()->joinWith([
            'baseCurrency curfrom' => function ($query) use ($from) {
                $query->andWhere(['curfrom.code' => $from]);
            },
            'currency curto' => function ($query) use ($to) {
                $query->andWhere(['curto.code' => $to]);
            },
        ])->where(['provider_id' => $this->exchangerId])->one();

        if (!($rate instanceof ExchangerModel)) {
            return null;
        }

        // check if there is time expired for valid rate data
        if ($diffTime < (time() - strtotime($rate->updated_at))) {
            return null;
        }

        return new ExchangeCurrency($rate->baseCurrency->code, $rate->currency->code, $rate->rate);

    }

    /**
     * Insert rate to DB
     * @param string $from
     * @param string $to
     * @param float $rate
     * @return bool
     */
    public function setRateDb(string $from, string $to, float $rate): bool
    {

        /** @var Currency $currencyFrom */
        $currencyFrom = Currency::find()->where(['code' => $from])->one();

        /** @var Currency $currencyTo */
        $currencyTo = Currency::find()->where(['code' => $to])->one();

        if ($currencyFrom === null || $currencyTo === null) {
            return false;
        }
        // check out if the from-to combination is present
        /** @var ExchangerModel $exchangerModel */
        $exchangerModel = ExchangerModel::find()
            ->where([
                'provider_id' => $this->exchangerId,
                'base_currency_id'=>$currencyFrom->id,
                'currency_id' => $currencyTo->id,
            ])->one();

        if (!$exchangerModel) {
            $exchangerModel = new ExchangerModel();
        }

        $exchangerModel->provider_id         = $this->exchangerId;
        $exchangerModel->base_currency_id    = $currencyFrom->id;
        $exchangerModel->currency_id         = $currencyTo->id;
        $exchangerModel->rate                = $rate;
        if (!$exchangerModel->save()) {
            return false;
        }
        return true;
    }

    /**
     * Gets rates through client of provider
     * @param string $from
     * @param array $to
     * @return array ExchangeCurrency[]
     */
    public function getRateOnline(string $from, array $to): array
    {
        try {
            $result = $this->client->convert($from, $to);
        } catch (ClientException $e) {
            return [];
        }
        return $result;
    }

    /**
     * Update rates in DB from client of provider
     * @return bool
     */
    public function refreshRatesToDb(): bool
    {

        /** @var ExchangerModel $model */
        $rates = ExchangerModel::find()
            ->with('baseCurrency', 'currency')
            ->where(['provider_id' => $this->exchangerId])
            ->all();

        $codes = [];
        ArrayHelper::index($rates, function ($element) use (&$codes) {
            /** @var ExchangerModel $element */
            $codes[$element->baseCurrency->code][] = $element->currency->code;
        });

        $rates = ArrayHelper::index($rates, null, [
            function ($element) {
                /** @var ExchangerModel $element */
                return $element->baseCurrency->code;
            },
            function ($element) {
                /** @var ExchangerModel $element */
                return $element->currency->code;
            },

        ]);

        foreach ($codes as $from => $to) {
            $answer = $this->client->convert($from, $to);
            if (!empty($answer)) {
                /** @var ExchangeCurrency $item */
                foreach ($answer as $item) {
                    /** @var ExchangerModel $obj */
                    $obj = $rates[$from][$item->to][0];
                    $obj->rate = $item->rate;
                    $obj->save();
                }
            }

        }

        return true;
    }
}
