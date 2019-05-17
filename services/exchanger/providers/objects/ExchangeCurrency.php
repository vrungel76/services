<?php

/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 04.12.18
 * Time: 9:53
 */

namespace app\services\exchanger\providers\objects;

class ExchangeCurrency
{
    /**
     * @var string
     */
    public $from;
    /**
     * @var string
     */
    public $to;

    /**
     * @var float
     */
    public $rate;

    public function __construct(string $from, string $to, float $rate)
    {
        $this->from = $from;
        $this->to = $to;
        $this->rate = $rate;
    }

}