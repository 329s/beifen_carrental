<?php
namespace backend\widgets;

use common\helpers\CMyHtml;

/* 
 * The order editor widget.
 */
class OrderEditorEasyuiWidget extends \yii\base\Widget
{
    /**
     * @var string $action insert|update 
     */
    public $action;
    /**
     *
     * @var string $title default empty string
     */
    public $title = '';
    /**
     *
     * @var object $vehicleOrder Model
     */
    public $vehicleOrder;
    
    public $orderType;
    
    public $vehicleModelId;
    public $vehicleId;
    public $userId;
    public $account;
    public $infoaccount;
    
    public $orderReadonly = false;
    public $customerReadonly = false;
    public $disableSubmit = false;

    private $_idPrefix;
    private $_autoId;
    private $_orderModel;
    private $_formModel;
    private $_vehicleModel;
    private $_userInfoModel;
    private $_urlGetVehicleList;
    private $_urlSearchUser;
    private $_urlSearchVehicleModel;
    private $_urlGetPrice;
    private $_urlGetPreferentialData;
    private $_urlGetUserOrders;
    private $_urlPrintBookingOrder;
    private $_urlPrintDispatchOrder;
    private $_urlPrintValidationOrder;
    private $_urlPrintSettlementOrder;
    private $_urlPaymentinput;
    private $_urlGetOptionalPrices;
    private $_urlServicePriceBetweenOffice;
    private $_urlServicePriceByAddress2Office;
    private $_formId;
    private $_paymentModalId;
    private $_inputsArray = [];
    private $_curTime;
    private $_vehicleQueryParams = [];
    
    private $_enableSettlement = false;
    private $_enableReplaceVehicle = false;
    private $_isAdmin = false;
    private $_curOfficeId = null;

    private $_printerClass;
    private $_rootUrl;
    
    private $_myOrderTypesArray = [];

    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init()
    {
        parent::init();
        $this->_autoId = CMyHtml::genID();
        if (!isset($this->action) || empty($this->action)) {
            $this->action = 'update';
        }
        if (!isset($this->orderType) || empty($this->orderType)) {
            $this->orderType = \common\models\Pro_vehicle_order::TYPE_PERSONAL;
        }
        
        $this->_rootUrl = \common\helpers\Utils::getRootUrl();
        $this->_curOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        $this->_isAdmin = false;
        if ($this->_curOfficeId <= 0) {
            if ($this->_curOfficeId == \common\components\OfficeModule::HEAD_OFFICE_ID) {
                $this->_isAdmin = true;
            }
            $this->_curOfficeId = null;
        }
        $this->_vehicleQueryParams = [];
        
        $this->_orderModel = isset($this->vehicleOrder) ? $this->vehicleOrder : null;
        if (!$this->_orderModel) {
            $this->_orderModel = new \common\models\Pro_vehicle_order();
            $this->action = 'insert';
            $this->_orderModel->type = $this->orderType;
            $this->_orderModel->pay_type = \common\models\Pro_vehicle_order::PRICE_TYPE_OFFICE;
            $this->_orderModel->status = \common\models\Pro_vehicle_order::STATUS_BOOKED;
            $this->_orderModel->source = \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE;
            if ($this->_curOfficeId && $this->_curOfficeId > 0) {
                $this->_orderModel->belong_office_id = $this->_curOfficeId;
            }
        }
        else {
            if ($this->_orderModel->status < \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                $this->_orderModel->status = \common\models\Pro_vehicle_order::STATUS_BOOKED;
            }

            $this->_vehicleQueryParams['vehicle_model_id'] = $this->_orderModel->vehicle_model_id;
            $this->_vehicleQueryParams['start_time'] = $this->_orderModel->start_time;
            $this->_vehicleQueryParams['end_time'] = $this->_orderModel->end_time;
            $this->_vehicleQueryParams['pay_type'] = $this->_orderModel->pay_type;
            $this->_vehicleQueryParams['skip_order_id'] = $this->_orderModel->id;
            
            if ($this->_orderModel->vehicle_id) {
                $this->_vehicleQueryParams['vehicle_id'] = $this->_orderModel->vehicle_id;
                //if ($this->orderReadonly || $this->_orderModel->status >= \common\models\Pro_vehicle_order::STATUS_RENTING) {
                //    $this->_vehicleQueryParams['vehicle_id'] = $this->_orderModel->vehicle_id;
                //}
            }


            $cdb = \common\models\Pub_user::find();
            $cdb->where(['info_id'=>$this->_orderModel->user_id]);
            $infoaccount = $cdb->asarray()->one();
            $this->account = $infoaccount['account'];
        }
        if (isset($this->vehicleModelId) && $this->vehicleModelId && !isset($this->_vehicleQueryParams['vehicle_model_id'])) {
            $this->_vehicleQueryParams['vehicle_model_id'] = $this->vehicleModelId;
        }
        if (empty($this->vehicleId)) {
            if ($this->_orderModel->vehicle_id) {
                $this->vehicleId = $this->_orderModel->vehicle_id;
            }
            else {
                $this->vehicleId = 0;
            }
        }
        $this->_vehicleModel = \common\models\Pro_vehicle::findById($this->vehicleId);
        if ($this->userId) {
            $this->_userInfoModel = \common\models\Pub_user_info::findById($this->userId);
        }
        if (!$this->_userInfoModel) {
            $this->_userInfoModel = new \common\models\Pub_user_info();
        }
        
        if ($this->action == 'settlement' 
            || ($this->_orderModel->status >= \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING
                && $this->_orderModel->status <= \common\models\Pro_vehicle_order::STATUS_COMPLETED)) {
            $this->_enableSettlement = true;
        }
        
        $curTime = time();
        $this->_urlGetVehicleList = \yii\helpers\Url::to(['vehicle/nonbooked_vehicle_list', '_'=>$curTime]);
        $this->_urlSearchUser = \yii\helpers\Url::to(['user/searchuserslike', 'name'=>'']);
        $this->_urlSearchVehicleModel = \yii\helpers\Url::to(['api/search-vehicle-model', 'name'=>'']);
        $this->_urlGetPrice = \yii\helpers\Url::to(['order/get_order_price', '_'=>$curTime]);
        $this->_urlGetPreferentialData = \yii\helpers\Url::to(['options/preferential_combo_data']);
        $this->_urlGetUserOrders = \yii\helpers\Url::to(['order/userrentlist_index', '_'=>$curTime]);
        $this->_urlPrintBookingOrder = \yii\helpers\Url::to(['print/booking_vehicle_order', '_'=>$curTime]).'&id=';
        $this->_urlPrintDispatchOrder = \yii\helpers\Url::to(['print/dispatch_vehicle_order', '_'=>$curTime]).'&id=';
        $this->_urlPrintValidationOrder = \yii\helpers\Url::to(['print/validation_vehicle_order', '_'=>$curTime]).'&id=';
        $this->_urlPrintSettlementOrder = \yii\helpers\Url::to(['print/settlement_vehicle_order', '_'=>$curTime]).'&id=';
        $this->_urlPaymentinput = \yii\helpers\Url::to(['order/paymentinput', 'is_relet'=>0, 'is_settlement'=>$this->_enableSettlement?1:0, 'order_action'=> $this->action]).'&order_id=';
        $this->_urlGetOptionalPrices = \yii\helpers\Url::to(['api/optional_service_prices']);
        $this->_urlServicePriceBetweenOffice = \yii\helpers\Url::to(['api/service_price_between_office']);
        $this->_urlServicePriceByAddress2Office = \yii\helpers\Url::to(['api/service_price_by_address2office']);
        $this->_idPrefix = self::$autoIdPrefix.'_'.CMyHtml::getIDPrefix();
        $this->_formId = "{$this->_idPrefix}orderform{$this->_autoId}";
        $this->_paymentModalId = "{$this->_idPrefix}paymentinput{$this->_autoId}";
        $this->_inputsArray = [];
        $this->_formModel = new \common\models\Form_pro_vehicle_order();
        $this->_printerClass = self::$autoIdPrefix."_cls_printer_{$this->_autoId}";
        
        /*if ($this->_orderModel->status == \common\models\Pro_vehicle_order::STATUS_RENTING) {
            $this->_enableReplaceVehicle = true;
        }
        else {
            $this->_enableReplaceVehicle = false;
        }*/
        
        if (!empty($this->_orderModel->start_time)) {
            $this->_curTime = $this->_orderModel->start_time;
        }
        else {
            $this->_curTime = strtotime(date('Y-m-d H').':00:00');
        }
        
        $this->_myOrderTypesArray = [
            \common\models\Pro_vehicle_order::TYPE_PERSONAL => \Yii::t('carrental', 'Personal'),
            \common\models\Pro_vehicle_order::TYPE_ENTERPRISE => \Yii::t('carrental', 'Corporate'),
        ];
    }

    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $inputs = [];
        $hiddenFields = [];
        $arrScripts = [];
        $arrScripts[] = $this->renderScriptsContent();
        
        if ($this->_enableSettlement) {
            $this->appendArray($inputs, $this->getSettlementInputFieldsInfo());
        }
        else {
            $hiddenFields[] = ['value'=>$this->_orderModel->price_overtime, 'id'=>"{$this->_idPrefix}settlement_price_overtime{$this->_autoId}"];
            //$hiddenFields[] = ['value'=>$this->_orderModel->price_overmileage, 'id'=>"{$this->_idPrefix}settlement_price_overmileage{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_insurance_overtime, 'id'=>"{$this->_idPrefix}settlement_price_insurance_overtime{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_oil, 'id'=>"{$this->_idPrefix}settlement_price_oil{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_oil_agency, 'id'=>"{$this->_idPrefix}settlement_price_oil_agency{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_other, 'id'=>"{$this->_idPrefix}settlement_price_other{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_car_damage, 'id'=>"{$this->_idPrefix}settlement_price_car_damage{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_violation, 'id'=>"{$this->_idPrefix}settlement_price_violation{$this->_autoId}"];
            //$hiddenFields[] = ['value'=>$this->_orderModel->price_designated_driving, 'id'=>"{$this->_idPrefix}settlement_price_designated_driving{$this->_autoId}"];
            //$hiddenFields[] = ['value'=>$this->_orderModel->price_designated_driving_overtime, 'id'=>"{$this->_idPrefix}settlement_price_designated_driving_overtime{$this->_autoId}"];
            //$hiddenFields[] = ['value'=>$this->_orderModel->price_designated_driving_overmileage, 'id'=>"{$this->_idPrefix}settlement_price_designated_driving_overmileage{$this->_autoId}"];
            $hiddenFields[] = ['value'=>$this->_orderModel->price_preferential, 'id'=>"{$this->_idPrefix}settlement_price_preferential{$this->_autoId}"];
            //$hiddenFields[] = ['value'=>$this->_orderModel->price_free, 'id'=>"{$this->_idPrefix}settlement_price_free{$this->_autoId}"];
        }
        
        $this->appendArray($inputs, $this->getCustomerInputFieldsInfo());
        $this->appendArray($inputs, $this->getVehicleInputFieldsInfo());
        $this->appendArray($inputs, $this->getOptionalServicesInputFieldsInfo());
        $this->appendArray($inputs, $this->getInvoiceInputFieldsInfo());
        $this->appendArray($inputs, $this->getRefundInputFieldsInfo());
        
