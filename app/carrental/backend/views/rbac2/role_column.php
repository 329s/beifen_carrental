<?php

use yii\helpers\Html;
use common\helpers\CMyHtml;

$authHtmls = [];
if ($arrAuth) {
    $authHtmls[] = Html::tag('span', Yii::t('locale', "Attention! The columns were divided into {count} sections, The {number} layer is the actual permissions, If you choose the {number} layer directory, you should manually select the levels above, otherwise the selection is invalid.", ['count'=>\Yii::t('locale', 'two'), 'number'=>\Yii::t('locale', 'second')])."<br /><br />", ['style' => 'font-size:12px; color:red']);
    
    $authHtmls[] = \common\widgets\CheckboxTreeWidget::widget([
        'data' => $arrAuth,
        'name' => 'selected_column',
        'selects' => $arrRC,
        'childrenField' => 'children',
        'itemField' => 'o',
        'nameField' => false,
        'valueField' => 'name',
        'labelField' => 'description',
    ]);
}
else {
    $authHtmls[] = Yii::t('locale', 'Column preparation mistake!');
}

$inputs = [implode("\n", $authHtmls)];

$buttons = ['submit' => Yii::t('locale', 'Submit'), 'close' => Yii::t('locale', 'Cancel')];

$hiddenFields = ['action' => 'update', 'id' => $intId];

echo CMyHtml::form(Yii::t('locale', 'Edit Role'), \yii\helpers\Url::to(['rbac2/role_column']), 'post', [], $inputs, $buttons, $hiddenFields);
