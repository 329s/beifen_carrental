<?php

use common\helpers\CMyHtml;

$formTitle = \Yii::t('locale', 'Batch add gift code');

$inputs = [
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'type',
        'label' => \Yii::t('locale', 'Type'),
        'value' => '',
        'data' => \common\models\Pro_gift_code::getTypesArray(),
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => 'size',
        'label' => \Yii::t('locale', 'Count'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => 'amount',
        'label' => \Yii::t('locale', 'Gift amount'),
        'value' => '',
        'htmlOptions' => ['required' => true, 'precision'=>2],
    ],
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'status',
        'label' => \Yii::t('locale', 'Status'),
        'value' => \common\models\Pro_gift_code::STATUS_NORMAL,
        'data' => \common\models\Pro_gift_code::getStatusArray(),
        'htmlOptions' => ['editable'=>false],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => $action];

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['activities/gift_code_batch_add']), 'post', [], $inputs, $buttons, $hiddenFields);
