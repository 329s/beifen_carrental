<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_code',
        'label' => Yii::t('locale', 'Column code'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_name',
        'label' => Yii::t('locale', 'Column name'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_url',
        'label' => Yii::t('locale', 'Column url'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_icon',
        'label' => Yii::t('locale', 'Column icon'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'c_order',
        'label' => Yii::t('locale', 'C order'),
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => '',
        'data' => ['1' => Yii::t('locale', 'Enable'), '0' => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
    
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'insert'];

echo CMyHtml::form(Yii::t('locale', 'Create Column'), \yii\helpers\Url::to(['rbac2/column_add']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
