<?php

use common\helpers\CMyHtml;

$inputs = [
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'name',
        'label' => '真实姓名',
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'card_id',
        'label' => '身份证号',
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'phone',
        'label' => '手机号码',
        'value' => '',
        'htmlOptions' => ['required' => true],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'mail',
        'label' => '电子邮件',
        'value' => '',
        'htmlOptions' => [],
    ],
    ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => 'sign',
        'label' => 'sign',
        'value' => '',
        'htmlOptions' => [],
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'save', 'card_type'=>1];

echo CMyHtml::form('测试修改详细信息', \yii\helpers\Url::to(['user/editinfo']), 'post', 
    [
        'successCallback' => "function (data) {\n".
            "    $.custom.easyui.alert.show(data, 'Result', 'info', 'info');\n".
            "\n}",
    ], $inputs, $buttons, $hiddenFields);
