<?php

use common\helpers\CMyHtml;

$urlInfo = ['order/purchase_order_list'];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'serial', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), '', []),
];

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$columnFields = ['id', 'serial', 'bind_id', 'user_id', 'type', 'channel_type', 'channel_trade_no', 'amount', 
    'receipt_amount', 'purchased_at', 'status', 'bind_param', 'extra_info',
    'purchase_code', 'purchase_msg', 'tried_count', 'edit_user_id', 'created_at', 'updated_at'];

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, Yii::t('locale', 'Retrieval'), '', '', []);

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_purchase_order(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [
		'data-options'=>[
            'sortName' => 'created_at',
            'sortOrder' => 'desc',
        ],
	],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
