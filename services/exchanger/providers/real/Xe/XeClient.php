<?php

/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 30.11.18
 * Time: 14:15
 */

namespace app\services\exchanger\providers\real\Xe;

use app\services\exchanger\providers\ClientException;
use app\services\exchanger\providers\ExchangerClient;
use app\services\exchanger\providers\objects\ExchangeCurrency;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;


class XeClient implements ExchangerClient
{

    // bvtafnrcb@emlpro.com
    private $baseUrl = 'https://xecdapi.xe.com';

    private $url = "https://xecdapi.xe.com/v1/convert_from.json/";
    private $accountId;
    private $apiKey;

    private $client;

    public function __construct()
    {
        $this->accountId = \Yii::$app->params['exchangers']['xe']['id'];
        $this->apiKey = \Yii::$app->params['exchangers']['xe']['key'];

        $this->client = new Client(array_merge([
            RequestOptions::TIMEOUT => 15,
            RequestOptions::CONNECT_TIMEOUT => 15,
            'base_uri' => $this->baseUrl,
            RequestOptions::AUTH => [
                $this->accountId,
                $this->apiKey,
            ],
        ], []));
    }


    /**
     * @inheritdoc
     */
    public function convert(string $from, array $to): array
    {
        $to = implode(',',$to);
        /** @var Response $responce */
        try {
            $responce = $this->send($from, $to);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            throw new ClientException();
        }

        if ($responce->getStatusCode() != 200 OR $responce->getReasonPhrase() != 'OK') {
            throw new ClientException();
        }
/*ANSWER:
 * {
 *      "terms":"http://www.xe.com/legal/dfs.php",
 *      "privacy":"http://www.xe.com/privacy.php",
 *      "from":"USD",
 *      "amount":1.0,
 *      "timestamp":"2018-12-04T00:00:00Z",
 *      "to":[
 *              {
 *                  "quotecurrency":"EUR",
 *                  "mid":0.8807062308
 *              }
 *           ]
 * }
 */
        $json = json_decode($responce->getBody()->getContents());
        if (!isset($json->to) || empty($json->to)) {
            throw new ClientException();
        }

        $arr = [];
        foreach ($json->to as $conversion) {
            $arr[] = new ExchangeCurrency($from, $conversion->quotecurrency, $conversion->mid);
        }

        return $arr;

    }

    private function send(string $from, string $to): Response
    {
        return $this->client->request('GET', $this->url, [
            'query' => [
                'from'  => $from,
                'to'  => $to,
            ]
        ]);
    }


}