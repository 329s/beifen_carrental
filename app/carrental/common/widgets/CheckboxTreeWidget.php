<?php

namespace common\widgets;

/**
 * Description of CheckboxTreeWidget
 *
 * @author kevin
 */
class CheckboxTreeWidget extends \yii\bootstrap\Widget
{
    
    public $data = [];
    
    public $selects = [];
    
    public $name = '';
    
    public $childrenField = 'children';
    public $itemField = false;
    public $nameField = 'name';
    public $valueField = 'value';
    public $labelField = 'label';
    
    public $_checkBoxes = [];
    
    public function init() {
        parent::init();
        
        $this->_checkBoxes = $this->prepareTreeData($this->data);
    }
    
    public function run() {
        return $this->renderItems($this->_checkBoxes);
    }
    
    public function prepareTreeData($items) {
        $arrData = [];
        foreach ($items as $k => $item) {
            if (is_array($item)) {
                $o = [];
                $v = $this->itemField ? $item[$this->itemField] : $item;
                if ($this->nameField && isset($v[$this->nameField])) {
                    $o['name'] = $v[$this->nameField];
                }
                if ($this->valueField && isset($v[$this->valueField])) {
                    $o['value'] = $v[$this->valueField];
                }
                if ($this->labelField && isset($v[$this->labelField])) {
                    $o['label'] = $v[$this->labelField];
                }
                if ($this->childrenField && isset($item[$this->childrenField])) {
                    $o['children'] = $this->prepareTreeData($item[$this->childrenField]);
                }
                $arrData[] = $o;
            }
            else {
                $arrData[] = ['value' => $k, 'label'=>$item];
            }
        }
        return $arrData;
    }
    
    public function renderItems($items, $deep = 0) {
        $htmlArray = [];
        $isInline = false;
        if ($deep > 2) {
            $isInline = true;
            foreach ($items as $k => $item) {
                if (is_array($item)) {
                    if (isset($item['children']) && !empty($item['children'])) {
                        $isInline = false;
                        break;
                    }
                }
            }
        }
        $deepx = $deep;
        foreach ($items as $k => $item) {
            $deepx = $deep;
            if ($deep == 0) {
                $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class' => 'checkbox-group-wrapper-'.($deepx++)]);
            }
            $children = false;
            if (is_array($item)) {
                $name = \yii\helpers\ArrayHelper::getValue($item, 'name', $this->name);
                $value = \yii\helpers\ArrayHelper::getValue($item, 'value', '');
                $label = \yii\helpers\ArrayHelper::getValue($item, 'label', '');
                if (isset($item['children']) && !empty($item['children'])) {
                    $children = true;
                }
            }
            else {
                $value = $k;
                $name = $this->name;
                $label = $item;
            }
            if (substr($name, -1) != ']') {
                $name .= '[]';
            }
            if ($isInline) {
                $htmlArray[] = $this->renderCheckbox($name, $label, isset($this->selects[$value]), ['value'=>$value, 'labelOptions' => ['style' => 'margin-right:24px;']]);
            }
            else {
                $htmlArray[] = $this->renderCheckbox($name, $label, isset($this->selects[$value]), ['value'=>$value]).' <br />';
                if ($children) {
                    $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class' => 'checkbox-group-wrapper-'.($deepx++), 'style'=>'margin-left:18px;']);
                    $htmlArray[] = $this->renderItems($item['children'], $deepx++);
                    $htmlArray[] = \yii\bootstrap\Html::endTag('div');
                    $htmlArray[] = '<br />';
                }
                
            }
            if ($deep == 0) {
                $htmlArray[] = \yii\bootstrap\Html::endTag('div');
            }
        }
        
        return implode("\n", $htmlArray);
    }
    
    public function renderCheckbox($name, $label, $checked = false, $options = []) {
        if (!isset($options['onclick'])) {
            $options['onclick'] = '$.custom.utils.checkboxgroup.onCheck(this)';
        }
        $labelOptions = \yii\helpers\ArrayHelper::remove($options, 'labelOptions', []);
        return \yii\bootstrap\Html::beginTag('label', $labelOptions) . 
                \yii\bootstrap\Html::checkbox($name, $checked, $options) . 
                $label . \yii\bootstrap\Html::endTag('label');
    }
    
}
