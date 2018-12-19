<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['vehicle/inner_usevehicle_list']),
    'saveUrl' => \yii\helpers\Url::to(['vehicle/inner_usevehicle_add']),
    'updateUrl' => \yii\helpers\Url::to(['vehicle/inner_usevehicle_edit']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/inner_usevehicle_delete']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', []),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, '', ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, '', ''),
];

$objModel = new backend\models\Pro_inner_applying();
$objForm = new backend\models\Form_pro_inner_applying();

$funcId = CMyHtml::genID(); 
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Vehicle inner using management'), // $title
    $objModel,    // $model
    ['vehicle_id', 'office_id', 'user_name', 'start_time', 'vehicle_outbound_mileage', 'end_time', 'vehicle_inbound_mileage', 'content'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = $containerPart;

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$defaultOfficeId = \backend\components\AdminModule::getAdminActualOfficeId();

$arrScripts = [];
$arrScripts[] = <<<EOD
setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {{$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {office_id:{$defaultOfficeId}});
}, 50);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
