<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'hphm',
        'label' => Yii::t('locale', 'Lsnum'),
        'value' => $objInquiry->hphm,
        'htmlOptions' => ['readonly'=>true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'money',
        'label' => Yii::t('locale', 'Deduction Price'),
        'value' => $objInquiry->money,
        'htmlOptions' => ['readonly'=>true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'fen',
        'label' => Yii::t('locale', 'Deduction Score'),
        'value' => $objInquiry->fen,
        'htmlOptions' => ['readonly'=>true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'date',
        'label' => \Yii::t('locale', 'Illegal Time'),
        'value' => $objInquiry->date,
        'htmlOptions' => ['readonly'=>true],
    ],
	
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'area',
        'label' => \Yii::t('locale', '{name} address', ['name'=>\Yii::t('locale', 'Illegal')]),
        'value' => $objInquiry->area,
        'htmlOptions' => ['style'=>'width:300px;','readonly'=>true],
    ],
	['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'act',
        'label' => \Yii::t('locale', '{name} content', ['name'=>\Yii::t('locale', 'Illegal')]),
        'value' => $objInquiry->act,
        'htmlOptions' => ['style'=>'width:300px;','readonly'=>true],
    ],
	
    ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'status',
        'label' => Yii::t('locale', 'Status'),
        'value' => $objInquiry->status,
        'data' => \common\models\Pro_violation_inquiry::getStatusArray(),
        'htmlOptions' => ['required' => true, 'editable'=>false,'style'=>'width:80px;'],
    ],
	['type' => CMyHtml::INPUT_TEXTAREA, 'name' => 'remarks',
        'label' => Yii::t('locale', 'Remarks'),
		'value' => $objInquiry->remarks,
        'htmlOptions' => [ 'required' => false, 'style'=>'width:400px;height:100px;'],
        'columnindex' => 0,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update', 'id' => $objInquiry->id];

echo CMyHtml::form('', \yii\helpers\Url::to(['inquiry/violation_edit']), 'post', ['dialog'=>true], $inputs, $buttons, $hiddenFields);
