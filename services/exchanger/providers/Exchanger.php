<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 30.11.18
 * Time: 14:24
 */

namespace app\services\exchanger\providers;


use app\services\exchanger\providers\objects\ExchangeCurrency;

interface Exchanger
{
    /**
     * Get rate from DB of provider ant return array of Object
     * @param string $from
     * @param string $to
     * @param int $diffTime
     * @return ExchangeCurrency|null
     */
    public function getRateDb(string $from, string $to, int $diffTime): ?ExchangeCurrency;

    /**
     * Insert rate to DB
     * @param string $from
     * @param string $to
     * @param float $rate
     * @return bool
     */
    public function setRateDb(string $from, string $to, float $rate): bool;

    /**
     * Gets rates through client of provider
     * @param string $from
     * @param array $to
     * @return array ExchangeCurrency[]
     */
    public function getRateOnline(string $from, array $to): array;

    /**
     * Update rates in DB from client of provider
     * @return bool
     */
    public function refreshRatesToDb(): bool;




}