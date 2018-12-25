<?php

$head = '';
$headClass = 'alert alert-default';
if ($shopId) {
    if ($result == 0) {
        $headClass = 'alert alert-info';
    }
    else {
        $headClass = 'alert alert-danger';
    }
    $head = \yii\helpers\Html::tag('span', "RESULT:", []).\yii\helpers\Html::tag('span', $result, []);
    $head .= \yii\helpers\Html::tag('span', '', []);
    $head .= \yii\helpers\Html::tag('span', $msg, []);
}

$columns = [
    'car_id', 'car_name', 'property_text', 'left', 'price_shop', 'price_online', 'price_3days', 'price_week', 'price_month','special_festivals_price_month'
];
$dataProvider = new \yii\data\ArrayDataProvider([
    'allModels' => $carList,
    'pagination' => [
        'pageSize' => count($carList),
    ],
]);
$bodyHtml = yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
]);

$htmlArray = [];

$htmlArray[] = \yii\helpers\Html::tag('div', $head, ['class'=>$headClass]);

$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();
$url = \yii\helpers\Url::to(['test/api_car_list', '_'=>time()]);

$headerHtmlArray = [];

$headerHtmlArray[] = \yii\bootstrap\Html::label(\Yii::t('locale', 'Office').':').\common\helpers\CEasyUI::inputField(\common\helpers\CMyHtml::INPUT_COMBOTREE, 'sid', $shopId, \common\components\OfficeModule::getOfficeComboTreeData(['showAll'=>true]), [
    'data-options' => "editable:false",
    'id' => "{$idPrefix}sid{$autoId}",
], Yii::t('locale', 'Office'));
$headerHtmlArray[] = \yii\bootstrap\Html::label('取车时间').\common\helpers\CEasyUI::inputField(\common\helpers\CMyHtml::INPUT_DATETIMEBOX, 'take_car_time', $takeCarTime, [], [
    'data-options' =>"editable:false",
    'id' => "{$idPrefix}take_car_time{$autoId}",
], '');
$headerHtmlArray[] = \yii\bootstrap\Html::label('还车时间').\common\helpers\CEasyUI::inputField(\common\helpers\CMyHtml::INPUT_DATETIMEBOX, 'return_car_time', $returnCarTime, [], [
    'data-options' =>"editable:false",
    'id' => "{$idPrefix}return_car_time{$autoId}",
], '');
$headerHtmlArray[] = \yii\bootstrap\Html::tag('a', '测试', ['class'=>'btn btn-info', 'onclick'=>"funcTestCarList{$autoId}()"]);

$arrScripts[] = <<<EOD
function funcTestCarList{$autoId}() {
    var url = '{$url}';
    var params = {
        sid:$('#{$idPrefix}sid{$autoId}').combotree('getValue'),
        take_car_time:$('#{$idPrefix}take_car_time{$autoId}').datetimebox('getValue'),
        return_car_time:$('#{$idPrefix}return_car_time{$autoId}').datetimebox('getValue')
    };
    for (var k in params) {
        url += '&'+k+'='+encodeURI(params[k]);
    }
    easyuiFuncNavTabReloadCurTab(url);
}
EOD;

$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel(implode("\n", $headerHtmlArray), [
    'body' => $bodyHtml,
]);
$htmlArray[] = \common\helpers\BootstrapHtml::endPanel();

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
