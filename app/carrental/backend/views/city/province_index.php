<?php

use common\helpers\CMyHtml;

$cityData = \common\components\CityModule::getCityTreeData();

$arrData = [];
foreach ($cityData as $key => $value) {
    $arrData[] = \common\components\CityModule::_genCityTreeChildrenData($key, $value, true);
}

$flagEnabled = \common\models\Pro_city::STATUS_NORMAL;
$flagDisabled = \common\models\Pro_city::STATUS_DISABLED;

$urlAlterStatus = \yii\helpers\Url::to(['city/alterstatus', 'skip_notify_msg'=>'true']);

$strTreeData = \common\helpers\CEasyUI::convertComboTreeDataToString($arrData);
$strOnCheckFunc = <<<EOD
function (node, checked) {
    var url = '{$urlAlterStatus}&id='+node.id+'&status=';
    if (checked) { url += {$flagEnabled}; }
    else { url += {$flagDisabled}; }
    easyuiFuncQueryUrlAjax(url, '', 'get', easyuiFuncNavTabRefreshCurTab);
}
EOD;

$htmlArray = [];
$htmlArray[] = CMyHtml::beginPanel(Yii::t('locale', 'City enable/disable management'), ['data-options'=>"fit:true"]);
$htmlArray[] = CMyHtml::treeList([], ['data-options'=>
    "checkbox:true,lines:true,data:{$strTreeData},onCheck:{$strOnCheckFunc},cascadeCheck:false"]);
$htmlArray[] = CMyHtml::endPanel();

echo implode("\n", $htmlArray);
