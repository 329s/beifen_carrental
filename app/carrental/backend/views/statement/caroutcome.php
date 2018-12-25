<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true', 'justified'=>'true', 'pill'=>'false']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false', 'style' => 'padding:16px'];

$arrCostTypes = \common\components\VehicleModule::getVehicleExpenditureTypesArray();

foreach ($arrCostTypes as $type => $text) {
    $htmlArray[] = CMyHtml::beginTabsChild($text, array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['statement/caroutcome_index', 'type'=>$type]), 'style' => 'padding:0px']));
    $htmlArray[] = CMyHtml::endTabsChild();
}


$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);