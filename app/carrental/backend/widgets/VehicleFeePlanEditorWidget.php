<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\widgets;

use common\helpers\CMyHtml;

/**
 * Description of VehicleFeePlanEditorWidget
 *
 * @author kevin
 */
class VehicleFeePlanEditorWidget extends \yii\base\Widget
{
    public $vehicleModelId;
    public $officeId;
    public $feesBySources;
    public $submitUrl;
    
    public $title = '';
    
    private $_idPrefix;
    private $_autoId;
    private $_formId;
    
    private $_editingFees;
    private $_festivalFields;
    private $_form;

    public function init() {
        parent::init();
        
        if (empty($this->title)) {
            $this->title = \Yii::t('locale', '{operation} vehicle fee plan', ['operation' => \Yii::t('locale', 'Edit')]);
        }
        if ($this->vehicleModelId == null) {
            $this->vehicleModelId = '';
        }
        if ($this->officeId == null) {
            $this->officeId = '';
        }
        if ($this->feesBySources == null) {
            $this->feesBySources = [];
        }
        
        $arrFestivals = \common\components\OptionsModule::getFestivalsArray();
        $objFeePlan = new \common\models\Pro_vehicle_fee_plan();
        $objFeePlan->setFestivalNames($arrFestivals);
        
        $this->_idPrefix = self::$autoIdPrefix.'_'.\common\helpers\CMyHtml::getIDPrefix();
        $this->_autoId = \common\helpers\CMyHtml::genID();
        $this->_formId = "{$this->_idPrefix}orderform{$this->_autoId}";
        $this->_festivalFields = [];
        $this->_editingFees = [];
        
        foreach ($objFeePlan->festivalFieldsArray as $field => $festivalId) {
            $this->_festivalFields[$festivalId] = $field;
        }
        ksort($this->_festivalFields);
        
        foreach ($this->feesBySources as $source => $obj) {
            $obj->setFestivalNames($arrFestivals);
            $this->_editingFees[$source] = $obj;
        }
        foreach (\common\models\Pro_vehicle_fee_plan::getDefaultEditFeePlanSources() as $source) {
            if (!isset($this->_editingFees[$source])) {
                $o = new \common\models\Pro_vehicle_fee_plan();
                $o->setFestivalNames($arrFestivals);
                $o->status = \common\models\Pro_vehicle_fee_plan::STATUS_NORMAL;
                $o->source = $source;
                $this->_editingFees[$source] = $o;
            }
        }
        ksort($this->_editingFees);
        
        $this->_form = \common\widgets\ActiveFormExtendWidget::begin([
            'id' => $this->_formId,
            'action' => $this->submitUrl,
        ]);
        
    }
    
    public function run() {
        $inputs = [];
        $buttons = ['submit' => \Yii::t('locale', 'Submit'), 'close' => \Yii::t('locale', 'Cancel')];
        $hiddenFields = [
            'action' => 'save',
        ];
        $arrScripts = [];
        
        $this->appendArray($inputs, $this->getCommonInputInfo());
        foreach ($this->_editingFees as $source => $obj) {
            $this->appendArray($inputs, $this->getFeeInfoBySource($source, $obj));
        }
        
        $htmlArray = [];
        
        //$headerId = "{$this->_idPrefix}form_header_{$this->_autoId}";
        $formHtmlOptions = ['id'=>$this->_formId];
        //$formHtmlOptions['header'] = $headerId;
        //if (!$this->_enableSettlement) {
        //    $formHtmlOptions['onOpen'] = "funcInitializeElements{$this->_autoId}";
        //}
        $htmlArray[] = \common\helpers\CMyHtml::form($this->title, $this->submitUrl, 'post', $formHtmlOptions, $inputs, $buttons, $hiddenFields);
        
        
        return implode("\n", $htmlArray);
    }
    
    protected function appendArray(&$inputs, $arr) {
        foreach ($arr as $e) {
            $inputs[] = $e;
        }
    }
    
