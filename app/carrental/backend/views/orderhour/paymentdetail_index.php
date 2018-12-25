<?php

use common\helpers\CMyHtml;

$urlInfo = ['order/paymentdetail_list', 'serial'=>$serial];

$toolbarArray = [
    CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'serial', Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')]), $serial, []),
];

$urlsArray = [
    'url' => \yii\helpers\Url::to($urlInfo),
];

$columnFields = ['id', 'order_id', 'belong_office_id', 'relet_mark', 'status', 'time', 'edit_user_id', 'created_at', 
    'pay_source', 'deposit_pay_source', 'summary_amount', 'summary_deposit', 'remark', 
    'price_rent', 'price_overtime', 'price_overmileage', 'price_designated_driving', 'price_designated_driving_overtime',
    'price_designated_driving_overmileage', 'price_oil', 'price_oil_agency', 'price_car_damage', 'price_violation',
    'price_poundage', 'price_basic_insurance', 'price_deposit', 'price_deposit_violation', 'price_optional_service',
    'price_insurance_overtime', 'price_take_car', 'price_return_car', 'price_working_loss', 
    'price_accessories', 'price_agency', 'price_other', 'operation'];

$toolbarArray[] = CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_BUTTON, Yii::t('locale', 'Retrieval'), '', '', []);

echo CMyHtml::datagrid('   ', // $title
    new \common\models\Pro_vehicle_order_price_detail(),    // $model
    $columnFields,            // $columns
    [],            // $dataArray
    '100%', '100%',     // $width, $height
    [],            // $htmlsOptions,
    $urlsArray, $toolbarArray,   // $urlsArray, $toolbarArray
    0, 0                // $frozenColumnIndex, $frozenRowIndex
);
