<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['city/city_area_list']),
    'deleteUrl' => \yii\helpers\Url::to(['city/delete_area']),
];

$tblTitle = '    ';
$tblWidth = '100%';
$tblHeight = '100%';
$tblColumns = ['id', 'name', 'city_id', 'status', 'operation'];

$toolbarArray = [
    Yii::$app->user->can('city/add_area') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, Yii::t('locale', '{name} area', ['name'=>Yii::t('locale', 'Add')]), ['dialog'=>\yii\helpers\Url::to(['city/add_area'])]) : null,
    Yii::$app->user->can('city/edit_area') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['city/edit_area']), 'needSelect' => true]) : null,
    Yii::$app->user->can('city/delete_area') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,

    // search areas
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOBOX, 'region', Yii::t('locale', 'Province'), \common\components\CityModule::getAllProvincesArray(true), ['searchOnChange'=>true, 'style'=>'width:120px']),
];

echo CMyHtml::datagrid($tblTitle, // $title
    new \common\models\Pro_city_area(),    // $model
    $tblColumns,            // $columns
    [],            // $dataArray
    $tblWidth, $tblHeight,     // $width, $height
    ['dialogWidth'=>'400px', 'dialogHeight'=>'226px'],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
