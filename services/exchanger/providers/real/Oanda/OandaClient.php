<?php

/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 30.11.18
 * Time: 14:15
 */

namespace app\services\exchanger\providers\real\Oanda;

use app\services\exchanger\providers\ClientException;
use app\services\exchanger\providers\ExchangerClient;
use app\services\exchanger\providers\objects\ExchangeCurrency;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;

class OandaClient implements ExchangerClient
{

    /* ipbeolhcb@emlhub.com */

    private $apiKey;
    private $url = 'https://web-services.oanda.com/rates/api/v2/rates/spot.json';

    private $client;

    public function __construct()
    {
        $this->apiKey = \Yii::$app->params['exchangers']['oanda']['key'];

        $this->client = new Client(array_merge([
            RequestOptions::TIMEOUT => 15,
            RequestOptions::CONNECT_TIMEOUT => 15,
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept'        => 'application/json',
            ]
        ], []));
    }

    /**
     * @param string $from
     * @param array $to
     * @return ExchangeCurrency[]
     * @throws ClientException
     */
    public function convert(string $from, array $to): array
    {
        /** @var Response $responce */
        try {
            $responce = $this->send($from, $to);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new ClientException();
        }

        if ($responce->getStatusCode() != 200 OR $responce->getReasonPhrase() != 'OK') {
            throw new ClientException();

        }
        /* ANSWER:
         * {
         *  "meta":
         *      {
         *      "effective_params":
         *          {
         *            "data_set":"OANDA",
         *            "base_currencies":["USD"],
         *            "quote_currencies":["CZK","EUR"]
         *          },
         *      "endpoint":"spot",
         *      "request_time":"2018-12-05T15:17:24+00:00",
         *      "skipped_currency_pairs":[]
         *      },
         * "quotes":[
         *              {
         *                  "base_currency":"USD",
         *                  "quote_currency":"CZK",
         *                  "bid":"22.8553",
         *                  "ask":"22.8637",
         *                  "midpoint":"22.8595"
         *              },
         *              {
         *                  "base_currency":"USD",
         *                  "quote_currency":"EUR",
         *                  "bid":"0.882200",
         *                  "ask":"0.882301",
         *                  "midpoint":"0.882250"
         *              }
         *          ]
         * }
         */

        $json = json_decode($responce->getBody()->getContents());
        if (!isset($json->quotes) || empty($json->quotes)) {
            throw new ClientException();
        }

        $arr = [];
        foreach ($json->quotes as $conversion) {
            $arr[] = new ExchangeCurrency($from, $conversion->quote_currency, $conversion->midpoint);
        }

        return $arr;


    }


    private function send(string $from, array $to): Response
    {
        $to = '&quote='.implode('&quote=', $to);
        $string = "base={$from}$to";
        return $this->client->request('GET', $this->url, [
            'query' => $string
        ]);
    }
}