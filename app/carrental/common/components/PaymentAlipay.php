<?php

namespace common\components;

/* 
 * Alipay payment component
 */
class PaymentAlipay extends \common\components\PaymentComponent
{
    public $appKey;
    public $priKey;
    
    public $skipSignType = false;
    
    public function init() {
        parent::init();
        
        if (empty($this->appKey) && isset(\Yii::$app->params['payment.alipay.alipaypubkey'])) {
            $this->appKey = \Yii::$app->params['payment.alipay.alipaypubkey'];
        }
        if (empty($this->priKey) && isset(\Yii::$app->params['payment.alipay.appprikey'])) {
            $this->priKey = \Yii::$app->params['payment.alipay.appprikey'];
        }
        $this->dataFormat = static::DATA_FORMAT_POST;
    }
    
    public function validate() {
        $arrData = $this->getAttributes();
        $arrVerifyStrings = [];
        $sign = '';
        $signType = '';
        ksort($arrData);
        foreach ($arrData as $k => $v) {
            if ($k == 'sign'){
                $sign = $v;
            }
            elseif ($this->skipSignType && $k == 'sign_type') {
                $signType = $v;
            }
            else {
                $arrVerifyStrings[] = "{$k}={$v}";
            }
        }
        $verifyString = implode("&", $arrVerifyStrings);
        $result = $this->rsaVerify($verifyString, $sign);
        if ($result) {
            return true;
        }
        
        $this->addError(\Yii::t('locale', 'Verify package signment failed')." {$verifyString}");
        return false;
    }
    
    public function generateSignment($signString = null) {
        if (empty($signString)) {
            $arrData = $this->getAttributes();
            $arrVerifyStrings = [];
            //$sign = '';
            ksort($arrData);
            foreach ($arrData as $k => $v) {
                if ($k == 'sign' || ($this->skipSignType && $k == 'sign_type')) {
                    //$sign = $v;
                }
                else {
                    $arrVerifyStrings[] = "{$k}={$v}";
                }
            }
            $signString = implode("&", $arrVerifyStrings);
        }
        
        \Yii::trace("alipayment generate signment verifyint string:[{$signString}]", 'order');
        
        return $this->rsaSign($signString);
    }
    
    public function rsaSign($signString) {
        $priKey = openssl_get_privatekey($this->priKey);
        $sign = '';
        openssl_sign($signString, $sign, $priKey);
        openssl_free_key($priKey);
        
        return base64_encode($sign);
    }
    
    public function rsaVerify($data, $sign) {
        $pubKey = openssl_get_publickey($this->appKey);
        $result=(bool)openssl_verify($data, base64_decode($sign), $pubKey);
        openssl_free_key($pubKey);
        
        if ($result) {
            return true;
        }
        return false;
    }
    
    public function responseData($code, $msg) {
        if ($code == 'SUCCESS') {
            echo 'success';
        }
        echo 'fail';
    }
    
}
