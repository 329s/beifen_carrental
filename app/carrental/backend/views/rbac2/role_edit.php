<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'role_name',
        'label' => Yii::t('locale', 'Name'),
        'value' => $objRole->role_name,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objRole->status,
        'data' => ['1' => Yii::t('locale', 'Enable'), '0' => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'authority',
        'label' => Yii::t('locale', 'Authority level'),
        'value' => $objRole->authority,
        'data' => \backend\models\Rbac_role::getAuthoritiesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update', 'id' => $objRole->id];

echo CMyHtml::form(Yii::t('locale', 'Edit Role'), \yii\helpers\Url::to(['rbac2/role_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
