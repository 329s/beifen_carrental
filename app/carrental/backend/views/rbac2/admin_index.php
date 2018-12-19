<?php 

use common\helpers\CMyHtml;

$urlsArray = [
    'url' => \yii\helpers\Url::to(['rbac2/admin_list']),
    'deleteUrl' => \yii\helpers\Url::to(['rbac2/admin_delete']),
];

$toolbarArray = [
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_APPEND, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/admin_add'])]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_EDIT, '', ['dialog'=>\yii\helpers\Url::to(['rbac2/admin_edit']), 'needSelect' => true]),
    CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_REMOVE, '', ''),
    //CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_SINGLEMULTIPLE),
];

$authority = \backend\components\AdminModule::getCurRoleAuthoration();
if ($authority >= \backend\models\Rbac_role::AUTHORITY_DOMAIN_MANAGER) {
    $menuToolArray = array(
        array('event' => array('tab' => \yii\helpers\Url::to(['rbac2/role_index'])), 'name' => Yii::t('locale', 'Role management'), 'title' => Yii::t('locale', 'Role management'), 'icon' => 'icon-user'),
        array('type'=>'sep'),
    );
    $toolbarArray[] = CMyHtml::formatDatagridToolConfig(CMyHtml::DG_TOOL_MENU, Yii::t('locale', 'Role management'), $menuToolArray, 'icon-user');
}

echo CMyHtml::datagrid('   ', // $title
    new backend\models\Rbac_admin(),    // $model
    ['id', 'username', 'email', 'remark', 'login_count', 'login_at', 'created_at', 'authority_at', 'status', 'belong_office_id', 'operation'],            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
