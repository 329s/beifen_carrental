<?php
namespace backend\controllers;

/* 
 * Payment api controller
 */
class PaymentController extends \common\helpers\BasicController
{
    public $enableCsrfValidation = false;
    
    public function getView() {
        $prefix = \backend\components\AdminHtmlService::getViewPrefix();
        if ($prefix) {
            return \Yii::createObject([
                'class' => \common\components\ViewExtend::className(),
                'prefix' => $prefix,
            ]);
        }
        return parent::getView();
    }
    
    /**
     * weixin peyment callback
     */
    public function actionWxpay() 
    {
        $body = file_get_contents('php://input');
        $payment = \common\components\PaymentWxpay::create([
            'data' => $body,
        ]);
        
        $arrData = $payment->getAttributes();
        $arrResult = ['code'=>'FAIL', 'msg'=>'Unknown'];
        
        do
        {
            
            $wxAppId = \Yii::$app->params['payment.weixin.appid'];
            if (!$payment->validate()) {
                $arrResult['msg'] = 'Verify signment failed.';
                \Yii::error("wxpay payment notified while validate the input with error:".$payment->getErrorMessage(), 'order');
                break;
            }
            
            // check payment
            
            if ($arrData['appid'] != $wxAppId) {
                $arrResult['msg'] = "Invalid appid";
                break;
            }
            
            if ($arrData['return_code'] != 'SUCCESS') {
                $arrResult['msg'] = $arrData['return_msg'];
                break;
            }
            
            $arrExtraInfo = [
                'userid' => $arrData['attach']
            ];
            
            $arrPurchaseData = [];
            $channelType = \common\models\Pro_purchase_order::CHANNEL_TYPE_WXPAY;
            $arrPurchaseData['channel_trade_no'] = $arrData['transaction_id'];
            $arrPurchaseData['purchased_at'] = strtotime($arrData['time_end']);
            $arrPurchaseData['extra_info'] = json_encode($arrExtraInfo);
            $arrPurchaseData['serial'] = $arrData['out_trade_no'];
            $arrPurchaseData['pay_source'] = \common\models\Pro_vehicle_order::PAY_TYPE_WEIXIN;
            
            if ($arrData['result_code'] == 'SUCCESS') {
                $arrPurchaseData['status'] = \common\models\Pro_purchase_order::STATUS_SUCCEES;
                $arrPurchaseData['purchase_code'] = $arrPurchaseData['status'];
                $arrPurchaseData['purchase_msg'] = 'SUCCESS';
            }
            else {
                $arrPurchaseData['status'] = \common\models\Pro_purchase_order::STATUS_FAILED;
                $arrPurchaseData['purchase_code'] = $arrPurchaseData['status'];
                $arrPurchaseData['purchase_msg'] = $arrData['err_code_des'];
            }
            $arrPurchaseData['amount'] = intval($arrData['total_fee']) / 100;
            if (isset($arrData['cash_fee'])) {
                $arrPurchaseData['receipt_amount'] = intval($arrData['cash_fee']) / 100;
            }
            else {
                $arrPurchaseData['receipt_amount'] = $arrPurchaseData['amount'];
            }
            
            $arrProcessResult = \common\components\PurchaseService::doPurchase($channelType, $arrPurchaseData);
            if ($arrProcessResult[0]) {
                $arrResult['code'] = 'SUCCESS';
            }
            $arrResult['msg'] = $arrProcessResult[1];
        }while(0);
        
        $payment->responseData($arrResult['code'], $arrResult['msg']);
        exit(0);
    }
    
