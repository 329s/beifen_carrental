<?php 

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['rbac2/column_list']),
];
$toolbarArray = [
	Yii::$app->user->can('rbac2/column_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/column_add'])]) : null,
    
];

echo CMyHtml::datagrid('栏目管理', // $title
    new \backend\models\Rbac_column(),    // $model
    ['id', 'column_code','column_name',  'column_url','status','operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
