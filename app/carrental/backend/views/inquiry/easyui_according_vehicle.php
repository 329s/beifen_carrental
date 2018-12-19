<?php

use common\helpers\CMyHtml;

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', Yii::t('locale', 'Plate number'), '', []),
];

$urlInfo = ['inquiry/according_vehicle_list'];
$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$columnFields = ['id','plate_number','engine_number','vehicle_number','inquiryCount','operation_button'];
$toolbarArray[] = CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'Vehicle Inquiry') , ['tab'=>\yii\helpers\Url::to(['inquiry/index'])]);

$toolbarArray[] = \Yii::$app->user->can('inquiry/query_vehicle_violation') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'Query vehicle violation information') , ['dialog'=>\yii\helpers\Url::to(['inquiry/query_vehicle_violation'])]) : null;
$toolbarArray[] = \Yii::$app->user->can('inquiry/view_log') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_GETCHANGES, \Yii::t('locale', 'View Log') , ['tab'=>\yii\helpers\Url::to(['inquiry/view_log'])]) : null;



echo CMyHtml::datagrid('车辆违章列表', // $title
    new \common\models\Pro_vehicle(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [
		'data-options'=>[
            'sortName' => 'inquiryCount',
            'sortOrder' => 'desc',
        ],
	],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);