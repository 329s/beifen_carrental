<?php
namespace common\helpers;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class BootstrapHtml extends BaseHtmlUI
{
    const ID_PREFIX = 'w_bs_';
    
    const MAIN_CONTENT_ID = 'bs_main_content';
    
    public static function getIDPrefix()
    {
        return static::ID_PREFIX;
    }
    
    public static function beginPanel($title = '', $options = [])
    {
        $htmlArray = [];
        $panelClass = isset($options['class']) ? $options['class'] : 'panel-default';
        if (!empty($title)) {
            $htmlArray[] = \yii\helpers\Html::tag('div', $title, ['class' => 'panel-heading']);
        }
        if (isset($options['body'])) {
            $htmlArray[] = \yii\helpers\Html::tag('div', $options['body'], ['class' => 'panel-body']);
            unset($options['body']);
        }
        if (isset($options['footer'])) {
            $htmlArray[] = \yii\helpers\Html::tag('div', $options['footer'], ['class' => 'panel-footer']);
            unset($options['footer']);
        }
        return \yii\helpers\Html::beginTag('div', array_merge($options, ['class'=>"panel {$panelClass}"])) . "\n" . implode("\n", $htmlArray);
    }
    
    public static function endPanel($footer = null) {
        $htmlArray = [];
        if (!empty($footer)) {
            $htmlArray[] = \yii\helpers\Html::tag('div', $footer, ['class' => 'panel-footer']);
        }
        $htmlArray[] = \yii\helpers\Html::endTag('div');
        return implode("\n", $htmlArray);
    }
    
    public static function panel($title = '', $options = [], $container = '', $footer = '')
    {
        return static::beginPanel($title, array_merge(['body'=>$container, 'footer'=>$footer], $options))."\n".static::endPanel();
    }
    
    public static function dialog($options = [])
    {
        $id = isset($options['id']) ? $options['id'] : '';
        if (empty($id)) {
            $id = static::getIDPrefix()."dialog".static::genID();
            $options['id'] = $id;
        }
        
        $htmlArray = [];
        $htmlArray[] = static::beginTag('div', array_merge(['class'=>"modal fade", 
            'tabindex'=>'-1', 
            'role'=>'dialog', 
            'aria-hidden'=>'true', 
            'data-backdrop'=>'static', 
            'data-keyboard'=>'false'], $options));
        // in the remote dialog display view you'd better surround the html with bellow.
        //$htmlArray[] = static::beginTag('div', ['class'=>'modal-dialog']);
        //$htmlArray[] = static::beginTag('div', ['class'=>'modal-content']);
        //$htmlArray[] = static::endTag('div');
        //$htmlArray[] = static::endTag('div');
        $htmlArray[] = static::endTag('div');
        
        $script = "$('#{$id}').on('hidden', function() { $(this).removeData('modal'); });";
        
        $htmlArray[] = \yii\helpers\Html::script($script, ['type'=>"text/javascript"]);
        
        return implode("\n", $htmlArray);
    }
    
