<?php

use common\helpers\CMyHtml;

$formTitle = \Yii::t('carrental', 'Match real name authentication information');

$inputs = [
    ['type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => 'info_id',
        'label' => \Yii::t('carrental', 'Real name information'),
        'value' => '',
        'data' => $arrUserInfos,
        'htmlOptions' => ['required' => true],
        'columnindex' => 0,
    ],
];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'match', 'id'=>$accountId];

echo CMyHtml::form($formTitle, \yii\helpers\Url::to(['user/account_realname_match']), 'post', [], $inputs, $buttons, $hiddenFields);
