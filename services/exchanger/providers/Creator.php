<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 21.02.19
 * Time: 18:05
 */

namespace app\services\exchanger\providers;


use app\models\ExchangeProvider;
use app\services\exchanger\ExchangeException;

class Creator
{

    /**
     * @param string $name
     * @param bool $active
     * @return BaseExchanger
     * @throws ExchangeException
     */
    public function createExchanger(string $name, bool $active = true): BaseExchanger
    {
        $class = \Yii::$app->params['exchangers'][$name]['class'];

        $params = ['name' => $name];
        if ($active) {
            $params['is_active'] = $active;
        }
        /** @var ExchangeProvider $exchanger */
        $exchanger = ExchangeProvider::findOne($params);

        if ($exchanger === null) {
            throw new ExchangeException('There is no active exchanger like '.$name);
        }

        return new $class($exchanger->id);
    }
}