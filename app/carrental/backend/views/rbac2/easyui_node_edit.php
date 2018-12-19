<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'name',
        'label' => Yii::t('locale', 'Node name'),
        'value' => $objNode->name,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'category',
        'label' => Yii::t('locale', 'Node category'),
        'value' => $objNode->category,
		'data' => \backend\models\Rbac_permission::getIdentityTypesArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false, 'style'=>"width:150px"],
        'columnindex' => 0,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'parent',
        'label' => Yii::t('locale', 'Node parent'),
        'value' => $objNode->parent,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'href',
        'label' => Yii::t('locale', 'Node href'),
        'value' => $objNode->href,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'description',
        'label' => Yii::t('locale', 'Node description'),
        'value' => $objNode->description,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'icon',
        'label' => Yii::t('locale', 'Node icon'),
        'value' => $objNode->icon,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'icon_traditional',
        'label' => Yii::t('locale', 'Node icon traditional'),
        'value' => $objNode->icon_traditional,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'c_order',
        'label' => Yii::t('locale', 'C order'),
        'value' => $objNode->c_order,
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objNode->status,
        'data' => ['0' => Yii::t('locale', 'Enable'), '1' => Yii::t('locale', 'Disable')],
        'htmlOptions' => ['required' => true],
    ],
    
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update'];

echo CMyHtml::form(Yii::t('locale', 'Edit Node'), \yii\helpers\Url::to(['rbac2/node_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