    protected function getCommonInputInfo() {
        $model = new \common\models\Pro_vehicle_fee_plan();
        $inputs = [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('locale', '{name} info', ['name'=>\Yii::t('locale', 'Basic')])],
            ['type' => CMyHtml::INPUT_COMBOBOX, 'name' => 'vehicle_model_id',
                'label' => $model->getAttributeLabel('vehicle_model_id'),
                'value' => $this->vehicleModelId,
                'data' => \common\components\VehicleModule::getVehicleModelNamesArray(),
                'htmlOptions' => ['style'=>"width:200px", 'required'=>true, 'editable'=>false],
                'columnindex' => 0,
            ],
            ['type' => CMyHtml::INPUT_COMBOTREE, 'name' => 'office_id',
                'label' => $model->getAttributeLabel('office_id'),
                'value' => ($this->officeId ? $this->officeId : ''),
                'data' => \common\components\OfficeModule::getOfficeComboTreeData(['showUniversal'=>true]),
                'htmlOptions' => ['style'=>"width:200px", 'required'=>true, 'editable'=>false],
                'columnindex' => 1,
            ],
        ];
        return $inputs;
    }
    
    protected function getFeeInfoBySource($source, $data) {
        $arrAllSourceTypes = \common\models\Pro_vehicle_fee_plan::getSourceTypesArray();
        $objForm = new \backend\models\Form_pro_vehicle_fee_plan();
        $scope = $objForm->formName().$source;
        
        $htmlArray = [];
        
        $htmlArray[] = $this->generateFeeInputGroup($objForm, $data, 
                ['price_default', 'price_3days', 'price_week', 'price_month','special_festivals_price_month', 'price_15days'], 
                $scope, \Yii::t('locale', 'Basic fee info'));
        
        $htmlArray[] = $this->generateFeeInputGroup($objForm, $data, 
                ['price_monday', 'price_tuesday', 'price_wednesday', 'price_thirsday', 'price_friday', 'price_saturday', 'price_sunday'], 
                $scope, \Yii::t('locale', 'Daily fee info'));
        
        $htmlArray[] = $this->generateFeeInputGroup($objForm, $data, 
                $this->_festivalFields, 
                $scope, \Yii::t('locale', 'Festival fee info'));
        if (!empty($data->id)) {
            $htmlArray[] = \yii\helpers\Html::hiddenInput("{$scope}[id]", $data->id, []);
        }
        $htmlArray[] = \yii\helpers\Html::hiddenInput("{$scope}[status]", $data->status, []);
        
        return [
            ['type' => CMyHtml::INPUT_TYPE_GROUP, 'label' => \Yii::t('carrental', '{name} fee plan', ['name'=>(isset($arrAllSourceTypes[$source])?$arrAllSourceTypes[$source]:'')])],
            [
                'type'=>  \common\helpers\CMyHtml::INPUT_TYPE_HTML,
                'html'=>  implode("\n", $htmlArray),
            ]
        ];
    }
    
    protected function generateFeeInputGroup($formModel, $dataModel, $fields, $scope, $groupTitle) {
        $htmlArray = [];
        
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'row']);
        $htmlArray[] = \yii\helpers\Html::tag('div', $groupTitle, ['class'=>'col-sm-1']);
        $htmlArray[] = \yii\helpers\Html::beginTag('div', ['class'=>'col-sm-11']);
        
        $htmlArray[] = \yii\helpers\Html::beginTag('table', ['class'=>'table']);
        $htmlArray[] = \yii\helpers\Html::beginTag('tr');
        $cols = [];
        foreach ($fields as $idx => $field) {
            $htmlArray[] = \yii\helpers\Html::tag('th', $dataModel->getAttributeLabel($field), []);
            
            if (isset($dataModel->festivalFieldsArray[$field])) {
                $val = (isset($dataModel->festivalPricesArray[$idx]) ? $dataModel->festivalPricesArray[$idx] : '');
            }
            else {
                $val = $dataModel->$field;
            }
            $cols[] = [
                'value'=> $this->_form->field($formModel, $field, 
                    ['template'=>"{input}\n{hint}\n{error}", 'options'=>['class' => 'input-group input-group-sm', 'style'=>"width:80px;"]])->textInput([
                        'type'=>"number", 'value'=>$val, 'name'=>"{$scope}[{$field}]"]),
                'options' => ['style'=>'padding:4px;vertical-align: middle;'],
            ];
        }
        $htmlArray[] = \yii\helpers\Html::endTag('tr');
        $htmlArray[] = \yii\helpers\Html::beginTag('thead');
        $htmlArray[] = \yii\helpers\Html::endTag('thead');
        $htmlArray[] = \yii\helpers\Html::beginTag('tbody');
        $htmlArray[] = $this->generateTableRowHtml($cols);
        $htmlArray[] = \yii\helpers\Html::endTag('tbody');
        $htmlArray[] = \yii\helpers\Html::endTag('table');
        
        $htmlArray[] = \yii\helpers\Html::endTag('div');
        $htmlArray[] = \yii\helpers\Html::endTag('div');
        
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
    
}