    /**
     * alipay peyment callback
     */
    public function actionAlipay() 
    {
        $payment = \common\components\PaymentAlipay::create([
            'data' => \Yii::$app->request->post(),
            //'appKey' => \Yii::$app->params['payment.alipay.apppubkey'],
            'skipSignType' => true,
        ]);
        
        $arrData = $payment->getAttributes();
        $arrResult = ['code'=>'FAIL', 'msg'=>'Unknown'];
        
        do
        {
            if (!$payment->validate()) {
                $arrResult['msg'] = 'Verify signment failed.';
                \Yii::error("alipay payment notified while validate the input with error:".$payment->getErrorMessage(), 'order');
                break;
            }
            
            // check payment
            
            $appId = \Yii::$app->params['payment.alipay.appid'];
            if ($arrData['app_id'] != $appId) {
                $arrResult['msg'] = "Invalid appid";
                \Yii::error("alipay payment notified while appid:{$arrData['appid']} not equals to {$appId}", 'order');
                break;
            }
            
            $isTradeSuccess = true;
            if ($arrData['trade_status'] != 'TRADE_SUCCESS' && $arrData['trade_status'] != 'TRADE_FINISHED') {
                $arrResult['msg'] = "Trade status is not success";
                $isTradeSuccess = false;
                \Yii::error("alipay payment notified while trade_status:{$arrData['trade_status']} success", 'order');
                break;
            }
            
            $arrExtraData = [];
            if (isset($arrData['buyer_logon_id'])) {
                $arrExtraData['buyer_id'] = $arrData['buyer_logon_id'];
            }
            if (isset($arrData['seller_email'])) {
                $arrExtraData['seller_id'] = $arrData['seller_email'];
            }
            
            $arrPurchaseData = [];
            $channelType = \common\models\Pro_purchase_order::CHANNEL_TYPE_ALIPAY;
            $arrPurchaseData['channel_trade_no'] = $arrData['out_trade_no'];
            $arrPurchaseData['purchased_at'] = strtotime($arrData['gmt_payment']);
            $arrPurchaseData['extra_info'] = json_encode($arrExtraData);
            $arrPurchaseData['serial'] = $arrData['out_trade_no'];
            $arrPurchaseData['pay_source'] = \common\models\Pro_vehicle_order::PAY_TYPE_ALIPAY;
            
            if ($isTradeSuccess) {
                $arrPurchaseData['status'] = \common\models\Pro_purchase_order::STATUS_SUCCEES;
                $arrPurchaseData['purchase_code'] = 0;
                $arrPurchaseData['purchase_msg'] = $arrData['trade_status'];
            }
            else {
                $arrPurchaseData['status'] = \common\models\Pro_purchase_order::STATUS_FAILED;
                $arrPurchaseData['purchase_code'] = -1;
                $arrPurchaseData['purchase_msg'] = $arrData['trade_status'];
            }
            $arrPurchaseData['amount'] = floatval($arrData['total_amount']);
            if (isset($arrData['receipt_amount'])) {
                $arrPurchaseData['receipt_amount'] = floatval($arrData['receipt_amount']);
            }
            else {
                $arrPurchaseData['receipt_amount'] = $arrPurchaseData['amount'];
            }
            
            $arrProcessResult = \common\components\PurchaseService::doPurchase($channelType, $arrPurchaseData);
            if ($arrProcessResult[0]) {
                $arrResult['code'] = 'SUCCESS';
            }
            $arrResult['msg'] = $arrProcessResult[1];
            \Yii::info("alipay payment notified processed with code:{$arrResult['code']} and msg:{$arrResult['msg']}", 'order');
        }while(0);
        
        $payment->responseData($arrResult['code'], $arrResult['msg']);
        exit(0);
    }
    
    /**
     * alipay peyment callback
     */
    public function actionAlipay_gateway() 
    {
        $payment = \common\components\PaymentAlipay::create([
            'data' => \Yii::$app->request->post(),
        ]);
        
        $arrData = $payment->getAttributes();
        $arrResult = ['success'=>'false', 'biz_content'=>''];
        
        do
        {
            if (!$payment->validate()) {
                //$arrResult['msg'] = 'Verify signment failed.';
                \Yii::error("alipay payment notified while validate the input with error:".$payment->getErrorMessage(), 'order');
                break;
            }
            
            libxml_disable_entity_loader(true);
            $bizContent = '';
            if (isset($arrData['charset'])) {
                if (substr(trim($arrData['biz_content']), 0, 5) != '<?xml') {
                    $bizContent = '<?xml version="1.0" encoding="'.$arrData['charset'].'"?>';
                }
            }
            $bizContent .= $arrData['biz_content'];
            $xml = simplexml_load_string($bizContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            if ($xml) {
                $arrTmp = json_decode(json_encode($xml), true);
                $arrData = [];
                foreach ($arrTmp as $k => $v) {
                    $arrData[$k] = $v;
                }
            }
            
            $appId = \Yii::$app->params['payment.alipay.appid'];
            if ($arrData['AppId'] != $appId) {
                //$arrResult['msg'] = "Invalid appid";
                break;
            }
            
            $pubKey0 = \Yii::$app->params['payment.alipay.apppubkey'];
            $pubKey0 = str_replace('-----BEGIN PUBLIC KEY-----', '', $pubKey0);
            $pubKey0 = str_replace('-----END PUBLIC KEY-----', '', $pubKey0);
            $pubKey = str_replace(["\n", "\r"], '', $pubKey0);
            
            $arrResult['biz_content'] = $pubKey;
            $arrResult['success'] = 'true';
        }while(0);
        
        $arrResultData = [];
        ksort($arrResult);
        foreach ($arrResult as $k => $v) {
            $arrResultData[] = "<{$k}>{$v}</$k>";
        }
        
        $signString = implode("", $arrResultData);
        \Yii::error("alipay gateway sign string: {$signString}", 'order');
        $sign = $payment->rsaSign($signString);
        
        $arrResponseContent = [
            'response' => implode("", $arrResultData),
            'sign' => $sign,
            'sign_type' => 'RSA',
        ];
        
        $arrResponseXml = [];
        $arrResponseXml[] = '<?xml version="1.0" encoding="GBK"?>';
        $arrResponseXml[] = '<alipay>';
        foreach ($arrResponseContent as $k => $v) {
            $arrResponseXml[] = "<{$k}>{$v}</$k>";
        }
        $arrResponseXml[] = '</alipay>';
        
        $responseText = implode("", $arrResponseXml);
        echo $responseText;
        
        \Yii::error("alipay gateway response:{$responseText}", 'order');
        
        exit(0);
    }





}
