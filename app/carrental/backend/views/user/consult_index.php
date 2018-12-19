<?php

use common\helpers\CMyHtml;

$objForm = new \backend\models\Form_pro_user_consult();

$urlInfo = ['user/consult_list'];

if (isset($status)) {
    $urlInfo['status'] = $status;
}
else {
    $status = 0; 
}

$columnFields = ['id', 'time', 'office_id', 'customer_name', 'customer_phone', 
    'content', 'price', 'inputer_name', 'status'];

$objModel = new \backend\models\Pro_user_consult();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['user/consult_add']),
    'updateUrl' => \yii\helpers\Url::to(['user/consult_edit']),
    'deleteUrl' => \yii\helpers\Url::to(['user/consult_delete']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', ''),
];

$funcId = CMyHtml::genID();
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$htmlArray = [];

$htmlArray[] = CMyHtml::datagrid(Yii::t('carrental', 'Customer consult records'), // $title
    $objModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId, 'data-options' => ['onLoadSuccess'=>"funOnLoadDataSuccess{$funcId}"]],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$defaultTimeStr = date('Y-m-d H:i:s');

$defaultOfficeId = \backend\components\AdminModule::getAdminActualOfficeId();

$arrScripts = [];
$arrScripts[] = <<<EOD
function funOnLoadDataSuccess{$funcId}() {
    var curDate = new Date();
    var curTimeStr = $.custom.utils.humanTime(Math.ceil(curDate.getTime() / 1000));
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {time:curTimeStr});
}

setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {{$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {time:'{$defaultTimeStr}',office_id:{$defaultOfficeId}});
}, 50);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
