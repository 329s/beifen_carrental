<?php

use common\helpers\CMyHtml;
use yii\helpers\Html;

$autoId = CMyHtml::genID();

$unitTextRMB = Yii::t('locale', 'RMB Yuan');
$unitTextDays = Yii::t('carrental', 'days');

$yiiCsrfKey = Yii::$app->request->csrfParam;
$yiiCsrfToken = Yii::$app->request->getCsrfToken();

$htmlArray = [];
$arrScripts = [];

$scopeConfig = 'ConfigRent';
$urlSaveConfig = yii\helpers\Url::to(['options/config_rent_edit']);

// 服务费用标准
$arrOptionalServices = \common\components\OptionsModule::getOptionalServiceObjectsArray();
$urlSaveServicePrice = \yii\helpers\Url::to(['options/service_price_edit']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Service price standard'), ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$count = 0;
foreach ($arrOptionalServices as $row) {
    if ($count++ % 2 == 0) {
        if ($count > 1) {
            $htmlArray[] = CMyHtml::endTag('div');
        }
        $htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:table-row']);
    }
    $htmlArray[] = CMyHtml::tag('div', $row->name, ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
        \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $row->price, [], 
            [
                'style'=>"width:100px",
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveServicePrice}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', 'id':{$row->id}, 'price':newValue }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$row->unit_name, 
        ['style'=>"display:table-cell;padding:3px 36px 3px 6px;"]
    );
}
if ($count % 2 != 0) {
    $htmlArray[] = CMyHtml::endTag('div');
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);

// 税率设置
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Tax rate settings'), ['style'=>"width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
// 发票税率
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_INVICE_TAX, ['float_value'=>6]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '发票税率', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->float_value, [], 
            [
                'style'=>"width:60px", 'precision'=>2,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'float_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'(%)', 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
// 刷卡费率及封顶金额
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_SWIPE_CARD_TAX, ['float_value'=>0.72, 'int_value'=>0]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '刷卡费率及封顶金额', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->float_value, [], 
            [
                'style'=>"width:60px", 'precision'=>2,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'float_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'(%)'.
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:60px",
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextRMB, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();

// 分期结算设置
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Installment settlement settings'), ['style'=>"width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
// 分期结算周期
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_INSTALLMENT_SETTLEMENT_PERIOD, ['int_value'=>90]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '分期结算周期', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextDays, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
// 提前提醒时间
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_INSTALLMENT_SETTLEMENT_REMIND, ['int_value'=>3]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '提前提醒时间', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextDays, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();

// 取车、还车提前提醒时间设置
$htmlArray[] = CMyHtml::beginPanel('取车、还车提前提醒时间设置', ['style'=>"width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
$htmlArray[] = CMyHtml::tag('div', '', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
// 取车、还车提前提醒时间设置
$arrConfigs = [
    ['prefix'=>'取车、还车在', 'endfix'=>'小时内<font style="background-color:red">红色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CAR_REMIND_RED_HOURS, 'int_value'=>2],
    ['prefix'=>'，至', 'endfix'=>'小时间<font style="background-color:orange">黄色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CAR_REMIND_YELLOW_HOURS, 'int_value'=>4],
    ['prefix'=>'，超过以上时间且在', 'endfix'=>'天内<font style="background-color:blue">蓝色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CAR_REMIND_BLUE_DAYS, 'int_value'=>1],
];
foreach ($arrConfigs as $cfg) {
    $objConfig = \common\models\Pro_config_rent::instanceByType($cfg['name'], ['int_value'=>$cfg['int_value']]);
    if ($objConfig) {
        $htmlArray[] = $cfg['prefix'].
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:60px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$cfg['endfix'];
    }
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();

// 结算差额提醒
$htmlArray[] = CMyHtml::beginPanel('结算差额提醒', ['style'=>"width:100%"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row;width:100%"]);
$htmlArray[] = CMyHtml::tag('div', '', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
// 结算差额提醒
$arrConfigs = [
    ['prefix'=>'实收比应收少收', 'endfix'=>'元以内<font style="background-color:orange">黄色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CHECKOUT_MONEY_REMIND_YELLOW, 'float_value'=>20],
    ['prefix'=>'，至', 'endfix'=>'元间<font style="background-color:pink">粉色提醒</font>，超过以上金额<font style="background-color:red">红色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CHECKOUT_MONEY_REMIND_PINK, 'float_value'=>200],
    ['prefix'=>'；多收', 'endfix'=>'元以上<font style="background-color:green">绿色提醒</font>', 
        'name'=> \common\components\Consts::KEY_CHECKOUT_MONEY_REMIND_GREEN, 'float_value'=>20],
];
foreach ($arrConfigs as $cfg) {
    $objConfig = \common\models\Pro_config_rent::instanceByType($cfg['name'], ['float_value'=>$cfg['float_value']]);
    if ($objConfig) {
        $htmlArray[] = $cfg['prefix'].
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->float_value, [], 
            [
                'style'=>"width:60px", 'precision'=>2,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'float_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$cfg['endfix'];
    }
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();

$htmlArray[] = CMyHtml::endTag('div');

// 计费规则设置
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:block:width:100%"]);
$htmlArray[] = CMyHtml::beginPanel(Yii::t('carrental', 'Charging rule settings'), ['style'=>"width:100%;display:block"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row-group"]);
// 超时计费，忽略时间范围设置
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_OVERTIME_FREE_MINUTES, ['int_value'=>60]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
    $htmlArray[] = CMyHtml::tag('div', '超时计费，忽略时间范围设置', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', '<div>'. Yii::t('locale', 'overtime').
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
                '', $objConfig->int_value, [], 
                [
                    'style'=>"width:100px",
                    'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                ]).'分钟以内不收取超时费用</div><div>超过免费时间后'.
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_RATIOBUTTONLIST, 
                '', $objConfig->flag, [1=>'从超时起开始计费', 2=>'从超出免费范围外部分开始计费'], 
                [
                    //'style'=>"width:200px",
                    'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'flag':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                ]).'</div>', 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
    $htmlArray[] = CMyHtml::endTag('div');
}
$arrChargingRuleOptions = [
    [
        'label' => '超时开始后，“1小时”的计时规则设置',
        'name' => \common\components\Consts::KEY_OVERTIME_1HOUR_MINUTES,
        'int_value' => 30,
        'prefix' => Yii::t('locale', 'overtime'),
        'endfix' => '分钟计为1小时',
    ],
    [
        'label' => '租车、代驾超时，时间计算规则',
        'name' => \common\components\Consts::KEY_OVERTIME_1DAY_HOURS,
        'int_value' => 4,
        'prefix' => Yii::t('locale', 'overtime'),
        'endfix' => '小时按1日(24小时)计算，以内按“小时”计算',
    ],
    [
        'label' => '超时时保险、GPS、座椅，时间计算规则',
        'name' => \common\components\Consts::KEY_OVERTIME_HALF_DAY_HOURS,
        'int_value' => 4,
        'prefix' => Yii::t('locale', 'overtime'),
        'endfix' => '小时内按半日(12小时)计算，以外按1日(24小时)计算',
    ],
];
foreach ($arrChargingRuleOptions as $cfg) {
    $objConfig = \common\models\Pro_config_rent::instanceByType($cfg['name'], ['int_value'=>$cfg['int_value']]);
    if ($objConfig) {
        $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['label'], ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['prefix'].
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
                '', $objConfig->int_value, [], 
                [
                    'style'=>"width:200px",
                    'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                ]).$cfg['endfix'], 
            ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
        $htmlArray[] = CMyHtml::endTag('div');
    }
}
// 超时时超里程，里程、时间计算规则
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_OVERMILEAGE_BY_OVER_HOURS, ['int_value'=>0, 'float_value'=>0]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
    $htmlArray[] = CMyHtml::tag('div', '超时时超里程，里程、时间计算规则', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', Yii::t('locale', 'overtime').
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px",
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'小时即按1日(24小时)计算，以内按每“小时”'.
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->float_value, [], 
            [
                'style'=>"width:100px",
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'float_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'公里的里程限制计算', 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
    $htmlArray[] = CMyHtml::endTag('div');
}

$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%']);

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('预授权提醒设置', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$arrOptions = [
    [
        'label' => '预授权有效期',
        'name' => \common\components\Consts::KEY_POUNDAGE_VALID_PERIOD,
        'int_value' => 22,
        'prefix' => '',
        'endfix' => $unitTextDays,
    ],
    [
        'label' => '提前提醒天数',
        'name' => \common\components\Consts::KEY_POUNDAGE_REMIND_DAYS,
        'int_value' => 3,
        'prefix' => '',
        'endfix' => $unitTextDays,
    ],
];
foreach ($arrOptions as $cfg) {
    $objConfig = \common\models\Pro_config_rent::instanceByType($cfg['name'], ['int_value'=>$cfg['int_value']]);
    if ($objConfig) {
        $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['label'], ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['prefix'].
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
                '', $objConfig->int_value, [], 
                [
                    'style'=>"width:80px", 'precision'=>0,
                    'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                ]).$cfg['endfix'], 
            ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
        $htmlArray[] = CMyHtml::endTag('div');
    }
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('违章处理提醒设置', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$arrOptions = [
    [
        'label' => '违章押金期限',
        'name' => \common\components\Consts::KEY_VIOLATION_POUNDAGE_VALID_PERIOD,
        'int_value' => 330,
        'prefix' => '',
        'endfix' => $unitTextDays,
    ],
    [
        'label' => '提前提醒天数',
        'name' => \common\components\Consts::KEY_VIOLATION_POUNDAGE_REMIND_DAYS,
        'int_value' => 3,
        'prefix' => '',
        'endfix' => $unitTextDays,
    ],
];
foreach ($arrOptions as $cfg) {
    $objConfig = \common\models\Pro_config_rent::instanceByType($cfg['name'], ['int_value'=>$cfg['int_value']]);
    if ($objConfig) {
        $htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['label'], ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
        $htmlArray[] = CMyHtml::tag('div', $cfg['prefix'].
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
                '', $objConfig->int_value, [], 
                [
                    'style'=>"width:80px", 'precision'=>0,
                    'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
                ]).$cfg['endfix'], 
            ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
        $htmlArray[] = CMyHtml::endTag('div');
    }
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%']);

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('违章押金设置', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_VIOLATION_POUNDAGE_AMOUNT, ['int_value'=>0]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '违章押金数额', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextRMB, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('车辆保养提醒', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
//$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-row"]);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_VEHICLE_MAINTENANCE_REMIND_DAYS, ['int_value'=>6]);
if ($objConfig ) {
    $htmlArray[] = '按时间周期提前'.
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextDays;
}
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_VEHICLE_MAINTENANCE_REMIND_MILEAGE, ['int_value'=>800]);
if ($objConfig ) {
    $htmlArray[] = '，按里程提前'.
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).'公里提醒';
}
$htmlArray[] = CMyHtml::endTag('div');
//$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'display:block;width:100%']);

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('年检提醒设置', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_VEHICLE_YEAR_CHECK_REMIND_DAYS, ['int_value'=>30]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '提前提醒时间', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextDays, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::beginTag('div', ['style'=>'width:50%;float:left']);
$htmlArray[] = CMyHtml::beginPanel('续保提醒设置', ['width'=>'100%']);
$htmlArray[] = CMyHtml::beginTag('div', ['style'=>"display:table"]);
$objConfig = \common\models\Pro_config_rent::instanceByType(\common\components\Consts::KEY_VEHICLE_YEAR_CHECK_REMIND_DAYS, ['int_value'=>30]);
if ($objConfig) {
    $htmlArray[] = CMyHtml::tag('div', '提前提醒时间', ['style'=>"display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;"]);
    $htmlArray[] = CMyHtml::tag('div', 
            \common\helpers\CEasyUI::inputField(CMyHtml::INPUT_NUMBERBOX, 
            '', $objConfig->int_value, [], 
            [
                'style'=>"width:100px", 'precision'=>0,
                'onChange'=>"function(newValue, oldValue) { easyuiFuncAjaxSendData('{$urlSaveConfig}', 'post', {'{$yiiCsrfKey}':'{$yiiCsrfToken}', '{$scopeConfig}':{'id':{$objConfig->id}, 'int_value':newValue} }, easyuiFuncNavTabRefreshCurTab); }",
            ]).$unitTextDays, 
        ['style'=>"display:table-cell;padding:3px 16px 3px 6px;"]);
}
$htmlArray[] = CMyHtml::endTag('div');
$htmlArray[] = CMyHtml::endPanel();
$htmlArray[] = CMyHtml::endTag('div');

$htmlArray[] = CMyHtml::endTag('div');

$arrScripts[] = <<<EOD

EOD;

$htmlArray[] = Html::script(implode("\n", $arrScripts));

echo implode("\n", $htmlArray);