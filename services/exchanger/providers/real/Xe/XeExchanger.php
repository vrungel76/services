<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 11.02.19
 * Time: 16:27
 */

namespace app\services\exchanger\providers\real\Xe;


use app\services\exchanger\providers\BaseExchanger;
use app\services\exchanger\providers\ExchangerClient;


class XeExchanger extends BaseExchanger
{

    public function getClient(): ExchangerClient
    {
        return new XeClient();
    }
}