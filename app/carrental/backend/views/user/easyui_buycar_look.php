<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'name',
        'label' => Yii::t('locale', 'Buy car name'),
        'value' => $objInfo->name,
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'mobile',
        'label' => Yii::t('locale', 'Buy car mobile'),
        'value' => $objInfo->mobile,
		'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'sex',
        'label' => Yii::t('locale', 'Buy car sex'),
        'value' => $objInfo->sex,
        'data' => ['0' => Yii::t('locale', 'Man'), '1' => Yii::t('locale', 'Woman')],
        'htmlOptions' => ['required' => true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'car_models',
        'label' => Yii::t('locale', 'Buy car models'),
        'value' => $objInfo->car_models,
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'buy_city',
        'label' => Yii::t('locale', 'Buy car city'),
        'value' => $objInfo->buy_city,
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

echo CMyHtml::form(Yii::t('locale', 'Look buy car'), \yii\helpers\Url::to(['user/buycar_look']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
