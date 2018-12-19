<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['vehicle/feeplan_list']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/deletefeeplan']),
];

$toolbarArray = [
    Yii::$app->user->can('vehicle/addfeeplan') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Add')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/addfeeplan'])]) : null,
    Yii::$app->user->can('vehicle/editfeeplan') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Edit')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/editfeeplan']), 'needSelect' => true]) : null,
    Yii::$app->user->can('vehicle/deletefeeplan') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Delete')]), '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model', Yii::t('locale', 'Vehicle model'), \common\components\VehicleModule::getVehicleModelNamesArray(['enableNone'=>true]), ['searchOnChange'=>true]),
];

$feeModel = new \common\models\Pro_vehicle_fee_plan();
$feeModel->setFestivalNames(\common\components\OptionsModule::getFestivalsArray());
$feeColumns = ['id', 'vehicle_model_id', 'office_id', 'operation'];
/*$festivalFields = [];
foreach ($feeModel->festivalFieldsArray as $field => $festivalId) {
    $festivalFields[$festivalId] = $field;
}
ksort($festivalFields);
foreach ($festivalFields as $festivalId => $field) {
    $feeColumns[] = $field;
}
$feeColumns[] = 'edit_user_id';
$feeColumns[] = 'created_at';
$feeColumns[] = 'updated_at';
*/
echo CMyHtml::datagrid('   ', // $title
    $feeModel,    // $model
    $feeColumns,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['dialogWidth'=>'80%', 'dialogHeight'=>'580px'],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    4, 0                // $frozenColumnIndex, $frozenRowIndex
);
