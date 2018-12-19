<?php

namespace common\widgets;

/**
 * Description of AutoLayoutFormWidget
 *
 * @author kevin
 */
class AutoLayoutFormWidget extends \yii\bootstrap\Widget
{
    /**
     *
     * @var \common\helpers\ActiveFormModel 
     */
    public $formModel;
    
    /**
     *
     * @var \common\helpers\ActiveRecordModel
     */
    public $data;
    
    /**
     *
     * @var array
     */
    public $attributes = [];
    
    public $labels = [];
    
    public $attributeTypes = [];
    
    public $attributeOptions = [];
    
    public $options = [];
    
    public $action = null;
    
    public $layout = 'horizontal';
    
    public $columnCount = 1;
    
    public $hiddenValues = [];
    
    public $submitParams;
    
    public $successCallback;
    
    /**
     * @var string the submit button name
     */
    public $submitButton;
    
    public $resetButton = false;
    
    public $cancelButton = false;

    public $enableFulidLayout = false;
    
    private $_idPrefix;
    private $_autoId;
    private $_formId;
    /**
     * @var \common\widgets\ActiveFormExtendWidget
     */
    private $_formWidget;
    
    private $_groupGridSize = 12;
    
    public function init() {
        $this->_idPrefix = static::$autoIdPrefix.time().'_alfw_';
        $this->_autoId = static::$counter++;
        $this->_formId = \yii\helpers\ArrayHelper::getValue($this->options, 'id', $this->_idPrefix.'form'.$this->_autoId);
        $this->options['id'] = $this->_formId;
        parent::init();
        
        $this->prepareAttributeTypes();
        $colCountInfo = $this->_prepareColumnCount($this->columnCount);
        $this->columnCount = $colCountInfo[0];
        $this->_groupGridSize = $colCountInfo[1];
        
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->_formId;
        }
        
        if ($this->action) {
            if (!isset($this->options['action'])) {
                $this->options['action'] = $this->action;
            }
        }
        elseif (isset($this->options['action'])) {
            $this->action = $this->options['action'];
        }
        
        if ($this->data) {
            $values = $this->data->getAttributes();
            $this->formModel->setAttributes($values);
        }
        
