<?php
/* @var $this yii\web\View */
$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();
$myId = "{$idPrefix}idea-view{$autoId}";

?>
<div id="<?= $myId ?>">
<a href="javascript:void(0)" onclick="$.custom.bootstrap.loadElement($('#<?= $myId ?>').parent(), '<?= $backUrl ?>')">返回</a>
<div class="panel panel-default">
    <div class="panel-heading">审批管理</div>
    <div class="panel-body">
<?php
$modelData = new \common\models\Pro_splendid_idea();

echo \common\widgets\AutoLayoutFormWidget::widget([
    'action' => \yii\helpers\Url::to(['/splendididea/approval', 'id'=>$objItem->id]),
    'formModel' => new \backend\models\Form_pro_splendid_idea(),
    'data' => $objItem,
    'columnCount' => 2,
    'attributes' => [
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>''],
        'type',
        //['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>''],
        //'image_info',
        //'attachment_info',
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>'', 'columnCount'=>1],
        'content',
        ['type'=>\common\helpers\InputTypesHelper::TYPE_GROUP, 'label'=>'', 'columnCount'=>2],
        'publisher',
        'status',
        'award_amount',
    ],
    'attributeTypes' => [
        'content' => \common\helpers\InputTypesHelper::TYPE_TEXTAREA,
        'title' => common\helpers\InputTypesHelper::TYPE_HIDDEN,
    ],
    'hiddenValues' => [
        'id' => $objItem->id,
        'title' => $objItem->title,
        'action' => 'save',
    ],
    'successCallback' => "function(){ $.custom.bootstrap.loadElement($('#{$myId}').parent(), '{$backUrl}'); }",
    
]);

?>
    </div>
</div>

</div>
