<?php
$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();
$myId = "{$idPrefix}idea-view{$autoId}";
$publishCommentId = "{$idPrefix}publish-comment{$autoId}";
$objUser = null;
$avatarImg = 'data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
if ($objItem) {
    $objUser = backend\models\Rbac_admin::findIdentity($objItem->created_by);
}
else {
    $objItem = new \common\models\Pro_splendid_idea();
}
if ($objUser) {
    $avatarImg = common\helpers\Utils::toFileUri($objUser->avatar);
}
?>
<div id="<?= $myId ?>">
<a href="javascript:void(0)" onclick="$.custom.bootstrap.loadElement($('#<?= $myId ?>').parent(), '<?= $backUrl ?>')">返回</a>
<div class="media">
  <div class="media-left">
    <a href="javascript:void(0)">
      <img class="media-object" src="<?= $avatarImg ?>" alt="...">
    </a>
  </div>
  <div class="media-body">
    <span class="label label-primary pull-right"><?php echo \common\models\Pro_splendid_idea::getStatusArray(true)[$objItem->status]; ?></span>
    <h4 class="media-heading"><?= $objItem->title ?></h4>
    <blockquote>
    <p><?= $objItem->content ?></p>
    </blockquote>
    <p>金点子奖励：<span style="color:orange;font-size:24px;font-weight:bold;"><?= $objItem->award_amount ?></span>元</p>
    <div class="row text-muted" style="vertical-align: bottom;">
        <div class="col-xs-4"><span><?= $objItem->getAttributeLabel('created_at').'&nbsp;'. date('Y-m-d H:i', $objItem->created_at) ?></span></div>
        <div class="col-xs-4"><span>关注&nbsp;<i style="color:blue;"><?= $objItem->focus_count ?></i>&nbsp;|&nbsp;浏览&nbsp;<i style="color:blue;"><?= $objItem->visits ?></i>&nbsp;|&nbsp;评论&nbsp;<i style="color:blue;"><?= $objItem->comment_count ?></i></span></div>
        <div class="col-xs-4"><span><a href="<?= '#'.$publishCommentId ?>" onclick="">回复</a></span></div>
    </div>
  </div>
</div>
<?php

$dataProvider->manualFormatModelValues();

$commentsHtml = yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'layout'=>"{items}\n{pager}",
    'options' => ['class'=>'list-view list-group'],
    'viewParams' => ['mainPageId' => $myId],
    'emptyText' => '',
    'itemView' => function ($model, $key, $index, $widget) {
        $htmlArray = [];
        $htmlArray[] = yii\bootstrap\Html::beginTag('a', ['class'=>'list-group-item', 'href'=>'javascript:void(0)']);
        $htmlArray[] = yii\bootstrap\Html::beginTag('blockquote');
        $htmlArray[] = yii\bootstrap\Html::tag('p', $model['content']);
        if (!empty($model['comment_to'])) {
            $htmlArray[] = yii\bootstrap\Html::beginTag('blockquote');
            $htmlArray[] = yii\bootstrap\Html::tag('p', $model['comment_to']);
            $htmlArray[] = yii\bootstrap\Html::endTag('blockquote');
        }
        $htmlArray[] = yii\bootstrap\Html::endTag('blockquote');
        $htmlArray[] = yii\bootstrap\Html::beginTag('div', ['class'=>'row text-muted']);
        $htmlArray[] = yii\bootstrap\Html::tag('div', yii\bootstrap\Html::tag('span', $model['created_by'].'&nbsp;发表于&nbsp;'.$model['created_at'], []), ['class'=>'col-xs-6']);
        $htmlArray[] = yii\bootstrap\Html::tag('div', '', ['class'=>'col-xs-6']);
        $htmlArray[] = yii\bootstrap\Html::endTag('div');
        $htmlArray[] = yii\bootstrap\Html::endTag('a');
        return implode("\n", $htmlArray);
    },
    'pager' => [
        'class' => \common\widgets\LinkPager::className(),
        'containerSelector' => ".tab-content .tab-pane.active",
    ],
]);

echo common\helpers\BootstrapHtml::beginPanel('', ['body'=>$commentsHtml]);
echo common\helpers\BootstrapHtml::endPanel();

$formHtml = \common\widgets\AutoLayoutFormWidget::widget([
    'action' => \yii\helpers\Url::to(['/splendididea/comment']),
    'formModel' => new \backend\models\Form_pro_splendid_idea_comments(),
    'attributes' => [
        'content',
    ],
    'attributeTypes' => [
        'content' => \common\helpers\InputTypesHelper::TYPE_TEXTAREA,
    ],
    'hiddenValues' => [
        'main_id' => ($objItem ? $objItem->id : 0),
        'comment_to' => '',
        'action' => 'save',
    ],
    'submitButton' => ['class'=>'btn btn-primary', 'label'=>\Yii::t('locale', 'Publish')],
    'successCallback' => "function(){ $.custom.bootstrap.loadElement($('#{$myId}').parent(), {url:'".\yii\helpers\Url::to(['/splendididea/view', 'id'=>($objItem?$objItem->id:'')])."',data:{backUrl:'{$backUrl}', ".\Yii::$app->request->csrfParam.":'".\Yii::$app->request->csrfToken."'}, type:'post'}); }",
]);

echo common\helpers\BootstrapHtml::beginPanel(\Yii::t('locale', 'Publish comment'), ['body'=>$formHtml, 'id'=>$publishCommentId]);
echo common\helpers\BootstrapHtml::endPanel();

?>

</div>