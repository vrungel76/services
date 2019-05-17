<?php
/**
 * Created by PhpStorm.
 * User: agavrilenko
 * Date: 29.10.18
 * Time: 13:52
 */

namespace app\api;

use app\models\Currency;
use app\models\CurrencyExchange;
use app\models\Provider;
use yii;

class CurrencyController extends RestController
{
    /**
     * @SWG\Get(
     *        path="/currency",
     *        tags={"Currency"},
     *        summary="Gets all supported currency",
     *     security={
     *     {"client": {}},},
     *   @SWG\Response(
     *      response=200,
     *      description="Currency",
     *      schema=@SWG\Schema(ref="#/definitions/Currency")
     *   ),
     *)
     */
    public function actionIndex()
    {
        $currency = Currency::find()->all();
        return $this->sendResponse(200, $currency);
    }

    /**
     * @SWG\Post(
     *        path="/providers/{provider_id}/exchange/{currency_code}",
     *        tags={"Currency"},
     *        summary="Define exchange rate currency",
     *     security={
     *     {"client": {}},},
     *     @SWG\Parameter(
     *            name="currency_code",
     *            in="path",
     *            required=true,
     *            type="string",
     *            description="International code of currency ISO 4217 (3 symbols)",
     *        ),
     *      @SWG\Parameter(
     *            name="provider_id",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="ID of provider",
     *        ),
     *      @SWG\Parameter(
     *            name="rate",
     *            in="formData",
     *            required=true,
     *            type="number",
     *            description="Exchange rate currency to base one.",
     *        ),
     *   @SWG\Response(
     *      response=200,
     *      description="Currency",
     *      schema=@SWG\Schema(ref="#/definitions/CurrencyExchange")
     *   ),
     *)
     * @param $currency_code
     * @param $provider_id
     * @return mixed
     */
    public function actionCreate($currency_code, $provider_id)
    {
        $token_id = Yii::$app->user->identity->getId();


        /** @var Provider $provider */
        $provider = Provider::find()->where(['token_id' => $token_id, 'id' => $provider_id])->one();

        if ($provider === null) {
            return $this->sendError(400, 'Provider does not exist.');
        }

        /** @var Currency $currency */
        $currency =  Currency::find()->where(['code' => $currency_code])->one();

        if ($currency === null) {
            return $this->sendError(400, 'Currency name does not exist');
        }

        $exchange = new CurrencyExchange();
        $exchange->provider_id = $provider->id;
        $exchange->currency_id = $currency->id;
        $exchange->rate = (float)Yii::$app->request->post('rate');

        if (!$exchange->save()) {
            return $this->sendError(409, $exchange->getErrors());
        }

        return $this->sendResponse(200, $exchange);
    }

    /**
     *
     * @SWG\Get(
     *        path="/providers/{provider_id}/exchange/list",
     *        tags={"Currency"},
     *        summary="List of exchange rate currency for provider",
     *     security={
     *     {"client": {}},},
     *      @SWG\Parameter(
     *            name="provider_id",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="ID of provider",
     *        ),
     *   @SWG\Response(
     *      response=200,
     *      description="Currency",
     *      schema=@SWG\Schema(ref="#/definitions/CurrencyExchange")
     *   ),
     *)
     * @param $provider_id
     * @return mixed
     */
    public function actionList($provider_id)
    {
        $token_id = Yii::$app->user->identity->getId();

        /** @var Provider $provider */
        $provider = Provider::find()->where(['token_id' => $token_id, 'id' => $provider_id])->one();

        if ($provider === null) {
            return $this->sendError(400, 'Provider does not exist.');
        }

        $currencies =  CurrencyExchange::find()->where(['provider_id' => $provider->id])->all();

        $list = [];
        if (!empty($currencies)) {
            foreach ($currencies as $currency) {
                $decor = new \app\decorators\models\CurrencyExchange($currency);
                $list[] = $decor->toArray();
            }
        }
        return $this->sendResponse(200, $list);
    }

    /**
     *
     * @SWG\Delete(
     *        path="/providers/{provider_id}/exchange/{currency_code}",
     *        tags={"Currency"},
     *        summary="Delete exchange rate currency for provider",
     *     security={
     *     {"client": {}},},
     *      @SWG\Parameter(
     *            name="provider_id",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="ID of provider",
     *        ),
     *      @SWG\Parameter(
     *            name="currency_code",
     *            in="path",
     *            required=true,
     *            type="string",
     *            description="International code of currency ISO 4217 (3 symbols)",
     *        ),
     *   @SWG\Response(
     *      response=204,
     *      description="Currency",
     *   ),
     *)
     * @param $provider_id
     * @param $currency_code
     * @return mixed
     */
    public function actionDelete($provider_id, $currency_code)
    {
        $currency = Currency::findOrFail(['code' => $currency_code]);
        $exchange = CurrencyExchange::findOrFail(['provider_id' => $provider_id, 'currency_id' => $currency->id]);
        $exchange->delete();
        return $this->sendResponse(200);

    }

    /**
     * @SWG\Put(path="/providers/{provider_id}/exchange/{currency_code}",
     *     tags={"Currency"},
     *     summary="Update exchange rate currency for provider",
     *     security={
     *     {"client": {}},},
     *      @SWG\Parameter(
     *            name="provider_id",
     *            in="path",
     *            required=true,
     *            type="integer",
     *            description="ID of provider",
     *        ),
     *      @SWG\Parameter(
     *            name="currency_code",
     *            in="path",
     *            required=true,
     *            type="string",
     *            description="International code of currency ISO 4217 (3 symbols)",
     *        ),
     *      @SWG\Parameter(
     *            name="rate",
     *            in="formData",
     *            required=true,
     *            type="number",
     *            description="Exchange rate currency to base one.",
     *        ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "",
     *     ),
     * )
     * @param $provider_id
     * @param $currency_code
     * @return mixed
     */
    public function actionUpdate($provider_id, $currency_code)
    {
        $rate = Yii::$app->request->post('rate');
        $currency = Currency::findOrFail(['code' => $currency_code]);
        $exchange = CurrencyExchange::findOrFail(['provider_id' => $provider_id, 'currency_id' => $currency->id]);
        $exchange->rate = (float)$rate;
        if (!$exchange->save()) {
            return $this->sendError(400, $exchange->errors);
        }
        return $this->sendResponse(200, $exchange);
    }

    /**
     * @SWG\Get(
     *        path="/exchanger/list",
     *        tags={"Currency"},
     *        summary="Gets all supported providers of exchange services",
     *     security={
     *     {"client": {}},},
     *   @SWG\Response(
     *      response=200,
     *      description="Exchange providers",
     *   ),
     * )
     */
    public function actionExchanger()
    {
        $providers = \Yii::$app->exchanger->getExchangeProviders();
        return $this->sendResponse(200, $providers);
    }

}