        if ($this->submitButton !== false && empty($this->submitButton)) {
            $this->submitButton = \Yii::t('locale', 'Submit');
        }
        if ($this->resetButton !== false && (is_bool($this->resetButton) || empty($this->resetButton))) {
            $this->resetButton = \Yii::t('locale', 'Reset');
        }
        if ($this->cancelButton !== false && (is_bool($this->cancelButton) || empty($this->cancelButton))) {
            $this->cancelButton = \Yii::t('locale', 'Cancel');
        }
    }
    
    public function run() {
        //parent::run();
        $htmlArray = [];
        
        $this->_formWidget = \common\widgets\ActiveFormExtendWidget::begin([
            'id' => $this->_formId,
            'action' => $this->action,
            'layout' => $this->layout,
            'options' => ['role'=>'form',
                //'onsubmit' => "$.custom.bootstrap.form.onSubmit(this); return false;",
            ],
        ]);
        //$htmlArray[] = \yii\helpers\Html::beginForm($this->action, 'post', $this->options);
        $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'box-body']);
        $htmlArray[] = $this->renderActiveFields();
        foreach ($this->hiddenValues as $k => $v) {
            if ($this->formModel->hasProperty($k)) {
                $htmlArray[] = \yii\bootstrap\Html::activeHiddenInput($this->formModel, $k, ['value'=>$v]);
            }
            else {
                $htmlArray[] = \yii\bootstrap\Html::hiddenInput(trim($k), $v);
            }
        }
        $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'box-footer text-center']);
        $htmlArray[] = $this->renderButtons();
        $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        //$htmlArray[] = \yii\helpers\Html::endForm();
        
        if ($this->enableFulidLayout) {
        }
        
        echo implode("\n", $htmlArray);
        return \common\widgets\ActiveFormExtendWidget::end();
        
        //return implode("\n", $htmlArray);
    }
    
    protected function prepareAttributeTypes() {
        $this->attributeTypes = $this->_prepareAttributesKeysArray($this->attributeTypes);
        if ($this->formModel) {
            $model1 = null;
            if ($this->formModel instanceof \common\helpers\ActiveFormModel) {
                $model1 = $this->formModel->getActiveRecordModel();
            }
            else {
                $model1 = $this->formModel;
            }
            if ($model1 && $model1 instanceof \common\helpers\ActiveRecordModel) {
                $dataProvider = $model1->createDataProvider();
                if ($dataProvider && $dataProvider instanceof \common\helpers\ExtendActiveDataProvider) {
                    foreach ($dataProvider->formattingAttributes as $attr => $formatter) {
                        if (!isset($this->attributeTypes[$attr])) {
                            $formationOptions = \common\helpers\DataFormationHelper::guessFormationType($formatter);
                            switch ($formationOptions[0]) {
                                case 'dropdown':
                                    $this->attributeTypes[$attr] = ['type'=>\common\helpers\InputTypesHelper::TYPE_DROPDOWN_LIST, 'data'=>$formationOptions[1]];
                                    break;
                                case 'date':
                                    $this->attributeTypes[$attr] = \common\helpers\InputTypesHelper::TYPE_DATE;
                                    break;
                                case 'datetime':
                                    $this->attributeTypes[$attr] = \common\helpers\InputTypesHelper::TYPE_DATETIME;
                                    break;
                                case 'time':
                                    $this->attributeTypes[$attr] = \common\helpers\InputTypesHelper::TYPE_TIME;
                                    break;
                                case 'image':
                                    $this->attributeTypes[$attr] = \common\helpers\InputTypesHelper::TYPE_IMAGE;
                                    break;
                                case 'file':
                                    $this->attributeTypes[$attr] = \common\helpers\InputTypesHelper::TYPE_FILE;
                                    break;
                                case 'function':
                                    break;
                                default :
                                    break;
                            }
                            
                        }
                    }
                }
            }
            if ($this->formModel instanceof \yii\base\Model) {
                $rules = $this->formModel->rules();
                $builtInTypes = \common\helpers\InputTypesHelper::getBuiltInTypes();
                foreach ($rules as $rule) {
                    if (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                        foreach ((array)$rule[0] as $k) {
                            if (isset($builtInTypes[$rule[1]]) && !isset($this->attributeTypes[$k])) {
                                $this->attributeTypes[$k] = $builtInTypes[$rule[1]];
                            }
                        }
                    }
                }
            }
        }
        
    }
    
    private function _prepareAttributesKeysArray($array) {
        $result = [];
        if (!empty($array)) {
            foreach ($array as $k => $v) {
                $attrs = $this->_explodeAttributes($k);
                foreach ($attrs as $_k) {
                    $result[$_k] = $v;
                }
            }
        }
        return $result;
    }
    
    private function _explodeAttributes($attributesString) {
        $attrs0 = explode(',', $attributesString);
        $attrs = [];
        foreach ($attrs0 as $k) {
            $k = trim($k);
            if ($k != '') {
                $attrs[] = $k;
            }
        }
        return $attrs;
    }

    private function _prepareColumnCount($columnCount) {
        
        if ($columnCount < 1) {
            $columnCount = 1;
        }
        if ($this->layout == 'horizontal') {
            if ($columnCount > 6) {
                $columnCount = 6;
            }
        }
        else {
            if ($columnCount > 12) {
                $columnCount = 12;
            }
        }
        $groupGridSize = floor(12 / $columnCount);
        return [$columnCount, $groupGridSize];
    }
    
    protected function renderActiveFields() {
        $htmlArray = [];
        
        $hasBeginFieldset = false;
        $hasFormGroup = false;
        $count = 0;
        $columnCount = $this->columnCount;
        $groupGridSize = $this->_groupGridSize;
        foreach ($this->attributes as $attribute) {
            $asPlaceholder = false;
            $preProcessType = 0;
            $preProcessLabel = '';
            if (is_integer($attribute)) {
                $preProcessType = $attribute;
            }
            elseif (is_array($attribute)) {
                $opts = $attribute;
                $preProcessType = \yii\helpers\ArrayHelper::remove($opts, 'type', \common\helpers\InputTypesHelper::TYPE_NOP);
                $preProcessLabel = \yii\helpers\ArrayHelper::remove($opts, 'title', '');
                if (empty($preProcessLabel)) {
                    $preProcessLabel = \yii\helpers\ArrayHelper::remove($opts, 'label', '');
                }
                if (isset($opts['columnCount'])) {
                    $colCountInfo = $this->_prepareColumnCount($opts['columnCount']);
                    $columnCount = $colCountInfo[0];
                    $groupGridSize = $colCountInfo[1];
                }
                $attribute = \yii\helpers\ArrayHelper::remove($opts, 'attribute', '');
                if ($preProcessType == \common\helpers\InputTypesHelper::TYPE_HIDDEN) {
                    $fieldName = \yii\bootstrap\Html::getInputName($this->data, $attribute);
                    $fieldValue = \yii\bootstrap\Html::getAttributeValue($this->data, $attribute);
                    if (isset($this->hiddenValues[$fieldName])) {
                        $this->hiddenValues[$fieldName] = $fieldValue;
                    }
                }
            }
            
            if ($preProcessType) {
                if ($preProcessType == \common\helpers\InputTypesHelper::TYPE_NOP
                    || $preProcessType == \common\helpers\InputTypesHelper::TYPE_HIDDEN) {
                    $asPlaceholder = true;
                }
                elseif ($preProcessType == \common\helpers\InputTypesHelper::TYPE_GROUP) {
                    if ($hasBeginFieldset) {
                        if ($hasFormGroup) {
                            $htmlArray[] = $this->endFormGroup();
                            $hasFormGroup = false;
                        }
                        $htmlArray[] = $this->endFieldSet();
                        $hasBeginFieldset = false;
                    }
                    $htmlArray[] = $this->beginFieldSet($preProcessLabel);
                    $hasBeginFieldset = true;
                    $count = 0;
                    continue;
                }
            }
            
            if (isset($this->attributeTypes[$attribute])) {
                $attributeType = $this->attributeTypes[$attribute];
                if (is_array($attributeType)) {
                    $attributeType = isset($attributeType['type']) ? $attributeType['type'] : \common\helpers\InputTypesHelper::TYPE_NOP;
                }
                if ($attributeType == \common\helpers\InputTypesHelper::TYPE_NOP) {
                    $asPlaceholder = true;
                }
                elseif ($attributeType == \common\helpers\InputTypesHelper::TYPE_HIDDEN) {
                    $fieldName = \yii\bootstrap\Html::getInputName($this->data, $attribute);
                    $fieldValue = \yii\bootstrap\Html::getAttributeValue($this->data, $attribute);
                    if (isset($this->hiddenValues[$fieldName])) {
                        $this->hiddenValues[$fieldName] = $fieldValue;
                    }
                    continue;
                }
            }
            
            $index = $count % $columnCount;
            $count++;
            if ($columnCount > 1 && $index == 0) {
                if ($hasFormGroup) {
                    $htmlArray[] = $this->endFormGroup();
                    $hasFormGroup = false;
                }
                $htmlArray[] = $this->beginFormGroup();
                $hasFormGroup = true;
            }
            
            if ($asPlaceholder) {
                //$htmlArray[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('div', '', ['class'=>'form-control']), ['class'=>"form-group col-md-{$this->_groupGridSize}"]);
                $attribute = ['type'=>\common\helpers\InputTypesHelper::TYPE_NOP, 'label'=>'', 'attribute'=>'__empty'];
                //continue;
            }
            
            // render field
            $fieldOptions = [];
            if ($columnCount > 1) {
                $fieldOptions['options'] = ['class' => "form-group col-md-{$groupGridSize}"];
            }
            $htmlArray[] = $this->renderField($attribute, $fieldOptions);
            
        }
        
        if ($hasFormGroup) {
            $htmlArray[] = $this->endFormGroup();
        }
        if ($hasBeginFieldset) {
            $htmlArray[] = $this->endFieldSet();
        }
        
        return implode("\n", $htmlArray);
    }
    
    protected function renderField($attribute, $options = []) {
        $attributeType = null;
        $attributeTypeData = null;
        $attributeOptions = [];
        if (is_array($attribute)) {
            $attrOptions = $attribute;
            $attribute = $attrOptions['attribute'];
            if (isset($attrOptions['label'])) {
                $options['label'] = $attrOptions['label'];
            }
            if (isset($attrOptions['type'])) {
                $attributeType = $attrOptions['type'];
            }
            if (isset($attrOptions['options'])) {
                $attributeOptions = $attrOptions['options'];
            }
        }
        if ($attributeType === null) {
            $attributeType = isset($this->attributeTypes[$attribute]) ? $this->attributeTypes[$attribute] : \common\helpers\InputTypesHelper::TYPE_TEXT;
        }
        if (is_array($attributeType)) {
            $typeOptions = $attributeType;
            if (isset($typeOptions['data'])) {
                $attributeTypeData = $typeOptions['data'];
            }
            $attributeType = $typeOptions['type'];
        }
        
        if (isset($this->labels[$attribute])) {
            $options['labelOptions'] = ['label' => $this->labels[$attribute]];
        }
        if (isset($options['label'])) {
            $options['labelOptions'] = ['label' => $options['label']];
            unset($options['label']);
        }
        
        if ($attributeType == \common\helpers\InputTypesHelper::TYPE_DROPDOWN_TREE
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_DATE
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_DATETIME
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_TIME
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_IMAGE
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_FILE
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_CAPTCHA
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_URL
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_IP
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_NOP
            || $attributeType == \common\helpers\InputTypesHelper::TYPE_HIDDEN) {
            $options['class'] = ActiveFieldExtend::className();
        }
        
        $field = $this->_formWidget->field($this->formModel, $attribute, $options);
        switch ($attributeType) {
            case \common\helpers\InputTypesHelper::TYPE_TEXT:
                $field->textInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_INTEGER:
                $field->input('number', $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_DOUBLE:
                $field->input('number', $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_EMAIL:
                $field->input('email', $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_PASSWORD:
                $field->passwordInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_TELEPHONE:
                $field->textInput(array_merge(['type'=>'telephone'], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_TEXTAREA:
                $field->textarea($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_DATE:
                $field->datetimebox(array_merge(['type'=>'date'], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_DATETIME:
                $field->datetimebox(array_merge(['type'=>'datetime'], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_TIME:
                $field->datetimebox(array_merge(['type'=>'time'], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_DROPDOWN_LIST:
                $field->dropDownList($attributeTypeData, $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_DROPDOWN_TREE:
                $field->combotree($attributeTypeData, $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_CHECKBOX:
                $field->checkbox($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_CHECKBOX_LIST:
                $field->checkboxList($attributeTypeData, $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_RADIO:
                $field->radio($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_RADIO_LIST:
                $field->radioList($attributeTypeData, $attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_FILE:
                $field->fileInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_IMAGE:
                $field->imageInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_CAPTCHA:
                $field->captchaInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_URL:
                $field->inputmask(array_merge(['data-inputmask'=>"'alias': 'url'"], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_IP:
                $field->inputmask(array_merge(['data-inputmask'=>"'alias': 'ip'"], $attributeOptions));
                break;
            case \common\helpers\InputTypesHelper::TYPE_HIDDEN:
                $field->hiddenInput($attributeOptions);
                break;
            case \common\helpers\InputTypesHelper::TYPE_NOP:
                $field->placeholder($attributeOptions);
                break;
            default:
                throw new \yii\base\InvalidParamException('Unknown atribute type:'.strval($attributeType));
                break;
        }
        
        return $field;
    }
    
    protected function renderButtons() {
        $buttons = [];
        if ($this->cancelButton) {
            $btnInfo = $this->_prepareButtonProperty($this->cancelButton, ['class'=>'btn btn-default', 'label'=>\Yii::t('locale', 'Cancel')]);
            $buttons[] = \yii\bootstrap\Html::buttonInput($btnInfo[0], $btnInfo[1]);
        }
        if ($this->resetButton) {
            $btnInfo = $this->_prepareButtonProperty($this->resetButton, ['class'=>'btn btn-default', 'label'=>\Yii::t('locale', 'Reset')]);
            $buttons[] = \yii\bootstrap\Html::resetInput($btnInfo[0], $btnInfo[1]);
        }
        if ($this->submitButton) {
            $params = [];
            $successCallback = 'undefined';
            if (!empty($this->submitParams)) {
                if (!is_array($this->submitParams)) {
                    foreach ($this->submitParams as $k => $v) {
                        $params[] = "'{$k}':'{$v}'";
                    }
                }
            }
            if (!empty($this->successCallback)) {
                $successCallback = $this->successCallback;
            }
            $btnInfo = $this->_prepareButtonProperty($this->submitButton, ['class'=>'btn btn-primary', 'label'=>\Yii::t('locale', 'Submit'), 'onclick'=>"return $.custom.bootstrap.form.onSubmit('#{$this->_formId}', {". implode(',', $params)."}, {$successCallback});"]);
            $buttons[] = \yii\bootstrap\Html::buttonInput($btnInfo[0], $btnInfo[1]);
        }
        return implode("\n", $buttons);
    }
    
    private function _prepareButtonProperty($button, $options) {
        if (is_array($button)) {
            $btnText = \yii\helpers\ArrayHelper::remove($button, 'label', false);
            if ($btnText == false) {
                $btnText = \yii\helpers\ArrayHelper::remove($options, 'label');
            }
            $options = array_merge($options, $button);
        }
        else {
            \yii\helpers\ArrayHelper::remove($options, 'label');
            $btnText = $button;
        }
        return [$btnText, $options];
    }

    protected function beginFieldSet($title) {
        return \yii\bootstrap\Html::beginTag('fieldset').\yii\bootstrap\Html::tag('legend', $title);
    }
    protected function endFieldSet() {
        return \yii\bootstrap\Html::endTag('fieldset');
    }
    
    protected function beginFormGroup() {
        return \yii\bootstrap\Html::beginTag('div', ['class'=>'form-group']);
    }
    protected function endFormGroup() {
        return \yii\bootstrap\Html::endTag('div');
    }

    protected function renderPlaceHolder() {
        return \yii\bootstrap\Html::tag('div', '', ['class'=>'']);
    }
}
