<?php

namespace common\components;

/* 
 * Weixin payment component
 */
class PaymentWxpay extends \common\components\PaymentComponent
{
    
    public function init() {
        parent::init();
        
        if (empty($this->appKey) && isset(\Yii::$app->params['payment.weixin.appkey'])) {
            $this->appKey = \Yii::$app->params['payment.weixin.appkey'];
        }
        $this->dataFormat = static::DATA_FORMAT_XML;
    }
    
    public function validate() {
        $sign = $this->hasAttribute('sign') ? $this->getAttribute('sign') : '';
        $mySign = $this->generateSignment();
        
        if ($mySign != $sign) {
            $this->addError(\Yii::t('locale', 'Verify package signment failed')." {$mySign}");
            return false;
        }
        
        return true;
    }
    
    public function generateSignment() {
        $arrData = $this->getAttributes();
        $arrVerifyStrings = [];
        //$sign = '';
        ksort($arrData);
        foreach ($arrData as $k => $v) {
            if ($k == 'sign') {
                //$sign = $v;
            }
            elseif ($v !== '') {
                $arrVerifyStrings[] = "{$k}={$v}";
            }
        }
        $arrVerifyStrings[] = "key={$this->appKey}";
        $verifyString = implode("&", $arrVerifyStrings);
        
        \Yii::trace("wxpayment generate signment verifyint string:[{$verifyString}]", 'order');
        
        $mySign = strtoupper(md5($verifyString));
        return $mySign;
    }
    
    public function responseData($code, $msg) {
        $arrData = [
            'return_code' => $code,
            'return_msg' => $msg,
        ];
        
        echo $this->formatData($arrData);
    }
    
    public function formatData($arrData) {
        $arrXmls = ['<xml>'];
        foreach ($arrData as $k => $v) {
            if (is_numeric($v)) {
                $arrXmls[] = "  <{$k}>{$v}</{$k}>";
            }
            else {
                $arrXmls[] = "  <{$k}><![CDATA[{$v}]]></{$k}>";
            }
        }
        $arrXmls[] = '</xml>';
        
        return implode("\n", $arrXmls);
    }
    
}
