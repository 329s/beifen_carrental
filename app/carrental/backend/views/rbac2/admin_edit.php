<?php

use common\helpers\CMyHtml;

$objForm = new backend\models\Form_rbac_admin();
$formTitle = Yii::t('locale', '{operation} user', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = $objAdmin;
if (!$objData) {
    $objData = new \backend\models\Rbac_admin();
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'username',
        'label' => Yii::t('locale', 'Name'),
        'value' => $objData->username,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_EMAIL, 'name' => 'email',
        'label' => Yii::t('locale', 'Email'),
        'value' => $objData->email,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_PASSWORD, 'name' => 'password',
        'label' => Yii::t('locale', 'Password'),
        'value' => '',
        'htmlOptions' => [],
    ],
    ['type' => CMyHtml::INPUT_PASSWORD, 'name' => 'password_c',
        'label' => Yii::t('locale', 'Confirm Password'),
        'value' => '',
        'htmlOptions' => [],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'remark',
        'label' => Yii::t('locale', 'Remarks'),
        'value' => $objData->remark,
        'htmlOptions' => ['required' => false],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objData->status,
        'data' => [\backend\models\Rbac_admin::STATUS_ACTIVE => Yii::t('locale', 'Enable'), 
            \backend\models\Rbac_admin::STATUS_DELETED => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => 'authority_at',
        'label' => Yii::t('locale', 'Authority time'),
        'value' => $objData->authority_at,
        'htmlOptions' => ['required' => false, 'editable'=>false],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => 'belong_office_id',
        'label' => \Yii::t('locale', 'Belong office'),
        'value' => $objData->belong_office_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'role_id',
        'label' => \Yii::t('locale', 'Authority'),
        'value' => $objData->getRoleId(),
        'data' => \backend\components\AdminModule::getRolesArray($objData->id),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action, 'id' => $objAdmin->id];

echo CMyHtml::form(Yii::t('locale', 'Edit Role'), \yii\helpers\Url::to(['rbac2/admin_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
