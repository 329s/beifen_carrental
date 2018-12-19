<?php

$title = Yii::t('locale', '{name} authority', ['name'=> $action == 'create' ? Yii::t('locale', 'Add') : Yii::t('locale', 'Edit')]);

$htmlArray = [];
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'modal-dialog']);
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'modal-content']);
$htmlArray[] = yii\bootstrap\Html::beginTag('div', ['class'=>'modal-header']);
$htmlArray[] = yii\bootstrap\Html::tag('button', '&times;', ['type'=>'button', 'class'=>'close', 'data-dismiss'=>'modal', 'aria-hidden'=>'true']);
$htmlArray[] = yii\bootstrap\Html::tag('h4', $title, ['class'=>'modal-title']);
$htmlArray[] = yii\bootstrap\Html::endTag('div');

$htmlArray[] = \common\widgets\AutoLayoutFormWidget::widget([
    'formModel' => $objForm,
    'action' => \yii\helpers\Url::to(['/rbac/permissions/edit', 'act'=>'save']),
    'attributes' => [
        'name', 'category', 'parent', 'href', 'description', 'icon', 'icon_traditional', 'c_order', 'status', 'target'
    ],
    'hiddenValues' => [
        'action' => $action,
        'originName' => $objForm->name,
    ],
    'attributeTypes' => [
        'parent' => ['type'=> \common\helpers\InputTypesHelper::TYPE_DROPDOWN_TREE, 'data'=> \backend\modules\rbac\components\PermissionsService::getMenuPermissionsAsCombotreeData()],
    ],
    'resetButton' => true,
    'cancelButton' => ['data-dismiss'=>'modal'],
    'successCallback' => empty($callback)?"": "function() { {$callback}(); }",
]);

$htmlArray[] = \yii\helpers\Html::endTag('div');    // end of modal-content
$htmlArray[] = \yii\helpers\Html::endTag('div');    // end fo modal-dialog

echo implode("\n", $htmlArray);
