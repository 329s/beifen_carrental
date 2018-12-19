<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false', 'style' => 'padding:16px'];

$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('carrental', 'Vehicle validation settings'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['options/vehicle_validation_options_index']), 'style' => 'padding:0px']));
$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('carrental', 'Vehicle related settings'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['options/vehicle_options_index']), 'style' => 'padding:0px']));
$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);
