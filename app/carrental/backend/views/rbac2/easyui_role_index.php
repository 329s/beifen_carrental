<?php 

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['rbac2/role_list']),
];

$toolbarArray = [
    Yii::$app->user->can('rbac2/role_add') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/role_add'])]) : null,
    Yii::$app->user->can('rbac2/role_edit') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/role_edit']), 'needSelect' => true]) : null,
    //Yii::$app->user->can('rbac2/role_delete') ? CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', '') : null,
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
];

echo CMyHtml::datagrid('   ', // $title
    new \backend\models\Rbac_role(),    // $model
    ['id', 'role_name', 'status', 'authority', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
