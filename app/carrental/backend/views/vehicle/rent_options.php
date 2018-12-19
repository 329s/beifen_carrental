<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false', 'style' => 'padding:16px'];

$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('carrental', 'Vehicle contract settings'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['options/rent_contract_options']), 'style' => 'padding:0px']));
$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);
