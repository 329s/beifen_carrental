<?php
namespace frontend\controllers;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PaymentController extends \common\helpers\AuthorityController
{
    private $actionKey = \frontend\components\ApiModule::KEY;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function($rule, $action) {
                            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_NOT_LOGIN, 'desc' => \Yii::t('locale', 'Login required.')]);
                        },
                    ],
                ],
            ],
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'order' => ['post'],
                    'order_preview' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        $arrParams = [];
        $sign = '';
        $params = [];
        if ($action->id == '_') {
            $params = \Yii::$app->request->get();
        }
        else {
            $params = \Yii::$app->request->post();
        }
        foreach ($params as $k => $v) {
            if ($k == 'sign') {
                $sign = $v;
            }
            else {
                $arrParams[$k] = $v;
            }
        }

        $arrVerifys = [];
        ksort($arrParams);
        foreach ($arrParams as $k => $v) {
            $k = strval($k);
            if (is_array($v)) {
                $v = implode("|", $v);
            }
            else {
                $v = strval($v);
            }
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = $this->actionKey;

        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }

        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;
    }
    
    public function actionWxpay() {
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->post('order_id'));
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do
        {
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $desc = \Yii::$app->request->post('desc');
            if (empty($desc)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_DESC_SHOULD_NOT_EMPTY;
                $arrData['desc'] = \Yii::t('locale', 'Order description should not be empty');
                break;
            }
            
            if ($objOrder->total_amount <= $objOrder->paid_amount) {
                \Yii::warning("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while the order already paid.", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_ALREADY_PURCHASED;
                $arrData['desc'] = \Yii::t('locale', 'Order already purchased');
                break;
            }
            
            $payment0 = \common\components\PaymentWxpay::create([
            ]);
            
            $price = $objOrder->total_amount - $objOrder->paid_amount;
            $price *= 100;
            
            $arrRequestData = [
                'appid' => \Yii::$app->params['payment.weixin.appid'],
                'mch_id' => \Yii::$app->params['payment.weixin.mch_id'],
                'device_info' => 'WEB',
                'nonce_str' => \common\helpers\Utils::randomStr(32),
                'body' => $desc,
                'attach' => $objOrder->user_id,
                'out_trade_no' => $objOrder->serial,
                'fee_type' => 'CNY',
                'total_fee' => $price,
                'spbill_create_ip' => \common\helpers\Utils::getIP(),
                'notify_url' => \Yii::$app->params['app.host'].\common\helpers\Utils::getRootUrl().'app/carrental/admin/payment/wxpay',
                'trade_type' => 'APP',
            ];
            $payment0->setAttributes($arrRequestData);
            $arrRequestData['sign'] = $payment0->generateSignment();
            
            $strRequestData = $payment0->formatData($arrRequestData);
            
            $apiUrl = \Yii::$app->params['payment.weixin.pay_unifiedorder'];
            
            \Yii::trace("query wxpay api:{$apiUrl} data:{$strRequestData} appkey:{$payment0->appKey}", 'order');
            
            $arrResponse = \common\helpers\Utils::queryUrlPost($apiUrl, 
                $strRequestData, 
                30, 
                array(
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                    'Accept: application/xml',
            ));
            
            if ($arrResponse[0] != 200) {
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} failed with http_code:{$arrResponse[0]} message:{$arrResponse[1]}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = $arrResponse[1];
                break;
            }
            
            $payment = \common\components\PaymentWxpay::create([
                'data' => $arrResponse[1],
            ]);
            
            if ($payment->hasError()) {
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} got response:{$arrResponse[1]} while validate failed:{$payment->getErrorMessage()}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = $payment->getErrorMessage();
                break;
            }
            
            if ($payment->getAttribute('return_code') != 'SUCCESS' || $payment->getAttribute('result_code') != 'SUCCESS') {
                $errMsg = $payment->getAttribute('return_msg');
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} got response while not success:{$payment->getAttribute('return_msg')} return_code:{$payment->getAttribute('return_code')} result_code:{$payment->getAttribute('result_code')}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = empty($errMsg) ? \Yii::t('locale', 'Request purchase failed') : $errMsg;
                break;
            }
            
            $arrResult['trade_type'] = $payment->getAttribute('trade_type');
            $arrResult['prepay_id'] = $payment->getAttribute('prepay_id');
            
            $arrData2 = [
                'appid' => \Yii::$app->params['payment.weixin.appid'],
                'partnerid' => \Yii::$app->params['payment.weixin.mch_id'],
                'prepayid' => $arrResult['prepay_id'],
                'package' => 'Sign=WXPay',
                'noncestr' => \common\helpers\Utils::randomStr(32),
                'timestamp' => time(),
            ];
            $payment1 = \common\components\PaymentWxpay::create([
            ]);
            $payment1->setAttributes($arrData2);
            $arrData['sign'] = $payment1->generateSignment();
            foreach ($arrData2 as $k => $v) {
                $arrData[$k] = $v;
            }
            
        }while (0);

        echo json_encode($arrData);
        
    }
    
    public function actionAlipay() {
        $arrResult = \frontend\components\OrderService::getOrderBySerial(\Yii::$app->request->post('order_id'));
        // $arrResult = \frontend\components\OrderService::getOrderBySerial('12313');
        $arrData = $arrResult[0];
        $objOrder = $arrResult[1];
        do
        {
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $desc = \Yii::$app->request->post('desc');
            if (empty($desc)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_DESC_SHOULD_NOT_EMPTY;
                $arrData['desc'] = \Yii::t('locale', 'Order description should not be empty');
                break;
            }
            
            if ($objOrder->total_amount <= $objOrder->paid_amount) {
                \Yii::warning("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while the order already paid.", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_ALREADY_PURCHASED;
                $arrData['desc'] = \Yii::t('locale', 'Order already purchased');
                break;
            }
            
            $payment0 = \common\components\PaymentAlipay::create([
            ]);
            
            $price = $objOrder->total_amount - $objOrder->paid_amount;
            
            $arrBizContent = [
                'out_trade_no' => $objOrder->serial,
                'subject' => $desc,
                'timeout_express' => '30m',
                'total_amount' => $price,
                'product_code' => 'QUICK_MSECURITY_PAY',
            ];
            
            $arrRequestData = [
                'appid' => \Yii::$app->params['payment.alipay.appid'],
                'method' => 'alipay.trade.app.pay',
                'format' => 'JSON',
                'charset' => 'utf-8',
                'sign_type' => 'RSA',
                'timestamp' => date('Y-m-d H:i:s'),
                'version' => '1.0',
                'notify_url' => \Yii::$app->params['app.host'].\common\helpers\Utils::getRootUrl().'app/carrental/admin/payment/alipay',
                'biz_content' => json_encode($arrBizContent),
            ];
            $payment0->setAttributes($arrRequestData);
            $arrRequestData['sign'] = $payment0->generateSignment();
            
            $strRequestData = $payment0->formatData($arrRequestData);
            
            $apiUrl = \Yii::$app->params['payment.weixin.pay_unifiedorder'];
            
            \Yii::trace("query wxpay api:{$apiUrl} data:{$strRequestData} appkey:{$payment0->appKey}", 'order');
            
            $arrResponse = \common\helpers\Utils::queryUrlPost($apiUrl, 
                $strRequestData, 
                30, 
                array(
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
                    'Accept: application/xml',
            ));
            
            if ($arrResponse[0] != 200) {
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} failed with http_code:{$arrResponse[0]} message:{$arrResponse[1]}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = $arrResponse[1];
                break;
            }
            
            $payment = \common\components\PaymentWxpay::create([
                'data' => $arrResponse[1],
            ]);
            
            if ($payment->hasError()) {
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} got response:{$arrResponse[1]} while validate failed:{$payment->getErrorMessage()}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = $payment->getErrorMessage();
                break;
            }
            
            if ($payment->getAttribute('return_code') != 'SUCCESS' || $payment->getAttribute('result_code') != 'SUCCESS') {
                $errMsg = $payment->getAttribute('return_msg');
                \Yii::error("user:{$objOrder->user_id} purchase order:{$objOrder->serial} while call weixin unifiedorder api:{$apiUrl} got response while not success:{$payment->getAttribute('return_msg')} return_code:{$payment->getAttribute('return_code')} result_code:{$payment->getAttribute('result_code')}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = empty($errMsg) ? \Yii::t('locale', 'Request purchase failed') : $errMsg;
                break;
            }
            
            $arrResult['trade_type'] = $payment->getAttribute('trade_type');
            $arrResult['prepay_id'] = $payment->getAttribute('prepay_id');
            
            $arrData2 = [
                'appid' => \Yii::$app->params['payment.weixin.appid'],
                'partnerid' => \Yii::$app->params['payment.weixin.mch_id'],
                'prepayid' => $arrResult['prepay_id'],
                'package' => 'Sign=WXPay',
                'noncestr' => \common\helpers\Utils::randomStr(32),
                'timestamp' => time(),
            ];
            $payment1 = \common\components\PaymentWxpay::create([
            ]);
            $payment1->setAttributes($arrData2);
            $arrData['sign'] = $payment1->generateSignment();
            foreach ($arrData2 as $k => $v) {
                $arrData[$k] = $v;
            }
            
        }while (0);

        echo json_encode($arrData);
        
    }
    
}
