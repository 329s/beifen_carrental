<?php

use common\helpers\CMyHtml;

$urlInfo = ['vehicle/vehicle_office_change_list', 'vehicle_id'=>$vehicle_id];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'vehicle_id', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), $vehicle_id, []),
];

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$columnFields = ['id', 'vehicle_id','belong_office_id', 'new_belong_office_id', 'updated_at', 'created_at'];

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, Yii::t('locale', 'Retrieval'), '', '', []);

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_office_change(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
