<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['vehicle/model_list']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/deletemodel']),
];

$toolbarArray = [
    Yii::$app->user->can('vehicle/addmodel') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', '{operation} vehicle model', ['operation' => \Yii::t('locale', 'Add')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/addmodel'])]) : null,
    Yii::$app->user->can('vehicle/editmodel') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, Yii::t('locale', '{operation} vehicle model', ['operation' => \Yii::t('locale', 'Edit')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/editmodel']), 'needSelect' => true]) : null,
    Yii::$app->user->can('vehicle/deletemodel') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, Yii::t('locale', '{operation} vehicle model', ['operation' => \Yii::t('locale', 'Delete')]), '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    Yii::$app->user->can('vehicle/brand_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', 'Vehicle brand management'), ['tab'=>\yii\helpers\Url::to(['vehicle/brand_index'])], 'icon-car') : null,
    Yii::$app->user->can('vehicle/feeplan_index') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_BUTTON, Yii::t('locale', '{operation} vehicle fee plan', ['operation' => Yii::t('locale', 'Management')]), ['tab'=>\yii\helpers\Url::to(['vehicle/feeplan_index'])], 'icon-money_yen') : null,

    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'vehicle_model', Yii::t('locale', 'Vehicle model'), \common\components\VehicleModule::getVehicleModelNamesArray(['enableNone'=>true]), ['searchOnChange'=>true]),
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_model(),    // $model
    ['id', 'image_0', 'vehicle_model_info', 'gps', 'limit_flag', 'vehicle_price_info',
        'edit_info', 'extra_info'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
