<?php

use common\helpers\CMyHtml;

$objForm = new backend\models\Form_rbac_admin();
$objForm->setScenario($action);
$formTitle = Yii::t('locale', '{operation} user', ['operation' => ($action == 'update' ? Yii::t('locale', 'Edit') : Yii::t('locale', 'Add'))]);

$objData = $objAdmin;
if (!$objData) {
    $objData = new \backend\models\Rbac_admin();
}

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('username'),
        'label' => $objData->getAttributeLabel('username'),
        'value' => $objData->username,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_EMAIL, 'name' => $objForm->fieldName('email'),
        'label' => $objData->getAttributeLabel('email'),
        'value' => $objData->email,
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_PASSWORD, 'name' => $objForm->fieldName('password'),
        'label' => $objData->getAttributeLabel('password'),
        'value' => '',
        'htmlOptions' => ['required' => ($action == 'create' ? true : false), 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_PASSWORD, 'name' => $objForm->fieldName('password_repeat'),
        'label' => $objData->getAttributeLabel('password_repeat'),
        'value' => '',
        'htmlOptions' => ['required' => ($action == 'create' ? true : false), 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $objForm->fieldName('remark'),
        'label' => $objData->getAttributeLabel('remark'),
        'value' => $objData->remark,
        'htmlOptions' => ['required' => false, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $objForm->fieldName('status'),
        'label' => $objData->getAttributeLabel('status'),
        'value' => $objData->status,
        'data' => [\backend\models\Rbac_admin::STATUS_ACTIVE => Yii::t('locale', 'Enable'), 
            \backend\models\Rbac_admin::STATUS_DELETED => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $objForm->fieldName('authority_at'),
        'label' => $objData->getAttributeLabel('authority_at'),
        'value' => $objData->authority_at ? date('Y-m-d H:i:s', $objData->authority_at) : '',
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $objForm->fieldName('belong_office_id'),
        'label' => $objData->getAttributeLabel('belong_office_id'),
        'value' => $objData->belong_office_id,
        'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
        'htmlOptions' => ['required' => true, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $objForm->fieldName('role_id'),
        'label' => \Yii::t('locale', 'Authority'),
        'value' => $objData->getRoleId(),
        'data' => \backend\components\AdminModule::getRolesArray($objData->id),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:200px"],
    ],
    ['type' => CMyHtml::INPUT_IMAGEFIELD, 'name' => $objForm->fieldName('avatar'),
        'label' => $objData->getAttributeLabel('avatar'),
        'value' => '',
        'htmlOptions' => ['required' => false, 'editable'=>false, 'style'=>"width:200px",
            'width'=>'200px', 'height'=>'140px', 
            'fileSize'=>"400KB",
            'src'=>\common\helpers\Utils::toFileUri($objData->avatar)],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];
if ($objAdmin) {
    $hiddenFields['id'] = $objAdmin->id;
    $hiddenFields[$objForm->fieldName('id')] = $objAdmin->id;
}

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['rbac2/admin_edit']), 'post', ['enctype' => 'multipart/form-data', 'dialog'=>true], $inputs, $buttons, $hiddenFields);