    public static function combotree($name, $value, $data, $htmlOptions = [])
    {
        $idPrefix = static::getIDPrefix();
        $autoId = static::genID();
        $htmlArray = [];
        $id = \yii\helpers\ArrayHelper::remove($htmlOptions, 'id', $idPrefix.'combotree'.$autoId);
        $name = \yii\helpers\ArrayHelper::remove($htmlOptions, 'name', $name);
        $treeviewId = $idPrefix.'combotree_view'.$autoId;
        $inputId = $idPrefix.'combotree_input'.$autoId;
        $options = array_merge([
            'type'=>'text', 'class'=>'form-control select2', 'value'=>'', 'id'=>$inputId,
        ], $htmlOptions);
        $options2 = [
            'type'=>'text', 'name'=>$name, 'value'=>$value,
            'hidden' => 'hidden', 'id'=>$id
        ];
        $htmlArray[] = parent::tag('input', '', $options);
        $htmlArray[] = parent::tag('input', '', $options2);
        $htmlArray[] = parent::tag('div', '', ['id'=>$treeviewId, 'style'=>'display: none;width:auto;min-width:240px;height:300px;overflow:auto;position:fixed;z-index:1999;']);
        
        if (empty($value)) {
            $value = "''";
        }
        elseif (is_string($value)) {
            $value = "'{$value}'";
        }
        
        $arrScripts = [];
        if (is_string($data)) {
            $arrScripts[] = <<<EOD
$(function() {
    $.ajax({
        type : "get",
        url : "{$data}",
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
});
EOD;
        }
        else {
            $arrScripts[] = "$.custom.bootstrap.combotree.init($.custom.bootstrap.treeview.buildDomTree(".
                parent::convertComboTreeDataToString($data, 'id', 'text').
                ", 'id', 'text', {$value}),'#{$id}', '#{$inputId}', '#{$treeviewId}');";
        }
        
        $htmlArray[] = \yii\helpers\Html::script(implode("\n", $arrScripts), ['type'=>'text/javascript']);
        
        return implode("\n", $htmlArray);
    }
    
    public static function imageInput($name, $src, $options = [])
    {
        $se = str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $name);
        $idPrefix = static::getIDPrefix().$se;
        $autoId = static::genID();
        $textId = $idPrefix.'_imgtext'.$autoId;
        $imgId = $idPrefix.'_img'.$autoId;
        $wrapperId = $idPrefix.'_wrapper'.$autoId;
        $imgWidth = \yii\helpers\ArrayHelper::remove($options, 'imageWidth', '200px');
        $imgHeight = \yii\helpers\ArrayHelper::remove($options, 'imageHeight', '160px');
        $fileInputId = \yii\helpers\ArrayHelper::remove($options, 'id', $idPrefix.'_file'.$autoId);
        
        $hiddenOptions = [
            'id' => $fileInputId,
            'accept'=>'image/gif,image/jpeg,image/jpg,image/png',
            'onchange'=>"$.custom.utils.imgfilebox.previewImage(this, '{$imgId}', '{$wrapperId}', '{$textId}')",
            'hidden'=>'hidden',
            'style'=>'display:none;',
        ];
        
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'input-group']);
        $htmlArray[] = \yii\bootstrap\Html::input('text', '', $src, array_merge([
            'class'=>'form-control', 'id'=>$textId, 'readonly'=>'readonly',
            'onclick'=>"$('#{$fileInputId}').click();"],
            $options));
        $htmlArray[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('button', \Yii::t('locale', 'Choose file'), [
            'onclick'=>"$('#{$fileInputId}').click();", 'class'=>'btn btn-default', 'type'=>'button']), 
            ['class'=>'input-group-btn']);
        $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        $htmlArray[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::img($src, ['alt'=>'', 'id'=>$imgId]), ['class'=>'thumbnail', 'id'=>$wrapperId, 'style'=>"width:{$imgWidth};height:{$imgHeight}"]);
        
        $htmlArray[] = \yii\bootstrap\Html::fileInput($name, NULL, $hiddenOptions);
        
        return implode("\n", $htmlArray);
    }
    
    public static function fileInput($name, $value, $options = [])
    {
        $se = str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $name);
        $idPrefix = static::getIDPrefix().$se;
        $autoId = static::genID();
        $textId = $idPrefix.'_text'.$autoId;
        $fileInputId = \yii\helpers\ArrayHelper::remove($options, 'id', $idPrefix.'_file'.$autoId);
        
        $hiddenOptions = [
            'id' => $fileInputId,
            'onchange'=>"$('#{$textId}').attr('value', this.value);",
            'hidden'=>'hidden',
            'style'=>'display:none;',
        ];
        
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'input-group']);
        $htmlArray[] = \yii\bootstrap\Html::input('text', '', $value, array_merge([
            'class'=>'form-control', 'id'=>$textId, 'readonly'=>'readonly',
            'onclick'=>"$('#{$fileInputId}').click();"],
            $options));
        $htmlArray[] = \yii\bootstrap\Html::tag('div', \yii\bootstrap\Html::tag('button', \Yii::t('locale', 'Choose file'), [
            'onclick'=>"$('#{$fileInputId}').click();", 'class'=>'btn btn-default', 'type'=>'button']), 
            ['class'=>'input-group-btn']);
        $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        
        $htmlArray[] = \yii\bootstrap\Html::fileInput($name, NULL, $hiddenOptions);
        
        return implode("\n", $htmlArray);
    }
    
    public static function activeImageInput($model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : \yii\helpers\Html::getInputName($model, $attribute);
        $value = isset($options['value']) ? $options['value'] : \yii\helpers\Html::getAttributeValue($model, $attribute);
        if (!array_key_exists('id', $options)) {
            $options['id'] = \yii\helpers\Html::getInputId($model, $attribute);
        }
        
        return static::imageInput($name, $value, $options);
    }
    
    public static function activeFileInput($model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : \yii\helpers\Html::getInputName($model, $attribute);
        $value = isset($options['value']) ? $options['value'] : \yii\helpers\Html::getAttributeValue($model, $attribute);
        if (!array_key_exists('id', $options)) {
            $options['id'] = \yii\helpers\Html::getInputId($model, $attribute);
        }
        
        return static::fileInput($name, $value, $options);
    }
    
}
