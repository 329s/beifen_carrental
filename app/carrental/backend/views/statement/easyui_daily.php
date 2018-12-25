<?php
use common\helpers\CMyHtml;

$htmlArray = [];
$arrScripts = [];

$autoId = CMyHtml::genID();
$isAdministrator = \backend\components\AdminModule::isAuthorizedHeadOffice();

$reloadUrl = \yii\helpers\Url::to(['statement/daily', '_'=>time()]);

$dataProvider = new yii\data\ArrayDataProvider([
    'allModels' => $arrDailyDataModels,
    'modelClass' => '\backend\models\Sts_vehicle_order_data',
    'pagination' => [
        'pageSize' => count($arrDailyDataModels),
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
$headerHtmlArray[] = \yii\bootstrap\Html::label(Yii::t('locale', 'Time').':').\common\helpers\CEasyUI::inputField(CMyHtml::INPUT_DATEBOX, 'time', $date, [], [
    'data-options' =>"editable:false,\n".
        "onChange: function(newValue,oldValue) {\n".
        "    dailyQueryParams{$autoId}.date = newValue;\n".
        "    setTimeout(function() { funcDailyReloadPage{$autoId}(); }, 100);\n".
        "}"
], '');

// export button
if (\Yii::$app->user->can('statement/daily-export')) {
    $exportUrl = \yii\helpers\Url::to(['statement/daily-export', 'export'=>'excel', 'date'=>$date, 'office_id'=>$belongOfficeId]);
    $headerHtmlArray[] = CMyHtml::tag('a', '导出', ['class'=>'btn btn-info', 'href'=>$exportUrl]);
}

//$headerHtmlArray[] = \yii\helpers\Html::endTag('div');

$arrScripts[] = "var dailyQueryParams{$autoId} = {office_id:'{$belongOfficeId}', date:'{$date}'};\n".
    "function funcDailyReloadPage{$autoId}() {\n".
    "    var url = '{$reloadUrl}';\n".
    "    for (var k in dailyQueryParams{$autoId}) {\n".
    "        url += '&'+k+'='+encodeURI(dailyQueryParams{$autoId}[k]);\n".
    "    }\n".
    "    easyuiFuncNavTabReloadCurTab(url);\n".
    "}";

$columns = [];
foreach ($dailyDataColumns as $col) {
    $col = is_array($col) ? $col : ['attribute'=>$col];
    if (!isset($col['contentOptions'])) {
        if (substr($col['attribute'], -6, 6) == 'remark') {
        //if ($col['attribute'] == 'remark') {
            $col['headerOptions'] = ['style'=>'width:200px;'];
            $col['contentOptions'] = ['style'=>'word-break:keep-all; white-space:nowrap; width:200px; text-overflow:ellipsis;overflow:hidden;'];
        }
        else {
            $col['contentOptions'] = ['style'=>'word-break:keep-all; word-wrap:keep-all; white-space:nowrap;'];
        }
    }
    $columns[] = $col;
}
$bodyHtml = \yii\grid\GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => $columns,
]);

$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel(implode("\n", $headerHtmlArray), [
    'body' => $bodyHtml,
]);

$htmlArray[] = \common\helpers\BootstrapHtml::endPanel();

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
