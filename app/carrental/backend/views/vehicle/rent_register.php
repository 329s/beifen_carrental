<?php

use common\helpers\CMyHtml;

$htmlArray = [];

$htmlArray[] = CMyHtml::beginTabs(['fit'=>'true']);

$tabsChildHtmlOptions = ['fit'=>'true', 'method'=>"'get'", 'closable'=>'false'];

$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('locale', 'Personal driving'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['order/edit', 'vehicle_id'=>$vehicleId, 'id'=>$orderId, 'type'=>  \common\models\Pro_vehicle_order::TYPE_PERSONAL])]));
$htmlArray[] = CMyHtml::endTabsChild();

//$htmlArray[] = CMyHtml::beginTabsChild(Yii::t('locale', 'Enterprise driving'), array_merge($tabsChildHtmlOptions, ['href'=>\yii\helpers\Url::to(['order/edit', 'vehicle_id'=>$vehicleId, 'id'=>$orderId, 'type'=>  \common\models\Pro_vehicle_order::TYPE_ENTERPRISE])]));
//$htmlArray[] = CMyHtml::endTabsChild();

$htmlArray[] = CMyHtml::endTabs();

echo implode("\n", $htmlArray);
