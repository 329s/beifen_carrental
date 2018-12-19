<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'role_name',
        'label' => Yii::t('locale', 'Name'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => '',
        'data' => ['1' => Yii::t('locale', 'Enable'), '0' => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'authority',
        'label' => Yii::t('locale', 'Authority level'),
        'value' => \backend\models\Rbac_role::AUTHORITY_OPERATOR,
        'data' => \backend\models\Rbac_role::getAuthoritiesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'insert'];

echo CMyHtml::form(Yii::t('locale', 'Create Role'), \yii\helpers\Url::to(['rbac2/role_add']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
