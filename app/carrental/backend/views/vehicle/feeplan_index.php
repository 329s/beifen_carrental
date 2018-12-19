<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['vehicle/feeplan_list']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Add')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/addfeeplan'])]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Edit')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/editfeeplan']), 'needSelect' => true]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Delete')]), ''),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model', Yii::t('locale', 'Vehicle model'), \common\components\VehicleModule::getVehicleModelNamesArray(['enableNone'=>true]), ['searchOnChange'=>true]),
];

$feeModel = new \common\models\Pro_vehicle_fee_plan();
$feeModel->setFestivalNames(\common\components\OptionsModule::getFestivalsArray());
$feeColumns = ['id', 'vehicle_model_id', 'source', 'office_id', 'status', 'operation', 'price_default', 'price_3days', 'price_week',
    'price_15days', 'price_month', 'price_sunday', 'price_monday', 'price_tuesday', 
    'price_wednesday', 'price_thirsday', 'price_friday', 'price_saturday'];
$festivalFields = [];
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

echo CMyHtml::datagrid('   ', // $title
    $feeModel,    // $model
    $feeColumns,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['dialogWidth'=>'80%', 'dialogHeight'=>'580px'],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    4, 0                // $frozenColumnIndex, $frozenRowIndex
);
