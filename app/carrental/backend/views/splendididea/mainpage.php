<?php
/* @var $this yii\web\View */
$idPrefix = \common\helpers\CMyHtml::getIDPrefix();
$autoId = \common\helpers\CMyHtml::genID();

$splendidTotalAmount = 10000;
$splendidRewardedAmount = \common\models\Pro_splendid_idea::find()->sum('award_amount');

$filterModel = new \backend\models\searchers\Searcher_pro_splendid_idea();
$dataProvider = $filterModel->search(\Yii::$app->request->getQueryParams());

$myId = "{$idPrefix}idea-main{$autoId}";
$curUrl = \yii\helpers\Url::to(array_merge(['/splendididea/mainpage'], $filterModel->genUrlParams()));
?>
<div class="row" id="<?= $myId ?>">
    <div class="col-md-8">
        <div class="row" style="margin-left: 4px">
            <div class="panel panel-default">
                <div class="panel-heading">
                </div>
                <div class="panel-body">
                </div>
            </div>
            
        </div>
        <div class="row" id="<?= "{$idPrefix}idea_content{$autoId}" ?>" style="margin-left: 4px">
<?php
$arrTools = [];
$arrTools[] = yii\bootstrap\Html::beginForm('', 'get', ['onsubmit'=>"$.custom.bootstrap.form.onSubmit(this, {}, function(data){ $('#{$myId}').parent().html(data); }); return false;", 'class'=>'form-inline']);
$arrTools[] = yii\bootstrap\Html::activeDropDownList($filterModel, 'type', \common\models\Pro_splendid_idea::getTypesArray(), ['class'=>'form-control']);
$arrTools[] = yii\bootstrap\Html::activeDropDownList($filterModel, 'status', \common\models\Pro_splendid_idea::getStatusArray(true), ['class'=>'form-control']);
$arrTools[] = yii\bootstrap\Html::activeTextInput($filterModel, 'keyword', ['class'=>'form-control']);
$arrTools[] = yii\bootstrap\Html::submitButton(Yii::t('locale', 'Search'), ['class'=>'form-control btn btn-default fa fa-search']);
$arrTools[] = yii\bootstrap\Html::endForm();

$dataProvider->manualFormatModelValues();

$bodyHtml = yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    //'searchModel' => $filterModel,
    'layout'=>"{items}\n{pager}",
    'options' => ['class'=>'list-view list-group'],
    'viewParams' => ['mainPageId' => $myId, 'backUrl' => $curUrl, 'onClickCell'=>"funcOnClickIdeaCell{$autoId}"],
    'itemView' => 'itemview',
    'pager' => [
        'class' => \common\widgets\LinkPager::className(),
        'containerSelector' => ".tab-content .tab-pane.active",
    ],
]);

echo common\helpers\BootstrapHtml::beginPanel(implode("\n", $arrTools), ['body'=>$bodyHtml]);
echo common\helpers\BootstrapHtml::endPanel();

$monthlyDefaultWidgetOptions = [
    'layout'=>"{items}\n{pager}",
    'options' => ['class'=>'list-view list-group'],
    'viewParams' => ['mainPageId' => $myId, 'unitText'=>'', 'unitPrefix'=>''],
    'emptyText' => '暂无数据',
    'itemView' => function ($model, $key, $index, $widget) {
        $htmlArray = [];
        $htmlArray[] = yii\bootstrap\Html::beginTag('li', ['class'=>'']);
        $htmlArray[] = $model['publisher'];
        $htmlArray[] = yii\bootstrap\Html::tag('span', 
                $widget->viewParams['unitPrefix'].$model['n'].$widget->viewParams['unitText'], 
                ['class'=>'badge']);
        $htmlArray[] = yii\bootstrap\Html::endTag('li');
        return implode("\n", $htmlArray);
    }
];

