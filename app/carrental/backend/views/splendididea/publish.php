<?php
/* @var $this yii\web\View */

$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();
$myId = "{$idPrefix}idea-publish{$autoId}";

$modelData = new \common\models\Pro_splendid_idea();

echo yii\bootstrap\Html::beginTag('div', ['id'=>$myId]);

echo \common\widgets\AutoLayoutFormWidget::widget([
    'action' => \yii\helpers\Url::to(['splendididea/publish']),
    'formModel' => new \backend\models\Form_pro_splendid_idea(),
    'data' => $modelData,
    'columnCount' => 2,
    'attributes' => [
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>'发布内容'],
        'publisher',
        \common\helpers\InputTypesHelper::TYPE_NOP,
        'title',
        'type',
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>''],
        'image_info',
        'attachment_info',
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>'', 'columnCount'=>1],
        'content',
    ],
    'attributeTypes' => [
        'content' => \common\helpers\InputTypesHelper::TYPE_TEXTAREA,
    ],
    'hiddenValues' => [
        'status' => \common\models\Pro_splendid_idea::STATUS_APPROVAL_PENDING,
        'action' => 'save',
    ],
    'successCallback' => "function(){ var tabId = $('#{$myId}').parent().attr('id').slice(0, -5); $('#'+tabId+' a:first').tab('show'); $.custom.bootstrap.loadElement('#'+tabId+'-tab0', '".\yii\helpers\Url::to(['/splendididea/mainpage'])."'); }",
]);
    
echo yii\bootstrap\Html::endTag('div');
