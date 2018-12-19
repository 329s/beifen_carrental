<?php

namespace backend\widgets;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class OrderhourPaymentInputWidget extends \yii\base\Widget
{
    
    public $orderId;
    public $isRelet;
    public $isSettlement;
    public $orderAction;
    
    private $_idPrefix;
    private $_autoId;
    private $_rootUrl;
    private $_orderModel;
    private $_paidDetailModel;
    private $_formId;
    private $_time;
    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init() {
        parent::init();
        $this->_idPrefix = \common\helpers\CMyHtml::getIDPrefix();
        $this->_autoId = \common\helpers\CMyHtml::genID();
        $this->_rootUrl = \common\helpers\Utils::getRootUrl();
        $this->_orderModel = \common\models\Pro_vehicle_order::findById($this->orderId);
        $this->_paidDetailModel = \common\models\Pro_vehicle_order_price_detail::statisticObject(\common\models\Pro_vehicle_order_price_detail::TYPE_PAID, $this->orderId);
		
	$this->_time = date('Y-m-d H:i:i');
        
    }
    
    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $htmlArray = [];
        if (!$this->_orderModel) {
            return implode("\n", $htmlArray);
        }
        $htmlArray[] = \yii\helpers\Html::jsFile("{$this->_rootUrl}app/carrental/backend/web/js/paymentinputhelper.js");
        
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'modal-dialog']);
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'modal-content']);
        
        $submitUrl = \yii\helpers\Url::to(['orderhour/paymentinput']);
        $this->_formId = "{$this->_idPrefix}paymentinputform{$this->_autoId}";
        $objFormModel = new \backend\models\Form_pro_vehicle_order_price_detail();
        //$form = new \common\widgets\ActiveFormExtendWidget();
        $form = \common\widgets\ActiveFormExtendWidget::begin([
            'id' => $this->_formId,
            'action' => $submitUrl,
        ]);
        $htmlArray[] = \yii\helpers\Html::beginForm($submitUrl, 'post', ['id'=>$this->_formId, 'class'=>"form-horizontal"]);
        
        $title = \Yii::t('carrental', 'Order({serial}) details', ['serial'=>$this->_orderModel->serial]);
        //$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel($title, ['class'=>'panel-default']);
        $headerHtmlArray = [];
        $headerHtmlArray[] = \yii\helpers\Html::tag('button', '&times;', ['type'=>'button', 'class'=>'close', 'data-dismiss'=>'modal', 'aria-hidden'=>'true']);
        $headerHtmlArray[] = \yii\helpers\Html::tag('h3', $title);
        $htmlArray[] = \yii\helpers\Html::tag('div', implode("\n", $headerHtmlArray), ['class'=>'modal-header']);
        
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'modal-body']);
        //$content = "";
        $arrHeaders = [\Yii::t('locale', 'Item'), \Yii::t('locale', 'Amount'), \Yii::t('locale', 'Amount paid'), \Yii::t('locale', 'Amount payable'), \Yii::t('locale', 'Payment method')];
        $htmlArray[] = \yii\helpers\Html::beginTag('table', ['class'=>'table']);
        $htmlArray[] = \yii\helpers\Html::beginTag('tr');
        foreach ($arrHeaders as $h) {
            $htmlArray[] = \yii\helpers\Html::tag('th', $h, []);
        }
        $htmlArray[] = \yii\helpers\Html::endTag('tr');
        $htmlArray[] = \yii\helpers\Html::beginTag('thead');
        $htmlArray[] = \yii\helpers\Html::endTag('thead');
        $htmlArray[] = \yii\helpers\Html::beginTag('tbody');
        
        $fields = $this->_orderModel->getPriceAttributeFields();
        $preferentialFields = \common\models\Pro_vehicle_order::getPreferentialPriceFields();
        $orderAttributes = $this->_orderModel->getAttributes();
        $orderAttributes['price_rent'] = floatval($orderAttributes['price_rent']) - $this->_orderModel->getTotalPreferentialPrice();
        
        $tableRows1 = [];
        $tableRows2 = [];
        $summaryAmmountTotal = 0;
        $summaryAmmountPaid = 0;
        $summaryAmmount = 0;
        $summaryDepositTotal = 0;
        $summaryDepositPaid = 0;
        $summaryDeposit = 0;
		
        foreach ($fields as $k) {
            if (isset($preferentialFields[$k])) {
                continue;
            }
            $p1 = floatval($orderAttributes[$k]);
            $p2 = floatval($this->_paidDetailModel->$k);
           
            if ($p1 != $p2) {
				
                $v = ($p1>$p2)?($p1-$p2):0;
                $rowHtml = $this->generateTableRowHtml([
                    $this->_paidDetailModel->getAttributeLabel($k), 
                    $p1, 
                    $p2,
                    [
                        'value'=>$form->field($objFormModel, $k, 
                            ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:100px;"]])->textInput([
                                'type'=>"number", 'id'=>"{$this->_idPrefix}{$k}{$this->_autoId}", 'value'=>$v, 'onchange'=>"funcUpdatePaymentInputSummary{$this->_autoId}()"]),
                        'options' => ['style'=>'padding:4px;vertical-align: middle;'],
                    ],
                    ' - ',
                ]);
                
                if (substr($k, 0, 13) == 'price_deposit') {
                    $summaryDeposit += floatval($v);
                    $summaryDepositTotal += $p1;
                    $summaryDepositPaid += $p2;
                    $tableRows2[] = $rowHtml;
                }
                else {
                    $summaryAmmount += floatval($v);
                    $summaryAmmountTotal += $p1;
                    $summaryAmmountPaid += $p2;
                    $tableRows1[] = $rowHtml;
                }
            }
        }
       
        if (!empty($tableRows1)) {
            $htmlArray[] = implode("\n", $tableRows1);
            $htmlArray[] = $this->generateTableRowHtml([
                $this->_paidDetailModel->getAttributeLabel('summary_amount'), 
                $summaryAmmountTotal, 
                $summaryAmmountPaid,
                [
                    'value'=>$form->field($objFormModel, 'summary_amount', 
                        ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:100px;"]])->textInput([
                            'id'=>"{$this->_idPrefix}summary_amount{$this->_autoId}", 'value'=>$summaryAmmount, 'readonly'=>'readonly']),
                    'options' => ['style'=>'padding:4px;vertical-align: middle;'],
                ],
                [
                    'value'=>$form->field($objFormModel, 'pay_source', 
                        ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:100px;"]])->dropDownList(\common\components\OrderModule::getOrderPayTypeArray(),
                            ['id'=>"{$this->_idPrefix}pay_source{$this->_autoId}", 'value'=>NULL, 
                                'options'=>[
                                    //0=>['disabled'=>true]
                                    ]
                            ]),
                    'options' => ['style'=>'padding:4px;vertical-align: middle;'],
                ],
            ], 'danger');
        }
        if (!empty($tableRows2)) {
            $htmlArray[] = implode("\n", $tableRows2);
            $htmlArray[] = $this->generateTableRowHtml([
                $this->_paidDetailModel->getAttributeLabel('summary_deposit'), 
                $summaryDepositTotal, 
                $summaryDepositPaid,
                [
                    'value'=>$form->field($objFormModel, 'summary_deposit', 
                        ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:100px;"]])->textInput([
                            'id'=>"{$this->_idPrefix}summary_deposit{$this->_autoId}", 'value'=>$summaryDeposit, 'readonly'=>'readonly']),
                    'options' => ['style'=>'padding:4px;vertical-align: middle;'],
                ],
                [
                    'value'=> $form->field($objFormModel, 'deposit_pay_source', 
                        ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:100px;"]])->dropDownList(\common\components\OrderModule::getOrderPayTypeArray(),
                            ['id'=>"{$this->_idPrefix}deposit_pay_source{$this->_autoId}", 'value'=>'']),
                    'options' => ['style'=>'padding:4px;vertical-align: middle;'],
                ],
            ], 'danger');
        }
        
        $htmlArray[] = \yii\helpers\Html::endTag('tbody');
        $htmlArray[] = \yii\helpers\Html::endTag('table');
        
        $htmlArray[] = \yii\helpers\Html::endTag('div');
        
        $buttons = [];
        $buttons[] = \yii\helpers\Html::beginTag('div', ['class'=>'col-md-8']);
        $buttons[] = $form->field($objFormModel, 'time', [
            'template'=>"<span class=\"input-group-addon\">".\Yii::t('locale', 'Payment time')."</span>{input}<a class=\"input-group-addon btn btn-default\" onclick=\"$('#{$this->_idPrefix}remark_field{$this->_autoId}').css({display:'table'})\">".\Yii::t('locale', '{name} remark', ['name'=> \Yii::t('locale', 'Order')])."</a>\n{hint}{error}",
            'options' => ['class'=>'input-group input-group-sm input-append date', 'style'=>'width:300px;']])->textInput([
                'id'=>"{$this->_idPrefix}time{$this->_autoId}",'value'=>"{$this->_time}", 'data-date-format'=>'yyyy-mm-dd hh:ii:ss']);
        $buttons[] = $form->field($objFormModel, 'remark', [
            'template'=>"<span class=\"input-group-addon\">".\Yii::t('locale', '{name} remark', ['name'=> \Yii::t('locale', 'Order')])."</span>\n{input}\n{hint}{error}",
            'options' => ['class'=>'input-group input-group-sm', 'id'=>"{$this->_idPrefix}remark_field{$this->_autoId}", 'style'=>'width:300px;display:none;']
        ])->textInput([]);
        $buttons[] = \yii\helpers\Html::endTag('div');
        //$buttons[] = implode("\n", $summaryHtmlArray);
        //$buttons[] = \yii\helpers\Html::tag('button', \Yii::t('locale', 'Close'), ['type'=>'button', 'class'=>'btn btn-default', 'data-dismiss'=>'modal']);
        $buttons[] = \yii\helpers\Html::beginTag('div', ['class'=>'col-md-4']);
        $buttons[] = \yii\helpers\Html::tag('button', \Yii::t('locale', 'Submit'), ['type'=>'submit', 'class'=>'btn btn-info']);
        $buttons[] = \yii\helpers\Html::endTag('div');
        //$buttons[] = \yii\helpers\Html::submitButton(\Yii::t('locale', 'Submit'), ['class' => 'btn btn-primary', 'name' => 'insert']);
        //$buttons[] = \yii\helpers\Html::endTag('div'); // end of label form-group
        $footer = implode("\n", $buttons);
        
        //$htmlArray[] = \common\helpers\BootstrapHtml::endPanel($footer);
        $htmlArray[] = \yii\helpers\Html::tag('div', $footer, ['class'=>'modal-footer']);
        
        $curTime = time();
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('order_id'), $this->_orderModel->id);
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('relet_mark'), $this->isRelet ? 1 : 0);
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('type'), \common\models\Pro_vehicle_order_price_detail::TYPE_PAID);
        //$htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('time'), $curTime);
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('belong_office_id'), $this->_orderModel->belong_office_id);
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('status'), \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL);
        $htmlArray[] = \yii\helpers\Html::hiddenInput($objFormModel->fieldName('serial'), \common\models\Pro_vehicle_order_price_detail::generateSerial($this->_orderModel->id, \common\models\Pro_vehicle_order_price_detail::TYPE_PAID, $curTime));
        $htmlArray[] = \yii\helpers\Html::hiddenInput('action', 'insert');
        $htmlArray[] = \yii\helpers\Html::hiddenInput('order_action', $this->orderAction ? $this->orderAction : '');
        $htmlArray[] = \yii\helpers\Html::hiddenInput('is_settlement', $this->isSettlement ? $this->isSettlement : 0);
        $htmlArray[] = \yii\helpers\Html::endForm();
        //$form->field($objFormModel, 'id', ['name'=>'action', 'value'=>'insert'])->hiddenInput();
        //$htmlArray[] = \common\widgets\ActiveFormExtendWidget::end();
        
        $htmlArray[] = $form->getScriptHtml();
        $htmlArray[] = \yii\helpers\Html::script($this->renderScriptsContent(), ['type'=>'text/javascript']);
        
        $htmlArray[] = \yii\helpers\Html::endTag('div');    // end of modal-content
        $htmlArray[] = \yii\helpers\Html::endTag('div');    // end fo modal-dialog
        
        return implode("\n", $htmlArray);
    }
    
    public function generateTableRowHtml($columns = [], $rowClass = '') {
        $rowArray = [];
        $rowOptions = [];
        if (!empty($rowClass)) {
            $rowOptions['class'] = $rowClass;
        }
        $rowArray[] = \yii\helpers\Html::beginTag('tr', $rowOptions);
        foreach ($columns as $col) {
            $v = '';
            $options = [];
            if (is_array($col)) {
                if (isset($col['value'])) {
                    $v = $col['value'];
                    unset($col['value']);
                }
                if (isset($col['options'])) {
                    $options = $col['options'];
                    unset($col['options']);
                }
            }
            else {
                $v = strval($col);
            }
            
            $rowArray[] = \yii\helpers\Html::tag('td', $v, array_merge(['style'=>'vertical-align: middle;'], $options));
        }
        $rowArray[] = \yii\helpers\Html::endTag('tr');
        return implode("\n", $rowArray);
    }
    
    protected function renderScriptsContent() {
        $arrScripts = [];
        $modelScope = 'Form_pro_vehicle_order_price_detail';
        $arrScripts[] = <<<EOD
function funcUpdatePaymentInputSummary{$this->_autoId}() {
    PaymentInputSummary.summary('#{$this->_formId}', '{$modelScope}');
}

PaymentInputValidator.init('#{$this->_formId}', {
    //rules: {
    //},
    //messages: {
    //},
});

$(document).ready(function() {
    $('#{$this->_idPrefix}time{$this->_autoId}').datetimepicker({language:'zh-CN',autoclose:true,todayBtn:true});
});

EOD;
        
        return implode("\n\n", $arrScripts);
    }
    
}
