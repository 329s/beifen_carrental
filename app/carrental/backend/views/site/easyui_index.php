<?php

use common\helpers\CMyHtml;

$homeUrl = '/';
$accordionArray = [];
$isNotSelected = true;
$mainTabsId = CMyHtml::getIDPrefix().'navtab'.CMyHtml::genID();
if ($arrAuth) {
    foreach ($arrAuth as $k => $first) {
        $accordionPanel = [];
        $accordionPanel['title'] = $first['o']['description'];
        $accordionPanel['icon'] = $first['o']['icon_traditional'];
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
            $subArray['icon'] = $second['o']['icon_traditional'];
            $subArray['type'] = 'navTab';
            $subArray['target'] = $mainTabsId;
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

$leftPart = implode("\n", $accordionHtmlArray);

$_contentArr = [];
$_contentArr[] = CMyHtml::beginLayout();
$_contentArr[] = CMyHtml::beginPanel(Yii::t('locale', 'Home Page'));
$_contentArr[] = CMyHtml::endPanel();
$_contentArr[] = CMyHtml::endLayout();
$containerPart = CMyHtml::beginMainPageTabs(['id'=>$mainTabsId, 'fit'=>'true', 'border'=>'false', 'style' => "width:100%;height:100%", 
    'tabs'=>[['title'=>Yii::t('locale', 'Home Page'), 'href'=>\yii\helpers\Url::to(['site/homepanel'])]]]);
$containerPart .= "\n".CMyHtml::endMainPageTabs();

$htmlArray = [];

$htmlArray[] = CMyHtml::beginMainPageLayoutRegion('200px', '', Yii::t('locale', 'Main Menu'), 'west');
$htmlArray[] = $leftPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();
$htmlArray[] = CMyHtml::beginMainPageLayoutRegion('100%', '100%', '', 'center');
$htmlArray[] = $containerPart;
$htmlArray[] = CMyHtml::endMainPageLayoutRegion();

echo implode("\n", $htmlArray);
