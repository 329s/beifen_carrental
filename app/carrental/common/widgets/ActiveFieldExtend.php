<?php

namespace common\widgets;

/**
 * Description of ActiveFieldExtend
 *
 * @author kevin
 */
class ActiveFieldExtend extends \yii\bootstrap\ActiveField
{
    static $idPrefix = 'w_afe_';
    static $autoId = 0;
    
    /**
     * Renders a combotree input.
     * @param array $items
     * @param array $options
     * @return $this the field object itself.
     */
    public function combotree($items, $options = []) {
        $options = array_merge($this->inputOptions, $options);
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        
        $_idpart = \yii\bootstrap\Html::getInputId($this->model, $this->attribute);
        $autoId = static::$autoId++;
        $id = \yii\helpers\ArrayHelper::remove($options, 'id', static::$idPrefix.$_idpart.$autoId);
        $value = \yii\helpers\ArrayHelper::remove($options, 'value', \yii\bootstrap\Html::getAttributeValue($this->model, $this->attribute));
        $name = \yii\helpers\ArrayHelper::remove($options, 'name', null);
        $treeviewId = $id.'_view';
        $inputId = $id.'_input';
        $options1 = array_merge(['id'=>$inputId, 'hidden'=>'hidden', 'name'=>'', 'value'=>''], $options);
        $options2 = ['id'=>$id, 'value'=>$value];
        if ($name != null) {
            $options2['name'] = $name;
        }
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::activeInput('text', $this->model, $this->attribute, $options1);
        $htmlArray[] = \yii\bootstrap\Html::activeHiddenInput($this->model, $this->attribute, $options2);
        $htmlArray[] = \yii\bootstrap\Html::tag('div', '', ['id'=>$treeviewId, 'style'=>'display: none;width:auto;min-width:240px;height:300px;overflow:auto;position:fixed;z-index:1999;']);
        
        if (empty($value)) {
            $value = "''";
        }
        elseif (is_string($value)) {
            $value = "'{$value}'";
        }
        
        $arrScripts = [];
        if (is_string($items)) {
            $arrScripts[] = <<<EOD
$.ajax({
    type : "get",
    url : "{$items}",
    success : function(data, status) {
        if (status == "success") {
            var data = eval("(" + data + ")");
            $.custom.bootstrap.combotree.init($.custom.bootstrap.treeview.buildDomTree(data, 'id', 'text', {$value}),
                '#{$id}', '#{$inputId}', '#{$treeviewId}');
        }
    },
    error : function() {
        toastr.error('Error');
    },
});
EOD;
        }
        else {
            $arrScripts[] = "$.custom.bootstrap.combotree.init($.custom.bootstrap.treeview.buildDomTree(".
                \common\helpers\BaseHtmlUI::convertComboTreeDataToString($items, 'id', 'text').
                ", 'id', 'text', {$value}),'#{$id}', '#{$inputId}', '#{$treeviewId}');";
        }
        
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = implode("\n", $htmlArray);
        
        $this->addInitialJavascript($id, implode("\n", $arrScripts));
        
        return $this;
    }
    
