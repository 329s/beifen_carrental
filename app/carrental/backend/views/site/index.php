<?php

use common\helpers\CMyHtml;

$homeUrl = '/';
$accordionArray = [];
$isNotSelected = true;
//$targetId = CMyHtml::getIDPrefix().'navtab'.CMyHtml::genID();
$targetId = \common\helpers\BootstrapHtml::MAIN_CONTENT_ID;
if ($arrAuth) {
    foreach ($arrAuth as $k => $first) {
        $accordionPanel = [];
        $accordionPanel['title'] = $first['o']['description'];
        $accordionPanel['icon'] = $first['o']['icon'];
        $accordionPanel['column_code'] = $first['o']['name'];

        if ($isNotSelected) {
            $accordionPanel['selected'] = true;
            $isNotSelected = false;
        }

        $accordionPanel['tools'] = ['icon' => 'icon-reload', 'handler' => ""];

        $childArray = [];
        foreach ($first['children'] as $second) {
            $subArray = [];
            $subArray['name'] = $second['o']['description'];
            $subArray['icon'] = $second['o']['icon'];
            $subArray['type'] = 'navTab';
            $subArray['target'] = $targetId;
            $subArray['url'] = \yii\helpers\Url::to([$homeUrl.$second['o']['href']]);
            $subArray['tabPanelId'] = $second['o']['name'];
            $subArray['isIframe'] = ($second['o']['target'] == '_blank' ? true : false);

            $childArray[] = $subArray;
        }

        $accordionPanel['data'] = $childArray;

        $accordionArray[] = $accordionPanel;
    }
}

// format accordion here
$accordionHtmlArray = [];
$_linkbuttonCustomStyle = <<<EOD
.l-custom-large-icon .l-btn-large .l-btn-icon {
  width: 50px; height:50px; line-height: 50px; margin-top:-25px
}
.l-custom-large-icon .l-btn-large .l-btn-icon-top .l-btn-icon {
  margin: 0 0 0 -25px;
}
.l-custom-large-icon .l-btn-large .l-btn-icon-top .l-btn-text {
  margin-top: 54px; font-weight: bold;
}
EOD;
$accordionHtmlArray[] = yii\bootstrap\Html::style($_linkbuttonCustomStyle);
$accordionHtmlArray[] = CMyHtml::beginTag('div', ['class'=>'easyui-accordion', 'encode'=>false, 'data-options'=>"fit:true,onLoad:function(){}"]);
foreach ($accordionArray as $accordionInfo) {
    $_dataOptions = [];
    if (isset($accordionInfo['icon'])) {
        $_dataOptions[] = "iconCls:'{$accordionInfo['icon']}'";
    }
    if (isset($accordionInfo['selected']) && $accordionInfo['selected']) {
        $_dataOptions[] = "selected:true";
    }
    if (isset($accordionInfo['tools'])) {
        $tools = $accordionInfo['tools'];
        $toolsConfig = [];
        if (!isset($tools[0]) && (isset($tools['icon']) || isset($tools['handler']))) {
            $tools = [$tools];
        }
        foreach ($tools as $_idx => $tool) {
            if (is_string($_idx)) {
                continue;
            }
            $_cfg = [];
            $_cfg[] = "iconCls:'" . (isset($tool['icon']) ? $tool['icon'] : 'icon-cmy') . "'";
            $_cfg[] = 'handler:function(){' . (isset($tool['handler']) ? $tool['handler'] : '') . '}';
            $toolsConfig[] = '{' . implode(',', $_cfg) . '}';
        }
        $_dataOptions[] =  'tools:[' . implode(',', $toolsConfig) . ']';
    }
    $accordionHtmlArray[] = CMyHtml::beginTag('div', ['title'=>$accordionInfo['title'], 'data-options'=>implode(',', $_dataOptions), 'encode'=>false, ]);
    
    if (isset($accordionInfo['data'])) {
        $accordionHtmlArray[] = CMyHtml::beginTag('div', ['class'=>'l-custom-large-icon']);
        foreach ($accordionInfo['data'] as $row) {
            $_dataOptions = ["size:'large',iconAlign:'top',plain:true,toggle:true,group:'nav_btn_group_{$accordionInfo['column_code']}'"];
            $_dataOptions[] = "iconCls:'{$row['icon']}'";
            $funcName = 'easyuiFuncNavTabAddHref';
            if (\common\helpers\Utils::boolvalue($row['isIframe'])) {
                $funcName = 'easyuiFuncNavTabAddIframe';
            }

            $btnHtmlOptions = [
                'class' => 'easyui-linkbutton',
                'style' => 'color:#0A246A; align:center; display:block; margin: 6px 0px 6px 0px', 
                'href' => 'javascript:void(0);',
                'onclick' => "{$funcName}('#{$row['target']}', '{$row['name']}', '{$row['url']}', '{$row['tabPanelId']}');",
                //'onclick' => "easyuiFuncDebugThisValue(this)",
                'data-options' => implode(',', $_dataOptions),
                'encode' => false,
            ];
            $accordionHtmlArray[] = CMyHtml::tag('a', $row['name'], $btnHtmlOptions);
        }
        $accordionHtmlArray[] = CMyHtml::endTag('div');
    }
    $accordionHtmlArray[] = CMyHtml::endTag('div');
}
$accordionHtmlArray[] = CMyHtml::endTag('div'); // end div class easyui-accordion

//$leftPart = implode("\n", $accordionHtmlArray);

$leftPart = common\helpers\BootstrapAdminHtml::accordionList($accordionArray, ['title'=> Yii::t('locale', 'Main Menu')]);

$homePageUrl = \yii\helpers\Url::to(['site/homepanel']);

$_contentArr = [];
$_contentArr[] = CMyHtml::beginLayout();
$_contentArr[] = CMyHtml::beginPanel(Yii::t('locale', 'Home Page'));
$_contentArr[] = CMyHtml::endPanel();
$_contentArr[] = CMyHtml::endLayout();
$containerPart = CMyHtml::beginMainPageTabs(['id'=>$targetId, 'fit'=>'true', 'border'=>'false', 'style' => "width:100%;height:100%", 
    'tabs'=>[['title'=>Yii::t('locale', 'Home Page'), 'href'=>$homePageUrl]]]);
$containerPart .= "\n".CMyHtml::endMainPageTabs();

$htmlArray = [];

$htmlArray[] = \common\helpers\BootstrapAdminHtml::beginSidebar();
$htmlArray[] = $leftPart;
$htmlArray[] = \common\helpers\BootstrapAdminHtml::endSidebar();
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'content-wrapper']);
$htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'content', 'id'=>$targetId, 'style'=>"overflow:auto;"]);
//$htmlArray[] = $containerPart;
$htmlArray[] = \yii\helpers\Html::endTag('div');
$htmlArray[] = \yii\helpers\Html::endTag('div');

$contentOffset = 80;
$scripts = [];
$scripts[] = \common\helpers\BootstrapAdminHtml::getRegisterFixFluidWidowLayoutJs($targetId);
$scripts[] = "$(function () {";
$scripts[] = "$.custom.bootstrap.loadElement('#{$targetId}', '$homePageUrl');";
$scripts[] = "});";
$this->registerJs(implode("\n", $scripts));

echo implode("\n", $htmlArray);
