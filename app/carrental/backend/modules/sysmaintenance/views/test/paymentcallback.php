<?php

use common\helpers\CMyHtml;

$autoId = CMyHtml::genID();

$htmlArray = [];
$arrScripts = [];

$wxAppId = Yii::$app->params['payment.weixin.appid'];
$arrScripts[] = <<<EOD
var testWxpayData{$autoId} = '\
<xml>\
  <appid><![CDATA[{$wxAppId}]]></appid>\
  <attach><![CDATA[10001]]></attach>\
  <bank_type><![CDATA[CFT]]></bank_type>\
  <fee_type><![CDATA[CNY]]></fee_type>\
  <is_subscribe><![CDATA[Y]]></is_subscribe>\
  <mch_id><![CDATA[10000100]]></mch_id>\
  <nonce_str><![CDATA[5d2b6c2a8db53831f7eda20af46e531c]]></nonce_str>\
  <openid><![CDATA[oUpF8uMEb4qRXf22hE3X68TekukE]]></openid>\
  <out_trade_no><![CDATA[130005000019]]></out_trade_no>\
  <result_code><![CDATA[SUCCESS]]></result_code>\
  <return_code><![CDATA[SUCCESS]]></return_code>\
  <sub_mch_id><![CDATA[10000100]]></sub_mch_id>\
  <time_end><![CDATA[20160930131540]]></time_end>\
  <total_fee>1</total_fee>\
  <cash_fee>1</cash_fee>\
  <trade_type><![CDATA[JSAPI]]></trade_type>\
  <transaction_id><![CDATA[1004400740201409030005092169]]></transaction_id>\
  <debug>1</debug>\
  <sign><![CDATA[20C42DF9A73ED2D1D9061D3D5F645685]]></sign>\
</xml>';
EOD;
$wxTestUrl = \yii\helpers\Url::to(['payment/wxpay']);

$htmlArray[] = CMyHtml::beginPanel('测试微信支付回调', ['style'=>"width:160px;height:66px"]);
$htmlArray[] = CMyHtml::linkButton('测试', ['onclick'=>"$.ajax({type:'POST', url:'{$wxTestUrl}', data:testWxpayData{$autoId}, dataType:'xml', success:funcOutputSuccessDataXml{$autoId}, error:funcOutputErrorData{$autoId} });"]);
$htmlArray[] = CMyHtml::endPanel();

$outputId = CMyHtml::getIDPrefix().'output_'.$autoId;
$htmlArray[] = CMyHtml::beginPanel('测试结果', ['style'=>"width:100%;height:200px"]);
$htmlArray[] = CMyHtml::tag('pre', '', ['id'=>$outputId, 'style'=>'width:100%;height:100%']);
$htmlArray[] = CMyHtml::endPanel();

$arrScripts[] = <<<EOD
function funcOutputSuccessDataXml{$autoId}(data) {
    var innerHtml;
    try {
        var serializer = new XMLSerializer();
        innerHtml = serializer.serializeToString(data);
    } catch (e) {
        innerHtml = data.xml;
    }
    $('#{$outputId}').html(innerHtml);
}
function funcOutputSuccessData{$autoId}(data) {
    $('#{$outputId}').html(data);
}
function funcOutputErrorData{$autoId}(e) {
    var errText = '[CODE:' + e.status + '] [error:' + e.statusText + ']\\n' + e.responseText;
    $('#{$outputId}').html(errText);
}
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));
    
echo implode("\n", $htmlArray);
