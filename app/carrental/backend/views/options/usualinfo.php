<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false', 'style' => 'padding:16px'];

$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('locale', 'App initialize info'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['options/app_initial_index']), 'style' => 'padding:0px']));
$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);
