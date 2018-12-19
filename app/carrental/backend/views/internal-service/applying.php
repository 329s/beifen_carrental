<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['internal-service/applying_list']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['internal-service/applying_add'])]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['internal-service/applying_edit']), 'needSelect' => true]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ['ajax'=>\yii\helpers\Url::to(['internal-service/applying_delete']), 'needSelect' => true]),
];

$objModel = new backend\models\Pro_inner_applying();
$objForm = new backend\models\Form_pro_inner_applying();

$funcId = CMyHtml::genID(); 
$dgId = CMyHtml::getIDPrefix().'dg_'.CMyHtml::genID();

$containerPart = CMyHtml::datagrid(Yii::t('carrental', 'Vehicle applying management'), // $title
    $objModel,    // $model
    ['office_id', 'plate_number', 'content', 'applyer', 'type', 'approval_content', 'status', 'created_at'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    ['id'=>$dgId],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);

$htmlArray = [];

$htmlArray[] = $containerPart;

echo implode("\n", $htmlArray);
