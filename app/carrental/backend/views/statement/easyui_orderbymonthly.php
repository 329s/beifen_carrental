<?php
use common\helpers\CMyHtml;

$htmlArray = [];
$arrScripts = [];

$idPrefix = CMyHtml::getIDPrefix();
$autoId = CMyHtml::genID();
$isAdministrator = \backend\components\AdminModule::isAuthorizedHeadOffice();

$reloadUrl = \yii\helpers\Url::to(['statement/orderbymonthly', 'status'=>$status,'pay_type'=>$pay_type]);

$dataProvider = new yii\data\ArrayDataProvider([
    'allModels' => $models,
    'modelClass' => '\common\models\Pro_vehicle_order',
    'pagination' => [
        'pageSize' => count($models),
    ],
]);

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
// sjj
$headerHtmlArray[] =  \yii\bootstrap\Html::label(Yii::t('locale', '开始日期').':'). \yii\helpers\Html::input('text', 'date_start', $date_start, [
    'class'=>'form-control input-sm form_datetime_month',
    'readonly',
    'id'=>"{$idPrefix}date_start_month{$autoId}",
    'onchange'=>"dailyQueryParams{$autoId}.date_start = $(this).val(); setTimeout(function() { funcDailyReloadPage{$autoId}(); }, 100);",
]);
// sjj
$headerHtmlArray[] =  \yii\bootstrap\Html::label(Yii::t('locale', 'Date').':'). \yii\helpers\Html::input('text', 'date', $date, [
    'class'=>'form-control input-sm form_datetime_month',
    'readonly',
    'id'=>"{$idPrefix}date_month{$autoId}",
    'onchange'=>"dailyQueryParams{$autoId}.date = $(this).val(); setTimeout(function() { funcDailyReloadPage{$autoId}(); }, 100);",
]);
$arrScripts[] = "$('#{$idPrefix}date_month{$autoId}').datetimepicker({language:'zh-CN',autoclose:true,todayBtn:true,format:'yyyy-mm-dd',startView:'2',minView:'2',maxView:'decade'});";
$arrScripts[] = "$('#{$idPrefix}date_start_month{$autoId}').datetimepicker({language:'zh-CN',autoclose:true,todayBtn:true,format:'yyyy-mm-dd',startView:'2',minView:'2',maxView:'decade'});";
// $arrScripts[] = "$('#{$idPrefix}date_month{$autoId}').datetimepicker({language:'zh-CN',autoclose:true,todayBtn:true,format:'yyyy-mm-dd',startView:'year',minView:'year',maxView:'decade'});";

// export button
if (\Yii::$app->user->can('statement/orderbymonthly-export')) {
    $exportUrl = \yii\helpers\Url::to(['statement/orderbymonthly-export', 'export'=>'excel', 'status'=>$status,'pay_type'=>$pay_type, 'date'=>$date, 'date_start'=>$date_start, 'office_id'=>$belongOfficeId]);
    $headerHtmlArray[] = CMyHtml::tag('a', '导出', ['class'=>'btn btn-info', 'href'=>$exportUrl]);
}
//$headerHtmlArray[] = \yii\helpers\Html::endTag('div');

$arrScripts[] = "var dailyQueryParams{$autoId} = {office_id:'{$belongOfficeId}', date_start:'{$date_start}', date:'{$date}'};\n".
    "    var date_start = '{$date_start}';\n".
    "    var date = '{$date}';\n".
    "    if(date<date_start){\n".
    "       alert('开始时间不能大于结束时间');\n".
    "    }\n".
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
