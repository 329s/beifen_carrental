<?php
use common\helpers\CMyHtml;

$htmlArray = [];
$arrScripts = [];

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$isAdministrator = \backend\components\AdminModule::isAuthorizedHeadOffice();

$reloadUrl = \yii\helpers\Url::to(['statement/orderbymonthly', 'status'=>$status]);

$dataProvider = new yii\data\ArrayDataProvider([
    'allModels' => $models,
    'modelClass' => '\common\models\Pro_vehicle_order',
    'pagination' => [
        'pageSize' => count($models),
    ],
]);

$arrScripts[] = "$(document).ready(function() {";

$headerHtmlArray = [];
//$headerHtmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'pull-right']);
if ($isAdministrator) {
    $headerHtmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Office').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_COMBOTREE, 'office_id', $belongOfficeId, \common\components\OfficeModule::getOfficeComboTreeData(), [
        'data-options' => "editable:false,\n".
        "onChange: function(newValue,oldValue) {\n".
        "    dailyQueryParams{$autoId}.office_id = newValue;\n".
        "    funcDailyReloadPage{$autoId}();\n".
        "}"
    ], Yii::t('locale', 'Office'));
}

$htmlArray[] = yii\helpers\Html::style(".form_datetime_month { display:inline; width:120px; }");
$headerHtmlArray[] =  \yii\bootstrap\Html::label(Yii::t('locale', 'Date').':'). \yii\helpers\Html::input('text', 'date', $date, [
    'class'=>'form-control input-sm form_datetime_month',
    'readonly',
    'id'=>"{$idPrefix}date_month{$autoId}",
    'onchange'=>"dailyQueryParams{$autoId}.date = $(this).val(); setTimeout(function() { funcDailyReloadPage{$autoId}(); }, 100);",
]);
$arrScripts[] = "$('#{$idPrefix}date_month{$autoId}').datetimepicker({language:'zh-CN',autoclose:true,todayBtn:true,format:'yyyy-mm',startView:'year',minView:'year',maxView:'decade'});";

// export button
if (\Yii::$app->user->can('statement/orderbymonthly-export')) {
    $exportUrl = \yii\helpers\Url::to(['statement/orderbymonthly-export', 'export'=>'excel', 'status'=>$status, 'date'=>$date, 'office_id'=>$belongOfficeId]);
    $headerHtmlArray[] = CMyHtml::tag('a', '导出', ['class'=>'btn btn-info', 'href'=>$exportUrl]);
}
//$headerHtmlArray[] = \yii\helpers\Html::endTag('div');
$arrScripts[] = "});";

$arrScripts[] = "var dailyQueryParams{$autoId} = {office_id:'{$belongOfficeId}', date:'{$date}'};\n".
    "function funcDailyReloadPage{$autoId}() {\n".
    "    var url = '{$reloadUrl}';\n".
    "    for (var k in dailyQueryParams{$autoId}) {\n".
    "        url += '&'+k+'='+encodeURI(dailyQueryParams{$autoId}[k]);\n".
    "    }\n".
    "    easyuiFuncNavTabReloadCurTab(url);\n".
    "}";

$viewcolumns = [];
foreach ($columns as $col) {
    $col = is_array($col) ? $col : ['attribute'=>$col];
    if (!isset($col['contentOptions'])) {
        if (substr($col['attribute'], -6, 6) == 'remark') {
            $col['headerOptions'] = ['style'=>'width:200px;'];
            $col['contentOptions'] = ['style'=>'word-break:keep-all; white-space:nowrap; width:200px; text-overflow:ellipsis;overflow:hidden;'];
        }
        else {
            $col['contentOptions'] = ['style'=>'word-break:keep-all; word-wrap:keep-all; white-space:nowrap;'];
        }
    }
    $viewcolumns[] = $col;
}
$bodyHtml = \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $viewcolumns,
]);

$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel(implode("\n", $headerHtmlArray), [
    'body' => $bodyHtml,
]);

$htmlArray[] = \common\helpers\BootstrapHtml::endPanel();

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
