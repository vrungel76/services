<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 17.04.19
 * Time: 9:29
 */

namespace app\services\exchanger\providers\crypto;


/**
 * Class CryptoNormalize
 * @package app\services\exchanger\providers\crypto
 *
 * @method static float mBTC(float $value)
 * @method static float mETH(float $value)
 * @method static float mLTC(float $value)
 * @method static float mBCH(float $value)
 * @method static float mDASH(float $value)

 */
class CryptoNormalize
{
    protected static $symbols = [
        'mBTC' => 'BTC',
        'mETH' => 'ETH',
        'mLTC' => 'LTC',
        'mBCH' => 'BCH',
        'mDASH'=> 'DASH',
    ];


    /**
     * @param string $symbol Symbol of currency
     * @return string
     */
    public static function getSymbol(string $symbol): string
    {
        return self::$symbols[$symbol] ?? $symbol;
    }


    public static function __callStatic($method, $arguments)
    {
        return isset(self::$symbols[$method])
            ? self::normalizationBy100($arguments)
            : $arguments[0];

/*       return method_exists(static::class, $method)
            ? call_user_func_array([static::class, $method], $arguments)
            : self::normalizationBy100($arguments);
*/
    }

    /**
     * @param $arguments
     * @return float
     */
    protected static function normalizationBy100($arguments): float
    {
        return $arguments[0] / 1000;
    }

}