<?php

use common\helpers\CMyHtml;

$arrExistsRoleIds = [];
foreach ($arrUR as $k => $v) {
    $arrExistsRoleIds[] = $k;
}
$arrURCheckBoxList = [];
if ($objRole) {
    foreach ($objRole as $v) {
        $arrURCheckBoxList[$v->id] = $v->role_name;
    }
}

$inputs = [
    ['type' => CMyHtml::INPUT_CHECKBOXLIST, 'name' => 'select',
        'label' => ' ',
        'value' => $arrExistsRoleIds,
        'data' => $arrURCheckBoxList,
        'htmlOptions' => [],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update', 'id' => $intId];

echo CMyHtml::form(Yii::t('locale', 'Users - Role Mapping'), \yii\helpers\Url::to(['rbac2/admin_role']), 'post', [], $inputs, $buttons, $hiddenFields);
