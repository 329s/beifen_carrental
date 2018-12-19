<?php

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/feedback_list']),
];

$toolbarArray = [
    // search areas
    //CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_COMBOTREE, 'office_id', Yii::t('locale', 'Office'), \common\components\OfficeModule::getOfficeComboTreeData(), ['searchOnChange'=>true, 'style'=>'width:220px']),
];

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_feedback(),    // $model
    ['id', 'user_id', 'message', 'status', 'edit_user_id', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
