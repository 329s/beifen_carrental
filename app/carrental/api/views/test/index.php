<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode('Client test') ?></title>
    <?php $this->head() ?>
    <?php
    $rootDir = Yii::$app->homeUrl;
    $pos = strpos($rootDir, 'app/carrental/');
    if ($pos !== false && $pos >= 0) {
        $rootDir = substr($rootDir, 0, $pos);
    }
$htmlArray = [];
$htmlArray[] = \yii\bootstrap\Html::cssFile($rootDir.'assets/jquery-easyui/themes/default/easyui.css');
$htmlArray[] = \yii\bootstrap\Html::cssFile($rootDir.'assets/jquery-easyui/themes/icon.css');
$htmlArray[] = \yii\bootstrap\Html::cssFile($rootDir.'assets/css/easyui.custom.css');
$htmlArray[] = \yii\bootstrap\Html::cssFile($rootDir.'assets/css/icons.extension.css');
$htmlArray[] = '';
echo implode("\n", $htmlArray);
    ?>
</head>
<body class="easyui-layout">
<?php $this->beginBody() ?>
    <div data-options="region:'north',border:false" style="height:60px;">
<div>
    <?php
    NavBar::begin([
        'brandLabel' => 'Client test',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        ['label' => 'Home', 'url' => ['/site/index']],
        ['label' => 'About', 'url' => ['/site/about']],
        ['label' => 'Contact', 'url' => ['/site/contact']],
    ];
    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
        $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = [
            'label' => 'Logout (' . Yii::$app->user->identity->account . ')',
            'url' => ['/site/logout'],
            'linkOptions' => ['data-method' => 'post']
        ];
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

</div>
    </div>
    
    <div data-options="region:'south'" style="height:30px;">
<footer class="footer" style="height:30px;">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>
    </div>

<?php
// container
use common\helpers\CMyHtml;

$homeUrl = '/';
$accordionArray = [
    [
        'title' => '测试',
        'icon' => 'icon-bug',
        'data' => [
            [
                'name' => '测试一',
                [
                    'name' => '测试完善用户信息',
                    'icon' => 'icon-bug',
                    'type' => 'navTab',
                    'url' => \yii\helpers\Url::to(['test/user_editinfo']),
                    'target' => 'navTab',
                ],
                [
                    'name' => '测试下单接口',
                    'icon' => 'icon-bug',
                    'type' => 'navTab',
                    'url' => \yii\helpers\Url::to(['test/test_order']),
                    'target' => 'navTab',
                ],
            ],
        ],
    ],
];

$accordionOptions = ['id' => 'navTabMenu'];
$leftPart = CMyHtml::accordionList($accordionArray, $accordionOptions);

$_contentArr = [];
$_contentArr[] = CMyHtml::beginLayout();
$_contentArr[] = CMyHtml::beginPanel('Home page');
$_contentArr[] = CMyHtml::endPanel();
$_contentArr[] = CMyHtml::endLayout();
$containerPart = CMyHtml::beginMainPageTabs(['id'=>'navTab', 'fit'=>'true', 'border'=>'false', 'style' => "width:100%;height:100%", 
    'tabs'=>[['title'=>'Home page', 'content'=>implode("\n", $_contentArr)]]]);
$containerPart .= "\n".CMyHtml::endMainPageTabs();

$htmlArray = [];
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion('200px', '', 'Main menu', 'west');
$htmlArray[] = $leftPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion('100%', '100%', '', 'center');
$htmlArray[] = $containerPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();

echo implode("\n", $htmlArray);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
<?php

$htmlArray = [];
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/jquery-easyui/jquery.min.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/jquery-easyui/jquery.easyui.min.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/jquery-easyui/locale/easyui-lang-zh_CN.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/custom/js/common.custom.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/custom/js/utils.custom.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/custom/js/easyui.custom.js');
$htmlArray[] = yii\bootstrap\Html::jsFile($rootDir.'assets/custom/js/locale/custom.messages-zh_CN.js');
echo implode("\n", $htmlArray);

$arrScripts = [];
$arrScripts[] = "$(function(){ $.custom.uiframework = 'easyui';});";

if (isset(Yii::$app->params['tabs_in_iframe'])) {
    if (Yii::$app->params['tabs_in_iframe']) {
        $arrScripts[] = "if ($.custom.easyui.config) { $.custom.easyui.config.openTabInIframe = true; }";
    }
}

$arrScripts[] = <<<EOD
var loadingDelayTimer;
function navTabShowLoading(){
    if ($("#loading")) {
        $("#loading").fadeOut("normal", function(){
            $(this).remove();
            loadingDelayTimer = undefined;
        });
    }
}

$.parser.onComplete = function(){
    if($.custom.easyui.loadingDelayTimer) {
        clearTimeout(loadingDelayTimer);
    }
    loadingDelayTimer = setTimeout(navTabShowLoading,500);
}
EOD;

echo Html::script(implode("\n", $arrScripts));
?>
