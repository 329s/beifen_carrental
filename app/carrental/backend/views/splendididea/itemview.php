<a class="list-group-item" href="javascript:void(0)" onclick="">
    <span class="label label-primary pull-right"><?= $model->status; ?></span>
    <h4 class="media-heading"><?= $model->title ?></h4>
    <blockquote>
    <p><?= $model->content ?></p>
    </blockquote>
    <p>金点子奖励：<span style="color:orange;font-size:24px;font-weight:bold;"><?= $model->award_amount ?></span>元</p>
    <div class="row text-muted" style="vertical-align: bottom;">
        <div class="col-xs-4"><span><?= $model->getAttributeLabel('created_at').'&nbsp;'. $model->created_at ?></span></div>
        <div class="col-xs-4"><span>关注&nbsp;<i style="color:blue;"><?= $model->focus_count ?></i>&nbsp;|&nbsp;浏览&nbsp;<i style="color:blue;"><?= $model->visits ?></i>&nbsp;|&nbsp;评论&nbsp;<i style="color:blue;"><?= $model->comment_count ?></i></span></div>
        <div class="col-xs-4"><span><button class="btn btn-default" onclick="<?= $onClickCell."(".$model['id'].", 'view')" ?>">查看</button>
            <?php if (\Yii::$app->user->can('splendididea/approval') && $widget->dataProvider->originModelDatas[$index]['status'] == \common\models\Pro_splendid_idea::STATUS_APPROVAL_PENDING): ?>
                <button class="btn btn-default" onclick="<?= $onClickCell."(".$model['id'].", 'approval')" ?>">审批</button>
            <?php endif; ?>
            </span></div>
    </div>
</a>
