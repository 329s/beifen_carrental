<?php


use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['user/account_list', 'hide_real_name'=>$hideRealNameAccounts]),
    //'deleteUrl' => \yii\helpers\Url::to(['user/delete']),
    //'detailUrl' => \yii\helpers\Url::to(['user/getuserdetailview']),
];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'account', Yii::t('locale', 'Account'), '', ['searchOnChange'=>true]),
];

echo CMyHtml::datagrid('   ', // $title
    new common\models\Pub_user(),    // $model
    ['id', 'account', 'email', 'real_name_authenticated', 'login_count', 'login_at', 'status', 'unfreeze_at', 'created_at', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
