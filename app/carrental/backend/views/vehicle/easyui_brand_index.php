<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['vehicle/brand_list']),
    'deleteUrl' => \yii\helpers\Url::to(['vehicle/deletebrand']),
];

$toolbarArray = [
    Yii::$app->user->can('vehicle/addbrand') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Add')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/addbrand'])]) : null,
    Yii::$app->user->can('vehicle/editbrand') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Edit')]), ['dialog'=>\yii\helpers\Url::to(['vehicle/editbrand']), 'needSelect' => true]) : null,
    Yii::$app->user->can('vehicle/deletebrand') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, Yii::t('locale', '{operation} vehicle brand', ['operation' => \Yii::t('locale', 'Delete')]), '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_CHECKBOX, 'display_model_series', '', ['1' => Yii::t('locale', 'Display model series')], ['searchOnChange'=>true], ''),
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_brand(),    // $model
    ['id', 'name', 'belong_brand', 'flag', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