        $printButtons = [];
        $buttons = [];
        if ($this->disableSubmit) {
        }
        else {
            if ($this->action == 'insert') {
                $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('carrental', 'booking'), 'icon'=>'icon-ok', 'params'=>"{next_action:'print_booking'}", 'successCallback'=>"funcOnSubmitSuccessEvents{$this->_autoId}"];
                $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('carrental', 'Dispatch vehicle'), 'icon'=>'icon-car', 'params'=>"{next_action:'dispatch_vehicle'}", 'successCallback'=>"funcOnSubmitSuccessEvents{$this->_autoId}"];
            }
            else {
                if (!$this->_enableSettlement) {
                    //sjj 预定车辆列表 在租车辆列表订单编辑
                    $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('locale', 'Save'), 'icon'=>'icon-ok', 'params'=>"{next_action:''}"];
                }
                if ($this->_orderModel->status == \common\models\Pro_vehicle_order::STATUS_BOOKED) {
                    $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('carrental', 'Dispatch vehicle'), 'icon'=>'icon-car', 'params'=>"{next_action:'dispatch_vehicle'}", 'successCallback'=>"funcOnSubmitSuccessEvents{$this->_autoId}"];
                    $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/booking_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print booking vehicle order')];
                    //$printButtons[] = ['href'=>\yii\helpers\Url::to(['print/dispatch_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print dispatch vehicle order')];
                }
                else if ($this->_orderModel->status == \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/dispatch_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print dispatch vehicle order')];
                    $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/validation_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print validation vehicle order')];
                    //sjj2017-6-30在线租车编辑订单点击价格详情
                    // $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/settlement_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print settlement vehicle order')];
                    //sjj
                    if ($this->_enableSettlement) {
                        $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('locale', 'Settlement'), 'icon'=>'icon-car', 'params'=>"{next_action:'settlement_order'}", 'successCallback'=>"funcOnSubmitSuccessEvents{$this->_autoId}"];
                    }
                    //$printButtons[] = ['href'=>\yii\helpers\Url::to(['print/settlement_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print settlement vehicle order')];
                }
                else if ($this->_orderModel->status >= \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
                    //sjj历史结算编辑订单
                    $buttons[] = ['type'=>'submit', 'label'=>\Yii::t('locale', 'Save'), 'icon'=>'icon-ok', 'params'=>"{next_action:''}"];
                    $printButtons[] = ['href'=>\yii\helpers\Url::to(['print/settlement_vehicle_order', 'id'=>$this->_orderModel->id]), 'label'=>  \Yii::t('carrental', 'Print settlement vehicle order')];
                }
            }
        }
                        
        $hiddenFields['action'] = $this->action;
        $hiddenFields[$this->_formModel->fieldName('user_id')] = ['value'=>($this->_orderModel && $this->_orderModel->user_id ? $this->_orderModel->user_id : ($this->userId ? $this->userId : 0)), 'id'=>"{$this->_idPrefix}user_id{$this->_autoId}"];
        $hiddenFields[$this->_formModel->fieldName('preferential_type')] = ['value'=>(empty($this->_orderModel->preferential_type) ? '' : $this->_orderModel->preferential_type), 'id'=>"{$this->_idPrefix}preferential_type{$this->_autoId}"];
        $hiddenFields[$this->_formModel->fieldName('vehicle_model_id')] = ['value'=>(empty($this->_orderModel->vehicle_model_id) ? (isset($this->vehicleModelId) ? $this->vehicleModelId : '') : $this->_orderModel->vehicle_model_id), 'id'=>"{$this->_idPrefix}vehicle_model_id{$this->_autoId}"];
        if ($this->action != 'insert' && $this->vehicleOrder) {
            if (empty($this->vehicleId)) {
                $this->vehicleId = $this->vehicleOrder->vehicle_id;
            }
            $hiddenFields['id'] = $this->_orderModel->id;
            $hiddenFields[$this->_formModel->fieldName('id')] = $this->_orderModel->id;
            $hiddenFields[$this->_formModel->fieldName('serial')] = $this->_orderModel->serial;
            //$hiddenFields[$this->_formModel->fieldName('customer_id_type')] = $this->_orderModel->customer_id_type;
            $hiddenFields[$this->_formModel->fieldName('customer_operator_name')] = ['id'=>"{$this->_idPrefix}customer_operator_name{$this->_autoId}", 'value'=>$this->_orderModel->customer_operator_name];
        }
        else {
            $hiddenFields['id'] = '';
            $hiddenFields[$this->_formModel->fieldName('id')] = '';
            //$hiddenFields[$this->_formModel->fieldName('customer_id_type')] = \common\components\Consts::ID_TYPE_IDENTITY;
            $hiddenFields[$this->_formModel->fieldName('customer_operator_name')] = ['id'=>"{$this->_idPrefix}customer_operator_name{$this->_autoId}", 'value'=>''];
        }
        if (!isset($this->_myOrderTypesArray[$this->_orderModel->type])) {
            $hiddenFields[$this->_formModel->fieldName('type')] = $this->_orderModel->type;
        }
        
        if ($this->orderReadonly || $this->disableSubmit) {
            $hiddenFields['optional_service_readonly'] = 1;
        }
        
        if (!$this->_isAdmin) {
            $hiddenFields[$this->_formModel->fieldName('belong_office_id')] = ['id'=>"{$this->_idPrefix}belong_office_id_hidden{$this->_autoId}", 'value'=>intval($this->_orderModel->belong_office_id), 'onchange'=>"funcUpdateOptionalServicePrices{$this->_autoId}($(this).val())"];
        }
        
        $buttons['html'] = \yii\bootstrap\Html::tag('a', \Yii::t('locale', '{type} price', ['type'=>\Yii::t('locale', 'Total')]).':', ['href'=>"javascript:void(0)"]).
            \yii\bootstrap\Html::tag('a', $this->_orderModel->total_amount, ['id'=>"{$this->_idPrefix}ex_total_amount{$this->_autoId}", 'title'=>'', 'data-toggle'=>'popover', 'href'=>"javascript:void(0)"]);
        
        $htmlArray = [];
        
        $htmlArray[] = \yii\helpers\Html::jsFile("{$this->_rootUrl}app/carrental/backend/web/js/orderhelper.js");
        
        $headerId = "{$this->_idPrefix}form_header_{$this->_autoId}";
        $formHtmlOptions = ['id'=>$this->_formId];
        $formHtmlOptions['header'] = $headerId;
        if (!$this->_enableSettlement) {
            $formHtmlOptions['onOpen'] = "funcInitializeElements{$this->_autoId}";
        }
        $formHtmlOptions['onSubmitCallback'] = "funcOnSubmitCheck{$this->_autoId}";
        $htmlArray[] = CMyHtml::form($this->title, \yii\helpers\Url::to(['order/edit']), 'post', $formHtmlOptions, $inputs, $buttons, $hiddenFields);
        
        // header html
        $htmlArray[] = CMyHtml::beginTag('div', ['id'=>$headerId, 'style'=>"text-align:right"]);
        
        //$paymentInputUrl = \yii\helpers\Url::to(['order/paymentinput', 'order_id'=>empty($this->_orderModel->id) ? '0' : $this->_orderModel->id]);
        //$htmlArray[] = CMyHtml::tag('a', \Yii::t('locale', 'Payment'), ['href'=>"javascript:void(0)", 'class'=>"easyui-linkbutton", 'data-options'=>"iconCls:'icon-money'", 'onclick'=>"$.custom.bootstrap.showModal('#{$this->_paymentModalId}', '{$paymentInputUrl}')", 'encode'=>false]);
        $htmlArray[] = $this->generatePaymentButton();
        if (!empty($printButtons)) {
            foreach ($printButtons as $cfg) {
                $htmlArray[] = CMyHtml::tag('a', $cfg['label'], ['href'=>$cfg['href'], 'class'=>"easyui-linkbutton {$this->_printerClass}", 'data-options'=>"iconCls:'icon-printer'", 'encode'=>false]);
            }
        }
        $htmlArray[] = CMyHtml::endTag('div');
        
        $htmlArray[] = \common\helpers\BootstrapHtml::dialog(['id'=>$this->_paymentModalId]);
        
        $htmlArray[] = \yii\helpers\Html::script(implode("\n", $arrScripts));

        return implode("\n", $htmlArray);
    }
    
    protected function appendArray(&$inputs, $arr) {
        foreach ($arr as $e) {
            $inputs[] = $e;
        }
    }
    
    protected function getCustomerInputFieldsInfo() {
        $customerDefaultOptions = [];
        if ($this->orderReadonly || $this->customerReadonly || $this->disableSubmit) {
            $customerDefaultOptions['readonly'] = true;
        }
    
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Customer')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
            (isset($this->_myOrderTypesArray[$this->_orderModel->type]) ?
            [
                'type' => CMyHtml::INPUT_RATIOBUTTONLIST, 'name' => $this->_formModel->fieldName('type'),
                'label' => $this->_orderModel->getAttributeLabel('type'),
                'value' => $this->_orderModel->type,
                'data' => $this->_myOrderTypesArray,
            ] : null),
            ['type' => CMyHtml::INPUT_TYPE_HTML, 'name' => $this->_formModel->fieldName('customer_name'),
                'label' => $this->_orderModel->getAttributeLabel('customer_name'),
                'html' => \yii\bootstrap\Html::beginTag('div', ['class'=>'input-group input-group-sm']). \yii\bootstrap\Html::input('text', 
                        $this->_formModel->fieldName('customer_name'), 
                        ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->name : $this->_orderModel->customer_name), 
                        ['class'=>'form-control',
                            'id'=>"{$this->_idPrefix}customer_name{$this->_autoId}",
                            'autocomplete' => 'off',
                            'data-provide' => 'typeahead',
                        ]).
                        \yii\bootstrap\Html::tag('span', \yii\bootstrap\Html::tag('button', '', ['class'=>'btn btn-default glyphicon glyphicon-shopping-cart', 'title'=>\Yii::t('carrental', 'Car rental order'), 'type'=>'button', 'onclick'=>"funcSearchUserOrders{$this->_autoId}()"]), ['class'=>'input-group-btn'])
                        .\yii\bootstrap\Html::endTag('div'),
            ],
            /*['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('customer_name'),
                'label' => $this->_orderModel->getAttributeLabel('customer_name'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->name : $this->_orderModel->customer_name),
                'data' => $this->_urlSearchUser,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'id'=>"{$this->_idPrefix}customer_name{$this->_autoId}",
                    'data-options' => "valueField:'id',textField:'text',onChange:funcSearchUser{$this->_autoId},onSelect:funcOnselectUser{$this->_autoId},".
                        "formatter:function(row){ var val = row.text; if (row.identity_id) { val += '(' + row.identity_id + ')'; } return val; },".
                        "hasDownArrow:false", 
                    'tailhtml'=> CMyHtml::tag('span', CMyHtml::tag('a', '', ['href'=>"javascript:void(0);", 'class'=>"icon-car", 'onclick'=>"funcSearchUserOrders{$this->_autoId}()", 'style'=>"display:inline-block;width:16px;height:16px;margin: 0px 2px 0px 2px"]), []), 
                    'style'=>"width:220px"]),
                'columnindex' => 0,
            ],*/
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_telephone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_telephone'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->telephone : $this->_orderModel->customer_telephone),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'id'=>"{$this->_idPrefix}customer_telephone{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 
                'label' => '注册手机',
                // 'value' => ((!$this->vehicleOrder && $this->userId) ? $this->$this->account : $this->_orderModel->customer_telephone),
                'value' =>  $this->account,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}customer_telephone{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('customer_id_type'),
                'label' => $this->_orderModel->getAttributeLabel('customer_id_type'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->identity_type : $this->_orderModel->customer_id_type),
                'data' => \common\models\Pub_user_info::getIdentityTypesArray(),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 'editable' => false,
                    'id'=>"{$this->_idPrefix}customer_id_type{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_id'),
                'label' => $this->_orderModel->getAttributeLabel('customer_id'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->identity_id : $this->_orderModel->customer_id),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'id'=>"{$this->_idPrefix}customer_id{$this->_autoId}", 'style'=>"width:200px"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_address'),
                'label' => $this->_orderModel->getAttributeLabel('customer_address'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->home_address : $this->_orderModel->customer_address),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_address{$this->_autoId}", 
                    'style'=>'width:400px']),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_fixedphone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_fixedphone'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->fixedphone : $this->_orderModel->customer_fixedphone),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}customer_fixedphone{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_postcode'),
                'label' => $this->_orderModel->getAttributeLabel('customer_postcode'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->post_code : $this->_orderModel->customer_postcode),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_postcode{$this->_autoId}"]),
                'columnindex' => 2,
            ],*/
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_name : $this->_orderModel->customer_employer),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer{$this->_autoId}", 
                        'style'=>'width:400px']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $this->_formModel->fieldName('customer_driver_license_time'),
                'label' => $this->_orderModel->getAttributeLabel('customer_driver_license_time'),
                // 'value' => (empty($this->_orderModel->customer_driver_license_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_time) ? '' :  $this->_userInfoModel->driver_license_time) : '') : $this->_orderModel->customer_driver_license_time),
                'value' => (empty($this->_orderModel->customer_driver_license_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_time) ? '' : date('Y-m-d', $this->_userInfoModel->driver_license_time)) : '') : date('Y-m-d', $this->_orderModel->customer_driver_license_time)),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'editable'=>false,
                    'id'=>"{$this->_idPrefix}customer_driver_license_time{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $this->_formModel->fieldName('customer_driver_license_expire_time'),
                'label' => $this->_orderModel->getAttributeLabel('customer_driver_license_expire_time'),
                // 'value' => (empty($this->_orderModel->customer_driver_license_expire_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_expire_time) ? '' : $this->_userInfoModel->driver_license_expire_time) : '') : $this->_orderModel->customer_driver_license_expire_time),
                'value' => (empty($this->_orderModel->customer_driver_license_expire_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_expire_time) ? '' : date('Y-m-d', $this->_userInfoModel->driver_license_expire_time)) : '') : date('Y-m-d', $this->_orderModel->customer_driver_license_expire_time)),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'editable'=>false,
                    'id'=>"{$this->_idPrefix}customer_driver_license_expire_time{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_address'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_address'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_address : $this->_orderModel->customer_employer_address,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'style'=>'width:400px',
                    'id'=>"{$this->_idPrefix}customer_employer_address{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_postcode'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_postcode'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_postcode : $this->_orderModel->customer_employer_postcode,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_postcode{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_employer_phone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_phone'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_telephone : $this->_orderModel->customer_employer_phone,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_phone{$this->_autoId}"]),
                'columnindex' => 1,
            ],*/
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_certificate_id'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_certificate_id'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_license : $this->_orderModel->customer_employer_certificate_id,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_certificate_id{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('emergency_contact_name'),
                'label' => $this->_orderModel->getAttributeLabel('emergency_contact_name'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->emergency_contact : $this->_orderModel->emergency_contact_name,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}emergency_contact_name{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('emergency_contact_phone'),
                'label' => $this->_orderModel->getAttributeLabel('emergency_contact_phone'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->emergency_telephone : $this->_orderModel->emergency_contact_phone,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}emergency_contact_phone{$this->_autoId}"]),
                'columnindex' => 2,
            ],
        ];
        
        return $inputs;
    }
    
    protected function getEnterpriseInputFieldsInfo() {
        $customerDefaultOptions = [];
        if ($this->orderReadonly || $this->customerReadonly || $this->disableSubmit) {
            $customerDefaultOptions['readonly'] = true;
        }
    
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Customer')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('customer_name'),
                'label' => $this->_orderModel->getAttributeLabel('customer_name'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->name : $this->_orderModel->customer_name),
                'data' => $this->_urlSearchUser,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'data-options' => "valueField:'id',textField:'text',onChange:funcSearchUser{$this->_autoId},onSelect:funcOnselectUser{$this->_autoId},".
                        "formatter:function(row){ var val = row.text; if (row.identity_id) { val += '(' + row.identity_id + ')'; } return val; },".
                        "hasDownArrow:false", 
                    'style'=>"width:220px"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_telephone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_telephone'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->telephone : $this->_orderModel->customer_telephone),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'id'=>"{$this->_idPrefix}customer_telephone{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('customer_id_type'),
                'label' => $this->_orderModel->getAttributeLabel('customer_id_type'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->identity_type : $this->_orderModel->customer_id_type),
                'data' => \common\models\Pub_user_info::getIdentityTypesArray(),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 'editable' => false,
                    'id'=>"{$this->_idPrefix}customer_id_type{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_id'),
                'label' => $this->_orderModel->getAttributeLabel('customer_id'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->identity_id : $this->_orderModel->customer_id),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => true, 
                    'id'=>"{$this->_idPrefix}customer_id{$this->_autoId}", 'style'=>"width:200px"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_address'),
                'label' => $this->_orderModel->getAttributeLabel('customer_address'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->home_address : $this->_orderModel->customer_address),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_address{$this->_autoId}", 
                    'style'=>'width:400px']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_fixedphone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_fixedphone'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->fixedphone : $this->_orderModel->customer_fixedphone),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}customer_fixedphone{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer'),
                'value' => ((!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_name : $this->_orderModel->customer_employer),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer{$this->_autoId}", 
                        'style'=>'width:400px']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $this->_formModel->fieldName('customer_driver_license_time'),
                'label' => $this->_orderModel->getAttributeLabel('customer_driver_license_time'),
                'value' => (empty($this->_orderModel->customer_driver_license_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_time) ? '' : date('Y-m-d', $this->_userInfoModel->driver_license_time)) : '') : date('Y-m-d', $this->_orderModel->customer_driver_license_time)),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'editable'=>false,
                    'id'=>"{$this->_idPrefix}customer_driver_license_time{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_DATEBOX, 'name' => $this->_formModel->fieldName('customer_driver_license_expire_time'),
                'label' => $this->_orderModel->getAttributeLabel('customer_driver_license_expire_time'),
                'value' => (empty($this->_orderModel->customer_driver_license_expire_time) ? ((!$this->vehicleOrder && $this->userId) ? (empty($this->_userInfoModel->driver_license_expire_time) ? '' : date('Y-m-d', $this->_userInfoModel->driver_license_expire_time)) : '') : date('Y-m-d', $this->_orderModel->customer_driver_license_expire_time)),
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'editable'=>false,
                    'id'=>"{$this->_idPrefix}customer_driver_license_expire_time{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_address'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_address'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_address : $this->_orderModel->customer_employer_address,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false, 'style'=>'width:400px',
                    'id'=>"{$this->_idPrefix}customer_employer_address{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_postcode'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_postcode'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_postcode : $this->_orderModel->customer_employer_postcode,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_postcode{$this->_autoId}"]),
                'columnindex' => 1,
            ],*/
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('customer_employer_phone'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_phone'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_telephone : $this->_orderModel->customer_employer_phone,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_phone{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('customer_employer_certificate_id'),
                'label' => $this->_orderModel->getAttributeLabel('customer_employer_certificate_id'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->company_license : $this->_orderModel->customer_employer_certificate_id,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}customer_employer_certificate_id{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('emergency_contact_name'),
                'label' => $this->_orderModel->getAttributeLabel('emergency_contact_name'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->emergency_contact : $this->_orderModel->emergency_contact_name,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}emergency_contact_name{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TELEPHONE, 'name' => $this->_formModel->fieldName('emergency_contact_phone'),
                'label' => $this->_orderModel->getAttributeLabel('emergency_contact_phone'),
                'value' => (!$this->vehicleOrder && $this->userId) ? $this->_userInfoModel->emergency_telephone : $this->_orderModel->emergency_contact_phone,
                'htmlOptions' => array_merge($customerDefaultOptions, ['required' => false,
                    'id'=>"{$this->_idPrefix}emergency_contact_phone{$this->_autoId}"]),
                'columnindex' => 2,
            ],
        ];
        
        return $inputs;
    }
    
    protected function getVehicleInputFieldsInfo() {
        $orderDefaultOptions = [];
        if ($this->orderReadonly || $this->disableSubmit) {
            $orderDefaultOptions['readonly'] = true;
        }
        $vehicleOptions = array_merge([], $orderDefaultOptions);
        $replaceVehicleOptions = array_merge([], $orderDefaultOptions);
        $vehicleModelOptions = array_merge([], $orderDefaultOptions);
        $paidOptions = array_merge([], $orderDefaultOptions);
        $preferentialOptions = array_merge([], $orderDefaultOptions);
        if (!$this->_enableReplaceVehicle) {
            $replaceVehicleOptions['readonly'] = true;
        }
        else {
            $vehicleOptions['readonly'] = true;
        }
        if ($this->_orderModel->status > \common\models\Pro_vehicle_order::STATUS_BOOKED) {
            $vehicleModelOptions['readonly'] = true;
        }
        if (floatval($this->_orderModel->paid_amount) > 0) {
            $paidOptions['readonly'] = true;
        }
        if ($this->_isAdmin) {
            $preferentialOptions['readonly'] = false;
        }
        //if ($this->_orderModel->status >= \common\models\Pro_vehicle_order::STATUS_BOOKED
        //    && $this->_orderModel->vehicle_id
        //    ) {
        //    $vehicleModelOptions['readonly'] = true;
        //}
        
        $objVehicle = new \common\models\Pro_vehicle();
        $arrVehicleFields = ['id', 'plate_number', 'model_id', 'color', 'baught_time', 
            'cur_kilometers', 'belong_office_id', 'stop_office_id', 
            'annual_inspection_time', 'tci_renewal_time', 'vci_renewal_time'];
        $vehicleCustomFieldOptions = $objVehicle->attributeCustomTypes();
        $arrVehicleColumns = [];
        foreach ($arrVehicleFields as $field) {
            if (isset($vehicleCustomFieldOptions[$field])) {
                $fieldOptions = ['field' => $field, 'title' => $objVehicle->getAttributeLabel($field)];
                foreach ($vehicleCustomFieldOptions[$field] as $k => $v) {
                    $fieldOptions[$k] = $v;
                }
                $arrVehicleColumns[] = $fieldOptions;
            }
        }
        $vehicleData = [
            'columns' => $arrVehicleColumns,
            'url' => $this->_urlGetVehicleList,
            'method' => 'get',
            'panelWidth' => 800,
            'panelHeight' => 400,
            'idField' => 'id',
            'textField' => 'plate_number',
            'pagination' => 'true',
            'onSelect' => "funcOnSelectVehicle{$this->_autoId}",
            'queryParams' => \common\helpers\CEasyUI::convertArrayDataToJsArrayText($this->_vehicleQueryParams),
            'toolbar' => [CMyHtml::formatDatagridSearchAreaToolConfig(CMyHtml::DG_TOOL_SEARCH_TEXTBOX, 'plate_number', \Yii::t('locale', 'Plate number'), '', [])],
        ];
        
        $classicalRentDaysButtons = [
            \yii\bootstrap\Html::beginTag('div', ['class'=>'btn-group']),
        ];
        $_days = [1,2,3,4,5,6,7,8,9,10,15];
        foreach ($_days as $day) {
            $classicalRentDaysButtons[] = \yii\bootstrap\Html::button(\Yii::t('locale', '{number} days', ['number'=>$day]), ['onclick'=>"funcSetClassicalRentDays{$this->_autoId}({$day})", 'class'=>'btn btn-default']);
        }
        $classicalRentDaysButtons[] = \yii\bootstrap\Html::button(\Yii::t('locale', '{number} months', ['number'=>\Yii::t('locale', 'One')]), ['onclick'=>"funcSetClassicalRentDays{$this->_autoId}(30)", 'class'=>'btn btn-default']);
        $classicalRentDaysButtons[] = \yii\bootstrap\Html::button(\Yii::t('locale', '{number} years', ['number'=>\Yii::t('locale', 'One')]), ['onclick'=>"funcSetClassicalRentDays{$this->_autoId}(365)", 'class'=>'btn btn-default']);
        $classicalRentDaysButtons[] = \yii\bootstrap\Html::endTag('div');
        
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Rent')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],],
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_TYPE_HTML, 'label'=> \Yii::t('carrental', 'Recommend vehicle rent days:'),
                'html' => implode("\n", $classicalRentDaysButtons)],
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $this->_formModel->fieldName('start_time'),
                'label' => $this->_orderModel->getAttributeLabel('start_time'),
                'value' => (empty($this->_orderModel->start_time) ? '' : date('Y-m-d H:i:s', $this->_orderModel->start_time)),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'id'=>"{$this->_idPrefix}start_time{$this->_autoId}",
                    'onChange' => "funcOnStartTimeChanged{$this->_autoId}",
                    'showSeconds'=>'false']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $this->_formModel->fieldName('end_time'),
                'label' => $this->_orderModel->getAttributeLabel('end_time'),
                'value' => (empty($this->_orderModel->new_end_time) ? (empty($this->_orderModel->end_time) ? '' : date('Y-m-d H:i:s', $this->_orderModel->end_time)) : date('Y-m-d H:i:s', $this->_orderModel->new_end_time)),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'id'=>"{$this->_idPrefix}end_time{$this->_autoId}",
                    'onChange' => "funcOnEndTimeChanged{$this->_autoId}",
                    'showSeconds'=>'false']),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('rent_days'),
                'label' => $this->_orderModel->getAttributeLabel('rent_days'),
                'value' => $this->_orderModel->rent_days,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'id'=>"{$this->_idPrefix}rent_days{$this->_autoId}", 'readonly'=>true]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TYPE_HTML, 'name' => '',
                'label' => $this->_orderModel->getAttributeLabel('vehicle_model_id'),
                'html' => \yii\bootstrap\Html::beginTag('div', ['class'=>'input-group input-group-sm']). \yii\bootstrap\Html::input('text', 
                        '', 
                        \common\models\Pro_vehicle_model::getVehicleModelName(empty($this->_orderModel->vehicle_model_id) ? (isset($this->vehicleModelId) ? $this->vehicleModelId : '') : $this->_orderModel->vehicle_model_id), 
                        ['class'=>'form-control',
                            'id'=>"{$this->_idPrefix}vehicle_model_text{$this->_autoId}",
                            'autocomplete' => 'off',
                            'data-provide' => 'typeahead',
                        ]).
                        \yii\bootstrap\Html::endTag('div'),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('vehicle_model_id'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_model_id'),
                'value' => (empty($this->_orderModel->vehicle_model_id) ? (isset($this->vehicleModelId) ? $this->vehicleModelId : '') : $this->_orderModel->vehicle_model_id),
                //'data' => \yii\helpers\Url::to(['vehicle/getmodelnames', 'enableadd'=>'0']),
                'data' => \common\helpers\CEasyUI::convertComboTreeDataToString(\common\components\VehicleModule::getVehicleModelNamesWithPriceArray(['enableadd'=>false])),    // use convertComboTreeDataToString that to support extra parameters.
                'htmlOptions' => array_merge($vehicleModelOptions, ['required' => true, 'editable'=>false, 'style'=>"width:200px",
                    'id'=>"{$this->_idPrefix}vehicle_model_id{$this->_autoId}", 
                    'data-options' => "valueField:'id',textField:'text',onSelect:funcOnSelectVehicleModel{$this->_autoId}",
                ]),
                'columnindex' => 0,
            ],*/
            ['type' => CMyHtml::INPUT_COMBOGRID, 'name' => $this->_formModel->fieldName('vehicle_id'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_id'),
                'value' => ($this->_enableReplaceVehicle ? ($this->_orderModel->origin_vehicle_id ? $this->_orderModel->origin_vehicle_id : $this->_orderModel->vehicle_id) : (empty($this->_orderModel->vehicle_id) ? ($this->vehicleId ? $this->vehicleId : '') : $this->_orderModel->vehicle_id)),
                'data' => $vehicleData,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 'id'=>($this->_enableReplaceVehicle ? "" : "{$this->_idPrefix}vehicle_id{$this->_autoId}"),
                    'data-options'=>"onLoadSuccess:funcOnVehicleListLoadSuccess{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('vehicle_color'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_color'),
                'value' => empty($this->_orderModel->vehicle_color) ? '' : $this->_orderModel->vehicle_color,
                'data' => \common\components\VehicleModule::getVehicleColorsArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'readonly' => true, 'style' => 'width:200px',
                    'id' => "{$this->_idPrefix}vehicle_color{$this->_autoId}",
                    //'data-options' => "value:''",
                ]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('vehicle_oil_label'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_oil_label'),
                'value' => $this->_orderModel->vehicle_oil_label,
                'data' => \common\components\VehicleModule::getVehicleOilLabelsArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'style' => 'width:200px', 'readonly' => true, 
                    'id' => "{$this->_idPrefix}vehicle_oil_label{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('vehicle_outbound_mileage'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_outbound_mileage'),
                'value' => empty($this->_orderModel->vehicle_outbound_mileage) ? ($this->_vehicleModel ? $this->_vehicleModel->cur_kilometers : null) : $this->_orderModel->vehicle_outbound_mileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'readonly' => false, 
                    'data-options' => "validType:'integer'",
                    'id' => "{$this->_idPrefix}vehicle_outbound_mileage{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('pay_type'),
                'label' => $this->_orderModel->getAttributeLabel('pay_type'),
                'value' => $this->_orderModel->pay_type,
                'data' => \common\components\OrderModule::getPriceTypeArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 
                    'id'=>"{$this->_idPrefix}pay_type{$this->_autoId}",
                    'onChange'=>"funcUpdateRentDays{$this->_autoId}",
                ]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $this->_formModel->fieldName('office_id_rent'),
                'label' => $this->_orderModel->getAttributeLabel('office_id_rent'),
                'value' => ($this->_orderModel->office_id_rent ? $this->_orderModel->office_id_rent : ($this->_curOfficeId ? $this->_curOfficeId : ($this->_vehicleModel ? $this->_vehicleModel->stop_office_id : null))),
                'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 'lines'=>true, 
                    'id'=>"{$this->_idPrefix}office_id_rent{$this->_autoId}",
                    'onChange'=>"funcGetServicePriceBetweenOffice{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $this->_formModel->fieldName('office_id_return'),
                'label' => $this->_orderModel->getAttributeLabel('office_id_return'),
                'value' => ($this->_orderModel->office_id_return ? $this->_orderModel->office_id_return : ($this->_curOfficeId ? $this->_curOfficeId : ($this->_vehicleModel ? $this->_vehicleModel->stop_office_id : null))),
                'data' => \common\components\OfficeModule::getOfficeComboTreeData(['showAll'=>true]),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 'lines'=>true, 
                    'id'=>"{$this->_idPrefix}office_id_return{$this->_autoId}",
                    'onChange'=>"funcGetServicePriceBetweenOffice{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_different_office'),
                'label' => $this->_orderModel->getAttributeLabel('price_different_office'),
                'value' => $this->_orderModel->price_different_office,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'precision'=>2, 
                    'id'=>"{$this->_idPrefix}price_different_office{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('rent_per_day'),
                'label' => $this->_orderModel->getAttributeLabel('rent_per_day'),
                'value' => $this->_orderModel->rent_per_day,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'precision'=>2, 
                    'id'=>"{$this->_idPrefix}rent_per_day{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY/day')]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => ($this->orderReadonly ? '' : $this->_formModel->fieldName('price_rent')),
                'label' => $this->_orderModel->getAttributeLabel('price_rent'),
                'value' => $this->_orderModel->price_rent,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'precision'=>2, 
                    'id'=>"{$this->_idPrefix}price_rent{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => $this->_orderModel->getAttributeLabel('total_amount'),
                'value' => $this->_orderModel->total_amount,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'readonly'=>true, 'precision'=>2, 
                    'id'=>"{$this->_idPrefix}total_amount{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('pay_source'),
                'label' => $this->_orderModel->getAttributeLabel('pay_source'),
                'value' => $this->_orderModel->pay_source,
                'data' => \common\components\OrderModule::getOrderPayTypeArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'editable'=>false, 'readonly'=>true,
                    'data-options' => "onSelect:funcOnSelectRentPayMethod{$this->_autoId}",
                ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('paid_amount'),
                'label' => ((empty($this->_orderModel->status)||$this->_orderModel->status < \common\models\Pro_vehicle_order::STATUS_RENTING) ? \Yii::t('carrental', 'Prepaid rent amount') : $this->_orderModel->getAttributeLabel('paid_amount')),
                'value' => $this->_orderModel->paid_amount,
                'htmlOptions' => array_merge($paidOptions, ['required' => false, 'precision'=>2, 'readonly'=>true,
                    'id' => "{$this->_idPrefix}paid_amount{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY').$this->generatePaymentButton()]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_overtime'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_overtime'),
                'value' => $this->_orderModel->unit_price_overtime,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false,
                    'id' => "{$this->_idPrefix}unit_price_overtime{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY/hour')]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_overmileage'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_overmileage'),
                'value' => $this->_orderModel->unit_price_overmileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id' => "{$this->_idPrefix}unit_price_overmileage{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('carrental', 'CNY/kilometer')]),
                'columnindex' => 1,
            ],*/
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_designated_driving'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_designated_driving'),
                'value' => $this->_orderModel->unit_price_designated_driving,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id' => "{$this->_idPrefix}unit_price_designated_driving{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY/day')]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_designated_driving_overtime'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_designated_driving_overtime'),
                'value' => $this->_orderModel->unit_price_designated_driving_overtime,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id' => "{$this->_idPrefix}unit_price_designated_driving_overtime{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY/hour')]),
                'columnindex' => 2,
            ],*/
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_designated_driving_overmileage'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_designated_driving_overmileage'),
                'value' => $this->_orderModel->unit_price_designated_driving_overmileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id' => "{$this->_idPrefix}unit_price_designated_driving_overmileage{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('carrental', 'CNY/kilometer')]),
                'columnindex' => 2,
            ],*/
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_poundage'),
                'label' => $this->_orderModel->getAttributeLabel('price_poundage'),
                'value' => $this->_orderModel->price_poundage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false,
                    'id' => "{$this->_idPrefix}price_poundage{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('unit_price_basic_insurance'),
                'label' => $this->_orderModel->getAttributeLabel('unit_price_basic_insurance'),
                'value' => $this->_orderModel->unit_price_basic_insurance,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false,
                    'id' => "{$this->_idPrefix}unit_price_basic_insurance{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY/day')]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_deposit_violation'),
                'label' => $this->_orderModel->getAttributeLabel('price_deposit_violation'),
                'value' => $this->_orderModel->price_deposit_violation,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 
                    'id' => "{$this->_idPrefix}price_deposit_violation{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_deposit'),
                'label' => $this->_orderModel->getAttributeLabel('price_deposit'),
                'value' => $this->_orderModel->price_deposit,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false,
                    'id' => "{$this->_idPrefix}price_deposit{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('deposit_pay_source'),
                'label' => $this->_orderModel->getAttributeLabel('deposit_pay_source'),
                'value' => $this->_orderModel->deposit_pay_source,
                'data' => \common\components\OrderModule::getOrderPayTypeArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'readonly'=>true,
                    'data-options' => "onSelect:funcOnSelectDepositPayMethod{$this->_autoId}",
                ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => $this->_orderModel->getAttributeLabel('paid_deposit'),
                'value' => $this->_orderModel->paid_deposit,
                'htmlOptions' => array_merge($paidOptions, ['required' => false, 'precision'=>2, 'readonly'=>true,
                    'id' => "{$this->_idPrefix}paid_deposit{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY').$this->generatePaymentButton()]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('source'),
                'label' => $this->_orderModel->getAttributeLabel('source'),
                'value' => $this->_orderModel->source,
                'data' => \common\components\OrderModule::getOrderSourceArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false,
                    'id' => "{$this->_idPrefix}source{$this->_autoId}"]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('status'),
                'label' => $this->_orderModel->getAttributeLabel('status'),
                'value' => $this->_orderModel->status,
                'data' => \common\components\OrderModule::getOrderStatusArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 'readonly'=>true]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('preferential_info'),
                'label' => $this->_orderModel->getAttributeLabel('preferential_info'),
                'value' => $this->_orderModel->preferential_info,
                'data' => $this->_urlGetPreferentialData,
                'htmlOptions' => array_merge($preferentialOptions, ['required' => false, 'readonly' => false,
                    'data-options' => "onSelect:funcPreferentialType{$this->_autoId}",
                ]),
                'columnindex' => 0,
            ],
            // ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => ($this->_enableSettlement ? '' : $this->_formModel->fieldName('price_preferential')),
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_preferential'),
                'label' => $this->_orderModel->getAttributeLabel('price_preferential'),
                'value' => $this->_orderModel->price_preferential,
                'htmlOptions' => array_merge($preferentialOptions, ['required' => false, 'readonly' => false,
                    'id'=>"{$this->_idPrefix}price_preferential{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 1,
            ],
            /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('used_gift_code'),
                'label' => $this->_orderModel->getAttributeLabel('used_gift_code'),
                'value' => $this->_orderModel->used_gift_code,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false
                    ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_gift'),
                'label' => $this->_orderModel->getAttributeLabel('price_gift'),
                'value' => $this->_orderModel->price_gift,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'precision'=>2,
                    'id'=>"{$this->_idPrefix}price_gift{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 1,
            ],*/
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('remark'),
                'label' => $this->_orderModel->getAttributeLabel('remark'),
                'value' => $this->_orderModel->remark,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly' => false,
                    "style"=>"width:500px"]),
                'columnindex' => 0,
            ],
        ];
        
        /*if (!$this->orderReadonly && floatval($this->_orderModel->paid_amount) > 0) {
            array_splice($inputs, 20, 0, [
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_cur_payment'),
                'label' => \Yii::t('carrental', 'Complete rent'),
                'value' => '',
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly' => false,
                    'id'=>"{$this->_idPrefix}price_cur_payment{$this->_autoId}"]),
                'columnindex' => 2,
            ]
            ]);
        }*/
        
        if ($this->_isAdmin) {
            $inputs[] = ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => $this->_formModel->fieldName('belong_office_id'),
                'label' => $this->_orderModel->getAttributeLabel('belong_office_id'),
                'value' => ($this->_orderModel->belong_office_id ? $this->_orderModel->belong_office_id : ($this->_vehicleModel ? $this->_vehicleModel->stop_office_id : null)),
                'data' => \common\components\OfficeModule::getOfficeComboTreeData(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false, 
                    'id' => "{$this->_idPrefix}belong_office_id{$this->_autoId}", 'onChange'=>"funcUpdateOptionalServicePrices{$this->_autoId}"]),
                'columnindex' => 0,
            ];
        }
        
        return $inputs;
    }
    
    protected function getOptionalServicesInputFieldsInfo() {
        $orderDefaultOptions = [];
        if ($this->orderReadonly || $this->disableSubmit) {
            $orderDefaultOptions['readonly'] = true;
        }
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', 'Value-added services'),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
        ];

        $arrServicePriceObjects = \common\components\OptionsModule::getOptionalServiceObjectsArray();
        $formModelOptionalServices = new \backend\models\Form_pro_optional_services();
        $arrSelectedOptionalPrices = $this->_orderModel->getOptionalServicePriceArray();
        $idx = 0;
        foreach ($arrServicePriceObjects as $serviceObject) {
            if ($serviceObject->flag == \common\models\Pro_service_price::FLAG_ENABLED) {
                $inputs[] = [
                    'type' => CMyHtml::INPUT_NUMBERBOX,
                    'name' => $formModelOptionalServices->fieldName($serviceObject->id),
                    'label' => $serviceObject->name,
                    'value' => $serviceObject->price,
                    'prompt' => "({$serviceObject->unit_name})",
                    'htmlOptions' => array_merge($orderDefaultOptions, [
                        'tailhtml'=>$serviceObject->unit_name, 
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                        'id'=>"{$this->_idPrefix}optional_service_price{$this->_autoId}_{$serviceObject->id}"]),
                    'checkbox' => [
                        'name' => $formModelOptionalServices->fieldName('selections')."[{$serviceObject->id}]",
                        'selected' => (($serviceObject->requirement > 0 || (isset($arrSelectedOptionalPrices[$serviceObject->id]))) ? true : false),
                        'readonly' => (($serviceObject->requirement == 1 || $this->orderReadonly) ? true : false),
                        'htmlOptions' => [
                            'id' => "{$this->_idPrefix}optional_service_selection{$this->_autoId}_{$serviceObject->id}",
                            'onclick'=>"funcUpdateTotalPrice{$this->_autoId}",
                        ],
                    ],
                    'columnindex' => ($idx % 4),
                ];
                $idx++;
            }
        }
        
        $inputs[] = ['type'=>  CMyHtml::INPUT_TYPE_SUBGROUP];
        $inputs[] = [
            'type' => CMyHtml::INPUT_TEXTBOX,
            'name' => $this->_formModel->fieldName('address_take_car'),
            'label' => $this->_orderModel->getAttributeLabel('address_take_car'),
            'value' => $this->_orderModel->address_take_car,
            'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'style'=>"width:280px",
                'onChange'=>"funcGetServicePriceTakeCar{$this->_autoId}"]),
            'columnindex' => 0,
        ];
        $inputs[] = [
            'type' => CMyHtml::INPUT_NUMBERBOX,
            'name' => $this->_formModel->fieldName('price_take_car'),
            'label' => $this->_orderModel->getAttributeLabel('price_take_car'),
            'value' => $this->_orderModel->price_take_car,
            'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false,
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'id' => "{$this->_idPrefix}price_take_car{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                ]),
            'columnindex' => 1,
        ];
        $inputs[] = [
            'type' => CMyHtml::INPUT_TEXTBOX,
            'name' => $this->_formModel->fieldName('address_return_car'),
            'label' => $this->_orderModel->getAttributeLabel('address_return_car'),
            'value' => $this->_orderModel->address_return_car,
            'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'style'=>"width:280px", 
                'onChange'=>"funcGetServicePriceReturnCar{$this->_autoId}"]),
            'columnindex' => 0,
        ];
        $inputs[] = [
            'type' => CMyHtml::INPUT_NUMBERBOX,
            'name' => $this->_formModel->fieldName('price_return_car'),
            'label' => $this->_orderModel->getAttributeLabel('price_return_car'),
            'value' => $this->_orderModel->price_return_car,
            'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false,
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'id' => "{$this->_idPrefix}price_return_car{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                ]),
            'columnindex' => 1,
        ];
        
        return $inputs;
    }
    
    protected function getInvoiceInputFieldsInfo() {
        $invoiceDefaultOptions = [];
        if ($this->orderReadonly || $this->customerReadonly || $this->disableSubmit) {
            $invoiceDefaultOptions['readonly'] = true;
        }
    
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Invoice')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_title'),
                'label' => $this->_orderModel->getAttributeLabel('inv_title'),
                'value' => $this->_orderModel->inv_title,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_title{$this->_autoId}",
                ]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_name'),
                'label' => $this->_orderModel->getAttributeLabel('inv_name'),
                'value' => $this->_orderModel->inv_name,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_name{$this->_autoId}"]),
                'columnindex' => 1,
            ],*/
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_tax_number'),
                'label' => $this->_orderModel->getAttributeLabel('inv_tax_number'),
                'value' => $this->_orderModel->inv_tax_number,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_tax_number{$this->_autoId}"]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('inv_amount'),
                'label' => $this->_orderModel->getAttributeLabel('inv_amount'),
                'value' => $this->_orderModel->inv_amount,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 'precision'=>2, 
                    'id'=>"{$this->_idPrefix}inv_amount{$this->_autoId}",
                    'onChange'=>"funcUpdateInv_amount{$this->_autoId}",
                ]),
                'columnindex' => 2,
            ],
            /*['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_phone'),
                'label' => $this->_orderModel->getAttributeLabel('inv_phone'),
                'value' => $this->_orderModel->inv_phone,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_phone{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_address'),
                'label' => $this->_orderModel->getAttributeLabel('inv_address'),
                'value' => $this->_orderModel->inv_address,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_address{$this->_autoId}",
                    'style'=>'width:400px']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('inv_postcode'),
                'label' => $this->_orderModel->getAttributeLabel('inv_postcode'),
                'value' => $this->_orderModel->inv_postcode,
                'htmlOptions' => array_merge($invoiceDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}inv_postcode{$this->_autoId}"]),
                'columnindex' => 2,
            ],*/
        ];
        
        return $inputs;
    }
    
    protected function getSettlementInputFieldsInfo() {
        $orderDefaultOptions = [];
        
        $arrMainInfo = [
            CMyHtml::tag('font', \Yii::t('locale', '{name} time', ['name'=>\Yii::t('carrental', 'Rent car')])."[", ['style'=>"color:green"]),
            CMyHtml::tag('font', date('Y-m-d H:i', $this->_orderModel->start_time), ['style'=>'color:red']),
            CMyHtml::tag('font', "]-[", ['style'=>'color:green']),
            CMyHtml::tag('font', date('Y-m-d H:i', $this->_orderModel->new_end_time), ['style'=>'color:red']),
            CMyHtml::tag('font', "]", ['style'=>'color:green']),
            CMyHtml::tag('font', \Yii::t('carrental', 'Rent period')."[", ['style'=>'color:green']),
            CMyHtml::tag('font', \Yii::t('locale', '{number} days', ['number'=>$this->_orderModel->rent_days]), ['style'=>'color:red']),
            CMyHtml::tag('font', "]", ['style'=>'color:green'])
        ];
        
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Settlement')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('settlement_status'),
                'label' => $this->_orderModel->getAttributeLabel('settlement_status'),
                'value' => $this->_orderModel->settlement_status,
                'data' => \common\components\OrderModule::getSettlementTypeArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TYPE_HTML,
                'html' => implode('', $arrMainInfo),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TYPE_HTML,
                'html' => CMyHtml::tag('font', \Yii::t('carrental', 'Contract No.').":".$this->_orderModel->serial, ['style'=>"color:orange"]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_DATETIMEBOX, 'name' => $this->_formModel->fieldName('car_returned_at'),
                'label' => $this->_orderModel->getAttributeLabel('car_returned_at'),
                'value' => (empty($this->_orderModel->car_returned_at) ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', $this->_orderModel->car_returned_at)),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 
                    'onChange' => "funcUpdateSettlementPrices{$this->_autoId}",
                    'id'=>"{$this->_idPrefix}settlement_car_returned_at{$this->_autoId}",
                    'showSeconds'=>'false']),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('vehicle_inbound_mileage'),
                'label' => $this->_orderModel->getAttributeLabel('vehicle_inbound_mileage'),
                'value' => $this->_orderModel->vehicle_inbound_mileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 
                    'data-options' => "validType:'integer'",
                    'id'=>"{$this->_idPrefix}settlement_vehicle_inbound_mileage{$this->_autoId}"]),
                'columnindex' => 2,
            ],
            // ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('is_high_speed'),
            //     'label' => '是否上过高速',
            //     'value' => $this->_orderModel->is_high_speed,
            //     'data' => \common\components\OrderModule::getIsHighSpeedArray(),
            //     'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 'editable'=>false]),
            //     'columnindex' => 3,
            // ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_overmileage'),
                'label' => $this->_orderModel->getAttributeLabel('price_overmileage'),
                'value' => $this->_orderModel->price_overmileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => true, 
                        'id'=>"{$this->_idPrefix}settlement_price_overmileage{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    ]),
                'columnindex' => 3,
            ],*/
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_rent'),
                'label' => $this->_orderModel->getAttributeLabel('price_rent'),
                'value' => $this->_orderModel->price_rent,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_price_rent{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_overtime'),
                'label' => $this->_orderModel->getAttributeLabel('price_overtime'),
                'value' => $this->_orderModel->price_overtime,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_overtime{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_car_damage'),
                'label' => $this->_orderModel->getAttributeLabel('price_car_damage'),
                'value' => $this->_orderModel->price_car_damage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_car_damage{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_violation'),
                'label' => $this->_orderModel->getAttributeLabel('price_violation'),
                'value' => $this->_orderModel->price_violation,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_violation{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_insurance_overtime'),
                'label' => $this->_orderModel->getAttributeLabel('price_insurance_overtime'),
                'value' => $this->_orderModel->price_insurance_overtime,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_insurance_overtime{$this->_autoId}",
                    'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_oil'),
                'label' => $this->_orderModel->getAttributeLabel('price_oil'),
                'value' => $this->_orderModel->price_oil,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_oil{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_oil_agency'),
                'label' => $this->_orderModel->getAttributeLabel('price_oil_agency'),
                'value' => $this->_orderModel->price_oil_agency,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_oil_agency{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_other'),
                'label' => $this->_orderModel->getAttributeLabel('price_other'),
                'value' => $this->_orderModel->price_other,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_other{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('total_amount'),
                'label' => \Yii::t('carrental', 'Total amount to receive'),
                'value' => $this->_orderModel->total_amount,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_total_amount{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_preferential'),
                'label' => $this->_orderModel->getAttributeLabel('price_preferential'),
                'value' => $this->_orderModel->price_preferential,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_price_preferential{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY'),
                    ]),
                'columnindex' => 1,
            ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_designated_driving'),
                'label' => $this->_orderModel->getAttributeLabel('price_designated_driving'),
                'value' => $this->_orderModel->price_designated_driving,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_designated_driving{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    ]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_designated_driving_overtime'),
                'label' => $this->_orderModel->getAttributeLabel('price_designated_driving_overtime'),
                'value' => $this->_orderModel->price_designated_driving_overtime,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_designated_driving_overtime{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    ]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_designated_driving_overmileage'),
                'label' => $this->_orderModel->getAttributeLabel('price_designated_driving_overmileage'),
                'value' => $this->_orderModel->price_designated_driving_overmileage,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_designated_driving_overmileage{$this->_autoId}",
                        'onChange'=>"funcUpdateTotalPrice{$this->_autoId}",
                    ]),
                'columnindex' => 2,
            ],*/
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => $this->_orderModel->getAttributeLabel('paid_amount'),
                'value' => $this->_orderModel->paid_amount,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_paid_amount{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY').$this->generatePaymentButton()]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_bonus_point_deduction'),
                'label' => $this->_orderModel->getAttributeLabel('price_bonus_point_deduction'),    // 积分抵扣 找补金额扣除相应数额。
                'value' => $this->_orderModel->price_bonus_point_deduction,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_price_bonus_point_deduction{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'checkbox' => [
                    'name' => $this->_formModel->fieldName('selections').'[price_bonus_point_deduction]',
                    'position' => 'back',
                    'onchange' => "funcOnClickSettlementBonusPointDeduction{$this->_autoId}",
                ],
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => ''/*$this->_formModel->fieldName('price_cur_payment')*/,
                'label' => \Yii::t('carrental', 'Actual charge'),
                'value' => 0,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 
                    'id'=>"{$this->_idPrefix}settlement_price_need_pay{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 2,
            ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('price_free'),
                'label' => \Yii::t('carrental', 'Free amount'),
                'value' => 0,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_price_free{$this->_autoId}"]),
                'columnindex' => 3,
            ],*/
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => \Yii::t('carrental', 'Paid deposit'),
                'value' => $this->_orderModel->paid_deposit,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_price_deposit{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY').$this->generatePaymentButton()]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => \Yii::t('carrental', 'Deposit as rent'), // 押金扣租 选择后 清退押金清零，找补金额扣除押金数额， 不选中后做相反操作。
                'value' => $this->_orderModel->price_deposit,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_deposit_as_rent{$this->_autoId}"]),
                'checkbox' => [
                    'name' => $this->_formModel->fieldName('selections').'[deposit_as_rent]',
                    'position' => 'back',
                    'onclick' => "funcOnClickSettlementDepositAsRent{$this->_autoId}",
                ],
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => \Yii::t('carrental', 'Give back deposit'),   // 清退押金 
                'value' => $this->_orderModel->price_deposit,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_give_back_deposit{$this->_autoId}"]),
                'columnindex' => 2,
            ],*/
            ['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => '',
                'label' => \Yii::t('carrental', 'Left amount'), // 找补金额 
                'value' => floatval($this->_orderModel->total_amount - $this->_orderModel->paid_amount),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true, 
                    'id'=>"{$this->_idPrefix}settlement_left_amount{$this->_autoId}",
                    'tailhtml'=>\Yii::t('locale', 'CNY')]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => $this->_formModel->fieldName('settlement_pay_source'),
                'label' => \Yii::t('locale', 'Payment method'),
                'value' => $this->_orderModel->settlement_pay_source,
                'data' => \common\components\OrderModule::getOrderPayTypeArray(),
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'editable'=>false, 'readonly'=>true]),
                'columnindex' => 0,
            ],
            /*['type' => CMyHtml::INPUT_NUMBERBOX, 'name' => $this->_formModel->fieldName('settlement_invoice'),
                'label' => \Yii::t('carrental', 'Invoicing'), // 开具发票
                'value' => 0,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'readonly'=>true]),
                'checkbox' => [
                    'name' => $this->_formModel->fieldName('selections').'[settlement_invoice]',
                    'position' => 'back',
                ],
                'columnindex' => 1,
            ],*/
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_TEXTAREA, 'name' => $this->_formModel->fieldName('settlement_remark'),
                'label' => $this->_orderModel->getAttributeLabel('settlement_remark'),
                'value' => $this->_orderModel->settlement_remark,
                'htmlOptions' => array_merge($orderDefaultOptions, ['required' => false, 'style'=>"width:600px"]),
                'columnindex' => 0,
            ],
            
        ];
        
        return $inputs;
    }
    
    protected function getRefundInputFieldsInfo() {
        $refundDefaultOptions = [];
        //if ($this->orderReadonly || $this->customerReadonly || $this->disableSubmit) {
        //    $invoiceDefaultOptions['readonly'] = true;
        //}
    
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name' => \Yii::t('locale', 'Refund account')]),
                'htmlOptions' => [
                    'data-options' => "collapsible:true,collapsed:false",
                    'encode' => false,
                ],
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('refund_account_number'),
                'label' => $this->_orderModel->getAttributeLabel('refund_account_number'),
                'value' => $this->_orderModel->refund_account_number,
                'htmlOptions' => array_merge($refundDefaultOptions, ['required' => false]),
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('refund_account_name'),
                'label' => $this->_orderModel->getAttributeLabel('refund_account_name'),
                'value' => $this->_orderModel->refund_account_name,
                'htmlOptions' => array_merge($refundDefaultOptions, ['required' => false]),
                'columnindex' => 1,
            ],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('refund_bank_name'),
                'label' => $this->_orderModel->getAttributeLabel('refund_bank_name'),
                'value' => $this->_orderModel->refund_bank_name,
                'htmlOptions' => array_merge($refundDefaultOptions, ['required' => false]),
                'columnindex' => 2,
            ],
            ['type' => CMyHtml::INPUT_TYPE_SUBGROUP],
            ['type' => CMyHtml::INPUT_TEXTBOX, 'name' => $this->_formModel->fieldName('refund_remark'),
                'label' => $this->_orderModel->getAttributeLabel('refund_remark'),
                'value' => $this->_orderModel->refund_remark,
                'htmlOptions' => array_merge($refundDefaultOptions, ['required' => false, "style"=>"width:500px"]),
                'columnindex' => 0,
            ],
        ];
        
        return $inputs;
    }
    
    protected function generatePaymentButton() {
        //if ($this->action == 'insert') {
        //    return '';
        //}
        return CMyHtml::tag('a', \Yii::t('locale', 'Payment'), ['href'=>"javascript:void(0)", 'class'=>"easyui-linkbutton", 'data-options'=>"iconCls:'icon-money'", 'onclick'=>"funcLoadPaymentInputPage{$this->_autoId}()", 'encode'=>false]);
    }
    
    protected function renderScriptsContent() {
        $arrServicePriceObjects = \common\components\OptionsModule::getOptionalServiceObjectsArray();
        $arrServicePriceInfo = [];
        foreach ($arrServicePriceObjects as $serviceObject) {
            $arrServicePriceInfo[] = "{id:{$serviceObject->id},unit_type:{$serviceObject->unit_type},month_days:{$serviceObject->month_days}}";
        }
        $jsServicePriceInfo = "[".implode(",", $arrServicePriceInfo)."]";
        
        $arrScripts = [];
        
        $isUpdateOfficeId = $this->_curOfficeId ? true : false;
        $isOptionalServiceOvertimeAsOneDay = \common\components\Consts::OPTIONAL_SERVICE_OVERTIME_AS_ONE_DAY ? 'true' : 'false';
        $oneDayMinHours = \common\components\Consts::ONEDAY_MIN_HOURS;
        $vehicleModelId = ($this->_orderModel->vehicle_model_id ? $this->_orderModel->vehicle_model_id : 0);
        $belongOfficeId = empty($this->_orderModel->belong_office_id) ? ($this->_vehicleModel ? $this->_vehicleModel->stop_office_id : 0) : $this->_orderModel->belong_office_id;
        $defaultSourceOffice = \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE;
        $defaultPayType = \common\models\Pro_vehicle_order::PRICE_TYPE_OFFICE;
        $getPriceType = (empty($this->_orderModel->id) ? 'vehicle_model_id' : 'order_id');
        $orderId = intval($this->_orderModel->id);
        
        $originOptionalPrice = intval($this->_orderModel->price_optional_service);
        $originStartTime = intval($this->_orderModel->start_time);
        $originEndTime = intval($this->_orderModel->new_end_time);
        $jsIsSettlement = $this->_enableSettlement ? 'true' : 'false';
        $jsIsInsertAction = $this->action == 'insert' ? 'true' : 'false';
        // sjj 替换上面一句1571  $getPriceType 
        $getPriceType = (($this->_orderModel->status < 10 ) ? 'vehicle_model_id' : 'order_id');
        // sjj
        $isGiftOneDay = (intval($this->_orderModel->preferential_type) == \common\components\Consts::PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY) ? 'true' : 'false';
        $jsAheadReturnCarDeltaSecs = \common\components\Consts::AHEAD_RETURN_CAR_ALLOW_DELTA_SECONDS;
        
        $strCreditWarningText = \Yii::t('carrental', 'Current user credit level is {level}, please pay attention!', ['level'=>"{0}"]);
        $strCreditWarningWithReasonText = \Yii::t('carrental', 'Current user credit level were set to {level} because of {reason}, please pay attention!', ['level'=>"{0}", 'reason'=>"{1}"]);
        
        $arrScripts[] = <<<EOD
var optionalServicePriceInfo{$this->_autoId} = {$jsServicePriceInfo};
var userInfo{$this->_autoId} = undefined;
var isConfirmRentCar{$this->_autoId} = false;

function funcOnSelectVehicle{$this->_autoId}(idx, row, skipUpdateRentDays) {
    //sjj
    funcUpdateRentDays{$this->_autoId}();
    // alert(11);
    //sjj
    var parentForm = $('#{$this->_formId}');
    if ({$this->vehicleId} != row.id && row && parentForm.length == 1) {
        $('#{$this->_idPrefix}vehicle_model_text{$this->_autoId}', parentForm).val(row.model_id);
        $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val(row.o_model_id);
        //$('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('setValue', row.model_id);
        $('#{$this->_idPrefix}vehicle_color{$this->_autoId}', parentForm).combobox('setValue', row.o_color);
        $('#{$this->_idPrefix}vehicle_oil_label{$this->_autoId}', parentForm).combobox('setValue', row.vehicle_oil_label);
        $('#{$this->_idPrefix}vehicle_outbound_mileage{$this->_autoId}', parentForm).textbox('setValue', row.cur_kilometers);
        if ($('#{$this->_idPrefix}office_id_rent{$this->_autoId}', parentForm).combotree('getValue') == 0) {
            $('#{$this->_idPrefix}office_id_rent{$this->_autoId}', parentForm).combotree('setValue', row.o_stop_office_id);
        }
        if ($('#{$this->_idPrefix}office_id_return{$this->_autoId}', parentForm).combotree('getValue') == 0) {
            $('#{$this->_idPrefix}office_id_return{$this->_autoId}', parentForm).combotree('setValue', row.o_stop_office_id);
        }
        $('#{$this->_idPrefix}price_poundage{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_poundage);
        $('#{$this->_idPrefix}unit_price_basic_insurance{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_basic_insurance);
        $('#{$this->_idPrefix}price_deposit_violation{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_rent_deposit);
        $('#{$this->_idPrefix}unit_price_overtime{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_overtime_price_personal);
        //$('#{$this->_idPrefix}unit_price_designated_driving{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_designated_driving_price);
        //$('#{$this->_idPrefix}unit_price_designated_driving_overtime{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_overtime_price_designated);
        //$('#{$this->_idPrefix}unit_price_overmileage{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_overmileage_price_personal);
        //$('#{$this->_idPrefix}unit_price_designated_driving_overmileage{$this->_autoId}', parentForm).textbox('setValue', row.vehicle_overmileage_price_designated);
        var oBelongOfficeId = $('#{$this->_idPrefix}belong_office_id{$this->_autoId}', parentForm);
        var oBelongOfficeId2 = $('#{$this->_idPrefix}belong_office_id_hidden{$this->_autoId}', parentForm);
        if (!{$belongOfficeId} && row.o_stop_office_id > 0) {
            if (oBelongOfficeId && oBelongOfficeId.length == 1) {
                oBelongOfficeId.combotree('setValue', row.o_stop_office_id);
            } else if (oBelongOfficeId2 && oBelongOfficeId2.length == 1) {
                oBelongOfficeId2.val(row.o_stop_office_id);
            }
        }
        if (skipUpdateRentDays) {
        }
        else {
            funcUpdateRentDays{$this->_autoId}(undefined,undefined,true);
        }
    }
}

function funcCalculateRentDaysByTime{$this->_autoId}(startTime, endTime) {
    var rentDuration = endTime - startTime;
    var extraDuration = rentDuration % 86400;
    var rentDays = Math.floor(rentDuration / 86400);
    var rentHours = 0;
    if (extraDuration >= (3600*{$oneDayMinHours})) {
        rentDays++;
    } else {
        rentHours = Math.floor(extraDuration / 3600);
        rentHours += (extraDuration % 3600) >= 1800 ? 1 : 0;
    }
    return {rentDays:rentDays, rentHours:rentHours};
}

function funcGetOrderTimes{$this->_autoId}(forceReturnFixedTime) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return undefined;
    }
    var oStartTimeTarget = $('#{$this->_idPrefix}start_time{$this->_autoId}', parentForm);
    var oEndTimeTarget = $('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm);
    var startTime = $.custom.utils.toTimestamp(oStartTimeTarget.datetimebox('getValue'));
    var endTime = $.custom.utils.toTimestamp(oEndTimeTarget.datetimebox('getValue'));
    
    var startTimeOpts = oStartTimeTarget.datetimebox('options');
    var endTimeOpts = oEndTimeTarget.datetimebox('options');
    var originRentDaysData = null;

    if ({$jsIsSettlement}) {
        originRentDaysData = funcCalculateRentDaysByTime{$this->_autoId}(startTime, endTime);
        endTime = $.custom.utils.toTimestamp($('#{$this->_idPrefix}settlement_car_returned_at{$this->_autoId}', parentForm).datetimebox('getValue'));
    }
    else {
        if (startTime <= 0) {
            var startDate = new Date();
            startTime = Math.ceil(startDate.getTime() / 1000);
            //if (startTime < {$this->_curTime}) { startTime = {$this->_curTime}; }
            if (startTimeOpts.__settingTime === undefined) {
                startTimeOpts.__settingTime = true;
                oStartTimeTarget.datetimebox('setValue', $.custom.utils.humanTime(startTime));
            }
            if (forceReturnFixedTime) {
            }
            else {
                return undefined;
            }
        }
        if ((endTime - startTime) < 3600*{$oneDayMinHours}) {
            endTime = startTime + 86400 * 2;
            if (endTimeOpts.__settingTime === undefined) {
                endTimeOpts.__settingTime = true;
                oEndTimeTarget.datetimebox('setValue', $.custom.utils.humanTime(endTime));
            }
            if (forceReturnFixedTime) {
            }
            else {
                return undefined;
            }
        }
    }
    
    var rentDaysData = funcCalculateRentDaysByTime{$this->_autoId}(startTime, endTime);
    
    if ({$jsIsSettlement} && rentDaysData.rentDays < originRentDaysData.rentDays) {
        if ({$isGiftOneDay} && rentDaysData.rentDays < 2) {
            rentDaysData.rentDays = 2;
            rentDaysData.rentHours = 0;
        }
        else if (rentDaysData.rentHours > 0 || ((startTime + rentDaysData.rentDays*86400) < (endTime - ${jsAheadReturnCarDeltaSecs}))) {
            rentDaysData.rentDays++;
            rentDaysData.rentHours = 0;
            endTime = startTime + (rentDaysData.rentDays * 86400);
        }
    }
    //funcUpdateRentDays{$this->_autoId}(undefined, undefined, true);
    
    return {startTime:startTime, endTime:endTime, rentDays:rentDaysData.rentDays, rentHours:rentDaysData.rentHours};
}

function funcOnSelectVehicleModel{$this->_autoId}(record) {
    var parentForm = $('#{$this->_formId}');
    if (record) {
        var vehicleModelId = record.id;
        var setPrices = {
            '#{$this->_idPrefix}price_poundage{$this->_autoId}':record.poundage,
            '#{$this->_idPrefix}unit_price_basic_insurance{$this->_autoId}':record.basic_insurance,
            '#{$this->_idPrefix}price_deposit_violation{$this->_autoId}':record.rent_deposit,
            '#{$this->_idPrefix}unit_price_overtime{$this->_autoId}':record.overtime_price_personal,
        }
        //var originVehicleModelId = parseInt($('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('getValue'));
        var originVehicleModelId = parseInt($('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val());
        setTimeout(function(){
        if (vehicleModelId > 0 && vehicleModelId != originVehicleModelId) {
            var timeData = funcGetOrderTimes{$this->_autoId}(true);
            if (timeData) {
                if (vehicleModelId != originVehicleModelId) {
                    funcUpdateRentPrice{$this->_autoId}(vehicleModelId, timeData);
                    
                    for (var k in setPrices) {
                        var objField = $(k, parentForm);
                        if (objField && objField.length > 0) {
                            objField.textbox('setValue', setPrices[k]);
                        }
                    }
                }
                
                funcUpdateVehicleList{$this->_autoId}(vehicleModelId, timeData);
            }
        }
        else {
            for (var k in setPrices) {
                var objField = $(k, parentForm);
                if (objField && objField.length > 0) {
                    var originValue = objField.textbox('getValue');
                    if (!originValue) {
                        objField.textbox('setValue', setPrices[k]);
                    }
                }
            }
        }
        }, 100);
    }
}

function funcUpdateRentDays{$this->_autoId}(newValue,oldValue,skipUpdateVehicleList) {
    var parentForm = $('#{$this->_formId}');
    var timeData = funcGetOrderTimes{$this->_autoId}();
    if (timeData === undefined || parentForm.length != 1) {
        return;
    }
    
    $('#{$this->_idPrefix}rent_days{$this->_autoId}', parentForm).numberbox('setValue', timeData.rentDays);
    
    //var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('getValue');
    var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val();
    if (vehicleModelId) {
        funcUpdateRentPrice{$this->_autoId}(vehicleModelId, timeData);
        
        if (skipUpdateVehicleList) {
        }
        else {
            //funcUpdateVehicleList{$this->_autoId}(vehicleModelId, timeData);
        }
    }
}

function funcOnStartTimeChanged{$this->_autoId}(newValue,oldValue) {
    var parentForm = $('#{$this->_formId}');
    var opts = $(this).datetimebox('options');
    if (opts.__settingTime !== undefined) {
        setTimeout(function(){opts.__settingTime = undefined; /*funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false);*/ },10);
        return;
    }
    var opts2 = $('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm).datetimebox('options');
    if (opts2.__settingTime !== undefined) {
        //funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false);
        return;
    }
    funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false);
    if (opts.__settingTime === undefined && opts2.__settingTime === undefined) {
        var startTime = $.custom.utils.toTimestamp($(this).datetimebox('getValue'));
        var endTime = $.custom.utils.toTimestamp($('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm).datetimebox('getValue'));
        if (startTime > 0 && endTime > 0 && ((endTime - startTime) % 86400 > 59)) {
            var rentDaysData = funcCalculateRentDaysByTime{$this->_autoId}(startTime, endTime);
            var rentDays = rentDaysData.rentDays;
            if (rentDays < 1) {
                rentDays = 2;
            }
            opts2.__settingTime = true;
            endTime = startTime + 86400 * rentDays;
            $('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm).datetimebox('setValue', $.custom.utils.humanTime(endTime));
        }
    }
}

function funcOnEndTimeChanged{$this->_autoId}(newValue,oldValue) {
    var parentForm = $('#{$this->_formId}');
    var opts = $(this).datetimebox('options');
    if (opts.__settingTime !== undefined) {
        setTimeout(function(){ opts.__settingTime = undefined; funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false); },10);
        return;
    }
    var opts2 = $('#{$this->_idPrefix}start_time{$this->_autoId}', parentForm).datetimebox('options');
    if (opts2.__settingTime !== undefined) {
        //setTimeout(function(){ funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false); },10);
        return;
    }
    funcUpdateRentDays{$this->_autoId}(newValue,oldValue,false);
}

function funcSetClassicalRentDays{$this->_autoId}(days) {
    var parentForm = $('#{$this->_formId}');
    var opts2 = $('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm).datetimebox('options');
    opts2.__settingTime = true;
    var timeData = funcGetOrderTimes{$this->_autoId}(true);
    if (timeData === undefined || parentForm.length != 1) {
        return;
    }
    
    timeData.endTime = timeData.startTime + (86400 * days);
    $('#{$this->_idPrefix}end_time{$this->_autoId}', parentForm).datetimebox('setValue', $.custom.utils.humanTime(timeData.endTime));
}
            
function funcUpdateRentPrice{$this->_autoId}(vehicleModelId, timeData) {
    var parentForm = $('#{$this->_formId}');
    // alert(22);
    if (parentForm.length != 1) {
        return undefined;
    }
    var sourceType = parseInt($('#{$this->_idPrefix}source{$this->_autoId}', parentForm).combobox('getValue'));
    var payType = parseInt($('#{$this->_idPrefix}pay_type{$this->_autoId}', parentForm).combobox('getValue'));
    var officeId = parseInt($('#{$this->_idPrefix}office_id_rent{$this->_autoId}', parentForm).combotree('getValue'));
    var vehicleId = parseInt($('#{$this->_idPrefix}vehicle_id{$this->_autoId}', parentForm).combobox('getValue'));
    var userId = $('#{$this->_idPrefix}user_id{$this->_autoId}', parentForm).val();
    if (isNaN(officeId)) { officeId = 0; }
    if (isNaN(sourceType)) { sourceType = {$defaultSourceOffice}; }
    if (isNaN(payType)) { payType = {$defaultPayType}; }
    if (isNaN(vehicleId)) { vehicleId = 0; }
    var getPriceParams = {
        type: '{$getPriceType}',
        vehicle_model_id: vehicleModelId,
        office_id: officeId,
        order_id: {$orderId},
        source_type: sourceType,
        pay_type: payType,
        vehicle_id: vehicleId,
        start_time: timeData.startTime,
        end_time: timeData.endTime,
        user_id: userId,
    }
    //console.log(getPriceParams);
    if (!vehicleId) {
        getPriceParams.type = 'vehicle_model_id';
    }
    easyuiFuncAjaxSendDataWithoutAlert('{$this->_urlGetPrice}', 'get', getPriceParams, function(data){
        var obj = eval('(' + data + ')');
        if (obj.code == 0) {
            var priceValue = parseFloat(obj.value);
            var pricePerDay = (timeData.rentDays ? priceValue / timeData.rentDays : 0);
            $('#{$this->_idPrefix}price_rent{$this->_autoId}', parentForm).numberbox('setValue', priceValue);
            $('#{$this->_idPrefix}rent_per_day{$this->_autoId}', parentForm).numberbox('setValue', pricePerDay);
            
            var targetSettlementPriceRent = $('#{$this->_idPrefix}settlement_price_rent{$this->_autoId}', parentForm);
            if (targetSettlementPriceRent && targetSettlementPriceRent.length > 0) {
                targetSettlementPriceRent.numberbox('setValue', priceValue);
                $('#{$this->_idPrefix}settlement_rent_per_day{$this->_autoId}', parentForm).numberbox('setValue', pricePerDay);
            }
            
            funcUpdateTotalPrice{$this->_autoId}();
            
            var htmlText = formatOrderDetailedDailyPriceTips(obj, '订单租金详情');
            var exTotalDisplayObj = $('#{$this->_idPrefix}ex_total_amount{$this->_autoId}');
            if (exTotalDisplayObj.length > 0) {
                exTotalDisplayObj.popover('destroy');
                exTotalDisplayObj.popover({
                    placement: 'top',
                    html: true,
                    title: htmlText,
                    trigger: 'hover click',
                });
            }
        }
        else if (obj.msg) {
            $.custom.easyui.alert.show(obj.msg, $.custom.utils.lan.defaults.titleWarning, '', 'warning');
        }
    });
}

function funcUpdateVehicleList{$this->_autoId}(vehicleModelId, timeData) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var vehicleFields = ['{$this->_idPrefix}vehicle_id{$this->_autoId}', '{$this->_idPrefix}replace_vehicle_id{$this->_autoId}'];
    var sourceType = parseInt($('#{$this->_idPrefix}source{$this->_autoId}', parentForm).combobox('getValue'));
    var priceType = parseInt($('#{$this->_idPrefix}pay_type{$this->_autoId}', parentForm).combobox('getValue'));
    if (isNaN(sourceType)) { sourceType = {$defaultSourceOffice}; }
    if (isNaN(priceType)) { priceType = {$defaultPayType}; }
    for (var i in vehicleFields) {
        var dgTarget = $('#'+vehicleFields[i], parentForm);
        if (!dgTarget || dgTarget.length == 0) {
            continue;
        }
        var vehicleId = dgTarget.combogrid('getValue');
        var g = dgTarget.combogrid('grid');
        //g.datagrid('options').queryParams = {}; // set a new instance to avoid affect all datagrid.
        var queryParams = g.datagrid('options').queryParams;
        queryParams.start_time = timeData.startTime;
        queryParams.end_time = timeData.endTime;
        queryParams.pay_type = priceType;
        queryParams.source_type = sourceType;
        queryParams.vehicle_model_id = vehicleModelId;
        if (vehicleModelId == {$vehicleModelId} && vehicleId) {
            queryParams.vehicle_id = vehicleId;
        }
        else {
            queryParams.vehicle_id = undefined;
        }

        g.datagrid('reload');
    }
}

function funcOnVehicleListLoadSuccess{$this->_autoId}(data) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var opts = $(this).datagrid('options');
    var dgTarget = $('#'+opts.id);
    var val = dgTarget.combogrid('getValue');
    if (!val) {
        if ({$this->vehicleId}) {
            val = {$this->vehicleId};
        }
    }
    if (data.rows) {
        var isNotFound = true;
        var matchIndex = 0;
        var matchRow = undefined;
        for (var i in data.rows) {
            if (val == data.rows[i].id) {
                isNotFound = false;
                matchIndex = i;
                matchRow = data.rows[i];
                break;
            }
        }
        if (isNotFound) {
            dgTarget.combogrid('clear');
        }
        else {
            dgTarget.combogrid('setValue', val);
            if (matchIndex) {
                setTimeout(function() { funcOnSelectVehicle{$this->_autoId}(matchIndex, matchRow, true); }, 100);
            }
        }
    }
    else {
        dgTarget.combogrid('clear');
    }
}

function funcOnselectUser{$this->_autoId}(record) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var arrAttributes = {
        customer_telephone : record.telephone,
        customer_fixedphone : record.fixedphone,
        customer_id : record.identity_id,
        customer_address : record.home_address,
        customer_postcode : record.post_code,
        customer_employer : record.company_name,
        customer_employer_address : record.company_address,
        customer_employer_postcode : record.company_postcode,
        customer_employer_phone : record.company_telephone,
        customer_employer_certificate_id : record.company_license,
        emergency_contact_name : record.emergency_contact,
        emergency_contact_phone : record.emergency_telephone,
    };
    var arrAttributes1 = {
        customer_id_type : record.identity_type,
    }
    var arrAttributes2 = {
        user_id : record.user_id,
        customer_operator_name : record.text,
    }
    userInfo{$this->_autoId} = {
        id : record.user_id,
        name : record.text,
        credit_level : record.credit_level,
        credit_level_disp : record.credit_level_disp,
        blacklist_reason : record.blacklist_reason,
    }
    
    for (var k in arrAttributes) {
        var oEle = $('#{$this->_idPrefix}'+k+'{$this->_autoId}', parentForm);
        if (oEle.length > 0) {
            oEle.textbox('setValue', arrAttributes[k]);
        }
    }
    for (var k in arrAttributes1) {
        var oEle = $('#{$this->_idPrefix}'+k+'{$this->_autoId}', parentForm);
        if (oEle.length > 0) {
            oEle.combobox('setValue', arrAttributes1[k]);
        }
    }
    for (var k in arrAttributes2) {
        var oEle = $('#{$this->_idPrefix}'+k+'{$this->_autoId}', parentForm);
        if (oEle.length > 0) {
            oEle.val(arrAttributes2[k]);
        }
    }
    
    
    if (record.driver_license_time > 0) {
        if ($('#{$this->_idPrefix}customer_driver_license_time{$this->_autoId}', parentForm).length > 0) {
            var str=$.custom.utils.humanTime(record.driver_license_time);
            var dt = str.slice(0,10);
            
            $('#{$this->_idPrefix}customer_driver_license_time{$this->_autoId}', parentForm).textbox('setValue', dt);
        }
    }
    if (record.driver_license_expire_time > 0) {
        if ($('#{$this->_idPrefix}customer_driver_license_expire_time{$this->_autoId}', parentForm).length > 0) {
            var str1=$.custom.utils.humanTime(record.driver_license_expire_time);
            var det = str1.slice(0,10);
            $('#{$this->_idPrefix}customer_driver_license_expire_time{$this->_autoId}', parentForm).textbox('setValue', det);
        }
    }

    if (record.credit_level < 0) {
        var msg = '';
        if (record.blacklist_reason) {
            msg = '$strCreditWarningWithReasonText'.Format(record.credit_level_disp, record.blacklist_reason);
        } else {
            msg = '{$strCreditWarningText}'.Format(record.credit_level_disp);
        }
        
        $.custom.easyui.alert.show(msg, $.custom.utils.lan.defaults.titleWarning, 'error', 'warning', 0);
    }
}

function funcSearchUser{$this->_autoId}(newValue, oldValue) {
    var obj = $(this);
    obj.combobox('reload', '{$this->_urlSearchUser}'+encodeURI(newValue));
}

function funcGetOptionalServicePrice{$this->_autoId}(timeData) {
    var parentForm = $('#{$this->_formId}');
    var priceOptional = 0;
    var rentDays = timeData.rentDays;
    if ({$isOptionalServiceOvertimeAsOneDay} && timeData.rentHours > 0) {
        rentDays++;
    }
    for (var i in optionalServicePriceInfo{$this->_autoId}) {
        var o = optionalServicePriceInfo{$this->_autoId}[i];
        var e1 = $('#{$this->_idPrefix}optional_service_price{$this->_autoId}_' + o.id, parentForm);
        var e2 = $('#{$this->_idPrefix}optional_service_selection{$this->_autoId}_' + o.id, parentForm);
        if (e1.length && e2.length) {
            if (e2[0].checked) {
                var unitPrice = parseFloat(e1.numberbox('getValue'));
                if (isNaN(unitPrice)) { unitPrice = 0; }
                if (o.unit_type == 1)   // per days
                {
                    var days = rentDays;
                    if (o.month_days > 0 && o.month_days < 30 && days > o.month_days) {
                        var d = Math.floor(days / 30);
                        var m = days % 30;
                        if (m > o.month_days) {
                            m = o.month_days;
                        }
                        days = d * o.month_days + m;
                    }
                    priceOptional += unitPrice * days;
                }
                else if (o.unit_type == 5)  // per kilometer
                {
                    // todo
                }
                else {
                    priceOptional += unitPrice;
                }
            }
        }
    }
        
    return priceOptional;
}

function funcUpdateOptionalServicePrices{$this->_autoId}(newValue, oldValue) {
    var parentForm = $('#{$this->_formId}');
    easyuiFuncAjaxSendDataWithoutAlert('{$this->_urlGetOptionalPrices}', 'get', {office: newValue}, function(data){
        var obj = eval('(' + data + ')');
        for (var i in optionalServicePriceInfo{$this->_autoId}) {
            var o = optionalServicePriceInfo{$this->_autoId}[i];
            if (obj[o.id] != undefined) {
                var e1 = $('#{$this->_idPrefix}optional_service_price{$this->_autoId}_' + o.id, parentForm);
                if (e1.length && e1.numberbox('getValue') != obj[o.id]) {
                    e1.numberbox('setValue', obj[o.id]);
                }
            }
        }
    });
}

function funcUpdateServicePriceCommon{$this->_autoId}(url, params, fieldTarget) {
    easyuiFuncAjaxSendDataWithoutAlert(url, 'get', params, function(data){
        var obj = eval('(' + data + ')');
        if (obj.result == 0) {
            fieldTarget.numberbox('setValue', obj.price);
        }
        else if (obj.desc) {
            $.custom.easyui.alert.show(obj.desc, $.custom.utils.lan.defaults.titleWarning, '', 'error');
        }
    });
}

function funcGetServicePriceBetweenOffice{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    //var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('getValue');
    var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val();
    var officeId1 = $('#{$this->_idPrefix}office_id_rent{$this->_autoId}', parentForm).combotree('getValue');
    var officeId2 = $('#{$this->_idPrefix}office_id_return{$this->_autoId}', parentForm).combotree('getValue');
    var oTarget = $('#{$this->_idPrefix}price_different_office{$this->_autoId}', parentForm);
    if (vehicleModelId == '' || officeId1 == '' || officeId2 == '') {
        return ;
    }
    if (officeId1 == officeId2) {
        oTarget.numberbox('setValue', 0);
        return;
    }
    funcUpdateServicePriceCommon{$this->_autoId}('{$this->_urlServicePriceBetweenOffice}', {vehicle_model:vehicleModelId, office1:officeId1, office2:officeId2}, oTarget);
}

function funcGetServicePriceTakeCar{$this->_autoId}(newValue, oldValue) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    //var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('getValue');
    var vehicleModelId = $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val();
    var officeId = $('#{$this->_idPrefix}office_id_rent{$this->_autoId}', parentForm).combotree('getValue');
    if (vehicleModelId == '' || officeId == '') {
        return ;
    }
    var oTarget = $('#{$this->_idPrefix}price_take_car{$this->_autoId}', parentForm);
    if (newValue == '') {
        oTarget.numberbox('setValue', 0);
        return ;
    }
    funcUpdateServicePriceCommon{$this->_autoId}('{$this->_urlServicePriceByAddress2Office}', {vehicle_model:vehicleModelId, address:newValue, office:officeId}, oTarget);
}

function funcUpdateInv_amount{$this->_autoId}(newValue,oldValue){
    console.log(newValue,oldValue);
    if(parseInt(newValue) < parseInt(oldValue)){
        alert("新开发票数字不能小于已开发票数字,请重新确认后输入");
        var invDisplayObj = $('#{$this->_idPrefix}inv_amount{$this->_autoId}');
        invDisplayObj.val(oldValue);
        invDisplayObj.html(oldValue);
        invDisplayObj.parent().find('span').find('.textbox-value').val(oldValue);

        return ;
    }
}

function funcGetServicePriceReturnCar{$this->_autoId}(newValue, oldValue) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var officeId = $('#{$this->_idPrefix}office_id_return{$this->_autoId}', parentForm).combotree('getValue');
    if (officeId == '') {
        return ;
    }
    var oTarget = $('#{$this->_idPrefix}price_return_car{$this->_autoId}', parentForm);
    if (newValue == '') {
        oTarget.numberbox('setValue', 0);
        return ;
    }
    funcUpdateServicePriceCommon{$this->_autoId}('{$this->_urlServicePriceByAddress2Office}', {address:newValue, office:officeId}, oTarget);
}

function funcUpdateTotalPrice{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    
    var timeData = funcGetOrderTimes{$this->_autoId}(true);
    
    var rentDays = timeData.rentDays;
    var priceRent = 0;
    var unitPriceOvertime = 0;
    var unitPriceDesignatedDriving = 0;
    var unitPriceBasicInsurance = 0;
    var pricePoundage = 0;
    var pricePreferential = 0;
    var unitPriceDesignatedDriving = 0;
    var priceDesignatedDriving = 0;
    var priceDesignatedDrivingOvertime = 0;
    var priceDesignatedDrivingOvermileage = 0;
    var priceOvertime = 0;
    var priceOvermileage = 0;
    var priceOil = 0;
    var priceOilPoundage = 0;
    var priceOther = 0;
    var priceCarDamage = 0;
    var priceViolation = 0;
    var priceFree = 0;
    var priceOptional = {$originOptionalPrice};
    var priceDifferentOffice = 0;
    var priceTakeCar = 0;
    var priceReturnCar = 0;
    
    priceRent = parseFloat($('#{$this->_idPrefix}price_rent{$this->_autoId}', parentForm).numberbox('getValue'));
    unitPriceOvertime = parseFloat($('#{$this->_idPrefix}unit_price_overtime{$this->_autoId}', parentForm).numberbox('getValue'));
    //unitPriceDesignatedDriving = parseFloat($('#{$this->_idPrefix}unit_price_designated_driving{$this->_autoId}', parentForm).numberbox('getValue'));
    pricePoundage = parseFloat($('#{$this->_idPrefix}price_poundage{$this->_autoId}', parentForm).numberbox('getValue'));
    unitPriceBasicInsurance = parseFloat($('#{$this->_idPrefix}unit_price_basic_insurance{$this->_autoId}', parentForm).numberbox('getValue'));
    pricePreferential = parseFloat($('#{$this->_idPrefix}price_preferential{$this->_autoId}', parentForm).numberbox('getValue'));
    priceDifferentOffice = parseFloat($('#{$this->_idPrefix}price_different_office{$this->_autoId}', parentForm).numberbox('getValue'));
    priceTakeCar = parseFloat($('#{$this->_idPrefix}price_take_car{$this->_autoId}', parentForm).numberbox('getValue'));
    priceReturnCar = parseFloat($('#{$this->_idPrefix}price_return_car{$this->_autoId}', parentForm).numberbox('getValue'));
    
    if ({$jsIsSettlement}) {
        //priceOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_overtime{$this->_autoId}', parentForm).numberbox('getValue'));
        //priceOvermileage = parseFloat($('#{$this->_idPrefix}settlement_price_overmileage{$this->_autoId}', parentForm).numberbox('getValue'));
        priceInsuranceOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_insurance_overtime{$this->_autoId}', parentForm).numberbox('getValue'));
        priceOil = parseFloat($('#{$this->_idPrefix}settlement_price_oil{$this->_autoId}', parentForm).numberbox('getValue'));
        priceOilPoundage = parseFloat($('#{$this->_idPrefix}settlement_price_oil_agency{$this->_autoId}', parentForm).numberbox('getValue'));
        priceOther = parseFloat($('#{$this->_idPrefix}settlement_price_other{$this->_autoId}', parentForm).numberbox('getValue'));
        priceCarDamage = parseFloat($('#{$this->_idPrefix}settlement_price_car_damage{$this->_autoId}', parentForm).numberbox('getValue'));
        priceViolation = parseFloat($('#{$this->_idPrefix}settlement_price_violation{$this->_autoId}', parentForm).numberbox('getValue'));
        //priceDesignatedDriving = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving{$this->_autoId}', parentForm).numberbox('getValue'));
        //priceDesignatedDrivingOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving_overtime{$this->_autoId}', parentForm).numberbox('getValue'));
        //priceDesignatedDrivingOvermileage = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving_overmileage{$this->_autoId}', parentForm).numberbox('getValue'));
        pricePreferential = parseFloat($('#{$this->_idPrefix}settlement_price_preferential{$this->_autoId}', parentForm).numberbox('getValue'));
        //priceFree = parseFloat($('#{$this->_idPrefix}settlement_price_free{$this->_autoId}', parentForm).numberbox('getValue'));

        if (timeData.endTime > {$originEndTime}) {
            //priceOptional = funcGetOptionalServicePrice{$this->_autoId}(timeData);
        }
        else {
            var originRentDaysData = funcCalculateRentDaysByTime{$this->_autoId}({$originStartTime}, {$originEndTime});
            if (rentDays < originRentDaysData.rentDays) {
                if (rentDays < 1) {
                    pricePreferential = 0;
                }
                // TODO update preferential in fields
            }
        }
        priceOptional = funcGetOptionalServicePrice{$this->_autoId}(timeData);
    }
    else {
        //priceOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_overtime{$this->_autoId}', parentForm)[0].value);
        //priceOvermileage = parseFloat($('#{$this->_idPrefix}settlement_price_overmileage{$this->_autoId}', parentForm)[0].value);
        priceInsuranceOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_insurance_overtime{$this->_autoId}', parentForm)[0].value);
        priceOil = parseFloat($('#{$this->_idPrefix}settlement_price_oil{$this->_autoId}', parentForm)[0].value);
        priceOilPoundage = parseFloat($('#{$this->_idPrefix}settlement_price_oil_agency{$this->_autoId}', parentForm)[0].value);
        priceOther = parseFloat($('#{$this->_idPrefix}settlement_price_other{$this->_autoId}', parentForm)[0].value);
        priceCarDamage = parseFloat($('#{$this->_idPrefix}settlement_price_car_damage{$this->_autoId}', parentForm)[0].value);
        priceViolation = parseFloat($('#{$this->_idPrefix}settlement_price_violation{$this->_autoId}', parentForm)[0].value);
        //priceDesignatedDriving = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving{$this->_autoId}', parentForm)[0].value);
        //priceDesignatedDrivingOvertime = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving_overtime{$this->_autoId}', parentForm)[0].value);
        //priceDesignatedDrivingOvermileage = parseFloat($('#{$this->_idPrefix}settlement_price_designated_driving_overmileage{$this->_autoId}', parentForm)[0].value);
        //pricePreferential = parseFloat($('#{$this->_idPrefix}settlement_price_preferential{$this->_autoId}', parentForm)[0].value);
        //priceFree = parseFloat($('#{$this->_idPrefix}settlement_price_free{$this->_autoId}', parentForm)[0].value);
        
        priceOptional = funcGetOptionalServicePrice{$this->_autoId}(timeData);
    }
    
    if (isNaN(rentDays)) { rentDays = 1; }
    if (isNaN(priceRent)) { priceRent = 0; }
    if (isNaN(unitPriceOvertime)) { unitPriceOvertime = 0; }
    if (isNaN(unitPriceDesignatedDriving)) { unitPriceDesignatedDriving = 0; }
    if (isNaN(pricePoundage)) { pricePoundage = 0; }
    if (isNaN(unitPriceBasicInsurance)) { unitPriceBasicInsurance = 0; }
    if (isNaN(pricePreferential)) { pricePreferential = 0; }
    if (isNaN(priceDifferentOffice)) { priceDifferentOffice = 0; }
    if (isNaN(priceTakeCar)) { priceTakeCar = 0; }
    if (isNaN(priceReturnCar)) { priceReturnCar = 0; }
    if (isNaN(priceOvertime)) { priceOvertime = 0; }
    //if (isNaN(priceOvermileage)) { priceOvermileage = 0; }
    if (isNaN(priceInsuranceOvertime)) { priceInsuranceOvertime = 0; }
    if (isNaN(priceOil)) { priceOil = 0; }
    if (isNaN(priceOilPoundage)) { priceOilPoundage = 0; }
    if (isNaN(priceOther)) { priceOther = 0; }
    if (isNaN(priceCarDamage)) { priceCarDamage = 0; }
    if (isNaN(priceViolation)) { priceViolation = 0; }
    //if (isNaN(priceDesignatedDriving)) { priceDesignatedDriving = 0; }
    //if (isNaN(priceDesignatedDrivingOvertime)) { priceDesignatedDrivingOvertime = 0; }
    //if (isNaN(priceDesignatedDrivingOvermileage)) { priceDesignatedDrivingOvermileage = 0; }
    //if (isNaN(priceFree)) { priceFree = 0; }
    
    // not support designated driving calculating yet
    priceDesignatedDriving = unitPriceDesignatedDriving * rentDays;
    priceBasicInsurance = unitPriceBasicInsurance * rentDays;
    
    priceOvertime = unitPriceOvertime * timeData.rentHours;
    
    var priceTotal = priceRent + priceOptional + pricePoundage + priceDesignatedDriving + priceBasicInsurance +
        priceOvertime + priceOvermileage + priceInsuranceOvertime + priceOil + priceOilPoundage +
        priceOther + priceCarDamage + priceViolation + priceDesignatedDriving + priceDesignatedDrivingOvertime + priceDesignatedDrivingOvermileage +
        + priceDifferentOffice + priceTakeCar + priceReturnCar
        - (pricePreferential + priceFree);

    var targetTotalPrice = $('#{$this->_idPrefix}total_amount{$this->_autoId}', parentForm);
    var targetPricePerDay = $('#{$this->_idPrefix}rent_per_day{$this->_autoId}', parentForm);
    var targetPriceOvertime = $('#{$this->_idPrefix}price_overtime{$this->_autoId}', parentForm);
    if (targetTotalPrice && targetTotalPrice.length > 0) {
        targetTotalPrice.numberbox('setValue', priceTotal);
    }
    if (targetPricePerDay && targetPricePerDay.length > 0) {
        var pricePerDay = (rentDays ? priceRent / rentDays : 0);
        targetPricePerDay.numberbox('setValue', pricePerDay);
    }
    if (targetPriceOvertime && targetPriceOvertime.length > 0) {
        targetPriceOvertime.numberbox('setValue', priceOvertime);
    }
    
    if ({$jsIsSettlement}) {
        $('#{$this->_idPrefix}settlement_price_overtime{$this->_autoId}', parentForm).numberbox('setValue', priceOvertime);
        $('#{$this->_idPrefix}settlement_total_amount{$this->_autoId}', parentForm).numberbox('setValue', priceTotal);

        var pricePaid = parseFloat($('#{$this->_idPrefix}settlement_paid_amount{$this->_autoId}', parentForm).numberbox('getValue'));
        $('#{$this->_idPrefix}settlement_left_amount{$this->_autoId}', parentForm).numberbox('setValue', (priceTotal - pricePaid));
    }
        
    var exTotalDisplayObj = $('#{$this->_idPrefix}ex_total_amount{$this->_autoId}');
    if (exTotalDisplayObj.length > 0) {
        exTotalDisplayObj.val(priceTotal);
        exTotalDisplayObj.html(priceTotal);
    }
}

function funcInitializeElements{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    for (var i in optionalServicePriceInfo{$this->_autoId}) {
        var o = optionalServicePriceInfo{$this->_autoId}[i];
        var e2 = $('#{$this->_idPrefix}optional_service_selection{$this->_autoId}_' + o.id, parentForm);
        if (e2.length) {
            e2.click(function(){
                setTimeout(function() { funcUpdateTotalPrice{$this->_autoId}(); },100);
            });
        }
    }
}

function funcOnSelectRentPayMethod{$this->_autoId}(record) {
    if (true) {
        return;
    }
    setTimeout(function() {
    if (record.value == 0) {
        $('#{$this->_idPrefix}paid_amount{$this->_autoId}').numberbox('readonly', true);
    } else {
        $('#{$this->_idPrefix}paid_amount{$this->_autoId}').numberbox('readonly', false);
    }
    }, 100);
}

function funcOnSelectDepositPayMethod{$this->_autoId}(record) {
    if (true) {
        return;
    }
    setTimeout(function() {
    if (record.value == 0) {
        $('#{$this->_idPrefix}price_deposit_violation{$this->_autoId}').numberbox('readonly', true);
    } else {
        $('#{$this->_idPrefix}price_deposit_violation{$this->_autoId}').numberbox('readonly', false);
    }
    }, 100);
}

function funcOnSubmitSuccessEvents{$this->_autoId}(data, params) {
    if (data == '') return;
    easyuiFuncOnProcessSuccessEvents(data);
    var obj = undefined;
    if ($.type(data) == 'string') {
        try {
            obj = eval('(' + data + ')');
        }
        catch (e) {
            obj = undefined;
        }
    }
    if (obj) {
        if (obj.statusCode == 200 && params.next_action && obj.attributes) {
            var urlPrintOrder = undefined;
            if (params.next_action == 'print_booking') {
                urlPrintOrder = '{$this->_urlPrintBookingOrder}' + obj.attributes.orderId;
            }
            else if (params.next_action == 'dispatch_vehicle') {
                urlPrintOrder = '{$this->_urlPrintDispatchOrder}' + obj.attributes.orderId;
            }
            else if (params.next_action == 'validation_vehicle') {
                urlPrintOrder = '{$this->_urlPrintValidationOrder}' + obj.attributes.orderId;
            }
            else if (params.next_action == 'settlement_order') {
                urlPrintOrder = '{$this->_urlPrintSettlementOrder}' + obj.attributes.orderId;
            }
                
            if (urlPrintOrder) {
                setTimeout(function() {
                    $.printPreview.loadPrintPreview(undefined, {url:urlPrintOrder, selector:false, pageSize:'A4', pageDirection:'portrait', margin:'2cm'});
                }, 200);
            }
        }
    }
}

function funcSearchUserOrders{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    
    var userId = $('#{$this->_idPrefix}user_id{$this->_autoId}', parentForm).val();
    var userName = $('#{$this->_idPrefix}customer_name{$this->_autoId}', parentForm).val();
    if (userId && userName) {
        easyuiFuncNavTabAddDoNotKnownId(userName+'的订单', '{$this->_urlGetUserOrders}&user_id='+userId);
    }
}

function funcPreferentialType{$this->_autoId}(record) {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var processType = parseInt(record.type);
    if (isNaN(processType) || processType < 0) {
        processType = 0;
    }
    var oTarget = $('#{$this->_idPrefix}preferential_type{$this->_autoId}', parentForm);
    if (oTarget.length == 1) {
        oTarget.val(processType);
    }
}

function funcLoadPaymentInputPage{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    easyuiFuncFormOnSubmit('#{$this->_formId}', {next_action:'paymentinput'}, function(data, params){
        easyuiFuncAjaxEndLoading();
        if (data == '') return;
        var obj = undefined;
        if ($.type(data) == 'string') {
            try {
                obj = eval('(' + data + ')');
            }
            catch (e) {
                obj = undefined;
            }
        }
        if (obj) {
            if (obj.statusCode == 200 && obj.attributes) {
                var orderId = obj.attributes.orderId;
                if ({$jsIsInsertAction}) {
                    $('input[name=id]', parentForm).val(orderId);
                    $("input[name='{$this->_formModel->formName()}[id]']", parentForm).val(orderId);
                    $('input[name=action]', parentForm).val('update');
                }
                $.custom.bootstrap.showModal('#{$this->_paymentModalId}', '{$this->_urlPaymentinput}'+orderId);
            }
            else {
                easyuiFuncOnProcessSuccessEvents(data);
            }
        }
    }, funcOnSubmitCheck{$this->_autoId});
}

function funcOnSubmitCheck{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return false;
    }
    
    if (userInfo{$this->_autoId} && userInfo{$this->_autoId}.credit_level < 0) {
        var msg = '';
        if (userInfo{$this->_autoId}.blacklist_reason) {
            msg = '$strCreditWarningWithReasonText'.Format(userInfo{$this->_autoId}.credit_level_disp, userInfo{$this->_autoId}.blacklist_reason);
        } else {
            msg = '{$strCreditWarningText}'.Format(userInfo{$this->_autoId}.credit_level_disp);
        }
        
        $.custom.easyui.alert.show(msg, $.custom.utils.lan.defaults.titleWarning, 'error', 'warning', 0);
        return false;
    }
    var expireTime = $.custom.utils.toTimestamp($('#{$this->_idPrefix}customer_driver_license_expire_time{$this->_autoId}', parentForm).textbox('getValue'));
    if (expireTime) {
        var timeData = funcGetOrderTimes{$this->_autoId}(true);
        if (timeData.endTime > expireTime && (!isConfirmRentCar{$this->_autoId})) {
            msg = $.custom.lan.defaults.vehicle.drivingLisenceWouldExpired;
            $.messager.confirm($.custom.utils.lan.defaults.titleWarning, msg, function(r){
                if (r) {
                    isConfirmRentCar{$this->_autoId} = true;
                    $.custom.easyui.alert.show($.custom.lan.defaults.vehicle.youConfirmedToRentPleaseSubmitAgain, $.custom.utils.lan.defaults.titlePrompt, 'info', 'info', 0);
                }
            });
            return false;
        }
    }
    
    return true;
}

EOD;
        if ($this->_enableSettlement) {
            $arrScripts[] = <<<EOD
function funcUpdateSettlementPrices{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    
    var timeData = funcGetOrderTimes{$this->_autoId}(true);
    var vehicleModelId = parseInt($('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).val());
    //var vehicleModelId = parseInt($('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}', parentForm).combobox('getValue'));
    //var unitPriceOvertime = parseFloat($('#{$this->_idPrefix}unit_price_overtime{$this->_autoId}', parentForm).numberbox('getValue'));
    //var priceOvertime = unitPriceOvertime * timeData.rentHours;
    //$('#{$this->_idPrefix}settlement_price_overtime{$this->_autoId}', parentForm).numberbox('setValue', priceOvertime);
    funcUpdateRentDays{$this->_autoId}(undefined,undefined,true);
    //funcUpdateRentPrice{$this->_autoId}(vehicleModelId, timeData);
}
function funcOnClickSettlementDepositAsRent{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    //
}
function funcOnClickSettlementBonusPointDeduction{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    var myObj = $(this);
    if (myObj.attr('checked') != false) {
        alert('selected');
    }
    else {
        alert('unselected');
    }
}

setTimeout(function(){
    funcUpdateSettlementPrices{$this->_autoId}();
},100);

EOD;
        }
        
        if (!empty($this->_orderModel->id)) {
            $getPaidAmountUrl = \yii\helpers\Url::to(['order/get_order_paid_amount', 'order_id'=>$this->_orderModel->id]);
            $arrScripts[] = <<<EOD
var nSyncOrderPricePaidCount{$this->_autoId} = 0;
function funcSyncOrderPricePaid{$this->_autoId}() {
    var parentForm = $('#{$this->_formId}');
    if (parentForm.length != 1) {
        return ;
    }
    $.ajax({
        url:'{$getPaidAmountUrl}', 
        type:'get', 
        error:function(e){
            easyuiFuncOnProcessErrorEvents(e);
        },
        success:function(data){
            var obj = eval('(' + data + ')');
            if (obj.code == 0) {
                var oPaidTarget1 = $('#{$this->_idPrefix}paid_amount{$this->_autoId}', parentForm);
                if (oPaidTarget1.length == 0) {
                    return;
                }
                var oPaidTarget2 = $('#{$this->_idPrefix}settlement_paid_amount{$this->_autoId}', parentForm);
                var opts1 = oPaidTarget1.numberbox('options');
                if (opts1.editable == true) {
                    if (obj.amount > parseFloat(oPaidTarget1.numberbox('getValue'))) {
                        oPaidTarget1.numberbox('setValue', obj.amount);
                    }
                }
                else {
                    oPaidTarget1.numberbox('setValue', obj.amount);
                }
                if (oPaidTarget2.length == 1) {
                    var opts2 = oPaidTarget2.numberbox('options');
                    if (opts2.editable == true) {
                        if (obj.amount > parseFloat(oPaidTarget2.numberbox('getValue'))) {
                            oPaidTarget2.numberbox('setValue', obj.amount);
                        }
                    }
                    else {
                        oPaidTarget2.numberbox('setValue', obj.amount);
                    }
                }

                nSyncOrderPricePaidCount{$this->_autoId}++;
                var dt = 10000;
                if (nSyncOrderPricePaidCount{$this->_autoId} < 15) {
                    dt = 2000;
                }
                else if (nSyncOrderPricePaidCount{$this->_autoId} < 90) {
                    dt = 1000;
                }
                else if (nSyncOrderPricePaidCount{$this->_autoId} < 690) {
                    dt = 5000;
                }
                setTimeout(function() {
                    funcSyncOrderPricePaid{$this->_autoId}();
                }, dt);
            }
            else if (obj.msg) {
                $.custom.easyui.alert.show(obj.msg, $.custom.utils.lan.defaults.titleWarning, '', 'warning');
            }
        }
    });
}

//setTimeout(function() {
//    funcSyncOrderPricePaid{$this->_autoId}();
//}, 3000);
EOD;
        }
        
        $printLoadingMessage = \Yii::t('locale', 'Loading your printing document');
        $arrScripts[] = <<<EOD
$(document).ready(function() {
    $(".{$this->_printerClass}").printPreview({
        'message':'{$printLoadingMessage}',
    });
    
    $.fn.typeahead.Constructor.prototype.blur = function() {
        var that = this;
        setTimeout(function () { that.hide() }, 250);
    };
    $('#{$this->_idPrefix}customer_name{$this->_autoId}').typeahead({
        source: function (query, process) {
            var element = this.\$element;
            $.get('{$this->_urlSearchUser}'+encodeURI(query), undefined, function (data) {
                var obj = eval('(' + data + ')');
                if (obj.length) {
                    element.data('datalist', obj);
                    var results = _.map(obj, function(item) {
                        return item.user_id+"";
                    });
                    process(results);
                }
            });
        },
        matcher: function (item) {
            return true;
        },
        highlighter: function (id) {
            var datalist = this.\$element.data('datalist');
            var item = _.find(datalist, function (_item) {
                return _item.user_id == id;
            });
            return item.text + '(' + item.identity_id + ')';
        },
        updater: function(id) {
            var datalist = this.\$element.data('datalist');
            var item = _.find(datalist, function (_item) {
                return _item.user_id == id;
            });
            funcOnselectUser{$this->_autoId}(item);
            return item.text;
        }
    });
    $('#{$this->_idPrefix}vehicle_model_text{$this->_autoId}').typeahead({
        source: function (query, process) {
            var element = this.\$element;
            $.get('{$this->_urlSearchVehicleModel}'+encodeURI(query), undefined, function (data) {
                var obj = eval('(' + data + ')');
                if (obj.length) {
                    element.data('datalist', obj);
                    var results = _.map(obj, function(item) {
                        return item.id+"";
                    });
                    process(results);
                }
            });
        },
        matcher: function (item) {
            return true;
        },
        highlighter: function (id) {
            var datalist = this.\$element.data('datalist');
            var item = _.find(datalist, function (_item) {
                return _item.id == id;
            });
            return item.text;
        },
        updater: function(id) {
            var datalist = this.\$element.data('datalist');
            var item = _.find(datalist, function (_item) {
                return _item.id == id;
            });
            funcOnSelectVehicleModel{$this->_autoId}(item);
            $('#{$this->_idPrefix}vehicle_model_id{$this->_autoId}').val(item.id);
            return item.text;
        }
    });
});
EOD;
        
        if ($this->_userInfoModel->id) {
            if ($this->_userInfoModel->credit_level <= \common\models\Pub_user_info::CREDIT_LEVEL_WARNING) {
                $arrCreditLevels = \common\models\Pub_user_info::getCreditLevelsArray();
                $msg = '';
                if ($this->_userInfoModel->blacklist_reason) {
                    $msg = \Yii::t('carrental', 'Current user credit level were set to {level} because of {reason}, please pay attention!', ['level'=>(isset($arrCreditLevels[$this->_userInfoModel->credit_level]) ? $arrCreditLevels[$this->_userInfoModel->credit_level] : $this->_userInfoModel->credit_level), 'reason'=>  $this->_userInfoModel->blacklist_reason]);
                }
                else {
                    $msg = \Yii::t('carrental', 'Current user credit level is {level}, please pay attention!', ['level'=>(isset($arrCreditLevels[$this->_userInfoModel->credit_level]) ? $arrCreditLevels[$this->_userInfoModel->credit_level] : $this->_userInfoModel->credit_level)]);
                }
                $arrScripts[] = "setTimeout(function(){ $.custom.easyui.alert.show('{$msg}', $.custom.utils.lan.defaults.titleWarning, 'error', 'warning', 0); }, 500);";
            }
        }
        
        return implode("\n\n", $arrScripts);
    }
    
}