    /**
     * Renders a datetime input.
     * @param array $options
     * @return $this the field object itself.
     */
    public function datetimebox($options = []) {
        $type = \yii\helpers\ArrayHelper::remove($options, 'type', 'datetime');
        $options = array_merge($this->inputOptions, $options);
        \yii\bootstrap\Html::addCssClass($options, 'form-control');
        \yii\bootstrap\Html::addCssClass($options, 'pull-right');
        $inputId = isset($options['id']) ? $options['id'] : static::$idPrefix.\yii\bootstrap\Html::getInputId($this->model, $this->attribute).static::$autoId++;
        $options['id'] = $inputId;
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $value = isset($options['value']) ? $options['value'] : \yii\bootstrap\Html::getAttributeValue($this->model, $this->attribute);
        if (empty($value)) {
            $options['value'] = '';
        }
        $iconCls = 'calendar';
        $jsInit = '';
        switch ($type) {
            case 'date':
                $iconCls = 'calendar';
                $jsInit = "datepicker({autoclose: true, format:'yyyy-mm-dd'})";
                if (!empty($value)) { $options['value'] = date('Y-m-d', \common\helpers\Utils::toTimestamp($value)); }
                break;
            case 'datetime':
                $iconCls = 'calendar';
                $jsInit = "datepicker({autoclose: true, timePicker: true, format:'yyyy-mm-dd HH:ii:ss'})";
                if (!empty($value)) { $options['value'] = date('Y-m-d H:i:s', \common\helpers\Utils::toTimestamp($value)); }
                break;
            case 'time':
                $iconCls = 'clock-o';
                $jsInit = "timepicker({showInputs: false})";
                break;
            case 'daterange':
                $iconCls = 'calendar';
                $jsInit = "daterangepicker({timePicker: false, format: 'yyyy-mm-dd'})";
                break;
            case 'datetimerange':
                $iconCls = 'calendar';
                $jsInit = "daterangepicker({timePicker: true, timePicker24Hour:true, timePickerIncrement: 30, format: 'yyyy-mm-dd HH:ii:ss'})";
                break;
            case 'timerange':
                $iconCls = 'clock-o';
                $jsInit = "timepicker({showInputs: false})";
                break;
            default :
                throw new \yii\base\InvalidParamException("Invlaid datetime control type:{$type}");
                break;
        }
        $this->parts['{input}'] = \yii\bootstrap\Html::tag('div',
            \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('i', '', ['class'=>"fa fa-{$iconCls}"]), ['class'=>'input-group-addon']).
            \yii\bootstrap\Html::activeInput('text', $this->model, $this->attribute, $options), 
            ['class'=>'input-group']
        );
        
        if (!empty($jsInit)) {
            $this->addInitialJavascript($inputId, "$('#{$inputId}').{$jsInit}");
        }
        
