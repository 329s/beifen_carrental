<?php

use common\helpers\CMyHtml;

$urlInfo = ['vehicle/maintenance_config_title_list'];

$columnFields = ['id', 'name', 'belong_brand', 'status', 'edit_user_id', 'created_at', 'updated_at', 'detailed_info'];

$objModel = new \common\models\Pro_vehicle_maintenance_config();
$objForm = new \backend\models\Form_pro_vehicle_maintenance_config();

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
    'saveUrl' => \yii\helpers\Url::to(['vehicle/maintenance_config_xadd']),
    'updateUrl' => \yii\helpers\Url::to(['vehicle/maintenance_config_xedit']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/maintenance_config_delete']),
    'detailUrl' => \yii\helpers\Url::to(['vehicle/maintenance_config_index', 'hide_info'=>'true']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_ACCEPT, null, null),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REJECT, null, null),
];

$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Maintenance config'), // $title
    $objModel,    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '962px', '472px',     // $width, $height
    ['id'=>$dgId],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = $containerPart;

$configDefaultStatus = \common\models\Pro_vehicle_maintenance_config::STATUS_ENABLED;

$arrScripts = [];
$arrScripts[] = <<<EOD
setTimeout(function() {
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'queryParams', {{$yiiCsrfKey}:'{$yiiCsrfToken}'});
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'modelName', '{$objForm->formName()}');
    easyuiFuncDatagridSetOptionsCustomValue('#{$dgId}', 'defaultValues', {status:{$configDefaultStatus}});
}, 110);
EOD;

$htmlArray[] = yii\helpers\Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);
