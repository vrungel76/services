<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 12.02.19
 * Time: 15:18
 */

namespace app\services\exchanger\providers;


interface ExchangerClient
{
    /**
     * @param string $from
     * @param array $to
     * @return array ExchangeCurrency[]
     */
    public function convert(string $from, array $to): array;
}