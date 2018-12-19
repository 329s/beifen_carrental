<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'name',
        'label' => Yii::t('locale', 'User Name'),
        'value' => $objInfo->name,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'phone',
        'label' => Yii::t('locale', 'Telephone'),
        'value' => $objInfo->phone,
		'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'address',
        'label' => Yii::t('locale', 'Address'),
        'value' => $objInfo->address,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'mail',
        'label' => Yii::t('locale', 'Email'),
        'value' => $objInfo->mail,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'message',
        'label' => Yii::t('locale', 'Message'),
        'value' => $objInfo->message,
    ],
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objInfo->status,
        'data' => ['0' => Yii::t('locale', 'Normal'), '1' => Yii::t('locale', 'Processed')],
        'htmlOptions' => ['required' => true],
    ],
    
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];
$hiddenFields = ['action' => 'update','id'=>$objInfo->id];
echo CMyHtml::form(Yii::t('locale', 'Look investapply'), \yii\helpers\Url::to(['user/investapply_look']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