        return $this;
    }
    
    /**
     * Renders a hidden input.
     *
     * Note that this method is provided for completeness. In most cases because you do not need
     * to validate a hidden input, you should not need to use this method. Instead, you should
     * use [[\yii\helpers\Html::activeHiddenInput()]].
     *
     * This method will generate the `name` and `value` tag attributes automatically for the model attribute
     * unless they are explicitly specified in `$options`.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using [[Html::encode()]].
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @return $this the field object itself.
     */
    public function placeholder($options = [])
    {
        $options = array_merge($this->inputOptions, $options);
        //$this->parts['{label}'] = \yii\bootstrap\Html::label('', null, $options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = \yii\bootstrap\Html::hiddenInput('', '', $options);
        //$this->parts['{error}'] = '';
        //$this->parts['{hint}'] = '';
        //$this->parts['{beginLabel}'] = '';
        //$this->parts['{labelTitle}'] = '';
        //$this->parts['{endLabel}'] = '';
        //$this->enableClientValidation = false;
        return $this;
    }
    
    /**
     * 
     * @param array $options
     * @return $this the field object itself.
     */
    public function inputmask($options = []) {
        $options = array_merge($this->inputOptions, $options);
        $inputId = isset($options['id']) ? $options['id'] : static::$idPrefix.\yii\bootstrap\Html::getInputId($this->model, $this->attribute).static::$autoId++;
        $options['id'] = $inputId;
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = \yii\bootstrap\Html::activeInput('text', $this->model, $this->attribute, $options);
        
        $jsInit = "$('#{$inputId}').inputmask()";
        $this->addInitialJavascript($inputId, $jsInit);
        
        return $this;
    }
    
    /**
     * Renders a file input.
     * This method will generate the `name` and `value` tag attributes automatically for the model attribute
     * unless they are explicitly specified in `$options`.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using [[Html::encode()]].
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @return $this the field object itself.
     */
    public function fileInput($options = [])
    {
        // https://github.com/yiisoft/yii2/pull/795
        if ($this->inputOptions !== ['class' => 'form-control']) {
            $options = array_merge($this->inputOptions, $options);
        }
        // https://github.com/yiisoft/yii2/issues/8779
        if (!isset($this->form->options['enctype'])) {
            $this->form->options['enctype'] = 'multipart/form-data';
        }
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $inputHtmls = '';
        if ($this->isMultiFiles()) {
            $values = isset($options['value']) ? $options['value'] : \yii\bootstrap\Html::getAttributeValue($this->model, $this->attribute);
            $filePaths = $this->convertMultiFileAttributeValue($values);
            foreach ($filePaths as $k => $path) {
                $options['value'] = $path;
                $inputHtmls .= \common\helpers\BootstrapHtml::activeFileInput($this->model, $this->attribute."[{$k}]", $options);
            }
            if (empty($filePaths)) {
                $inputHtmls .= \common\helpers\BootstrapHtml::activeFileInput($this->model, $this->attribute."[addfiles][]", $options);
            }
        }
        else {
            $inputHtmls = \common\helpers\BootstrapHtml::activeFileInput($this->model, $this->attribute, $options);
        }
        $this->parts['{input}'] = $inputHtmls;

        return $this;
    }

    /**
     * Renders a file input.
     * This method will generate the `name` and `value` tag attributes automatically for the model attribute
     * unless they are explicitly specified in `$options`.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. The values will be HTML-encoded using [[Html::encode()]].
     *
     * If you set a custom `id` for the input element, you may need to adjust the [[$selectors]] accordingly.
     *
     * @return $this the field object itself.
     */
    public function imageInput($options = [])
    {
        // https://github.com/yiisoft/yii2/pull/795
        if ($this->inputOptions !== ['class' => 'form-control']) {
            $options = array_merge($this->inputOptions, $options);
        }
        // https://github.com/yiisoft/yii2/issues/8779
        if (!isset($this->form->options['enctype'])) {
            $this->form->options['enctype'] = 'multipart/form-data';
        }
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $inputHtmls = '';
        if ($this->isMultiFiles()) {
            $values = isset($options['value']) ? $options['value'] : \yii\bootstrap\Html::getAttributeValue($this->model, $this->attribute);
            $filePaths = $this->convertMultiFileAttributeValue($values);
            foreach ($filePaths as $k => $path) {
                $options['value'] = $path;
                $inputHtmls .= \common\helpers\BootstrapHtml::activeImageInput($this->model, $this->attribute."[{$k}]", $options);
            }
            if (empty($filePaths)) {
                $inputHtmls .= \common\helpers\BootstrapHtml::activeImageInput($this->model, $this->attribute."[addfiles][]", $options);
            }
        }
        else {
            $inputHtmls = \common\helpers\BootstrapHtml::activeImageInput($this->model, $this->attribute, $options);
        }
        $this->parts['{input}'] = $inputHtmls;

        return $this;
    }
    
    protected function isMultiFiles() {
        $validators = $this->model->getValidators();
        foreach ($validators as $validator) {
            if ($validator instanceof \yii\validators\FileValidator) {
                foreach ($validator->attributes as $attr) {
                    if ($validator->maxFiles > 1 && $attr == $this->attribute) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    protected function convertMultiFileAttributeValue($value) {
        if ($this->model instanceof \common\helpers\ActiveFormModel) {
            $filePaths = $this->model->getFileArrayAttributePaths($this->attribute, $value);
        }
        else {
            $filePaths = [];
            if (is_array($value)) {
                foreach ($value as $i => $p) {
                    $filePaths[$i] = strval($p);
                }
            }
            else {
                $_arr = explode(";", strval($value));
                foreach ($_arr as $v) {
                    $_pos = strpos($v, ':');
                    if ($_pos) {
                        $filePaths[substr($v, 0, $_pos)] = substr($v, $_pos+1);
                    }
                }
            }
        }
        return $filePaths;
    }

    /**
     * Renders a captcha input.
     * @param array $options
     * @return $this the field object itself.
     */
    public function captchaInput($options = []) {
        // TODO
        $options = array_merge($this->inputOptions, $options);
        $this->addAriaAttributes($options);
        $this->adjustLabelFor($options);
        $this->parts['{input}'] = \yii\bootstrap\Html::activeInput('text', $this->model, $this->attribute, $options);
        return $this;
    }
    
    private function addInitialJavascript($key, $js) {
        if (!empty($js) && $this->form instanceof ActiveFormExtendWidget) {
            $this->form->extraInitScripts[$key] = $js;
        }
    }
    
}
