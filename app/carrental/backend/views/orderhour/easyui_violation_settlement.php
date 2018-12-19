<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false', 'style' => ''];

//$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('carrental', 'Renting vehicle edit'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['order/edit', 'vehicle_id'=>$vehicleId, 'id'=>$orderId])]));
//$htmlArray[] = CMyHtml::endTabsChild();

//$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('carrental', 'Return vehicle validation'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['vehicle/validation', 'purpose'=>'vehicle_validation', 'vehicle_id'=>$vehicleId, 'order_id'=>$orderId])]));
//$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);