?>
        </div>

    </div>
    <div class="col-md-4">
        <div class="row" style="margin:4px">
            <?php
            $arrInfoList = [
            yii\bootstrap\Html::tag('h3', "金点子奖池：".$splendidTotalAmount, []),
            yii\bootstrap\Html::tag('h4', "当前共产生奖金：".$splendidRewardedAmount, []),
            ];
            echo common\helpers\BootstrapHtml::beginPanel('', ['body'=> implode("\n", $arrInfoList)]);
            echo common\helpers\BootstrapHtml::endPanel();
            ?>
        </div>
        <div class="row" style="margin:4px">
            <?php
            $arrNoticeList = [];
            $arrNoticeList[] = yii\bootstrap\Html::beginTag('ul', ['class'=>"list-unstyled"]);
            $arrNoticeList[] = yii\bootstrap\Html::tag('li', '金点子相关规则（第一版）', []);
            $arrNoticeList[] = yii\bootstrap\Html::endTag('ul');
            echo common\helpers\BootstrapHtml::beginPanel('公告', ['body'=> implode("\n", $arrNoticeList)]);
            echo common\helpers\BootstrapHtml::endPanel();
            ?>
        </div>
        <div class="row" style="margin:4px">
            <?php
            $tabAdoptId = $idPrefix.'tab_adopt'.$autoId;
            $tabAwardId = $idPrefix.'tab_award'.$autoId;
            ?>
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="header">采纳达人</li>
                    <li class="pull-right"><a href="#<?= $tabAdoptId ?>-tab1" data-toggle="tab"><?= \Yii::t('locale', 'Monthly') ?></a></li>
                    <li class="pull-right active"><a href="#<?= $tabAdoptId ?>-tab0" data-toggle="tab"><?= \Yii::t('locale', 'Weekly') ?></a></li>
                </ul>
                <div class="tab-content">
                    <div id="<?= $tabAdoptId ?>-tab0" class="tab-pane active">
                        <?php
                        echo yii\widgets\ListView::widget(yii\helpers\ArrayHelper::merge($monthlyDefaultWidgetOptions, [
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels'=>$arrAdoptWeekly]),
                            'emptyText' => '本周无数据',
                            'viewParams' => ['unitText'=>'次', 'unitPrefix'=>'采纳'],
                        ]));
                        ?>
                    </div>
                    <div id="<?= $tabAdoptId ?>-tab1" class="tab-pane">
                        <?php
                        echo yii\widgets\ListView::widget(yii\helpers\ArrayHelper::merge($monthlyDefaultWidgetOptions, [
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels'=>$arrAdoptMonthly]),
                            'emptyText' => '本月无数据',
                            'viewParams' => ['unitText'=>'次', 'unitPrefix'=>'采纳'],
                        ]));
                        ?>
                    </div>
                </div>
            </div>
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="header">奖励达人</li>
                    <li class="pull-right"><a href="#<?= $tabAwardId ?>-tab1" data-toggle="tab"><?= \Yii::t('locale', 'Monthly') ?></a></li>
                    <li class="pull-right active"><a href="#<?= $tabAwardId ?>-tab0" data-toggle="tab"><?= \Yii::t('locale', 'Weekly') ?></a></li>
                </ul>
                <div class="tab-content">
                    <div id="<?= $tabAwardId ?>-tab0" class="tab-pane active">
                        <?php
                        echo yii\widgets\ListView::widget(yii\helpers\ArrayHelper::merge($monthlyDefaultWidgetOptions, [
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels'=>$arrAwardWeekly]),
                            'emptyText' => '本周无数据',
                            'viewParams' => ['unitText'=>'元'],
                        ]));
                        ?>
                    </div>
                    <div id="<?= $tabAwardId ?>-tab1" class="tab-pane">
                        <?php
                        echo yii\widgets\ListView::widget(yii\helpers\ArrayHelper::merge($monthlyDefaultWidgetOptions, [
                            'dataProvider' => new \yii\data\ArrayDataProvider(['allModels'=>$arrAwardMonthly]),
                            'emptyText' => '本月无数据',
                            'viewParams' => ['unitText'=>'元'],
                        ]));
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
function funcOnClickIdeaCell<?= $autoId?>(cellId, mode) {
    var url = '#';
    if (mode == 'view') {
        url = '<?= \yii\helpers\Url::to(['/splendididea/view', 'id'=>'']) ?>'+cellId;
    }
    <?php if (backend\components\AdminModule::getCurRoleAuthoration() >= backend\models\Rbac_role::AUTHORITY_DOMAIN_MANAGER): ?>
    else if (mode == 'approval') {
        url = '<?= \yii\helpers\Url::to(['/splendididea/approval', 'id'=>'']) ?>'+cellId;
    }
    <?php endif; ?>
    $.custom.bootstrap.loadElement($('#<?= $myId ?>').parent(), {url:url, data:{backUrl:'<?= $curUrl ?>', <?= \Yii::$app->request->csrfParam ?>:'<?= \Yii::$app->request->csrfToken ?>'}, type:'POST'});
}
</script>
