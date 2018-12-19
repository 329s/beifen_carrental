<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/longrentapplying_list']),
];

$toolbarArray = [
    // search areas
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id_take_car', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:220px']),
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_long_rent_applying(),    // $model
    ['id', 'name', 'phone', 'company', 'mail', 'message', 'office_id_take_car', 'start_time', 'end_time', 'status', 'edit_user_id', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
