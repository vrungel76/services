<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 11.02.19
 * Time: 16:27
 */

namespace app\services\exchanger\providers\real\Oanda;


use app\services\exchanger\providers\BaseExchanger;
use app\services\exchanger\providers\ExchangerClient;


class OandaExchanger extends BaseExchanger
{

    public function getClient(): ExchangerClient
    {
        return new OandaClient();
    }
}