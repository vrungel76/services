<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 16.04.19
 * Time: 14:53
 */

namespace app\services\exchanger\providers\crypto\CoinMarketCap;

use app\services\exchanger\providers\ClientException;
use app\services\exchanger\providers\crypto\CryptoNormalize;
use app\services\exchanger\providers\ExchangerClient;
use app\services\exchanger\providers\objects\ExchangeCurrency;
use yii\helpers\ArrayHelper;


class CoinMarketCapClient implements ExchangerClient
{

    protected $client;

    public function __construct()
    {
        $this->client = $this->getClient();
    }

    protected function getClient()
    {
        $param = \Yii::$app->params['exchangers']['coinmarketcap']['key'];

        return (new \CoinMarketCap\Api($param))->cryptocurrency();
    }


    /**
     * @param string $from
     * @param array $to
     * @return array ExchangeCurrency[]
     * @throws ClientException
     */
    public function convert(string $from, array $to): array
    {

        $arr = [];
        
        $cryptoArray = $this->getAllId();
        $normFrom = CryptoNormalize::getSymbol($from);

        foreach ($to as $item) {

            // Reverse Convert
            if (!array_key_exists($normFrom, $cryptoArray)) {
                $normItem = CryptoNormalize::getSymbol($item);
                if (array_key_exists($normItem, $cryptoArray)) {
                    try {
                        $result = $this->getQuote($normItem, $normFrom);
                    } catch (\Exception $e) {
                        continue;
                    }
                    $result = CryptoNormalize::{$item}($result);
                    $result = 1/$result;
                }
            } elseif (array_key_exists($normFrom, $cryptoArray)) {
                $normItem = CryptoNormalize::getSymbol($item);
                if (!array_key_exists($normItem, $cryptoArray)) {
                    try {
                        $result = $this->getQuote($normFrom, $normItem);
                    } catch (\Exception $e) {
                        continue;
                    }
                    $result = CryptoNormalize::{$from}($result);
                }
            } else {
                 continue;
            }


            if (isset($result)) {
                $arr[] = new ExchangeCurrency($from, $item, $result);
            } else {
                continue;
            }

        }

        return $arr;
    }

    /**
     * @param int|null $limit
     * @return array Return array like [ symbol => id ]
     * [
     *      'BTC' => 1,
     *      'LTC' => 2,
     *          ...
     * ]
     *
     * @throws ClientException
     */
    protected function getAllId(int $limit = null): array
    {
        $param = [];

        if ($limit != null ) {
            $param = ['limit' => $limit];
        }

        try {
            $result = $this->client->map($param);
        } catch (\Exception $e) {
            throw new ClientException($e->getMessage(), $e->getCode());
        }

        if (!isset($result->status) || !isset($result->status->error_code) || $result->status->error_code != 0) {
            throw new ClientException('Wrong responce from provider');
        }

        $data = $result->data;

        $data = ArrayHelper::map(
            $data,
            function ($element) {
                 return $element->symbol;
             },
            function ($element) {
                return $element->id;
            });

        return $data;
    }


    /**
     * @param string $from Crypto code
     * @param string $to Currency Code
     * @return mixed
     * @throws \Exception
     */
    protected function getQuote(string $from, string $to)
    {
        $result =  $this->client->quotesLatest(['symbol'=>$from,'convert' => $to]);
        $response =  $this->processResponse($result);
        return $response->{$from}->quote->{$to}->price ?? null;
    }


    /**
     * @param $result
     * @return mixed
     * @throws ClientException
     */
    protected function processResponse($result)
    {
        if (!isset($result->status) || !isset($result->status->error_code) || $result->status->error_code != 0) {
            throw new ClientException(
                ($result->status->error_message ?? 'Wrong responce from provider'),
                ($result->status->error_code ?? 404)
                );
        }
        return  $result->data;
    }

}