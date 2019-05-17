<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 17.04.19
 * Time: 10:47
 */

namespace app\services\exchanger\providers\crypto\CoinMarketCap;


use app\services\exchanger\providers\BaseExchanger;
use app\services\exchanger\providers\ExchangerClient;

class CoinMarketCapExchanger extends BaseExchanger
{

    /**
     * Fabric method
     * @return ExchangerClient
     */
    public function getClient(): ExchangerClient
    {
        return new CoinMarketCapClient();

    }
}