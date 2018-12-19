<?php

use common\helpers\CMyHtml;

$inputs = [
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_code',
        'label' => Yii::t('locale', 'Column code'),
        'value' => $objColumn->column_code,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_name',
        'label' => Yii::t('locale', 'Column name'),
        'value' => $objColumn->column_name,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_url',
        'label' => Yii::t('locale', 'Column url'),
        'value' => $objColumn->column_url,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'column_icon',
        'label' => Yii::t('locale', 'Column icon'),
        'value' => $objColumn->column_icon,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'c_order',
        'label' => Yii::t('locale', 'C order'),
        'value' => $objColumn->c_order,
        'htmlOptions' => ['required' => true],
    ],
	
	['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objColumn->status,
        'data' => ['1' => Yii::t('locale', 'Enable'), '0' => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
	
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update', 'id' => $objColumn->id];

echo CMyHtml::form(Yii::t('locale', 'Edit Column'), \yii\helpers\Url::to(['rbac2/column_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
