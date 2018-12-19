<?php
namespace common\helpers;

use Yii;
use yii\helpers\Html;
/**
 * CEasyUI is a static class that provides a collection of helper methods for creating HTML views using easyui.
 *
 * @author Yangjining <kevinyjn@gmail.com>
 * @package system.web.helpers
 * @since 1.0
 */
class CEasyUI extends \common\helpers\BaseHtmlUI
{
    const ID_PREFIX='yt_easyui_';
    
    const ASSETS_EASYUI_FOLDER = 'jquery-easyui';
    
    private static $_count = 0;
    
    private static function _renderDataOptions($config, $dataOptions) {
        $arrDataOptions = [];
        foreach ($config as $cfg) {
            $key = $cfg[0];
            if (isset($dataOptions[$key])) {
                if (is_string($dataOptions[$key]) && $dataOptions[$key] == '') {
                    continue;
                }
                if ($cfg[1] == 1) {
                    $arrDataOptions[] = "{$key}:'{$dataOptions[$key]}'";
                }
                else {
                    if ($key == 'width' || $key == 'height') {
                        if (strpos($dataOptions[$key], '%') !== false) {
                            $dataOptions[$key] = "'{$dataOptions[$key]}'";
                        }
                    }
                    else if ($cfg[1] == 2 && is_bool($dataOptions[$key])) {
                        $dataOptions[$key] = ($dataOptions[$key] ? 'true' : 'false');
                    }
                    $arrDataOptions[] = "{$key}:{$dataOptions[$key]}";
                }
            }
            else if (!empty($cfg[2])) {
                if ($cfg[1] == 1) {
                    $arrDataOptions[] = "{$key}:'{$cfg[2]}'";
                }
                else {
                    $arrDataOptions[] = "{$key}:{$cfg[2]}";
                }
            }
        }

        return implode(',', $arrDataOptions);
    }

    private static function _defaultDatagrid_data_options() {
        static $_config = [
            ['iconCls', 1, 'icon-table'],
            ['rownumbers', 2, ''],
            ['singleSelect', 2, 'true'],
            ['autoRowHeight', 2, ''],
            ['pagination', 2, 'true'],
            ['pageSize', 4, '20'],
            ['idField', 1, ''],
            ['treeField', 1, ''],
            ['toolbar', 1, ''],
            ['data', 5, ''],
            ['footer', 1, ''],
            ['fitColumns', 2, ''],
            ['showHeader', 2, ''],
            ['showFooter', 2, 'true'],
            ['url', 1, ''],
            ['saveUrl', 1, ''],
            ['updateUrl', 1, ''],
            ['destroyUrl', 1, ''],
            ['method', 1, ''],
            ['remoteSort', 2, ''],
            ['multiSort', 2, ''],
            ['sortName', 1, ''],
            ['sortOrder', 1, ''],
            ['collapsible', 2, 'false'], // 是否显示可折叠按钮
            ['onClickCell', 3, ''],
            ['onClickRow', 3, ''],
            ['onDblClickCell', 3, ''],
            ['onDblClickRow', 3, ''],
            ['onLoadSuccess', 3, ''],
            ['onLoadError', 3, ''],
            ['rowStyler', 3, ''],
            ['fixed', 2, ''],
            ['fit', 2, ''],
            ['nowrap', 2, ''],
            ['ctrlSelect', 2, ''],
            ['view', 3, ''],
            ['striped', 2, 'true'],
            ['detailFormatter', 3, ''],
            ['onExpandRow', 3, ''],
            ['onBeforeLoad', 3, ''],
            ['onSelect', 3, ''],
            ['queryParams', 5, ''],
            ['onHeaderContextMenu', 3, ''],
            ['onRowContextMenu', 3, ''],
        ];
        return $_config;
    }

    private static function _defaultDatagridColumn_data_option() {
        static $_config = [
            ['field', 1, ''],
            ['title', 1, ''],
            ['width', 2, ''],
            ['align', 1, ''],    // left,center,right
            ['halign', 1, ''],   // top,center,bottom
            ['formatter', 3, ''],
            ['editor', 5, ''],
            ['styler', 3, ''],
            ['checkbox', 2, ''],
            ['resizable', 2, 'true'],
            ['sortable', 2, ''],
            ['multiple', 2, ''],
            ['columns', 5, ''],
            ['rowspan', 2, ''],
            ['colspan', 2, ''],
            ['hidden', 2, ''],
        ];
        return $_config;
    }

    /**
     * generate one of datagrid column config with properties
     * @param string $model model
     * @param array $fieldConfigArray 
     * @param string $idField
     * @return array the column config array for format datagrid tag
     */
    public static function formatDatagridColumnConfig($model, $fieldConfigArray, &$idField = null)
    {
        static $_nonStringKey = ['required' => 1, 'multiple' => 1, 'columns' => 1];
        $fieldName = isset($fieldConfigArray['field']) ? $fieldConfigArray['field'] : '';
        $columnName = isset($fieldConfigArray['title']) ? $fieldConfigArray['title'] : $model->getAttributeLabel($fieldName);
        $width = null;
        $otherOptions = null;
        $editor = null;
        $formatter = null;
        
        if (isset($fieldConfigArray['data-options'])) {
            $otherOptions = $fieldConfigArray['data-options'];
            unset($fieldConfigArray['data-options']);
        }
        if (isset($fieldConfigArray['width'])) {
            $width = $fieldConfigArray['width'];
            unset($fieldConfigArray['width']);
        }
        if (isset($fieldConfigArray['editor'])) {
            $editor = $fieldConfigArray['editor'];
            unset($fieldConfigArray['width']);
        }
        if (isset($fieldConfigArray['formatter'])) {
            $formatter = $fieldConfigArray['formatter'];
            unset($fieldConfigArray['width']);
        }
        if (isset($fieldConfigArray['key'])) {
            if (empty($idField) && $fieldConfigArray['key'] === true) {
                $idField = $fieldName;
            }
            unset($fieldConfigArray['key']);
        }

        if ($otherOptions === null) {
            $otherOptions = [];
        }
        foreach ($fieldConfigArray as $k => $v) {
            if (!isset($otherOptions[$k])) {
                $otherOptions[$k] = $v;
            }
        }
        
        $arrColumn = ['name' => $columnName];
        $arrOptions = ['field' => $fieldName];
        if ($width && !empty($width)) {
            $arrOptions['width'] = "{$width}";
        }
        if ($otherOptions && is_array($otherOptions)) {
            foreach ($otherOptions as $k => $v) {
                $arrOptions[$k] = $v;
            }
        }

        if ($editor) {
            if (is_string($editor)) {
                $editor = trim($editor);
                $arrOptions['editor'] = "'{$editor}'";
            }
            elseif (is_array($editor)) {
                $type = $editor['type'];
                $arrOptions1 = $editor['options'];
                $arrOptions2 = [];
                foreach ($arrOptions1 as $k => $v) {
                    if (isset($_nonStringKey[$k]) || is_numeric($v) || is_bool($v) 
                            || $v == 'true' || $v == 'false' || $v[0] == '{' || $v[0] == '['
                            || strpos($v, 'function') === 0) {
                        $v = strval($v);
                        $arrOptions2[] = "{$k}:{$v}";
                    }
                    else {
                        $arrOptions2[] = "{$k}:'{$v}'";
                    }
                }

                $arrOptions['editor'] = "{type:'{$type}',options:{" . implode(',', $arrOptions2) . '}}';
            }
        }

        if ($formatter) {
            $arrOptions['formatter'] = "{$formatter}";
        }

        $arrColumn['options'] = $arrOptions;

        return $arrColumn;
    }
    
    private static function _convertDatagridToolFunctionConfigToStr($datagridId, $cfg, $title, &$hasDialog, &$hasWindow, $defaultFuncName = 'undefined') {
        if (is_array($cfg)) {
            $needSelect = 'false';
            $needReload = 'false';
            if (isset($cfg['needSelect'])) {
                if (\common\helpers\Utils::boolvalue($cfg['needSelect'])) {
                    $needSelect = 'true';
                }
            }
            //if (isset($cfg['needReload'])) {
            //    if ($cfg['needReload'] == true || $cfg['needReload'] == 'true') {
            //        $needReload = 'true';
            //    }
            //}
            if (isset($cfg['dialog'])) {
                $url = $cfg['dialog'];
                $dlgId = $datagridId . '_dlg';
                if (isset($cfg['bootstrapmodal'])) {
                    $modalId = $cfg['bootstrapmodal'];
                    if (substr($modalId, 0, 1) != '#') { $modalId = '#'.$modalId; }
                    $cfg = "easyuiFuncDatagridExecuteByFormatUrlWithSelected(function(url){ $.custom.bootstrap.showModal('{$modalId}', url); }, '#{$datagridId}', '{$url}', {$needSelect})";
                }
                else {
                    $cfg = "easyuiFuncDatagridOpenInDialog('#{$datagridId}', '#{$dlgId}', '{$url}', '{$title}', {$needSelect})";
                    $hasDialog = true;
                }
            }
            elseif (isset($cfg['window'])) {
                $url = $cfg['window'];
                $wndId = $datagridId . '_wnd';
                if (isset($cfg['bootstrapmodal'])) {
                    $modalId = $cfg['bootstrapmodal'];
                    if (substr($modalId, 0, 1) != '#') { $modalId = '#'.$modalId; }
                    $cfg = "easyuiFuncDatagridExecuteByFormatUrlWithSelected(function(url){ $.custom.bootstrap.showModal('{$modalId}', url); }, '#{$datagridId}', '{$url}', {$needSelect})";
                }
                else {
                    $cfg = "easyuiFuncDatagridOpenInWindow('#{$datagridId}', '#{$wndId}', '{$url}', '{$title}', {$needSelect})";
                }
                $hasWindow = true;
            }
            elseif (isset ($cfg['tab'])) {
                $url = $cfg['tab'];
                $cfg = "easyuiFuncDatagridOpenInTab('#{$datagridId}', '{$url}', '{$title}', {$needSelect})";
            }
            elseif (isset ($cfg['_blank'])) {
                $url = $cfg['_blank'];
                $cfg = "easyuiFuncDatagridOpenInTab('#{$datagridId}', '{$url}', '{$title}', {$needSelect}, undefined, true)";
            }
            elseif (isset ($cfg['ajax'])) {
                $url = $cfg['ajax'];
                $prompt = isset($cfg['prompt']) ? $cfg['prompt'] : '';
                if (empty($prompt)) {
                    if (isset($cfg['title']) && !empty($cfg['title'])) {
                        $prompt = $cfg['title'];
                    }
                    else if (!empty($title)) {
                        $prompt = $title;
                    }
                }
                $cfg = "easyuiFuncDatagridOpenInAjax('#{$datagridId}', '{$url}', {$defaultFuncName}, '{$prompt}', {$needSelect}, 'get')";
            }
            else {
                $cfg = 'javascript:void(0);';
            }
        }
        return $cfg;
    }

    private static function _convertDatagridToolFunctionStr($datagridId, $func, $defaultFuncName, $title, &$hasDialog, &$hasWindow) {
        if (empty($func)) {
            $func = $defaultFuncName . "('#{$datagridId}')";
        }
        elseif (is_string($func)) {
            if (strpos($func, 'function') === false && strpos($func, '(') === false) {
                $func = $defaultFuncName . "('#{$datagridId}', '{$func}')";
            }
        }
        elseif (is_array($func)) {
            $func = self::_convertDatagridToolFunctionConfigToStr($datagridId, $func, $title, $hasDialog, $hasWindow, $defaultFuncName);
        }
        return $func;
    }

    public static function _genDatagridMenuButton($datagridId, $arrMenus, &$hasDialog, &$hasWindow) {
        $menuButtonArray = [];
        foreach ($arrMenus as $menuCfg) {
            if (is_array($menuCfg)) {
                $_htmlOptions = ['encode' => false];
                if (isset($menuCfg['type'])) {
                    if ($menuCfg['type'] == 'sep') {
                        $_htmlOptions['class'] = 'menu-sep';
                    }
                }
                
                $name = isset($menuCfg['name']) ? $menuCfg['name'] : '  ';
                $title = isset($menuCfg['title']) ? $menuCfg['title'] : '';
                
                if (isset($menuCfg['event'])) {
                    $event = $menuCfg['event'];
                    if (!empty($event)) {
                        if (is_array($event)) {
                            $event = self::_convertDatagridToolFunctionConfigToStr($datagridId, $event, $title, $hasDialog, $hasWindow);
                        }
                        else {
                            $event = strval($event);
                        }
                        $_htmlOptions['onclick'] = $event;
                    }
                }
                
                if (isset($menuCfg['icon'])) {
                    $_htmlOptions['data-options'] = "iconCls:'{$menuCfg['icon']}'";
                }
                
                if (isset($menuCfg['children']) && !empty($menuCfg['children'])) {
                    $menuButtonArray[] = BaseHtmlUI::beginTag('div', $_htmlOptions);
                    $menuButtonArray[] = BaseHtmlUI::tag('span', $name);
                    $menuButtonArray[] = BaseHtmlUI::beginTag('div');
                    $menuButtonArray[] = implode("\n", self::_genDatagridMenuButton($datagridId, $menuCfg['children'], $hasDialog, $hasWindow));
                    $menuButtonArray[] = BaseHtmlUI::endTag('div');
                    $menuButtonArray[] = BaseHtmlUI::endTag('div');
                }
                else {
                    $menuButtonArray[] = BaseHtmlUI::tag('div', $name, $_htmlOptions);
                }
            }
            else {
                $menuButtonArray[] = strval($menuCfg);
            }
        }
        return $menuButtonArray;
    }

    public static function convertComboTreeDataToString($arr, $offset = 0) {
        $_str = '';
        for ($_i = 0; $_i < $offset; $_i++) {
            $_str .= '  ';
        }
        $strData = "[";
        foreach ($arr as $row) {
            if (is_array($row)) {
                if ($strData == "[") {
                    $strData .= "{";
                } else {
                    $strData .= ", {";
                }
                $_isFirst = true;
                foreach ($row as $_k => $_v) {
                    if ($_isFirst) {
                        $_isFirst = false;
                    }
                    else {
                        $strData .= ",";
                    }
                    if ($_k == 'children') {
                        $strData .= "\n{$_str}  {$_k}: " . self::convertComboTreeDataToString($_v, $offset + 1);
                    }
                    else {
                        if (is_string($_v)) {
                            $strData .= "\n{$_str}  {$_k}: '{$_v}'";
                        }
                        elseif (is_bool($_v)) {
                            $strData .= "\n{$_str}  {$_k}: " . ($_v ? 'true' : 'false');
                        }
                        elseif (is_numeric($_v)) {
                            $strData .= "\n{$_str}  {$_k}: {$_v}";
                        }
                        elseif (is_array($_v)) {
                            $strData .= "\n{$_str}  {$_k}: {";
                            $_bFlag = true;
                            foreach ($_v as $__k => $__v) {
                                if (is_bool($__v)) {
                                    $__v = ($__v ? 'true' : 'false');
                                }
                                elseif (is_numeric($_v)) {
                                }
                                else {
                                    $__v = "'{$__v}'";
                                }
                                if ($_bFlag) {
                                    $_bFlag = false;
                                }
                                else {
                                    $strData .= ",";
                                }
                                $strData .= "\n{$_str}    {$__k}: {$__v}";
                            }
                            $strData .= "\n{$_str}  }";
                        }
                        else {
                            $strData .= "\n{$_str}  {$_k}: '{$_v}'";
                        }
                    }
                }
                $strData .= "\n{$_str}}";
            }
        }
        $strData .= "]";
        return $strData;
    }
    
    public static function convertComboboxDataToString($arrData, $valueField = 'id', $textField = 'text') {
        $arr = [];
        foreach ($arrData as $k => $v) {
            if (is_string($k)) {
                $k = "'{$k}'";
            }
            $arr[] = "{'{$valueField}':{$k},'{$textField}':'{$v}'}";
        }
        return "[".implode(",\n    ", $arr)."]";
    }
    
    public static function convertComboboxDataToFormatterFunc($arrData, $valueField = 'value') {
        $arr = [];
        
        foreach ($arrData as $k => $v) {
            if (is_string($k)) {
                $k = "'{$k}'";
            }
            $arr[] = "if ({$valueField} == {$k}) { return '{$v}';}";
        }
        $arr[] = "{ return '';}";
        return implode("\n    else ", $arr);
    }
    
    public static function convertArrayDataToJsArrayText($arrData) {
        $arr = [];
        foreach ($arrData as $k => $v) {
            if (is_string($v)) {
                $v = "'{$v}'";
            }
            $arr[] = "{$k}:{$v}";
        }
        return "{\n".implode(",\n    ", $arr)."\n}";
    }
    
    public static function convertDatagridRowDataToString($row) {
        $_arr = [];
        foreach ($row as $k => $v) {
            if (is_string($v)) {
                $v = str_replace("'", "\\'", $v);
                $v = "'{$v}'";
            }
            $_arr[] = "$k:{$v}";
        }
        return "{".  implode(',', $_arr)."}";
    }
    
    public static function convertDatagridDataWithFooterToString($arrData) {
        $arrDgData = ['{ rows:['];
        foreach ($arrData['rows'] as $row) {
            $arrDgData[] = "    ".self::convertDatagridRowDataToString($row).",";
        }
        if (isset($arrData['footer'])) {
            $arrDgData[] = '], footer: [';
            foreach ($arrData['footer'] as $row) {
                $arrDgData[] = "    ".self::convertDatagridRowDataToString($row).",";
            }
        }
        $arrDgData[] = ']}';
        return implode("\n", $arrDgData);
    }
    
    /**
     * generate data grid tool item
     * @param string $datagridId datagrid id that this tool belongs to
     * @param integer $type tool type
     * @param string $name display name
     * @param string $func function string or function name
     * @param string $iconName icon class name
     * @return string datagrid tool html text
     */
    public static function genDatagridTool($datagridId, $type, $name, $func, $iconName, &$hasDialog, &$hasWindow) {
        if ($type & self::DG_TOOL_APPEND) {
            if (empty($name)) { $name = Yii::t('locale', 'Add'); }
            if (empty($iconName)) { $iconName = 'icon-add'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridAppend", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_REMOVE) {
            if (empty($name)) { $name = Yii::t('locale', 'Delete'); }
            if (empty($iconName)) { $iconName = 'icon-remove'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridDeleteSelected", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_EDIT) {
            if (empty($name)) { $name = Yii::t('locale', 'Edit'); }
            if (empty($iconName)) { $iconName = 'icon-edit'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridEdit", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_ACCEPT) {
            if (empty($name)) { $name = Yii::t('locale', 'Save'); }
            if (empty($iconName)) { $iconName = 'icon-save'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridAccept", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_REJECT) {
            if (empty($name)) { $name = Yii::t('locale', 'Cancel'); }
            if (empty($iconName)) { $iconName = 'icon-undo'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridReject", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_GETCHANGES) {
            if (empty($name)) { $name = Yii::t('locale', 'GetChanges'); }
            if (empty($iconName)) { $iconName = 'icon-search'; }
            $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "easyuiFuncDatagridGetChanges", $name, $hasDialog, $hasWindow);
        }
        elseif ($type & self::DG_TOOL_SINGLEMULTIPLE) {
            $checkboxId = $datagridId . '_ck_singlemultiple';
            //return Html::checkBox($checkboxName, false, ['encode'=>false,'value'=>'0','onchange'=>"$('#{$datagridId}').datagrid({singleSelect:(this.checked=='checked')})"]);
            return BaseHtmlUI::tag('input', '', ['type'=>'checkbox','encode'=>false,'id'=>$checkboxId,'value'=>'0','onchange'=>"$('#{$datagridId}').datagrid({singleSelect:(this.checked==false)})"]) . Html::label(Yii::t('locale', 'MultiSelect'), $checkboxId);
        }
        elseif ($type & self::DG_TOOL_MENU) {
            if (empty($name)) { $name = '  '; }
            if (empty($iconName)) { $iconName = 'icon-basket'; }
            self::$_count ++;
            $menuId = $datagridId . '_menu_' . self::$_count;
            $endFix = '';
            if (is_array($func)) {
                $menuHtmlArray = [];
                $menuHtmlArray[] = BaseHtmlUI::beginTag('div', ['id' => $menuId, 'style' => 'width:160px;']);
                $menuButtonArray = self::_genDatagridMenuButton($datagridId, $func, $hasDialog, $hasWindow);
                $menuHtmlArray[] = implode("\n", $menuButtonArray);
                $menuHtmlArray[] = BaseHtmlUI::endTag('div');
                $endFix = "\n".implode("\n", $menuHtmlArray);
            }
            else {
                $endFix = "\n".strval($func);
            }
            
            return BaseHtmlUI::tag('a', $name, ['href'=>'javascript:void(0)',
                    'class' => 'easyui-menubutton',
                    'encode' => false,
                    'data-options' => "iconCls:'{$iconName}',menu:'#{$menuId}'",
                ]
            ) . $endFix;
        }
        else {
            if (empty($func)) {
                return null;
            }
            elseif (is_array($func)) {
                if (isset($func['_blank'])) {
                    $url = $func['_blank'];
                    $htmlOptions = ['href'=>$url,
                        'class' => 'easyui-linkbutton',
                        'encode' => false,
                        'data-options' => "iconCls:'{$iconName}',plain:true",
                        'target' => '_blank'
                    ];
                    if (isset($func['onclick'])) {

                    }
                    $html = BaseHtmlUI::tag('a', $name, $htmlOptions);
                    return $html;
                }
                else {
                    $func = self::_convertDatagridToolFunctionStr($datagridId, $func, "undefined", $name, $hasDialog, $hasWindow);
                }
            }
        }

        if (is_string($func) && (strpos($func, 'function') === false && strpos($func, '(') === false)) {
            $func = trim($func) . "('#{$datagridId}')";
        }
        else {
            // $func = "";
            $func = strval($func);
        }

        $html = BaseHtmlUI::tag('a', $name, ['href'=>'javascript:void(0)',
            'class' => 'easyui-linkbutton',
            'encode' => false,
            'onclick' => $func,
            'data-options' => "iconCls:'{$iconName}',plain:true",
            ]
            );
        return $html;
    }

    /**
     *
     * @param string $datagridId
     * @param integer $type search tool type
     * @param string $name search field name
     * @param string $prompt Description
     * @param string $param prompt string or function string or function name
     * @param array $htmlOptions
     * @param string $selected optional for checkbox list, radiobox list, combobox list
     * @return string html text
     */
    public static function genDatagridSearchAreaTool($datagridId, $type, $name, $prompt, $param, $htmlOptions, $selected = '') {
        if (!is_array($htmlOptions)) {
            $htmlOptions = [];
        }

        $jsDatagridIdFormat = "'#{$datagridId}'";

        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }

        $searchOnChangeExec = '';
        if (isset($htmlOptions['searchOnChange'])) {
            if ($htmlOptions['searchOnChange']) {
                $searchOnChangeExec = "setTimeout(function(){easyuiFuncDatagridReload({$jsDatagridIdFormat});}, 100);";
            }
            unset($htmlOptions['searchOnChange']);
        }
        
        $htmlArray = [];
        $inputCount = 1;
        $inputNames = [];
        $inputLabels = [];
        $inputValues = [];
        $inputFieldHtml = false;
        
        $inputLabels[0] = (empty($prompt) ? "" : "{$prompt}:");
        if (is_array($name)) {
            $inputCount = 0;
            foreach ($name as $_n) {
                $inputNames[$inputCount++] = $_n;
            }
            if (!$inputCount) {
                return null;
            }
            for ($i = 1; $i < $inputCount; $i++) {
                $inputLabels[$i] = "-";
            }
            if (is_array($param)) {
                for ($i = 0; $i < $inputCount; $i++) {
                    $inputValues[$i] = (isset($param[$i]) ? $param[$i] : '');
                }
            }
            else {
                for ($i = 0; $i < $inputCount; $i++) {
                    $inputValues[$i] = '';
                }
            }
        }
        else {
            $inputNames[0] = $name;
            $inputValues[0] = $param;
        }
        
        for ($i = 0; $i < $inputCount; $i++) {
            $tagName = '';
            $content = false;
            $inputFieldHtml = false;
            $options = array_merge($htmlOptions);
            if ($type & self::DG_TOOL_SEARCH_TEXTBOX) {
                $tagName = 'input';
                if (isset($options['type'])) {
                    if ($options['type'] == 'numberbox') {
                        // TODO
                    }
                    unset($options['type']);
                }
                $options['class'] = 'easyui-searchbox';
                $options['data-options'] = <<<EOD
prompt:'{$prompt}',
searcher:function(){
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', this.value);
    easyuiFuncDatagridReload({$jsDatagridIdFormat});
},
onChange: function(newValue,oldValue) {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', newValue);
    easyuiFuncDatagridReload({$jsDatagridIdFormat});
}
EOD;
                if (!empty($inputValues[$i])) {
                    $options['value'] = $inputValues[$i];
                }
            } elseif ($type & self::DG_TOOL_SEARCH_COMBOBOX) {
                $dataOptionsArray = [];
                $hasMultiple = false;
                $hasDataoptions = false;
                $valueField = 'id';
                $textField = 'text';
                $comboEvents = '';
                $exeAfter = '';
                if (isset($options['data-options'])) {
                    $hasDataoptions = true;
                    $val = $options['data-options'];
                    $dataOptionsArray[] = $val;
                    $pos = strpos($val, 'multiple');
                    if ($pos !== false) {
                        $pos2 = strpos($val, ',', $pos);
                        if ($pos2 === false) {
                            $pos2 = null;
                        } else {
                            $pos2 = $pos2 - $pos;
                        }
                        $v2 = substr($val, $pos, $pos2);
                        if (strpos($v2, 'true')) {
                            $hasMultiple = true;
                        }
                    }
                } else {
                    $booleanKeys = ['multiple', 'multiline'];
                    foreach ($booleanKeys as $k) {
                        if (isset($options[$k])) {
                            if ($k == 'multiple') {
                                $hasMultiple = true;
                            }
                            $val = $options[$k];
                            if (is_string($val) && stripos($val, 'true') !== false) {
                                $dataOptionsArray[] = "{$k}:true";
                            } elseif (is_bool($val) && $val) {
                                $dataOptionsArray[] = "{$k}:true";
                            }
                            unset($options[$k]);
                        }
                    }

                    $dataOptionsArray[] = "prompt:'{$prompt}'";
                    if (isset($options['valueField'])) {
                        $valueField = $options['valueField'];
                        unset($options['valueField']);
                    }
                    if (isset($options['textField'])) {
                        $textField = $options['textField'];
                        unset($options['textField']);
                    }
                }

                $reloadUrlOnSelect = false;
                if (isset($options['reloadUrlOnSelect'])) {
                    $reloadUrlOnSelect = $options['reloadUrlOnSelect'];
                    unset($options['reloadUrlOnSelect']);
                }
                if (isset($options['onSelect']) && !empty($options['onSelect'])) {
                    $exeAfter = $options['onSelect']."(record);";
                    unset($options['onSelect']);
                }

                if ($hasMultiple) {
                    $comboEvents = <<<EOD
onSelect: function(record) {
    easyuiFuncDatagridSetOptionsValueMultiple({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.value); {$searchOnChangeExec}
},
onUnselect: function(record) {
    easyuiFuncDatagridDelOptionsValueMultiple({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.value); {$searchOnChangeExec}
}
EOD;
                } else {
                    if ($reloadUrlOnSelect) {
                        $exeAfter .= "easyuiFuncNavTabReloadCurTabWithDatagridParams('{$reloadUrlOnSelect}', {$jsDatagridIdFormat});";
                    }
                    else {
                        $exeAfter .= "{$searchOnChangeExec}";
                    }
                    $comboEvents = <<<EOD
onSelect: function(record) {
    setTimeout(function() {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.{$valueField});
    {$exeAfter}
    }, 100);
}
EOD;
                }

                if (is_array($param)) {
                    $tagName = 'select';
                    $options['class'] = 'easyui-combobox';
                    $dataOptionsArray[] = <<<EOD
onSelect: function(record) {
    setTimeout(function() {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.value);
    {$exeAfter}
    }, 100);
}
EOD;
                    $dataOptionsArray[] = "editable:false";
                    $options['data-options'] = implode(',', $dataOptionsArray);
                    $inputFieldHtml = Html::dropDownList($inputNames[$i], (empty($selected) ? '' : $selected), $param, $options);
                } elseif (is_string($param)) {    // consider as url for get combobox option list
                    $tagName = 'input';
                    $options['class'] = 'easyui-combobox';
                    $options['name'] = $inputNames[$i];

                    if (!$hasDataoptions) {
                        $dataOptionsArray[] = "url:'{$param}',method:'get',valueField:'{$valueField}',textField:'{$textField}',groupField:'group'";
                    }
                    $dataOptionsArray[] = $comboEvents;
                    if (!empty($selected)) {
                        $options['value'] = $selected;
                    }
                    $dataOptionsArray[] = "editable:false";
                    $options['data-options'] = implode(',', $dataOptionsArray);
                }
            } elseif ($type & self::DG_TOOL_SEARCH_COMBOTREE) {
                $dataOptionsArray = [];
                $hasMultiple = false;
                $hasEvents = false;
                $hasDataoptions = false;
                if (isset($options['data-options'])) {
                    $hasDataoptions = true;
                    $val = $options['data-options'];
                    $dataOptionsArray[] = $val;
                    $pos = strpos($val, 'multiple');
                    if ($pos !== false) {
                        $pos2 = strpos($val, ',', $pos);
                        if ($pos2 === false) {
                            $pos2 = null;
                        } else {
                            $pos2 = $pos2 - $pos;
                        }
                        $v2 = substr($val, $pos, $pos2);
                        if (strpos($v2, 'true')) {
                            $hasMultiple = true;
                        }
                    }

                    if (strpos($val, 'onChange')) {
                        $hasEvents = true;
                    }
                } else {
                    $booleanKeys = ['multiple', 'multiline'];
                    foreach ($booleanKeys as $k) {
                        if (isset($options[$k])) {
                            if ($k == 'multiple') {
                                $hasMultiple = true;
                            }
                            $val = $options[$k];
                            if (is_string($val) && stripos($val, 'true') !== false) {
                                $dataOptionsArray[] = "{$k}:true";
                            } elseif (is_bool($val) && $val) {
                                $dataOptionsArray[] = "{$k}:true";
                            }
                            unset($options[$k]);
                        }
                    }

                    $dataOptionsArray[] = "prompt:'{$prompt}'";
                }

                $reloadUrlOnSelect = false;
                if (isset($options['reloadUrlOnSelect'])) {
                    $reloadUrlOnSelect = $options['reloadUrlOnSelect'];
                    unset($options['reloadUrlOnSelect']);
                }

                $comboEvents = '';
                if (!$hasEvents) {
                    if ($hasMultiple) {
                        $comboEvents = <<<EOD
onSelect: function(record) {
    easyuiFuncDatagridSetOptionsValueMultiple({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.value); {$searchOnChangeExec}
},
onUnselect: function(record) {
    easyuiFuncDatagridDelOptionsValueMultiple({$jsDatagridIdFormat}, '{$inputNames[$i]}', record.value); {$searchOnChangeExec}
}
EOD;
                    } else {
                        $exeAfter = "{$searchOnChangeExec}";
                        if ($reloadUrlOnSelect) {
                            $exeAfter = "easyuiFuncNavTabReloadCurTabWithDatagridParams('{$reloadUrlOnSelect}', {$jsDatagridIdFormat});";
                        }
                        $comboEvents = <<<EOD
onChange: function(newValue, oldValue) {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', newValue);
    {$exeAfter}
}
EOD;
                    }
                }

                $tagName = 'select';
                $options['class'] = 'easyui-combotree';
                $options['name'] = $inputNames[$i];

                if (!$hasDataoptions) {
                    if (is_array($param)) {
                        $dataOptionsArray[] = "data:" . self::convertComboTreeDataToString($param);
                    } elseif (is_string($param)) {    // consider as url for get combobox option list
                        $dataOptionsArray[] = "url:'{$param}',method:'get'";
                    }
                }
                if (!$hasEvents) {
                    $dataOptionsArray[] = $comboEvents;
                }
                if (!empty($selected)) {
                    $options['value'] = $selected;
                }
                $onBeforeCheck = "function(node, checked) { if (node.checkable !== undefined && node.checkable == false) { return false; } else if (node.attributes !== undefined && node.attributes.checkable !== undefined && node.attributes.checkable == false) { return false; } return true; }";
                if (isset($htmlOptions['onBeforeCheck'])) {
                    $onBeforeCheck = $htmlOptions['onBeforeCheck'];
                }
                $dataOptionsArray[] = "onBeforeSelect:{$onBeforeCheck}";
                $options['data-options'] = implode(',', $dataOptionsArray);
            } elseif ($type & self::DG_TOOL_SEARCH_RADIO) {
                if (!isset($options['onchange'])) {
                    $options['onchange'] = "easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', this.value);{$searchOnChangeExec}";
                }
                if (!isset($options['data-options']) && !empty($prompt)) {
                    $options['data-options'] = "prompt:'{$prompt}'";
                }
                $options['separator'] = '';
                $options['container'] = '';
                $inputFieldHtml = Html::radioList($inputNames[$i], (empty($selected) ? '' : $selected), $param, $options);
            } elseif ($type & self::DG_TOOL_SEARCH_CHECKBOX) {
                if (!isset($options['onchange'])) {
                    $options['itemOptions'] = ['onchange' => "easyuiFuncDatagridOnSelectOptionsValueMultiple({$jsDatagridIdFormat}, '{$inputNames[$i]}', this.value);{$searchOnChangeExec}"];
                } else {
                    $options['itemOptions'] = ['onchange' => $options['onchange']];
                    unset($options['onchange']);
                }
                if (!isset($options['data-options']) && !empty($prompt)) {
                    $options['data-options'] = "prompt:'{$prompt}'";
                }
                $options['separator'] = '';
                $options['container'] = '';
                $inputFieldHtml = Html::checkBoxList($inputNames[$i], (empty($selected) ? '' : $selected), $param, $options);
            } elseif ($type & self::DG_TOOL_SEARCH_TEXTFIELD) {
                $tagName = 'input';
                $options['class'] = 'easyui-textbox';

                $eventFunc = <<<EOD
onChange: function(newValue,oldValue) {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', newValue); {$searchOnChangeExec}
}
EOD;

                $dataOptionsArray = [];

                if (isset($options['data-options'])) {
                    $hasDataoptions = true;
                    $val = $options['data-options'];
                    $dataOptionsArray[] = $val;
                    if (strpos($val, 'onChange') === false) {
                        $dataOptionsArray[] = $eventFunc;
                    }
                } else {
                    $dataOptionsArray[] = "prompt:'{$prompt}'";
                    $dataOptionsArray[] = $eventFunc;
                }

                if (!empty($dataOptionsArray)) {
                    $options['data-options'] = implode(',', $dataOptionsArray);
                }

                if (!empty($inputValues[$i])) {
                    $options['value'] = $inputValues[$i];
                }
            } elseif ($type & self::DG_TOOL_SEARCH_DATEBOX) {
                $tagName = 'input';
                $options['class'] = 'easyui-datebox';
                $eventFunc = <<<EOD
onChange: function(date) {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', date); {$searchOnChangeExec}
}
EOD;
                $dataOptionsArray = [];
                if (isset($options['data-options'])) {
                    $hasDataoptions = true;
                    $val = $options['data-options'];
                    $dataOptionsArray[] = $val;
                    if (strpos($val, 'onChange') === false) {
                        $dataOptionsArray[] = $eventFunc;
                    }
                } else {
                    $dataOptionsArray[] = "prompt:'{$prompt}',editable:false";
                    $dataOptionsArray[] = $eventFunc;
                }
                if (!empty($dataOptionsArray)) {
                    $options['data-options'] = implode(',', $dataOptionsArray);
                }

                if (!empty($inputValues[$i])) {
                    $options['value'] = $inputValues[$i];
                }

                $inputFieldHtml = BaseHtmlUI::tag($tagName, '', $options);
            } elseif ($type & self::DG_TOOL_SEARCH_DATETIMEBOX) {
                $tagName = 'input';
                $options['class'] = 'easyui-datetimebox';
                $eventFunc = <<<EOD
onChange: function(date) {
    easyuiFuncDatagridSetOptionsValue({$jsDatagridIdFormat}, '{$inputNames[$i]}', date); {$searchOnChangeExec}
}
EOD;
                $dataOptionsArray = [];
                if (isset($options['data-options'])) {
                    $hasDataoptions = true;
                    $val = $options['data-options'];
                    $dataOptionsArray[] = $val;
                    if (strpos($val, 'onChange') === false) {
                        $dataOptionsArray[] = $eventFunc;
                    }
                } else {
                    $dataOptionsArray[] = "prompt:'{$prompt}',editable:false";
                    $dataOptionsArray[] = $eventFunc;
                }
                if (!empty($dataOptionsArray)) {
                    $options['data-options'] = implode(',', $dataOptionsArray);
                }

                if (!empty($inputValues[$i])) {
                    $options['value'] = $inputValues[$i];
                }

                $inputFieldHtml = BaseHtmlUI::tag($tagName, '', $options);
            } elseif ($type & self::DG_TOOL_SEARCH_BUTTON) {
                $tagName = 'a';
                $options['class'] = 'easyui-linkbutton';
                if (!isset($options['href'])) {
                    $options['href'] = 'javascript:void(0);';
                }
                //$options['plain'] = 'true';
                if (!isset($options['onclick'])) {
                    $options['onclick'] = "easyuiFuncDatagridReload({$jsDatagridIdFormat})";
                }
                $content = $inputNames[$i];
            }

            if (!empty($tagName)) {
                if ($inputFieldHtml === false) {
                    $inputFieldHtml = BaseHtmlUI::tag($tagName, $content, $options);
                }
                $htmlArray[] = BaseHtmlUI::tag('span', $inputLabels[$i] . $inputFieldHtml, ['nowrap'=>'', 'style'=>"white-space:nowrap;"]);
            }
        }
        
        if (empty($htmlArray)) {
            return null;
        }
        
        return implode("", $htmlArray);
    }

    /**
     * 
     * @param string $title
     * @param CModel $model
     * @param array $columns
     * @param array $dataArray
     * @param string $width
     * @param string $height
     * @param array $htmlOptions
     * @param array $urlsArray  
     *      ['url' => data url array, 
     *          'saveUrl' => save added data url array,
     *          'updateUrl' => update modified data url array,
     *          'deleteUrl' => delete url array,
     *      ]
     * @param array $toolbarArray
     * @param integer $frozenColumnIndex frozen column index
     * @param integer $frozenRowIndex frozen row index
     */
    public static function datagrid($title, $model, $columns = [], $dataArray = [], 
            $width = '', $height = '', $htmlOptions = [], $urlsArray = [], $toolbarArray = [], 
            $frozenColumnIndex = 0, $frozenRowIndex = 0) {
        
        $columnsConfigArray = [];
        $columnCustomTypesArray = [];
        $columnCustomTypesMethod = 'attributeCustomTypes';
        if (method_exists($model, $columnCustomTypesMethod)) {
            $columnCustomTypesArray = $model->$columnCustomTypesMethod();
        }
        $idField = '';
        foreach ($columns as $fieldName) {
            $columnName = $model->getAttributeLabel($fieldName);
            if (isset($columnCustomTypesArray[$fieldName])) {
                $columnCustomConfig = $columnCustomTypesArray[$fieldName];
                $columnCustomConfig['field'] = $fieldName;
                $columnCustomConfig['title'] = $columnName;
                
                $columnsConfigArray[] = self::formatDatagridColumnConfig($model, $columnCustomConfig, $idField);
            }
            else {
                $columnsConfigArray[] = self::formatDatagridColumnConfig($model, ['field'=>$fieldName, 'title'=>$columnName], $idField);
            }
        }
        
        if (empty($idField)) {
            $labels = $model->attributeLabels();
            if (isset($labels['id'])) {
                $idField = 'id';
            }
        }
        
        $dataOptions = [];
        
        if (!empty($idField)) {
            $dataOptions['idField'] = $idField;
        }
        
        if (isset($urlsArray['url'])) { $dataOptions['url'] = $urlsArray['url']; }
        if (isset($urlsArray['saveUrl'])) { $dataOptions['saveUrl'] = $urlsArray['saveUrl']; }
        if (isset($urlsArray['updateUrl'])) { $dataOptions['updateUrl'] = $urlsArray['updateUrl']; }
        if (isset($urlsArray['deleteUrl'])) { $dataOptions['destroyUrl'] = $urlsArray['deleteUrl']; }
        if (isset($urlsArray['detailUrl'])) { $dataOptions['detailUrl'] = $urlsArray['detailUrl']; }
        if (!empty($urlsArray)) {
            $dataOptions['method'] = 'get';
        }
        
        if (isset($htmlOptions['data-options'])) {
            $_options = $htmlOptions['data-options'];
            if (is_array($_options)) {
                foreach ($_options as $k => $v) {
                    $dataOptions[$k] = $v;
                }
                unset($htmlOptions['data-options']);
            }
        }
        
        return self::datagrid2($title, $columnsConfigArray, $dataArray, $width, $height, $dataOptions, $htmlOptions, $toolbarArray, $frozenColumnIndex, $frozenRowIndex);
    }

    /**
     *
     * @param string $title
     * @param array $columns
     * @param array $dataArray data array
     * @param string $width
     * @param string $height
     * @param array $datagridDataOptions
     * @param array $htmlOptions
     * @param array $toolbarArray
     * @param integer $frozenColumnIndex frozen column index
     * @param integer $frozenRowIndex frozen row index
     * @return string datagrid html text
     */
    public static function datagrid2($title, $columns, $dataArray, $width, $height, $datagridDataOptions = [], $htmlOptions = [], $toolbarArray = [], $frozenColumnIndex = 0, $frozenRowIndex = 0) {
        if (!empty($width)) {
            $htmlOptions['width'] = ''.$width;
        }
        if (!empty($height)) {
            $htmlOptions['height'] = ''.$height;
        }

        return self::_datagrid($title, $htmlOptions, $datagridDataOptions, $columns, $dataArray, $toolbarArray, $frozenColumnIndex, $frozenRowIndex);
    }

    /**
     *
     * @param type $title
     * @param type $columns
     * @param type $toolTypes
     * @param type $width
     * @param type $height
     * @param type $dataUrl
     * @param type $dataUrlMethod
     * @param type $onClickCell
     * @param type $datagridDataOptions
     * @param integer $frozenColumnIndex frozen column index
     * @param integer $frozenRowIndex frozen row index
     * @return string datagrid html text
     */
    public static function datagridWithDefaultTool($title, $columns, $toolTypes, $width, $height, $dataUrl, $dataUrlMethod = 'get', $onClickCell = '', $datagridDataOptions = [], $frozenColumnIndex = 0, $frozenRowIndex = 0) {
        if (!empty($width)) {
            $htmlOptions['width'] = ''.$width;
        }
        if (!empty($height)) {
            $htmlOptions['height'] = ''.$height;
        }

        $toolbarArray = [];
        if ($toolTypes & self::DG_TOOL_APPEND) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_APPEND, Yii::t('locale', 'Add'), null, null);
        }
        if ($toolTypes & self::DG_TOOL_REMOVE) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_REMOVE, Yii::t('locale', 'Delete'), null, null);
        }
        if ($toolTypes & self::DG_TOOL_ACCEPT) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_ACCEPT, Yii::t('locale', 'Save'), null, null);
        }
        if ($toolTypes & self::DG_TOOL_REJECT) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_REJECT, Yii::t('locale', 'Cancel'), null, null);
        }
        if ($toolTypes & self::DG_TOOL_GETCHANGES) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_GETCHANGES, Yii::t('locale', 'GetChanges'), null, null);
        }
        if ($toolTypes & self::DG_TOOL_EDIT) {
            $toolbarArray[] = self::formatDatagridToolConfig(self::DG_TOOL_EDIT, Yii::t('locale', 'Edit'), null, null);
        }

        if (!empty($onClickCell)) {
            if (!isset($datagridDataOptions['onClickCell'])) {
                $datagridDataOptions['onClickCell'] = $onClickCell;
            }
        }

        $datagridDataOptions['url'] = $dataUrl;
        $datagridDataOptions['method'] = $dataUrlMethod;

        return self::_datagrid($title, $htmlOptions, $datagridDataOptions, $columns, [], $toolbarArray, $frozenColumnIndex, $frozenRowIndex);
    }
    
    protected static function _genDatagridToolBarHtmlArray($dgId, $toolbarArray, &$hasDialog = null, &$hasWindow = null) {
        // generate datagrid tools
        $toolbarHtmlArray = [];
        $toolbarSearchAreaHtmlArray = [];
        if (is_array($toolbarArray)) {
            foreach ($toolbarArray as $cfg) {
                if (empty($cfg)) {
                    continue;
                }
                $toolType = $cfg[0];
                if ($toolType & self::DG_TOOL_SEARCH_TYPE_FLAG) {

                    if ($toolType & (self::DG_TOOL_SEARCH_COMBOBOX | self::DG_TOOL_SEARCH_COMBOTREE | self::DG_TOOL_SEARCH_CHECKBOX | self::DG_TOOL_SEARCH_RADIO)) {
                        if (!empty($cfg[5])) {
                            $loadDgParamsArray[$cfg[1]] = $cfg[5];
                        }
                    }
                    else {
                        if (!empty($cfg[3])) {
                            $loadDgParamsArray[$cfg[1]] = $cfg[3];
                        }
                    }

                    $toolHtml = self::genDatagridSearchAreaTool($dgId, $toolType, $cfg[1], $cfg[2], $cfg[3], $cfg[4], $cfg[5]);
                    if ($toolHtml) {
                        $toolbarSearchAreaHtmlArray[] = $toolHtml;
                    }
                }
                else {
                    $toolHtml = self::genDatagridTool($dgId, $toolType, $cfg[1], $cfg[2], $cfg[3], $hasDialog, $hasWindow);
                    if ($toolHtml) {
                        $toolbarHtmlArray[] = $toolHtml;
                    }
                }
            }
        }
        
        $htmlArray = [];
        if (!empty($toolbarSearchAreaHtmlArray)) {
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['region'=> 'north', 'border' => 'false', 'style' => 'border-bottom:1px solid #ddd;height:auto;padding:2px 2px 2px 2px;']);

            // tool buttons
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => 'float:left;height:auto']);
            $htmlArray[] = implode("\n", $toolbarHtmlArray);
            $htmlArray[] = BaseHtmlUI::endTag('div');

            // separator
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['class' => 'datagrid-btn-separator']);
            $htmlArray[] = BaseHtmlUI::endTag('div');

            // searches
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => 'float:right;height:auto']);
            $htmlArray[] = implode("\n", $toolbarSearchAreaHtmlArray);
            $htmlArray[] = BaseHtmlUI::endTag('div');

            // clear the float to fix causing parent auto height not correct
            $htmlArray[] = BaseHtmlUI::tag('br', '', ['style' => 'clear:both;']);

            $htmlArray[] = BaseHtmlUI::endTag('div');
        }
        else {
            $htmlArray[] = implode("\n", $toolbarHtmlArray);
        }
        
        //$retArray = ['tools' => $toolbarHtmlArray, 'searches' => $toolbarSearchAreaHtmlArray];
        return $htmlArray;
    }
    
    /**
     * generate datagrid
     * @param string $title datagrid title
     * @param array $htmlOptions html attributes for table
     * @param array $datagridDataOptions data-options for datagrid
     * @param array $columns datagrid columns config array
     * @param array $dataArray data array
     * @param array $toolbarArray datagrid toolbar config array
     * @param integer $frozenColumnIndex frozen column index
     * @param integer $frozenRowIndex frozen row index
     * @return string datagrid html text
     */
    protected static function _datagrid($title, $htmlOptions, $datagridDataOptions, $columns, $dataArray, $toolbarArray = null, $frozenColumnIndex = 0, $frozenRowIndex = 0) {
        // 1: string 2: boolean 3: function 4:number
        $tblDefaultDataOptions = self::_defaultDatagrid_data_options();
        $columnDefaultDataOptions = self::_defaultDatagridColumn_data_option();

        // generate datagrid id
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'dg_' . $currentID;
        self::$lastTagID = $id;
        if ($htmlOptions && isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            if (!is_array($htmlOptions)) { $htmlOptions = []; }
            $htmlOptions['id'] = $id;
        }

        // get datagrid display width and height
        $tableParentSize = [];
        if (isset($htmlOptions['width'])) {
            $tableParentSize[] = "width:".$htmlOptions['width'];
            unset($htmlOptions['width']);
        }
        if (isset($htmlOptions['height'])) {
            $tableParentSize[] = "height:".$htmlOptions['height'];
            unset($htmlOptions['height']);
        }
        if (isset($htmlOptions['style'])) {
            $txt = $htmlOptions['style'];
            $_arr = explode(';', $txt);
            $_txt2 = [];
            $_txt3 = [];
            foreach ($_arr as $v) {
                if (strpos($v, 'width') >= 0 || strpos($v, 'height') >= 0) {
                    $_txt3[] = $v;
                }
                else {
                    $_txt2[] = $v;
                }
            }

            if (!empty($_txt3)) {
                $_txt2[] = "width:100%;height:100%";
            }

            $htmlOptions['style'] = implode(';', $_txt2);
            $tableParentSize = $_txt3;
        }
        else {
            if (!empty($tableParentSize)) {
                $htmlOptions['style'] = "width:100%;height:100%";
            }
        }
        
        $hasDialog = false;
        $hasWindow = false;
        //$dlgWidth = '600px';
        //$dlgHeight = '400px';
        $dlgWidth = '80%';
        $dlgHeight = '80%';
        $loadDgParamsArray = [];
        
        if (isset($htmlOptions['dialogWidth'])) {
            $dlgWidth = $htmlOptions['dialogWidth'];
            unset($htmlOptions['dialogWidth']);
        }
        if (isset($htmlOptions['dialogHeight'])) {
            $dlgHeight = $htmlOptions['dialogHeight'];
            unset($htmlOptions['dialogHeight']);
        }
        
        // generate datagrid tools
        $toolbarHtmlArray = self::_genDatagridToolBarHtmlArray($id, $toolbarArray, $hasDialog, $hasWindow);
        // add toolbar id if nessesary
        $toolbarId = '';
        if (!empty($toolbarHtmlArray)) {
            $toolbarId = self::ID_PREFIX . 'tb_' . $currentID;
            if (isset($datagridDataOptions['toolbar'])) {
                if (!empty($datagridDataOptions['toolbar'])) {
                    $toolbarId = '';
                }
            }

            if (!empty($toolbarId)) {
                $datagridDataOptions['toolbar'] = '#'.$toolbarId;
            }
        }
        
        if (!isset($datagridDataOptions['onLoadError'])) {
            $datagridDataOptions['onLoadError'] = "function(e){ $.custom.easyui.datagrid.onLoadError(e); }";
        }
        
        $arrLoadSuccessExecs = [];
        $arrScripts = [];
        
        // columns
        $arrColumns = [];
        $arrColumnFields = [];
        $arrDetailedColumns = [];
        $arrDetailedFields = [];
        $hasEditor = false;
        $arrButtonOptions = [];
        $sortColumnName = false;
        $idField = isset($datagridDataOptions['idField']) ? $datagridDataOptions['idField'] : '';
        
        foreach ($columns as $col) {
            $colOptions = $col['options'];
            
            // if the column displays buttons
            if (isset($colOptions['buttons'])) {
                $scriptFuncName = '';
                $arrCurButtonOptions = null;
                $scriptContent = self::scriptDatagridCellButtons($id, $colOptions['buttons'], $arrCurButtonOptions, $scriptFuncName, $hasDialog, $hasWindow);
                if (!empty($scriptFuncName) && (!isset($colOptions['formatter']) || empty($colOptions['formatter']))) {
                    $arrScripts[] = $scriptContent;
                    unset($colOptions['buttons']);
                    $colOptions['formatter'] = "function(value,row){ return {$scriptFuncName}(value,row); }";
                    if ($arrCurButtonOptions) {
                        foreach ($arrCurButtonOptions as $k => $v) {
                            $arrButtonOptions[$k] = $v;
                        }
                    }
                }
            }
            
            $_columnCfg = [
                'name' => $col['name'],
                'data-options' => self::_renderDataOptions($columnDefaultDataOptions, $colOptions)
            ];
            $_fieldName = isset($colOptions['field']) ? $colOptions['field'] : '';
            
            if (isset($colOptions['detailed']) && $colOptions['detailed']) {
                $arrDetailedColumns[] = $_columnCfg;
                $arrDetailedFields[] = $_fieldName;
            }
            else {
                $arrColumns[] = $_columnCfg;
                $arrColumnFields[] = $_fieldName;
            }

            if (isset($colOptions['editor'])) {
                $hasEditor = true;
            }
            if ($sortColumnName === false && isset($colOptions['sortable'])) {
                if (\common\helpers\Utils::boolvalue($colOptions['sortable']) && isset($colOptions['field']) && $colOptions['field']) {
                    $sortColumnName = $colOptions['field'];
                }
            }
            
            if (isset($colOptions['afterLoad'])) {
                $arrLoadSuccessExecs[] = "{$colOptions['afterLoad']};";
            }
        }
        
        if (!empty($arrButtonOptions)) {
            foreach ($arrButtonOptions as $k => $v) {
                $arrLoadSuccessExecs[] = "$('.{$k}').linkbutton({{$v}});";
            }
            
        }
        
        $arrLoadSuccessExecs[] = "$(this).datagrid('fixRowHeight');";
        
        if (!empty($loadDgParamsArray)) {
            $paramsInJson = json_encode($loadDgParamsArray);
            $arrScripts[] = "$(function(){ easyuiFuncDatagridInitializeQueryParams('#{$id}', '{$paramsInJson}'); })";
        }
        
        // if specified frozen row
        if ($frozenRowIndex > 0 && $frozenRowIndex <= 10) {
            $arrLoadSuccessExecs[] = "$(this).datagrid('freezeRow',0).datagrid('freezeRow',{$frozenRowIndex});";
        }
        
        $onLoadSuccessFunc = isset($datagridDataOptions['onLoadSuccess']) ? $datagridDataOptions['onLoadSuccess'].'(data);' : '';
        if (!empty($arrLoadSuccessExecs) || !empty($onLoadSuccessFunc)) {
            $datagridDataOptions['onLoadSuccess'] = "function(data){\n".implode("\n    ", $arrLoadSuccessExecs)."\n{$onLoadSuccessFunc}}";
        }
        
        // if has editor
        if ($hasEditor && isset($datagridDataOptions['editable']) && $datagridDataOptions['editable']) {
            if ((!isset($datagridDataOptions['onClickCell']) || empty($datagridDataOptions['onClickCell']))
                && (!isset($datagridDataOptions['onDblClickCell']) || empty($datagridDataOptions['onDblClickCell']))
               ) {
                $datagridDataOptions['onDblClickCell'] = 'easyuiFuncDatagridOnDblClickCellDoEdit';
            }
        }
        if ($sortColumnName !== false) {
            if (!isset($datagridDataOptions['sortName']) || empty($datagridDataOptions['sortName'])) {
                $datagridDataOptions['sortName'] = $sortColumnName;
            }
        }
        
        if (!empty($arrDetailedColumns)) {
            $detailUrl = '';
            $onExpandRow = isset($datagridDataOptions['onExpandRow']) ? $datagridDataOptions['onExpandRow'].';' : '';
            if (isset($datagridDataOptions['detailUrl'])) {
                $detailUrl = $datagridDataOptions['detailUrl'];
            }
            if (!isset($datagridDataOptions['view'])) {
                $datagridDataOptions['view'] = 'detailview';
            }
            if (!isset($datagridDataOptions['detailFormatter'])) {
                if (empty($detailUrl)) {
                    $datagridDataOptions['detailFormatter'] = "function(index,row){ return '<div style=\'padding:2px\'><table class=\'ddv\'></table></div>'; }";
                }
                else {
                    $datagridDataOptions['detailFormatter'] = "function(index,row){ return '<div class=\'ddv\' style=\'padding:5px 0\'></div>'; }";
                }
            }
            if (empty($detailUrl)) {
                $_detailColumnsArr = [];
                foreach($arrDetailedColumns as $_cfg) {
                    $_str0 = $_cfg['data-options'];
                    $_str0 .= (empty($_str0) ? '' : ',') . "title:'{$_cfg['name']}'";
                    $_detailColumnsArr[] = '{' . $_str0 . '}';
                }
                $_detailColumns = implode(",\n            ", $_detailColumnsArr);
                $_detailFields = "'" . implode("','", $arrDetailedFields) . "'";
                $_expandRowFunc = <<<EOD
function(index,row){
    var ddv = $(this).datagrid('getRowDetail',index).find('table.ddv');
    var dtFields = [{$_detailFields}];
    var dtData0 = new Array();
    for (var i in dtFields) {
        dtData0[dtFields[i]] = row[dtFields[i]];
    }
    var dtData = [dtData0];
    ddv.datagrid({
        fitColumns:true,
        height:'auto',
        columns:[[
            {$_detailColumns}
        ]],
        data:dtData,
        onResize:function(){
            $('#{$id}').datagrid('fixDetailRowHeight',index);
        },
        onLoadSuccess:function(){
            setTimeout(function(){ $('#{$id}').datagrid('fixDetailRowHeight',index); },0);
        }
    });
    $(this).datagrid('fixDetailRowHeight',index);
    {$onExpandRow}
}
EOD;
                $datagridDataOptions['onExpandRow'] = $_expandRowFunc;
            }
            else {
                if (strpos($detailUrl, '?') === false) {
                    $detailUrl .= '?';
                }
                else {
                    $detailUrl .= '&';
                }
                $_keyField = empty($idField) ? 'id' : $idField;
                $datagridDataOptions['onExpandRow'] = <<<EOD
function(index,row){
    var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
    ddv.panel({
        height:'auto',
        border:false,
        cache:false,
        href:'{$detailUrl}{$_keyField}='+row.{$_keyField},
        onLoad:function(){
            $('#{$id}').datagrid('fixDetailRowHeight',index);
        }
    });
    {$onExpandRow}
}
EOD;
            }
        }
        
        // table html attribute options
        $tblOptions = [
            'title' => $title,
            'class' => 'easyui-datagrid',
            'data-options' => self::_renderDataOptions($tblDefaultDataOptions, $datagridDataOptions),
            'encode' => false,  // do not convert html charactors
        ];
        foreach ($htmlOptions as $k => $v) {
            $tblOptions[$k] = $v;
        }

        $htmlArray = [];
        
        if (!empty($arrDetailedColumns)) {
            $htmlArray[] = BaseHtmlUI::tag('script', '', ['type' => 'text/javascript', 'src' => \common\helpers\Utils::getRootUrl() . 'assets/'.self::ASSETS_EASYUI_FOLDER.'/extension/jquery.easyui.datagrid-detailview.js']);
        }
        
        // if spesified table size, wrap up a div to make sure datagrid display correctily
        if (!empty($tableParentSize)) {
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['style'=>implode(';', $tableParentSize), 'encode' => false, 'data-options' => "region:'center',border:false,fit:true"]);
        }
        else {
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['encode' => false, 'data-options' => "region:'center',border:false,fit:true"]);
        }

        $htmlArray[] = BaseHtmlUI::beginTag('table', $tblOptions);

        $columnCount = count($arrColumns);

        $theadArray = [];

        // check if has specified frozen columns
        if ($frozenColumnIndex > 0 && $frozenColumnIndex < $columnCount - 2) {
            for ($i = 0; $i <= $frozenColumnIndex; $i++) {
                $col = $arrColumns[$i];
                $theadArray[] = "<th data-options=\"{$col['data-options']}\">{$col['name']}</th>";
            }

            // frozen datagrid head
            $htmlArray[] = BaseHtmlUI::beginTag('thead', ['data-options' => 'frozen:true', 'encode' => false]);
            $htmlArray[] = BaseHtmlUI::beginTag('tr');
            $htmlArray[] = implode("\n", $theadArray);
            $htmlArray[] = BaseHtmlUI::endTag('tr');
            $htmlArray[] = BaseHtmlUI::endTag('thead');

            // format the left columns in to normal columns
            $theadArray = [];
            for ($i = $frozenColumnIndex + 1; $i < $columnCount; $i++) {
                $col = $arrColumns[$i];
                $theadArray[] = "<th data-options=\"{$col['data-options']}\">{$col['name']}</th>";
            }
        }
        else {
            // format each column into normal columns
            foreach ($arrColumns as $col) {
                $theadArray[] = "<th data-options=\"{$col['data-options']}\">{$col['name']}</th>";
            }
        }

        // normal datagrid head
        $htmlArray[] = BaseHtmlUI::beginTag('thead');
        $htmlArray[] = BaseHtmlUI::beginTag('tr');
        $htmlArray[] = implode("\n", $theadArray);
        $htmlArray[] = BaseHtmlUI::endTag('tr');
        $htmlArray[] = BaseHtmlUI::endTag('thead');

        if ((!isset($datagridDataOptions['url']) || empty($datagridDataOptions['url'])) && !empty($dataArray)) {
            $htmlArray[] = BaseHtmlUI::beginTag('tbody');

            $rowHtmlArray = [];
            foreach ($dataArray as $row) {
                $cellHtmlArray = [];
                foreach($arrColumnFields as $_field) {
                    $cellHtmlArray[] = '<td>'.(isset($row[$_field]) ? $row[$_field] : '').'</td>';
                }
                $rowHtmlArray[] = BaseHtmlUI::tag('tr', implode('', $cellHtmlArray));
            }
            $htmlArray[] = '  '.implode("\n  ", $rowHtmlArray);

            $htmlArray[] = BaseHtmlUI::endTag('tbody');
        }

        // close datagrid
        $htmlArray[] = BaseHtmlUI::endTag('table');

        // close the wrapped datagrid div if generated
        if (!empty($tableParentSize)) {
            $htmlArray[] = BaseHtmlUI::endTag('div');
        }
        else {
            $htmlArray[] = BaseHtmlUI::endTag('div');
        }

        if (!empty($toolbarId)) {
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['id' => $toolbarId, 'style' => 'height:auto']);
            foreach ($toolbarHtmlArray as $htmlLine) {
                $htmlArray[] = $htmlLine;
            }
            $htmlArray[] = BaseHtmlUI::endTag('div');
        }
        
        if ($hasDialog) {
            $htmlArray[] = BaseHtmlUI::tag('div', '', [
                'id' => $id . '_dlg',
                'class' => 'easyui-dialog',
                'title' => '    ',
                'style' => "width:{$dlgWidth};height:{$dlgHeight};",
                'data-options' => "iconCls:'icon-save',modal:true,resizable:true,inline:true,closed:true,top:24,onLoadError:$.custom.easyui.dialog.onLoadError,constrain:true"
            ]);
        }
        
        if ($hasWindow) {
            $htmlArray[] = BaseHtmlUI::tag('div', '', [
                'id' => $id . '_wnd',
                'class' => 'easyui-window',
                'title' => '    ',
                'style' => "width:{$dlgWidth};height:{$dlgHeight};",
                'data-options' => "iconCls:'icon-save',modal:true,inline:true,closed:true,top:24,onLoadError:$.custom.easyui.window.onLoadError,constrain:true"
            ]);
        }
        
        if (!empty($arrScripts)) {
            $htmlArray[] = Html::script(implode("\n", $arrScripts));
        }
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param array $buttons
     *      [
     *          [
     *              'type' => 'dialog' | 'window' | 'ajax' | 'tab',     // default tab
     *              'title' => '',      // if type is dialog or window or tab, this title would 
     *                                  // be displayed in opend element's title, or it would be
     *                                  // the confirm message for send ajax data if the title is not empty.
     *              'name' => '',       // button name
     *              'url' => '',        // the link that would be opened or querried.
     *              'icon' => '',       // icon-class for button, optional, default empty.
     *              'paramField' => ''  // if filled this field, the field is the model->field name, and convert into url + row.{paramField}
     *          ],
     *          ...
     *      ];
     * @param string $scriptFuncName
     * @return string
     */
    public static function scriptDatagridCellButtons($datagridId, $buttons, &$arrButtonOptions, &$scriptFuncName, &$hasDialog, &$hasWindow) {
        $pushBtnArray = [];
        foreach ($buttons as $btnConfig) {
            if (empty($btnConfig)) {
                continue;
            }
            $title = isset($btnConfig['title']) ? $btnConfig['title'] : '';
            $name = isset($btnConfig['name']) ? $btnConfig['name'] : '';
            $url = isset($btnConfig['url']) ? $btnConfig['url'] : '';
            $type = isset($btnConfig['type']) ? $btnConfig['type'] : '';
            $paramField = isset($btnConfig['paramField']) ? $btnConfig['paramField'] : '';
            $icon = isset($btnConfig['icon']) ? $btnConfig['icon'] : '';
            $condition = isset($btnConfig['condition']) ? $btnConfig['condition'] : '';
            $needReload = isset($btnConfig['needReload']) ? $btnConfig['needReload'] : false;
            $showText = isset($btnConfig['showText']) ? $btnConfig['showText'] : false;
            
            $scriptFuncPrefix = '';
            $scriptParamEndfix = '';
            if ($type == 'dialog') {
                $hasDialog = true;
                $scriptFuncPrefix = "easyuiFuncOpenUrlFromCurTabPrefer(\\'dialog\\', ";
            }
            else if ($type == 'window') {
                $hasWindow = true;
                $scriptFuncPrefix = "easyuiFuncOpenUrlFromCurTabPrefer(\\'window\\', ";
            }
            else if ($type == 'ajax') {
                $scriptFuncPrefix = "easyuiFuncQueryUrlAjax(";
                if ($needReload == true || $needReload == 'true') {
                    $scriptParamEndfix = ", \\'get\\', function(){ $(\\'#{$datagridId}\\').datagrid(\\'reload\\'); }";
                }
            }
            else if ($type == '_blank') {
                $scriptFuncPrefix = "easyuiFuncOpenUrlFromCurTabForDebug(\\'tab\\', ";
            }
            else {
                $scriptFuncPrefix = "easyuiFuncOpenUrlFromCurTabPrefer(\\'tab\\', ";
            }
            
            $urlEndfix = '';
            if (!empty($paramField)) {
                $urlEndfix = "' + row.{$paramField} + '";
            }
            $classExtra = '';
            if (!empty($icon)) {
                //$dataOptionsStr = "data-options=\"iconCls:\\'{$icon}\\'\"";
                $classExtra .= " {$icon}";
            }
            
            $curBtnHtml = '';
            if (empty($url)) {
                $curBtnHtml = "'<label href=\"javascript:void(0);\" style=\"display:block;float:left;margin: 4px 2px 0px 2px;\">{$name}</label>'";
            }
            elseif (empty($icon) || $showText) {
                if ($arrButtonOptions == null) {
                    $arrButtonOptions = [];
                }
                $cls = 'dg-cell-button-' . self::genID();
                $_opts = [];
                if (!empty($icon)) {
                    $_opts[] = "iconCls:'{$icon}'";
                }
                $arrButtonOptions[$cls] = implode(",", $_opts);
                $curBtnHtml = "'<a href=\"javascript:void(0);\" class=\"$cls\" onclick=\"{$scriptFuncPrefix}\\'{$url}{$urlEndfix}\\', \\'{$title}\\'{$scriptParamEndfix})\" title=\"{$name}\" style=\"display:block;float:left;margin: 0px 2px 0px 2px\" >{$name}</a>'";
            }
            else {
                $curBtnHtml = "'<a href=\"javascript:void(0);\" class=\"easyui-dg-cell-button-icon{$classExtra}\" onclick=\"{$scriptFuncPrefix}\\'{$url}{$urlEndfix}\\', \\'{$title}\\'{$scriptParamEndfix})\" title=\"{$name}\" style=\"display:block;width:16px;height:16px;float:left;margin: 0px 2px 0px 2px\" ></a>'";
            }
            $op = "";
            if (is_array($condition) && !empty($condition)) {
                if (isset($condition['field'])) {
                    $field = $condition['field'];

                    if (isset($condition['contains'])) {
                        $op = "row.{$field}.indexOf('{$condition['condvalue']}') != -1";
                    }
                    elseif (isset($condition['operator'])) {
                        $op = "row.{$field} {$condition['operator']} '{$condition['condvalue']}'";
                    }
                }
                else {
                    if (isset($condition[0])) {
                        $op = $condition[0];
                        $rep = isset($condition[1]) ? $condition[1] : [];
                        if (is_array($rep)) {
                            foreach ($rep as $_k => $_v) {
                                if ($_k == '{field}') {
                                    $_v = "row.{$_v}";
                                }
                                $op = str_replace($_k, $_v, $op);
                            }
                        }
                        elseif (is_string($rep) && !empty($rep)) {
                            $op = str_replace('{field}', "row.{$rep}", $op);
                        }
                    }
                }
            }

            if (!empty($op)) {
                $pushBtnArray[] = "if ({$op}) { a.push({$curBtnHtml}); }";
            }
            else {
                $pushBtnArray[] = "a.push({$curBtnHtml});";
            }
            
        }
        
        $scriptFuncName = 'formatter_dg_buttons_' . self::genID();
        $scriptContent = "function {$scriptFuncName}(value,row) {\n    var a = new Array();\n    " . implode("\n    ", $pushBtnArray) . "\n    return '<div>' + a.join('') + '</div>';\n}\n";
        return $scriptContent;
    }

    /**
     * 
     * @param string $title
     * @param array $htmlOptions
     * @param string $content default false, the dialog content
     * @return string html text
     */
    public static function dialog($title, $htmlOptions = [], $content = false) {
        $htmlOptions['class'] = 'easyui-dialog';
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'dlg_' . $currentID;
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            $htmlOptions['id'] = $id;
        }
        self::$lastTagID = $id;
        
        $isClosed = 'false';
        if (isset($htmlOptions['closed'])) {
            if (Utils::boolvalue($htmlOptions['closed'])) {
                $isClosed = 'true';
            }
            unset($htmlOptions['closed']);
        }
        
        $htmlOptions['title'] = $title;
        if (!isset($htmlOptions['data-options'])) {
            $htmlOptions['data-options'] = "iconCls:'icon-edit',modal:true,resizable:true,inline:true,onLoadError:$.custom.easyui.dialog.onLoadError,closed:{$isClosed}";
        }
        
        return BaseHtmlUI::tag('div', $content, $htmlOptions);
    }

    /**
     * 
     * @param string $title
     * @param array $htmlOptions
     * @param string $content default false, the window content
     * @return string html text
     */
    public static function window($title, $htmlOptions = [], $content = false) {
        $htmlOptions['class'] = 'easyui-window';
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'wnd_' . $currentID;
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            $htmlOptions['id'] = $id;
        }
        self::$lastTagID = $id;
        
        $isClosed = 'false';
        if (isset($htmlOptions['closed'])) {
            if (Utils::boolvalue($htmlOptions['closed'])) {
                $isClosed = 'true';
            }
            unset($htmlOptions['closed']);
        }
        
        $htmlOptions['title'] = $title;
        if (!isset($htmlOptions['data-options'])) {
            $htmlOptions['data-options'] = "iconCls:'icon-edit',modal:true,inline:true,onLoadError:$.custom.easyui.dialog.onLoadError,closed:{$isClosed}";
        }
        
        return BaseHtmlUI::tag('div', $content, $htmlOptions);
    }
    
    /**
     * 
     * @param string $name
     * @param string $value
     * @param string $label display label prompt for input field
     * @param array $htmlOptions
     * @return string html text
     */
    public static function textFieldWithLabel($name, $value = '', $label = '', $htmlOptions = []) {
        $htmlOptions['class'] = 'easyui-textbox';
        $htmlArray = [
            BaseHtmlUI::beginTag('div', ['style' => '']),
            Html::label($label, $name),
            Html::textInput($name, $value, $htmlOptions),
            BaseHtmlUI::endTag('div')
        ];
        return implode("\n", $htmlArray);
    }
    
    /**
     * generate the input field by type
     * @param integer $type input type, supported:
     *      textbox|number|email|password|telephone|datebox|datetimebox|textarea|combobox|checkBoxList|radioButtonList
     * @param string $name field name
     * @param string $value default '', initial value or selected value
     * @param array $data [value=>name] for combobox, checkboxlist, radioboxlist data
     * @param array $htmlOptions [k=>v]
     * @param string $prompt
     * @return string html text
     */
    public static function inputField($type = self::INPUT_TEXTBOX, $name = '', $value = '', $data = [], $htmlOptions = [], $prompt = '') {
        if (is_string($type)) {
            $_t = self::convertInputFieldType($type);
            if ($_t) {
                $type = $_t;
            }
        }
        $tag = 'input';
        $className = 'easyui-textbox';
        $dataOptionsStr = '';
        $tailHtml = '';
        if (isset($htmlOptions['data-options'])) {
            $dataOptionsStr = $htmlOptions['data-options'];
            unset($htmlOptions['data-options']);
        }
        $boolDataoptionFields = ['required', 'editable', 'readonly'];
        $isReadonly = false;
        foreach ($boolDataoptionFields as $_field) {
            if (isset($htmlOptions[$_field])) {
                $boolVal = Utils::boolvalue($htmlOptions[$_field]);
                if ($_field == 'readonly') {
                    if ($boolVal) {
                        $isReadonly = true;
                    }
                }
                if (strpos($dataOptionsStr, $_field) === false) {
                    $strBooleanValue = ($boolVal ? 'true' : 'false');
                    $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "{$_field}:{$strBooleanValue}";
                }
                unset($htmlOptions[$_field]);
            }
        }
        if (!empty($prompt)) {
            if ($prompt != strip_tags($prompt)) {
                $tailHtml = $prompt;
            }
            else {
                $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "prompt:'{$prompt}'";
            }
        }
        $htmlOptions['name'] = $name;
        $htmlOptions['value'] = $value;
        $label = '';
        if (isset($htmlOptions['label'])) {
            $label = $htmlOptions['label'];
            unset($htmlOptions['label']);
        }
        
        $checkboxName = false;
        if (isset($htmlOptions['checkbox'])) {
            if ($htmlOptions['checkbox']) {
                $checkboxName = $htmlOptions['checkbox'];
            }
            unset($htmlOptions['checkbox']);
        }
        $_defaultEventsArray = ['onChange', 'onSelect'];
        $_defaultStringPropertiesArray = [
            'missingMessage'=>\Yii::t('locale', '{name} is required.', ['name'=>(empty($label) ? \Yii::t('locale', 'This field') : $label)]),
            'invalidMessage'=>\Yii::t('locale', '{name} content is invalid.', ['name'=>(empty($label) ? \Yii::t('locale', 'This field') : $label)]),
            'tipPosition'=>'bottom',
        ];
        $_defaultNonStringPropertiesArray = [
            'validateOnBlur'=>'true',
        ];
        foreach ($_defaultEventsArray as $k) {
            if (isset($htmlOptions[$k])) {
                $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',')."{$k}:{$htmlOptions[$k]}";
                unset($htmlOptions[$k]);
            }
        }
        foreach ($_defaultStringPropertiesArray as $k => $v) {
            if (isset($htmlOptions[$k])) {
                $v = $htmlOptions[$k];
                unset($htmlOptions[$k]);
            }
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',')."{$k}:'{$v}'";
        }
        foreach ($_defaultNonStringPropertiesArray as $k => $v) {
            if (isset($htmlOptions[$k])) {
                $v = $htmlOptions[$k];
                unset($htmlOptions[$k]);
            }
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',')."{$k}:{$v}";
        }
        if (isset($htmlOptions['tailhtml'])) {
            $tailHtml = $htmlOptions['tailhtml'];
            unset($htmlOptions['tailhtml']);
        }
        
        if (self::INPUT_TEXTBOX == $type) {
            $className = 'easyui-textbox';
        }
        elseif (self::INPUT_NUMBERBOX == $type) {
            $className = 'easyui-numberbox';
            $htmlOptions['type'] = 'text';
            if (isset($htmlOptions['precision'])) {
                $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "precision:{$htmlOptions['precision']}";
                unset($htmlOptions['precision']);
            }
        }
        elseif (self::INPUT_EMAIL == $type) {
            $className = 'easyui-textbox';
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "validType:'email'";
        }
        elseif (self::INPUT_PASSWORD == $type) {
            $className = 'easyui-textbox';
            $htmlOptions['type'] = 'password';
        }
        elseif (self::INPUT_TELEPHONE == $type) {
            $className = 'easyui-numberbox';
            $htmlOptions['type'] = 'text';
        }
        elseif (self::INPUT_DATEBOX == $type) {
            $className = 'easyui-datebox';
            $htmlOptions['type'] = 'text';
        }
        elseif (self::INPUT_DATETIMEBOX == $type) {
            $className = 'easyui-datetimebox';
            $htmlOptions['type'] = 'text';
            $_optionsArray = ['showSeconds'];
            foreach ($_optionsArray as $k) {
                if (isset($htmlOptions[$k])) {
                    $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',')."{$k}:{$htmlOptions[$k]}";
                    unset($htmlOptions[$k]);
                }
            }
        }
        elseif (self::INPUT_TIMEBOX == $type) {
            $className = 'easyui-timespinner';
            $htmlOptions['type'] = 'text';
            // TODO formatter
        }
        elseif (self::INPUT_TEXTAREA == $type) {
            return Html::textArea($name, $value, array_merge(['class'=>'easyui-validatebox'], $htmlOptions)) . $tailHtml;
        }
        elseif (self::INPUT_COMBOBOX == $type) {
            $htmlOptions['class'] = 'easyui-combobox';
            if (!empty($dataOptionsStr)) {
                $htmlOptions['data-options'] = $dataOptionsStr;
            }
            if (is_string($data)) {
                $htmlOptions['encode'] = false;
                $fc = substr(trim($data), 0, 1);
                if ($fc == '{' || $fc == '[') {
                    $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "data:$data";
                }
                elseif (!preg_match("/url:/", $dataOptionsStr)) {
                    $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "method:'get',url:'$data'";
                }
                $htmlOptions['data-options'] = $dataOptionsStr;
                return self::tag('input', '', $htmlOptions) . $tailHtml;
            }
            else {
                return Html::dropDownList($name, $value, $data, $htmlOptions) . $tailHtml;
            }
        }
        elseif (self::INPUT_COMBOTREE == $type) {
            $className = 'easyui-combotree';
            $onlyLeafCheck = 'true';
            if (isset($htmlOptions['onlyLeafCheck'])) {
                $onlyLeafCheck = (Utils::boolvalue($htmlOptions['onlyLeafCheck']) ? 'true' : 'false');
            }
            $arrExtraOptions = ["onlyLeafCheck:{$onlyLeafCheck}"];
            $onBeforeCheck = "function(node, checked) { if (node.checkable !== undefined && node.checkable == false) { return false; } else if (node.attributes !== undefined && node.attributes.checkable !== undefined && node.attributes.checkable == false) { return false; } return true; }";
            if (isset($htmlOptions['onBeforeCheck'])) {
                $onBeforeCheck = $htmlOptions['onBeforeCheck'];
            }
            if (strpos($dataOptionsStr, 'onBeforeSelect:') === false) {
                //$arrExtraOptions[] = "onBeforeCheck:{$onBeforeCheck}";
                $arrExtraOptions[] = "onBeforeSelect:{$onBeforeCheck}";
            }
            if (is_array($data)) {
                $arrExtraOptions[] = "data:".self::convertComboTreeDataToString($data);
            }
            elseif (is_string($data)) {
                $arrExtraOptions[] = "url:'{$data}',method:'get'";
            }
            $arrExKeys = ['lines', 'dnd'];
            $arrNonStringKeys = ['lines'=>1, 'dnd'=>1];
            foreach ($arrExKeys as $k) {
                if (isset($htmlOptions[$k])) {
                    $v = strval($htmlOptions[$k]);
                    if (isset($arrNonStringKeys[$k])) {
                        if (is_bool($htmlOptions[$k])) {
                            $v = $htmlOptions[$k] ? 'true' : 'false';
                        }
                        $arrExtraOptions[] = "{$k}:{$v}";
                    }
                    else {
                        $arrExtraOptions[] = "{$k}:'{$v}'";
                    }
                }
            }
            
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . implode(",", $arrExtraOptions);
        }
        elseif (self::INPUT_CHECKBOXLIST == $type) {
            $clsName = (isset($htmlOptions['class']) ? $htmlOptions['class'] : '');
            $clsName = (empty($clsName) ? 'easyui-checkbox' : (strpos($clsName, 'easyui-checkbox') === false ? $clsName . ' easyui-checkbox' : $clsName ));
            $htmlOptions['class'] = $clsName;
            if ($isReadonly) {
                $htmlOptions['itemOptions'] = ['disabled'=>''];
            }
            $htmlText = Html::checkBoxList($name, $value, $data, $htmlOptions);
            if (count($data) > 5) {
                $htmlText = BaseHtmlUI::tag('div', $htmlText, ['style'=>'width:240px;height:100px; overflow:auto;']);
            }
            return $htmlText . $tailHtml;
        }
        elseif (self::INPUT_RATIOBUTTONLIST == $type) {
            if ($isReadonly) {
                $htmlOptions['itemOptions'] = ['disabled'=>''];
            }
            return Html::radioList($name, $value, $data, $htmlOptions) . $tailHtml;
        }
        elseif (self::INPUT_CHECKBOXDATAGRID == $type) {
            $columnsCfg = $data['columns'];
            $columns = [
                BaseHtmlUI::tag('th', ' ', ['data-options'=>"field:'ck',checkbox:true",'encode'=>false])
            ];
            if(substr($name,-2)!=='[]')
                $name.='[]';
            
            $fieldArray = [];
            $keyField = '';
            foreach ($columnsCfg as $col) {
                $_arrDataoptions = [];
                
                if (isset($col['field'])) {
                    $_arrDataoptions[] = "field:'{$col['field']}'";
                    $fieldArray[] = $col['field'];
                    if (empty($keyField)) {
                        $keyField = $col['field'];
                    }
                    elseif (isset($col['key']) && $col['key']) {
                        $keyField = $col['field'];
                    }
                }
                else {
                    $fieldArray[] = '';
                }
                if (isset($col['width'])) {
                    $_arrDataoptions[] = "width:{$col['width']}";
                }
                if (isset($col['align'])) {
                    $_arrDataoptions[] = "align:'{$col['align']}'";
                }
                
                $title = isset($col['title']) ? $col['title'] : '';
                
                $columns[] = BaseHtmlUI::tag('th', $title, ['data-options'=> implode(',', $_arrDataoptions), 'encode'=>false]);
            }
            
            $_dgId = self::ID_PREFIX . 'dg_checkbox_' . self::genID();
            $_tblLoadSuccessEvent = '';
            if (!empty($value)) {
                $_tmpArr = [];
                if (is_array($value)) {
                    foreach ($value as $_v) {
                        $_tmpArr[] = "'".trim($_v)."'";
                    }
                }
                else {
                    $_tmpArr[] = "'".trim(strval($value))."'";
                }
                $_selectionStr = implode(',', $_tmpArr);
                $_tblLoadSuccessEvent = <<<EOD
onLoadSuccess:function(data) {
        var obj = $('#{$_dgId}', this.parentNode);
        var a1 = new Array({$_selectionStr});
        for (var i in a1) {
            obj.datagrid('selectRecord', a1[i]);
        }
    }
EOD;
            }
            
            $singleSelect = 'false';
            if (isset($htmlOptions['single']) && Utils::boolvalue($htmlOptions['single'])) {
                $singleSelect = 'true';
            }
            
            $_tblDataOptions = <<<EOD
    idField:'{$keyField}',singleSelect:{$singleSelect},
    onSelect:function(index,row) {
        easyuiFuncCheckboxUpdateSelectValue('{$name}', row.{$keyField}, 'select', this.parentNode.parentNode.parentNode.parentNode);
    },
    onUnselect:function(index,row) {
        easyuiFuncCheckboxUpdateSelectValue('{$name}', row.{$keyField}, 'unselect', this.parentNode.parentNode.parentNode.parentNode);
    },
    onSelectAll:function(rows) {
        var a = new Array();
        for (var i in rows) {
            a[rows[i].{$keyField}] = 1;
        }
        easyuiFuncCheckboxUpdateSelectValue('{$name}', a, 'all', this.parentNode.parentNode.parentNode.parentNode);
    },
    onUnselectAll:function(rows) {
        var a = new Array();
        for (var i in rows) {
            a[rows[i].{$keyField}] = 1;
        }
        easyuiFuncCheckboxUpdateSelectValue('{$name}', a, 'none', this.parentNode.parentNode.parentNode.parentNode);
    },{$_tblLoadSuccessEvent}
EOD;
            $_tblHtmlOptions = [
                'class' => 'easyui-datagrid',
                'encode' => false,
                'width' => 'auto',
                'height' => 'auto',
                'data-options' => $_tblDataOptions,
                'id' => $_dgId,
            ];
            if (isset($htmlOptions['width'])) {
                $_tblHtmlOptions['width'] = $htmlOptions['width'];
            }
            if (isset($htmlOptions['height'])) {
                $_tblHtmlOptions['height'] = $htmlOptions['height'];
            }
            if (isset($htmlOptions['style'])) {
                $_tblHtmlOptions['style'] = $htmlOptions['style'];
            }
            
            $htmlArray = [];
            $htmlArray[] = BaseHtmlUI::beginTag('table', $_tblHtmlOptions);
            $htmlArray[] = "<thead>\n  <tr>";
            $htmlArray[] = "    ".implode("\n    ", $columns);
            $htmlArray[] = "  </tr>\n</thead>\n<tbody>";
            
            $rows = $data['data'];
            $checkBoxData = [];
            // body
            foreach ($rows as $rowData) {
                $htmlArray[] = "  <tr>";
                
                $keyValue = (isset($rowData[$keyField])?$rowData[$keyField]:'');
                
                $row = [
                    BaseHtmlUI::tag('td', $keyValue)
                ];
                
                $checkBoxData[$keyValue] = '';
                
                foreach($fieldArray as $fieldName) {
                    $row[] = BaseHtmlUI::tag('td', (isset($rowData[$fieldName]) ? $rowData[$fieldName] : ''));
                }
                $htmlArray[] = '    '.implode("\n    ", $row);
                
                $htmlArray[] = "  </tr>";
            }
            
            $htmlArray[] = "</tbody>\n</table>";
            
            // hiden the checkbox list
            $htmlArray[] = Html::checkBoxList($name, $value, $checkBoxData, ['style'=>'display:none']);
            
            return join("\n", $htmlArray);
        }
        elseif (self::INPUT_COMBOGRID == $type) {
            $className = 'easyui-combogrid';
            $columnDefaultDataOptions = self::_defaultDatagridColumn_data_option();
            
            $arrDataOptions = [];
            $arrOptionsFields = ['panelWidth', 'idField', 'textField', 'url', 'detailUrl', 'method', 'pagination', 'toolbar', 'onSelect', 'onChange', 'editable', 'onShowPanel', 'queryParams'];
            $arrNumOptionsFields = ['panelWidth' => 1, 'pagination' => 2, 'onSelect' => 2, 'onChange' => 2, 'editable'=>2, 'onShowPanel'=>2, 'queryParams'=>5];
            $arrDataOptions[] = "value:'{$value}'";
            $arrSrcOptions = $data;
            if (!is_array($data)) {
                $arrSrcOptions = [];
                $arrSrcOptions['url'] = strval($data);
            }
            
            foreach ($arrOptionsFields as $field) {
                if (isset($htmlOptions[$field])) {
                    $arrSrcOptions[$field] = $htmlOptions[$field];
                    unset($htmlOptions[$field]);
                }
            }
            
            // toolbar
            if (isset($arrSrcOptions['toolbar'])) {
                $toolbarArray = $arrSrcOptions['toolbar'];
                unset($arrSrcOptions['toolbar']);
                if (is_array($toolbarArray)) {
                    $dgId = (isset($htmlOptions['id'])? $htmlOptions['id'] : self::ID_PREFIX.'dg_'.self::genID());
                    $htmlOptions['id'] = $dgId;
                    $toolbarHtmlArray = self::_genDatagridToolBarHtmlArray($dgId, $toolbarArray);
                    
                    if (!empty($toolbarHtmlArray)) {
                        $toolbarId = self::ID_PREFIX.'tb_'.self::genID();
                        $arrSrcOptions['toolbar'] = '#'.$toolbarId;
                        $arrSrcOptions['onShowPanel'] = "function() { $.parser.parse('#{$toolbarId}') }";
                        
                        $toolbarHtmlArray2 = [];
                        $toolbarHtmlArray2[] = BaseHtmlUI::beginTag('div', ['id' => $toolbarId, 'style' => 'height:auto']);
                        foreach ($toolbarHtmlArray as $htmlLine) {
                            $toolbarHtmlArray2[] = $htmlLine;
                        }
                        $toolbarHtmlArray2[] = BaseHtmlUI::endTag('div');
                        
                        $tailHtml .= implode("\n", $toolbarHtmlArray2);
                    }
                }
                else if (is_string($toolbarArray)) {
                    if (substr($toolbarArray, 0, 1) != '#') {
                        $toolbarArray = '#'.$toolbarArray;
                    }
                    $arrSrcOptions['toolbar'] = $toolbarArray;
                }
            }
            
            foreach ($arrOptionsFields as $field) {
                if (isset($arrSrcOptions[$field])) {
                    if (isset($arrNumOptionsFields[$field])) {
                        $arrDataOptions[] = "{$field}:{$arrSrcOptions[$field]}";
                    }
                    else {
                        $arrDataOptions[] = "{$field}:'{$arrSrcOptions[$field]}'";
                    }
                }
            }

            $idField = '';
            $arrColumns = (isset($arrSrcOptions['columns']) ? $arrSrcOptions['columns'] : (isset($htmlOptions['columns']) ? $htmlOptions['columns'] : null));
            if (is_array($arrColumns)) {
                $arrColumnOptions = [];
                
                foreach ($arrColumns as $col) {
                    if (is_array($col)) {
                        $colConfig = self::formatDatagridColumnConfig(null, $col, $idField);
                        $colOptions = $colConfig['options'];
                        $arrColumnOptions[] = "{". self::_renderDataOptions($columnDefaultDataOptions, $colOptions) . "}";
                    }
                    else {
                        $arrColumnOptions[] = "{". strval($col) . "}";
                    }
                }
                
                $arrDataOptions[] = "columns:[[\n        ".implode(",\n        ", $arrColumnOptions)."\n    ]]";
            }
            elseif (is_string($arrColumns)) {
                $arrDataOptions[] = "columns:{$arrColumns}";
            }
            
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ",\n    ") . implode(",\n    ", $arrDataOptions);
        }
        elseif (self::INPUT_FILEFIELD == $type) {
            $className = 'easyui-filebox';
            $dataOptionsStr .= (empty($dataOptionsStr) ? '' : ',') . "buttonText:'" . Yii::t('locale', 'Choose file') . "'";
        }
        elseif (self::INPUT_IMAGEFIELD == $type) {
            $className = 'easyui-filebox';
            $width = (isset($htmlOptions['width']) ? $htmlOptions['width'] : '200px');
            $height = '160px';
            $imgSrc = '';
            $hiddenField = null;
            $hadDelete = false;
            if (isset($htmlOptions['height'])) {
                $height = $htmlOptions['height'];
                unset($htmlOptions['height']);
            }
            if (isset($htmlOptions['src'])) {
                $imgSrc = $htmlOptions['src'];
                unset($htmlOptions['src']);
            }
            if (isset($htmlOptions['hiddenField'])) {
                $_hiddenInfo = $htmlOptions['hiddenField'];
                if (is_array($_hiddenInfo)) {
                    $_hiddenName = (isset($_hiddenInfo['name']) ? $_hiddenInfo['name'] : '');
                    $_hiddenValue = (isset($_hiddenInfo['value']) ? $_hiddenInfo['value'] : $imgSrc);
                    unset($_hiddenInfo['name']);
                    unset($_hiddenInfo['value']);
                    $hiddenField = Html::hiddenInput($_hiddenName, $_hiddenValue, $_hiddenInfo);
                }
                else {
                    $hiddenField = Html::hiddenInput($_hiddenInfo, $imgSrc);
                }
                unset($htmlOptions['hiddenField']);
            }
            if (isset($htmlOptions['deleteable'])) {
                $hadDelete = Utils::boolvalue($htmlOptions['deleteable']);
                unset($htmlOptions['deleteable']);
            }
            $fileSizeLimit = "[600,'KB']";
            if (isset($htmlOptions['fileSize'])) {
                if (is_string($htmlOptions['fileSize'])) {
                    $_limitTxt = $htmlOptions['fileSize'];
                    if (!empty($_limitTxt)) {
                        if (substr($_limitTxt,0,1) == '[') {
                            $fileSizeLimit = $_limitTxt;
                        }
                        else {
                            $arr = [];
                            $_limitTxt = trim($_limitTxt);
                            if (preg_match('/^(\d+)/', $_limitTxt, $arr)) {
                                $num = $arr[1];
                                $unit = trim(substr($_limitTxt, strlen($num)));
                                if (empty($unit)) {
                                    $unit = 'bytes';
                                }
                                $fileSizeLimit = "[{$num},'{$unit}']";
                            }
                        }
                    }
                }
                unset($htmlOptions['fileSize']);
            }
            
            $divId = self::ID_PREFIX.'div_'.self::genID();
            $imgId = self::ID_PREFIX.'img_'.self::genID();
            $dataOptionsStr .= (empty($dataOptionsStr) ? "" : ",") . "buttonText:'" . \Yii::t('locale', 'Choose file') . "',onChange:function(){ var opts = $(this).filebox('options'); $.custom.utils.imgfilebox.previewImage($('#'+opts.fileboxId)[0], '{$imgId}', '{$divId}'); }, accept:'image/gif,image/jpeg,image/jpg,image/png', validType:{fileSize:{$fileSizeLimit}}";
            
            if ($hadDelete) {
                // TODO
            }
            $tailHtml .= BaseHtmlUI::tag('div', BaseHtmlUI::tag('img', '', ['id'=>$imgId, 'style'=>"width:100%;height:100%", 'src'=>$imgSrc]), ['class'=>'', 'id'=>$divId, 'encode'=>false, 'style' => "width:{$width};height:{$height};border:1px;border-color:#233EBF"]);
            if ($hiddenField) {
                $tailHtml .= $hiddenField;
            }
        }
        else {
            return '';
        }
        
        $htmlOptions['class'] = $className;
        if (!empty($dataOptionsStr)) {
            $htmlOptions['data-options'] = $dataOptionsStr;
        }
        
        return BaseHtmlUI::tag($tag, '', $htmlOptions) . $tailHtml;
    }
    
    /**
     * returns a completed html text for a form
     * @param string $title
     * @param string $action
     * @param string $method
     * @param array $htmlOptions [k=>v]
     * @param array $inputs [[
     *                              "type"=>INPUT_TEXTBOX,  // type of input field, @see inputField
     *                              "name"=>"",             // field name
     *                              "value"=>"",            // initialize value or selected value for field
     *                              "label"=>"",            // label for field
     *                              "data"=>[],        // data for combobox,checkboxlist,raidiobuttonlist
     *                              "htmlOptions"=>[k=>v], 
     *                              "prompt"=>""            // prompt for input
     *                            ],...
     *                       ]
     * @param array $buttons buttons type ("submit" => "button name", "reset" => "button name")
     * @param array $hiddenFields ('name' => 'value')
     * @return string html text
     */
    public static function form($title = '', $action = '', $method = 'post', $htmlOptions = [], $inputs = [], $buttons = [], $hiddenFields = []) {
        $htmlArray = [];
        
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'form_' . $currentID;
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            $htmlOptions['id'] = $id;
        }
        self::$lastTagID = $id;
        
        $defaultSuccessCallback = 'undefined';
        $defaultOnsubmitCallback = 'undefined';
        if (isset($htmlOptions['window'])) {
            unset($htmlOptions['window']);
        }
        if (isset($htmlOptions['dialog'])) {
            unset($htmlOptions['dialog']);
        }
        if (isset($htmlOptions['successCallback'])) {
            if (!empty($htmlOptions['successCallback'])) {
                $defaultSuccessCallback = $htmlOptions['successCallback'];
            }
            unset($htmlOptions['successCallback']);
        }
        if (isset($htmlOptions['onSubmitCallback'])) {
            if (!empty($htmlOptions['onSubmitCallback'])) {
                $defaultOnsubmitCallback = $htmlOptions['onSubmitCallback'];
            }
            unset($htmlOptions['onSubmitCallback']);
        }
        $headerId = null;
        if (isset($htmlOptions['header'])) {
            $headerId = $htmlOptions['header'];
            unset($htmlOptions['header']);
        }
        
        $inputGroupsArray = [[['label'=>'', 'options'=>[], 'inputs'=>[]]]];
        
        $hasRows = false;
        $maxGroupColumnIndex = 0;
        $curGroupColumnIndex = 0;
        $curGroupIndex = 0;
        $curRowIndex = -1;
        $extraHtmls = [];
        $groupColumnsArray = &$inputGroupsArray[$curGroupIndex];
        $inputGroupInfo = &$groupColumnsArray[$curGroupColumnIndex];
        foreach ($inputs as $o) {
            if ($o === null) {
                continue;
            }
            $inputRowsArray = &$inputGroupInfo['inputs'];
            
            if (is_string($o)) {
                $curRowIndex++;
                
                if (!isset($inputRowsArray[$curRowIndex])) {
                    $inputRowsArray[$curRowIndex] = [];
                }
                $inputColumnsArray = &$inputRowsArray[$curRowIndex];
                if (!isset($inputColumnsArray[0])) {
                    $inputColumnsArray[0] = [];
                }
                $_subArray = &$inputColumnsArray[0];

                $_subArray[] = '<div style="display:table-row"><div style="display:table-cell;padding:3px 6px 3px 6px;"></div><div style="display:table-cell;padding:3px 12px 3px 6px;">';
                $_subArray[] = $o;
                $_subArray[] = "</div></div>";
            }
            else {
                $type = (isset($o['type']) ? $o['type'] : self::INPUT_TEXTBOX);
                $name = (isset($o['name']) ? $o['name'] : '');
                $value = (isset($o['value']) ? $o['value'] : '');
                $label = (isset($o['label']) ? $o['label'] : '');
                $data = (isset($o['data']) ? $o['data'] : []);
                $options = (isset($o['htmlOptions']) ? $o['htmlOptions'] : []);
                $prompt = (isset($o['prompt']) ? $o['prompt'] : '');
                $curColumnIndex = (isset($o['columnindex']) ? $o['columnindex'] : 0);
                $checkboxOptions = (isset($o['checkbox']) ? $o['checkbox'] : false);
                $endfixHtml = '';
                
                if ($checkboxOptions) {
                    $_checkboxName = (isset($checkboxOptions['name']) ? $checkboxOptions['name'] : '');
                    $_position = 'front';
                    $_isSelected = (isset($checkboxOptions['selected']) ? $checkboxOptions['selected'] : false);
                    $_value = (isset($checkboxOptions['value']) ? $checkboxOptions['value'] : '');
                    $_checkboxOptions = array_merge(['style'=>"margin-top:6px", 'value'=>$_value], (isset($checkboxOptions['htmlOptions']) ? $checkboxOptions['htmlOptions'] : []));
                    if (isset($checkboxOptions['readonly']) && Utils::boolvalue($checkboxOptions['readonly'])) {
                        $_checkboxOptions['disabled'] = '';
                    }
                    if (isset($checkboxOptions['position'])) {
                        $_position = $checkboxOptions['position'];
                    }
                    
                    if ($_position == 'front') {
                        $label = CMyHtml::checkBox($_checkboxName, $label, $_isSelected, $_checkboxOptions);
                    }
                    else {
                        $endfixHtml = CMyHtml::checkBox($_checkboxName, '', $_isSelected, $_checkboxOptions);
                    }
                }
                
                if ($curColumnIndex == 0) {
                    $curRowIndex++;
                }
                
                if ($type == self::INPUT_TYPE_GROUP) {
                    if ($hasRows) {
                        $curGroupColumnIndex = $curColumnIndex;
                        if ($curGroupColumnIndex == 0) {
                            $curGroupIndex += 1;
                        }
                        else {
                            if ($maxGroupColumnIndex < $curGroupColumnIndex) {
                                $maxGroupColumnIndex = $curGroupColumnIndex;
                            }
                        }
                        if (!isset($inputGroupsArray[$curGroupIndex])) {
                            $inputGroupsArray[$curGroupIndex] = [];
                        }
                        $groupColumnsArray = &$inputGroupsArray[$curGroupIndex];
                        $groupColumnsArray[$curGroupColumnIndex] = ['label'=>'', 'options'=>[], 'inputs'=>[]];
                        $inputGroupInfo = &$groupColumnsArray[$curGroupColumnIndex];
                    }
                    
                    $inputGroupInfo['label'] = $label;
                    $inputGroupInfo['options'] = $options;
                    $curRowIndex = -1;
                }
                else if ($type == self::INPUT_CHECKBOXDATAGRID) {
                    $_dgOptions = [
                        'class'=>'easyui-panel', 
                        'data-options' => "title:'{$label}'",
                        'encode' => false,
                        'style' => 'display:block;float:none',
                    ];
                    if (isset($options['width'])) {
                        $_dgOptions['width'] = $options['width'];
                        $options['width'] = '100%';
                    }
                    if (isset($options['height'])) {
                        $_dgOptions['height'] = $options['height'];
                        $options['height'] = '100%';
                    }
                    if (!isset($options['label'])) {
                        $options['label'] = $label;
                    }
                    $extraHtmls[] = BaseHtmlUI::beginTag('div', $_dgOptions);
                    $extraHtmls[] = self::inputField($type, $name, $value, $data, $options, $prompt);
                    $extraHtmls[] = BaseHtmlUI::endTag('div');
                }
                else {
                    if (!isset($inputRowsArray[$curRowIndex])) {
                        $inputRowsArray[$curRowIndex] = [];
                    }
                    $inputColumnsArray = &$inputRowsArray[$curRowIndex];
                    if (!isset($inputColumnsArray[$curColumnIndex])) {
                        $inputColumnsArray[$curColumnIndex] = [];
                    }
                    $_subArray = &$inputColumnsArray[$curColumnIndex];
                    
                    if ($type == self::INPUT_TYPE_APPENDELEMENTBUTTON) {
                        $_funGetAppendingHtml = '';
                        $_funAppendHtml = "opts._curAddCount++; var _html = '<div style=\'display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;\'></div><div id=\''+opts.id+'_n_'+opts._curAddCount+'\' style=\'display:table-cell;padding:3px 16px 3px 6px;\'>' + data + '</div>'; appendingObj.after(_html); $.parser.parse('#'+opts.id+'_n_'+opts._curAddCount);";
                        if (is_array($data)) {
                            if (isset($data['url'])) {
                                $_funGetAppendingHtml = "$.ajax({url:'{$data['url']}', type:'get', beforeSend:easyuiFuncAjaxLoading, success:function(data){ easyuiFuncAjaxEndLoading(); if (data) { {$_funAppendHtml} } }, error: function (e) { easyuiFuncAjaxEndLoading(); easyuiFuncOnProcessErrorEvents(e);} });";
                            }
                            elseif (isset($data['func'])) {
                                $_funGetAppendingHtml = "var data = {$data['func']}('{$name}'); if (data) { {$_funAppendHtml} }";
                            }
                        }
                        elseif (is_string($data)) {
                            $_funGetAppendingHtml = "var data = {$data}('{$name}'); if (data) { {$_funAppendHtml} }";
                        }
                        
                        if (!empty($_funGetAppendingHtml)) {
                            $_subArray[] = '<div style="display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;">'.
                                '</div><div style="display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 16px 3px 6px;">';
                            $appendButtonId = (isset($options['id']) ? $options['id'] : CMyHtml::getIDPrefix().'appendbtn_'.self::genID());
                            $_subArray[] = self::tag('a', $label, ['encode'=>false, 'id'=>$appendButtonId, 'class'=>'easyui-linkbutton', 
                                'data-options'=> <<<EOD
iconCls:'icon-add',onClick:function(){
        var parentEle = this.parentElement;
        var appendingObj = $(parentEle.previousElementSibling.previousElementSibling);
        var opts = $(this).linkbutton('options');
        if (!opts._curAddCount) {
            opts._curAddCount = 0;
        }
        $_funGetAppendingHtml
}
EOD
                            ]);
                            $_subArray[] = "</div>";
                        }
                        
                    }
                    elseif ($type == self::INPUT_TYPE_SUBGROUP) {
                        if (empty($label)) {
                            $_subArray[] = '</div></div>';
                            $_subArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table"]);
                            $_subArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table-row"]);
                        }
                        else {
                            $inputGroupInfo['subgroup'] = ['label'=>$label, 'options'=>$options, 'inputs'=>[]];
                            $inputGroupInfo = &$inputGroupInfo['subgroup'];
                        }
                    }
                    elseif ($type == self::INPUT_TYPE_HTML) {
                        $subHtml = (isset($o['html']) ? $o['html'] : '');
                        if (empty($label)) {
                            $_subArray[] = '<div style="display:table-cell;padding:3px 16px 3px 6px">'.$subHtml.'</div>';
                        }
                        else {
                            $_subArray[] = '<div style="display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;">'.
                                $label.'</div><div style="display:table-cell;padding:3px 16px 3px 6px;">';
                            $_subArray[] = $subHtml;
                            $_subArray[] = "</div>";
                        }
                    }
                    else {
                        $_subArray[] = '<div style="display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 6px 3px 16px;">'.
                            $label.'</div><div style="display:table-cell;white-space:nowrap;word-wrap:nowrap;padding:3px 16px 3px 6px;">';
                        if (!isset($options['label'])) {
                            $options['label'] = $label;
                        }
                        $_subArray[] = self::inputField($type, $name, $value, $data, $options, $prompt).$endfixHtml;
                        $_subArray[] = "</div>";
                    }
                    
                }
            }
            $hasRows = true;
        }
        
        $width = self::popHtmlOptionsStyleAttribute($htmlOptions, 'width', 'auto');
        $height = self::popHtmlOptionsStyleAttribute($htmlOptions, 'height', 'auto');
        
        $footerId = '';
        $mainPanelHtmlOptions = ['class' => 'easyui-panel', 'title' => $title, 'style' => "width:{$width};height:{$height};", 'encode'=>false];
        $_dataOptions = [];
        if (!isset($htmlOptions['fit'])) {
            $_dataOptions[] = "fit:true";
        }
        else {
            if (Utils::boolvalue($htmlOptions['fit'])) {
                $_dataOptions[] = "fit:true";
            }
            unset($htmlOptions['fit']);
        }
        if (!empty($buttons)) {
            $footerId = CMyHtml::getIDPrefix().'formfooter_'.self::genID();
            $_dataOptions[] = "footer:'#{$footerId}'";
        }
        if (!empty($headerId)) {
            $_dataOptions[] = "header:'#{$headerId}'";
        }
        $_arrDataoptions = ['onLoad', 'onOpen'];
        foreach ($_arrDataoptions as $k) {
            if (isset($htmlOptions[$k])) {
                $_dataOptions[] = "{$k}:{$htmlOptions[$k]}";
                unset($htmlOptions[$k]);
            }
        }
        $mainPanelHtmlOptions['data-options'] = implode(',', $_dataOptions);
        
        // wrapper
        $htmlArray[] = BaseHtmlUI::beginTag('div', $mainPanelHtmlOptions);
        $htmlArray[] = Html::beginForm($action, $method, $htmlOptions);
        
        foreach ($inputGroupsArray as $_groupColumnsArray) {
            $groupColumnsCount = count($_groupColumnsArray);
            if ($groupColumnsCount > 1) {
                $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:block;width:100%"]);
            }
            foreach ($_groupColumnsArray as $_groupInfo) {
                if ($groupColumnsCount > 1) {
                    $groupCellWidth = intval((1 / $groupColumnsCount) * 100);
                    $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "float:left; width:{$groupCellWidth}%;"]);
                }
                $htmlArray[] = BaseHtmlUI::beginTag('div', array_merge(['class' => 'easyui-panel', 'title' => $_groupInfo['label'], 'style' => 'width:100%;height:auto;padding:12px', 'encode'=>false], $_groupInfo['options']));

                $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table;"]);
                foreach ($_groupInfo['inputs'] as $_inputColumnsArray) {
                    $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table-row"]);
                    foreach ($_inputColumnsArray as $_colsArray) {
                        $htmlArray[] = implode("\n", $_colsArray);
                    }
                    $htmlArray[] = BaseHtmlUI::endTag('div');
                }
                // close of div display:table-row-group
                $htmlArray[] = BaseHtmlUI::endTag('div');

                // close of div panel
                $htmlArray[] = BaseHtmlUI::endTag('div');
                
                if (isset($_groupInfo['subgroup'])) {
                    $htmlArray[] = BaseHtmlUI::beginTag('div', array_merge(['class' => 'easyui-panel', 'title' => $_groupInfo['subgroup']['label'], 'style' => 'width:100%;height:auto;padding:12px', 'encode'=>false], $_groupInfo['subgroup']['options']));

                    $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table;"]);
                    foreach ($_groupInfo['subgroup']['inputs'] as $_inputColumnsArray) {
                        $htmlArray[] = BaseHtmlUI::beginTag('div', ['style' => "display:table-row"]);
                        foreach ($_inputColumnsArray as $_colsArray) {
                            $htmlArray[] = implode("\n", $_colsArray);
                        }
                        $htmlArray[] = BaseHtmlUI::endTag('div');
                    }
                    // close of div display:table-row-group
                    $htmlArray[] = BaseHtmlUI::endTag('div');

                    // close of div panel
                    $htmlArray[] = BaseHtmlUI::endTag('div');
                }
                
                if ($groupColumnsCount > 1) {
                    $htmlArray[] = BaseHtmlUI::endTag('div');
                }
            }
            if ($groupColumnsCount > 1) {
                $htmlArray[] = BaseHtmlUI::endTag('div');
            }
        }
        
        //$hiddenId = null;
        if (!empty($hiddenFields)) {
            //$hiddenId = CMyHtml::getIDPrefix().'formhidden_'.self::genID();
            //$htmlArray[] = BaseHtmlUI::beginTag('div', ['id'=>$hiddenId]);
            foreach ($hiddenFields as $k => $v) {
                if (is_int($k)) {
                    $k = null;
                }
                if (is_array($v)) {
                    $_hv = isset($v['value']) ? $v['value'] : '';
                    unset($v['value']);
                    $htmlArray[] = Html::hiddenInput($k, $_hv, $v);
                }
                else {
                    $htmlArray[] = Html::hiddenInput($k, $v);
                }
            }
            //$htmlArray[] = BaseHtmlUI::endTag('div');
        }
        
        //$htmlArray[] = BaseHtmlUI::tag('div', '', ['style'=>'display:block; padding:6px']);
        
        $htmlArray[] = implode("\n", $extraHtmls);
        
        $htmlArray[] = Html::endForm();
        
        $htmlArray[] = BaseHtmlUI::endTag('div');
        
        if (!empty($buttons)) {
            $htmlArray[] = BaseHtmlUI::beginTag('div', ['id'=>$footerId, 'style' => "text-align:center;padding:5px 50px 5px 50px;width:auto;height:auto"]);
            
            $btnsArray = [];
            $closeBtn = null;
            if (isset($buttons['submit'])) {
                $btnsArray[] = ['type'=>'submit', 'label'=>$buttons['submit'], 'icon'=>'icon-ok', 'params'=>'', 'success'=>$defaultSuccessCallback, 'onsubmit'=>$defaultOnsubmitCallback];
                unset($buttons['submit']);
            }
            if (isset($buttons['reset'])) {
                $btnsArray[] = ['type'=>'reset', 'label'=>$buttons['reset'], 'icon'=>'icon-undo'];
                unset($buttons['reset']);
            }
            if (isset($buttons['close'])) {
                $closeBtn = ['type'=>'close', 'label'=>$buttons['close'], 'icon'=>'icon-cancel'];
                unset($buttons['close']);
            }
            
            $htmlTextInfo = null;
            if (isset($buttons['html'])) {
                if (is_array($buttons['html'])) {
                    $htmlTextInfo = ['html'=> (isset($buttons['html']['html']) ? $buttons['html']['html'] : ''), 'align'=>(isset($buttons['html']['align']) ? $buttons['html']['align'] : 'right')];
                }
                else {
                    $htmlTextInfo = ['html'=>  strval($buttons['html']), 'align'=>'right'];
                }
                unset($buttons['html']);
            }
            
            foreach ($buttons as $cfg) {
                if (is_array($cfg)) {
                    $btnType = (isset($cfg['type']) ? $cfg['type'] : '');
                    $btnText = (isset($cfg['label']) ? $cfg['label'] : '');
                    $btnIcon = (isset($cfg['icon']) ? $cfg['icon'] : 'icon-application');
                    $btnParams = (isset($cfg['params']) ? $cfg['params'] : '');
                    $btnSuccessCallback = (isset($cfg['successCallback']) ? $cfg['successCallback'] : $defaultSuccessCallback);
                    $btnOnSubmitCallback = (isset($cfg['onsubmitCallback']) ? $cfg['onsubmitCallback'] : $defaultOnsubmitCallback);
                    $btnsArray[] = ['type'=>$btnType, 'label'=>$btnText, 'icon'=>$btnIcon, 'params'=>$btnParams, 'success'=>$btnSuccessCallback, 'onsubmit'=>$btnOnSubmitCallback];
                }
            }
            if ($closeBtn) {
                $btnsArray[] = $closeBtn;
            }

            foreach ($btnsArray as $cfg) {
                if ($cfg['type'] == 'close') {
                    $htmlArray[] = BaseHtmlUI::tag('a', $cfg['label'], ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'onclick' => "easyuiFuncCloseCurrent()", 'data-options' => "iconCls:'{$cfg['icon']}',width:90"]);
                }
                elseif ($cfg['type'] == 'reset') {
                    $htmlArray[] = BaseHtmlUI::tag('a', $cfg['label'], ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'onclick' => "$('#{$id}').form('clear');", 'data-options' => "iconCls:'{$cfg['icon']}',width:90"]);
                }
                elseif ($cfg['type'] == 'button') {
                    $btnEvent = isset($cfg['success']) ? $cfg['success'] : '';
                    $htmlArray[] = BaseHtmlUI::tag('a', $cfg['label'], ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'data-options' => "iconCls:'{$cfg['icon']}',width:90", 'onclick'=>$btnEvent]);
                }
                else {
                    $exParams = '';
                    if (isset($cfg['params']) && !empty($cfg['params'])) {
                        $exParams.= ",".$cfg['params'];
                    }
                    else {
                        $exParams.= ",undefined";
                    }
                    if (isset($cfg['success']) && !empty($cfg['success'])) {
                        $exParams.= ",".$cfg['success'];
                    }
                    else {
                        $exParams.= ",undefined";
                    }
                    if (isset($cfg['onsubmit']) && !empty($cfg['onsubmit'])) {
                        $exParams.= ",".$cfg['onsubmit'];
                    }
                    else {
                        $exParams.= ",undefined";
                    }
                    $htmlArray[] = BaseHtmlUI::tag('a', $cfg['label'], ['href' => "javascript:void(0)", 'class' => 'easyui-linkbutton', 'onclick' => "easyuiFuncFormOnSubmit('#{$id}'{$exParams})", 'data-options' => "iconCls:'{$cfg['icon']}',width:90"]);
                }
            }
            
            if ($htmlTextInfo && $htmlTextInfo['align'] == 'right') {
                $htmlArray[] = BaseHtmlUI::tag('div', $htmlTextInfo['html'], ['style'=>"float:right;display:inline;height:auto;vertical-align:center;"]);
            }
            
            $htmlArray[] = BaseHtmlUI::endTag('div');
        }
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param array $accordions
     *      1. data formation is:
     *      [
     *          [
     *              'title' => title,
     *              'data' => [],      // subchildren or content, canbe array or string
     *              'icon' => icon-class,       // optional
     *              'htmlOptions' => [],   // optional
     *              'selected' => true|false,   // optional
     *              'tools' => [['icon' => icon-class, 'handler' => 'function content'],...] // optional
     *          ],
     *          ...
     *      ];
     * 
     *      2. htmlOptions data supportted field:
     *          1) default html tag attributes
     *          2) 'icon' : icon-class
     * 
     *      3. note: array['data'] supported format:
     *      1) accordion:
     *      [
     *          'type' => 'accordion' | or not set  // if set type and type is 
     *                    // accordion, the sub content is accordion, or sub content is tree
     *          'htmlOptions' => arary(),           // optional
     *          'data' => [] | string,         // sub data or content
     *                  // if not set the field data, the parent ['data'] would be considered as sub data content.
     *      ];
     *      2) tree list
     *      [
     *          'htmlOptions' => arary(),           // optional
     *          'data' => [] | string,         // sub data or content
     *                  // if not set the field data, the parent ['data'] would be considered as sub data content.
     *      ];
     *      3) html text
     *      
     * @param array $htmlOptions
     * @return string html text
     */
    public static function accordionList($accordions = [], $htmlOptions = []) {
        if (!isset($htmlOptions['data-options'])) {
            $htmlOptions['data-options'] = "fit:true,border:false";
        }
        if (isset($htmlOptions['icon'])) {
            $dataOptions = $htmlOptions['data-options'];
            if (strpos($dataOptions, 'iconCls') === false) {
                $dataOptions .= (empty($dataOptions) ? '' : ',') . "iconCls:'{$htmlOptions['icon']}'";
                $htmlOptions['data-options'] = $dataOptions;
            }
            unset($htmlOptions['icon']);
        }
        $htmlOptions['class'] = "easyui-accordion";
        $htmlOptions['encode'] = false;
        
        $htmlArray = [];
        $htmlArray[] = BaseHtmlUI::beginTag('div', $htmlOptions);
        
        foreach ($accordions as $k => $row) {
            if (is_string($k)) {
                continue;
            }
            $title = $row['title'];
            $content = $row['data'];
            $htmlOptions = (isset($row['htmlOptions']) ? $row['htmlOptions'] : []);
            
            if (!isset($htmlOptions['style'])) {
                $htmlOptions['style'] = "padding:10px;background-color:#F1FAFA;";   // accordion default style
            }
            
            $dataOptions = isset($htmlOptions['data-options']) ? $htmlOptions['data-options'] : '';
            if (isset($row['icon'])) {
                if (strpos($dataOptions, 'iconCls') === false) {
                    $dataOptions .= (empty($dataOptions) ? '' : ',') . "iconCls:'{$row['icon']}'";
                }
            }
            if (isset($row['selected'])) {
                if (\common\helpers\Utils::boolvalue($row['selected'])) {
                    if (strpos($dataOptions, 'selected:') === false) {
                        $dataOptions .= (empty($dataOptions) ? '' : ',') . "selected:true";
                    }
                }
            }
            if (isset($row['tools'])) {
                $tools = $row['tools'];
                if (strpos($dataOptions, 'tools:') === false) {
                    $toolsConfig = [];
                    if (!isset($tools[0]) && (isset($tools['icon']) || isset($tools['handler']))) {
                        $tools = [$tools];
                    }
                    foreach ($tools as $_idx => $tool) {
                        if (is_string($_idx)) {
                            continue;
                        }
                        $_cfg = [];
                        $_cfg[] = "iconCls:'" . (isset($tool['icon']) ? $tool['icon'] : 'icon-cmy') . "'";
                        $_cfg[] = 'handler:function(){' . (isset($tool['handler']) ? $tool['handler'] : '') . '}';
                        
                        $toolsConfig[] = '{' . implode(',', $_cfg) . '}';
                    }
                    
                    $dataOptions .= (empty($dataOptions) ? '' : ',') . 'tools:[' . implode(',', $toolsConfig) . ']';
                }
            }
            $htmlOptions['data-options'] = $dataOptions;
            $htmlOptions['title'] = $title;
            $htmlOptions['encode'] = false;
            
            if (is_array($content)) {
                $type = '';
                $childHtmlOptions = [];
                if (isset($content['type'])) {
                    $type = $content['type'];
                    unset($content['type']);
                }
                if (isset($content['htmlOptions'])) {
                    $childHtmlOptions = $content['htmlOptions'];
                    unset($content['htmlOptions']);
                }
                if (isset($content['data'])) {
                    $childData = $content['data'];
                    $content = $childData;
                }
                
                if ($type == 'accordion') {
                    $content = self::accordionList($content, $childHtmlOptions);
                }
                elseif ($type == 'tabs') {
                    $tabsArr = [];
                    foreach ($content as $_k => $subContent) {
                        $_tabName = '';
                        if (is_array($subContent)) {
                            if (isset($subContent['title'])) {
                                $_tabName = $subContent['title'];
                            }
                            else {
                                $_tabName = $_k;
                            }
                            if (isset($subContent['data'])) {
                                $subContent = $subContent['data'];
                                if (is_array($subContent)) {
                                    if (isset($subContent['type'])) {
                                        $_subType = $subContent['type'];
                                        $_subHtmlOptions = [];
                                        if (isset($subContent['data'])) {
                                            $subContent = $subContent['data'];
                                        }
                                        if (isset($subContent['htmlOptions'])) {
                                            $_subHtmlOptions = $subContent['htmlOptions'];
                                        }
                                        if ($_subType == 'accordion') {
                                            $subContent = self::accordionList($subContent, $_subHtmlOptions);
                                        }
                                        else {
                                            $subContent = self::treeList($subContent, $_subHtmlOptions);
                                        }
                                    }
                                    else {
                                        $subContent = self::treeList($subContent, $_subHtmlOptions);
                                    }
                                }
                            }
                            else {
                                //$subContent = json_encode($subContent);
                            }
                        }
                        else {
                            $_tabName = $_k;
                        }

                        $tabsArr[] = ['title'=>$_tabName, 'content'=>$subContent];
                    }

                    $arr2 = [];
                    $arr2[] = self::beginTabs(['fit'=>'true', 'border'=>'false', 'style' => "width:100%;height:100%"]);
                    foreach ($tabsArr as $_v) {
                        $arr2[] = self::beginTabsChild($_v['title'], ['style' => "width:100%;height:100%;padding:10px"]);
                        $arr2[] = $_v['content'];
                        $arr2[] = self::endTabsChild();
                    }
                    $arr2[] = self::endTabs();

                    $content = implode("\n", $arr2);
                }
                elseif ($type != '') {
                    $tabsArr = [];
                    foreach ($content as $_k => $subContent) {
                        $_tabName = '';
                        if (is_array($subContent)) {
                            if (isset($subContent['title'])) {
                                $_tabName = $subContent['title'];
                            }
                            else {
                                $_tabName = $_k;
                            }
                            if (isset($subContent['data'])) {
                                $subContent = $subContent['data'];
                            }
                        }
                        else {
                            $_tabName = $_k;
                        }

                        $_arr = [];
                        foreach($subContent as $_k => $_v) {
                            $_arr[$_k] = $_v;
                        }
                        $_arr['name'] = $_tabName;
                        $tabsArr[] = $_arr;
                    }
                    $content = self::treeList($tabsArr, $childHtmlOptions);
                }
                else {
                    $content = self::treeList($content, $childHtmlOptions);
                }
            }
            else {
                $content = strval($content);
            }
            
            $htmlArray[] = BaseHtmlUI::beginTag('div', $htmlOptions);
            $htmlArray[] = $content;
            $htmlArray[] = BaseHtmlUI::endTag('div');
        }
        
        $htmlArray[] = BaseHtmlUI::endTag('div');
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param array $treeArray
     *      1. data formation is:
     *      [
     *          [
     *              'name' => tree-node-display-name,
     *              'icon' => icon-class,           // optional
     *              'htmlOptions' => [],       // optional
     *          ],
     *          html-text,
     *          ...
     *      ];
     * 
     *      2. if the child of array is also array, the child array can have an
     *      event like configured like this comment for _genTreeChildren
     * @param array $htmlOptions
     * @return string html text
     */
    public static function treeList($treeArray, $htmlOptions) {
        if (!isset($htmlOptions['data-options'])) {
            $htmlOptions['data-options'] = "animate:true,lines:true";
        }
        if (isset($htmlOptions['icon'])) {
            $dataOptions = $htmlOptions['data-options'];
            if (strpos($dataOptions, 'iconCls') === false) {
                $dataOptions .= (empty($dataOptions) ? '' : ',') . "iconCls:'{$htmlOptions['icon']}'";
                $htmlOptions['data-options'] = $dataOptions;
            }
            unset($htmlOptions['icon']);
        }
        if (!isset($htmlOptions['style'])) {
            $htmlOptions['style'] = 'color:#0A246A';
        }
        
        $htmlOptions['class'] = "easyui-tree";
        $htmlOptions['encode'] = false;
        
        $htmlArray = [];
        $htmlArray[] = BaseHtmlUI::beginTag('ul', $htmlOptions);
        
        foreach ($treeArray as $k => $row) {
            if (is_string($k)) {
                continue;
            }
            $htmlOptions = [];
            $iconName = "";
            
            if (is_array($row)) {
                if (isset($row['htmlOptions'])) {
                    $htmlOptions = $row['htmlOptions'];
                    unset($row['htmlOptions']);
                }
                if (isset($row['icon'])) {
                    $iconName = $row['icon'];
                    unset($row['icon']);
                }
                
                if (!empty($iconName)) {
                    $htmlOptions['data-options'] = "iconCls:'{$iconName}'";
                }
                
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            else {
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            
            $htmlArray[] = $content;
        }
        
        $htmlArray[] = BaseHtmlUI::endTag('ul');
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param array $treeList
     *      the array format support:
     *      [
     *          'name' => name,
     *          'icon' => icon-class,       // optional
     *          
     *          // 1)
     *          [0] => tree-node-1,
     *          [1] => tree-node-2,
     *          ...
     *          // 2)
     *          'type' => 'navTab' | other  // if type is navTab the event would open the 
     *                                      // configured url in a tab
     *          'url' => open-url,
     *          'target' => target          // open on target element id
     *           // if type is 'navTab':
     *           'tabPanelId' => specify-tab-panel-id,  // optional
     *           'isIframe' => true|false   // optional
     *      ];
     * @param array $htmlOptions
     * @return string html text
     */
    private static function _genTreeChildren($treeList, $htmlOptions = []) {
        $content = '';
        if (is_array($treeList)) {
            $name = '';
            if (isset($treeList['name'])) {
                $name = strval($treeList['name']);
            }
            $isClose = false;
            if (isset($treeList['closed'])) {
                $isClose = is_bool($treeList['closed']) ? $treeList['closed'] : false;
                unset($treeList['closed']);
            }
            
            if (isset($treeList[0])) {
                $htmlArray = [];
                
                if (!empty($name)) {
                    if ($name == strip_tags($name)) {
                        $name = BaseHtmlUI::tag('span', $name);
                    }
                }
                
                if ($isClose) {
                    $htmlArray[] = BaseHtmlUI::beginTag('li', ['data-options' => "state:'closed'", 'encode' => false]);
                }
                else {
                    $htmlArray[] = BaseHtmlUI::beginTag('li');
                }
                $htmlArray[] = $name;
                
                $htmlArray[] = BaseHtmlUI::beginTag('ul', $htmlOptions);
                foreach ($treeList as $k => $row) {
                    if (is_string($k)) {
                        continue;
                    }

                    $htmlOptions = [];
                    if (is_array($row)) {
                        if (isset($row['htmlOptions'])) {
                            $htmlOptions = $row['htmlOptions'];
                            unset($row['htmlOptions']);
                        }
                    }
                    
                    $htmlArray[] = self::_genTreeChildren($row, $htmlOptions);
                }
                
                $htmlArray[] = BaseHtmlUI::endTag('ul');
                $htmlArray[] = BaseHtmlUI::endTag('li');
                $content = implode("\n", $htmlArray);
            }
            else {
                $iconName = isset($treeList['icon']) ? $treeList['icon'] : 'icon-application_view_detail';

                if (!empty($iconName)) {
                    $htmlOptions['data-options'] = "iconCls:'{$iconName}'";
                }
                $htmlOptions['encode'] = false;

                $type = isset($treeList['type']) ? $treeList['type'] : '';
                $url = isset($treeList['url']) ? $treeList['url'] : '';
                $target = isset($treeList['target']) ? $treeList['target'] : '';
                
                if ($type == 'navTab') {
                    $tabPanelId = isset($treeList['tabPanelId']) ? $treeList['tabPanelId'] : '';
                    $isIframe = isset($treeList['isIframe']) ? \common\helpers\Utils::boolvalue($treeList['isIframe']) : false;
                    
                    $funcName = 'easyuiFuncNavTabAddHref';
                    if ($isIframe) {
                        $funcName = 'easyuiFuncNavTabAddIframe';
                    }
                    
                    $btnHtmlOptions = [
                        'style' => 'color:#0A246A', 
                        'href' => 'javascript:void(0);',
                        'onclick' => "{$funcName}('#{$target}', '{$name}', '{$url}', '{$tabPanelId}');",
                        'encode' => false,
                    ];

                    // for debug
                    $_debug = isset(Yii::$app->params['debugHrefContent']) ? Yii::$app->params['debugHrefContent'] : false;
                    if ($_debug) {
                        $btnHtmlOptions['ondblclick'] = "easyuiFuncNavTabAddIframe('#{$target}', '{$name}', '{$url}', '{$tabPanelId}');";
                    }

                    $innerHtml = BaseHtmlUI::tag('a', 
                    $name,
                    $btnHtmlOptions
                    );
                    
                    $content = BaseHtmlUI::tag('li', $innerHtml, $htmlOptions);
                }
                else {
                    if (empty($url)) {
                        $content = BaseHtmlUI::tag('li', $name, $htmlOptions);
                    }
                    else {
                        $content = BaseHtmlUI::tag('li', BaseHtmlUI::tag('a', $name, ['href' => $url, 'target' => $target]), $htmlOptions);
                    }
                }
            }
        }
        else {
            $content = strval($treeList);
            if ($content == strip_tags($content)) {
                $content = BaseHtmlUI::tag('li', $content);
            }
        }
        return $content;
    }
    
    /**
     * 
     * @param string $width
     * @param string $height
     * @param string $title
     * @param string $region
     * @return string html text
     */
    public static function beginLayout($htmlOptions = []) {
        $dataOptions = [];
        $styleOptions = [];
        if (!empty($htmlOptions['width'])) {
            $styleOptions[] = "width:{$htmlOptions['width']}";
            unset($htmlOptions['width']);
        }
        if (!empty($htmlOptions['height'])) {
            $styleOptions[] = "height:{$htmlOptions['height']}";
            unset($htmlOptions['height']);
        }
        if (!empty($styleOptions)) {
            if (isset($htmlOptions['style']) && !empty($htmlOptions['style'])) {
                $htmlOptions['style'] = $htmlOptions['style'] . ';' . implode(';', $styleOptions);
            }
            else {
                $htmlOptions['style'] = implode(';', $styleOptions);
            }
        }
        
        if (isset($htmlOptions['fit'])) {
            $dataOptions[] = "fit:{$htmlOptions['fit']}";
            unset($htmlOptions['fit']);
        }
        
        $htmlOptions['class'] = 'easyui-layout';
        $htmlOptions['encode'] = false;
        $htmlOptions['data-options'] = implode(',', $dataOptions);
        return BaseHtmlUI::beginTag('div', $htmlOptions);
    }
    
    public static function endLayout() {
        return BaseHtmlUI::endTag('div');
    }
    
    /**
     * 
     * @param string $width
     * @param string $height
     * @param string $title
     * @param string $region
     * @return string html text
     */
    public static function beginLayoutRegion($width='', $height='', $title='', $region='', $_htmlOptions = []) {
        $dataOptions = [];
        $styleArray = [];
        if (!empty($width)) {
            $styleArray[] = "width:{$width}";
        }
        if (!empty($height)) {
            $styleArray[] = "height:{$height}";
        }

        if (!empty($title)) {
            $dataOptions[] = "title:'{$title}'";
        }
        if (!empty($region)) {
            $dataOptions[] = "region:'{$region}'";
        }
        else {
            $dataOptions[] = "region:'center'";
        }

        if (isset($_htmlOptions['icon'])) {
            $dataOptions[] = "iconCls:'{$_htmlOptions['icon']}'";
            unset($_htmlOptions['icon']);
        }
        
        if (isset($_htmlOptions['fit'])) {
            $dataOptions[] = "fit:{$_htmlOptions['fit']}";
            unset($_htmlOptions['fit']);
        }
        
        if (isset($_htmlOptions['split'])) {
            $_split = 'false';
            if ($_htmlOptions['split'] == true || $_htmlOptions['split'] == 'true') {
                $_split = 'true';
            }
            $dataOptions[] = "split:{$_split}";
            unset($_htmlOptions['split']);
        }

        if (isset($_htmlOptions['style'])) {
            $_style = $_htmlOptions['style'];
            $styleArray[] = $_style;
        }
        
        if (!empty($styleArray)) {
            $_htmlOptions['style'] = implode(";", $styleArray);
        }

        $_htmlOptions['encode'] = false;
        if (!empty($dataOptions)) {
            $_htmlOptions['data-options'] = implode(',', $dataOptions);
        }
        return BaseHtmlUI::beginTag('div', $_htmlOptions);
    }
    
    public static function endLayoutRegion() {
        return BaseHtmlUI::endTag('div');
    }
    
    public static function beginPanel($title = ' ', $htmlOptions = []) {
        $htmlOptions['class'] = 'easyui-panel';
        $htmlOptions['title'] = $title;
        $dataOptions = ['onLoadError' => "$.custom.easyui.panel.onLoadError"];
        $_keys = ['onLoad', 'onLoadError', 'onOpen', 'onClose', 'onDestroy', 'onExpand','onResize', 'onRestore',
            'method', 'queryParams', 'href', 'fit', 'tools', 'header', 'footer', 
            'collapsed', 'minimized', 'maximized', 'closed', 'collapsible', 'minimizable', 'maximizable', 'closable'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions);
        return BaseHtmlUI::beginTag('div', $htmlOptions);
    }
    
    public static function endPanel() {
        return BaseHtmlUI::endTag('div');
    }
    
    public static function beginTabs($htmlOptions = []) {
        $htmlOptions['class'] = 'easyui-tabs';
        $dataOptions = ['onLoadError' => "$.custom.easyui.panel.onLoadError", 'onSelect'=>"$.custom.easyui.navtab.onSelect"];
        $_keys = ['queryParams', 'href', 'method', 'fit', 'border', 'justified', 'plain', 'selected', 'narrow', 'pill', 'onLoadError', 'onSelect'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions);
        
        $tabs = [];
        if (isset($htmlOptions['tabs'])) {
            $tabs = $htmlOptions['tabs'];
            unset($htmlOptions['tabs']);
        }
        
        $htmlArray = [];
        $htmlArray[] = BaseHtmlUI::beginTag('div', $htmlOptions);

        foreach ($tabs as $_k) {
            $_title = false;
            $_content = false;
            if (is_array($_k)) {
                if (isset($_k['title'])) {
                    $_title = strval($_k['title']);
                    unset($_k['title']);
                }
                elseif (isset($_k['name'])) {
                    $_title = strval($_k['name']);
                    unset($_k['name']);
                }

                if ($_title) {
                    if (isset($_k['content'])) {
                        $_content = strval($_k['content']);
                        unset($_k['content']);
                    }
                    elseif (isset($_k['data'])) {
                        $_content = strval($_k['data']);
                        unset($_k['data']);
                    }
                }
            }
            else {
                $_title = strval($_k);
            }

            if ($_title) {
                $htmlArray[] = self::beginTabsChild($_title, array_merge($_k, ['style' => "width:100%;height:100%;padding:10px"]));
                if ($_content) {
                    $htmlArray[] = $_content;
                }
                else {
                    $htmlArray[] = self::beginLayout();
                    $htmlArray[] = self::endLayout();
                }
                $htmlArray[] = self::endTabsChild();
            }
        }

        return implode("\n", $htmlArray);
    }
    
    public static function endTabs() {
        return BaseHtmlUI::endTag('div');
    }

    public static function beginTabsChild($title = '', $htmlOptions = []) {
        $dataOptions = ['closable'=>'true', 'onLoadError' => "$.custom.easyui.panel.onLoadError"];
        $htmlOptions['title'] = $title;
        $_keys = ['closable', 'content', 'tools', 'fit', 'selected', 'url', 'method', 'onLoadError'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions);
        return BaseHtmlUI::beginTag('div', $htmlOptions);
    }

    public static function endTabsChild() {
        return BaseHtmlUI::endTag('div');
    }

    /**
     * 
     * @param string $width
     * @param string $height
     * @param string $title
     * @param string $region
     * @return string html text
     */
    public static function beginMainPageLayoutRegion($width='', $height='', $title='', $region='', $htmlOptions = []) {
        return self::beginLayoutRegion($width, $height, $title, $region, $htmlOptions);
    }
    
    public static function endMainPageLayoutRegion() {
        return self::endLayoutRegion();
    }
    
    public static function beginMainPageTabs($htmlOptions = []) {
        return self::beginTabs($htmlOptions);
    }
    
    public static function endMainPageTabs() {
        return self::endTabs();
    }

    public static function mainPageHeaderContent($content = []) {
        $htmlArray = [];
        $htmlArray[] = self::tag('a', 'Flag', ['class'=>'main-page-logo', 'href'=>'javascript:void(0);']);
        $htmlArray[] = self::beginTag('ul', ['class'=>'main-page-header']);
        if (is_string($content)) {
            $htmlArray[] = $content;
        }
        else {
            foreach ($content as $item) {
                if (is_string($item)) {
                    $htmlArray[] = self::tag('li', self::tag('a', $item, ['href'=>'javascript:void(0);']));
                }
                else {
                    $text = isset($item['text']) ? $item['text'] : strval($item);
                    $htmlOptions = isset($item['options']) ? $item['options'] : [];
                    $htmlArray[] = self::tag('li', self::tag('a', $text, $htmlOptions));
                }
            }
        }
        $htmlArray[] = self::endTag('ul');
        
        // menubutton part begin
        $htmlArray[] = self::beginTag('div', ['class'=>'main-page-header-options']);
        $menuId = self::ID_PREFIX.'menubutton_'.self::genID();
        $htmlArray[] = self::tag('a', Yii::t('base', 'Options'), ['class'=>'easyui-menubutton', 
            'href'=>'javascript:void(0);', 'encode'=>false,
            'data-options'=>"menu:'#{$menuId}',iconCls:'icon-setup'"]);
        
        $htmlArray[] = self::beginTag('div', ['id'=>$menuId, 'style'=>'width:150px']);
        
        // themes
        $htmlArray[] = self::beginTag('div', ['encode'=>false,'data-options'=>"iconCls:'icon-info'"]);
        $htmlArray[] = self::tag('span', Yii::t('base', 'Change theme'));
        $htmlArray[] = self::beginTag('div');
        $curUiTheme = self::getUiTheme();
        $themeItemHtmlOptions = ['encode'=>false];
        foreach (self::validThemes() as $themeName) {
            $displayName = $themeName;
            if (substr($themeName, 0, 3) == 'ui-') {
                $displayName = substr($themeName, 3);
            }
            $themeItemHtmlOptions['onclick'] = "$.custom.easyui.refreshTheme('{$themeName}')";
            if ($curUiTheme == $themeName) {
                $themeItemHtmlOptions['data-options'] = "iconCls:'icon-ok'";
            }
            else {
                unset($themeItemHtmlOptions['data-options']);
            }
            $htmlArray[] = self::tag('div', $displayName, $themeItemHtmlOptions);
        }
        $htmlArray[] = self::endTag('div');
        $htmlArray[] = self::endTag('div');
        
        $htmlArray[] = self::endTag('div');
        
        $htmlArray[] = self::endTag('div');
        // menubutton part end
        
        return implode("\n", $htmlArray);
    }
    
    public static function validThemes() {
        return [
            'default', 'bootstrap', 'gray', 'black', 
            'ui-cupertino', 'ui-dark-hive', 'ui-pepper-grinder', 'ui-sunny', 
            'metro', 'metro-blue', 'metro-gray', 'metro-green', 'metro-orange', 'metro-red'
        ];
    }
    
    public static function getUiTheme() {
        $uiTheme = "default";
        $cookies = \Yii::$app->request->cookies;
        $myTheme = $cookies->get('easyui_theme', $uiTheme);
        if (!empty($myTheme) && in_array($myTheme, self::validThemes())) {
            $uiTheme = $myTheme;
        }
        return $uiTheme;
    }

    public static function mainPageLayout($headerPart = '', $westPart = '', $containerPart = '', $footerPart = '', $eastPart = '') {
        $htmlArray = [];
        
        if (!empty($containerPart)) {
            $customScriptText = <<<EOD
var loadingDelayTimer;
function navTabShowLoading(){
    if ($("#loading")) {
        $("#loading").fadeOut("normal", function(){
            $(this).remove();
            loadingDelayTimer = undefined;
        });
    }
}

$.parser.onComplete = function(){
    if($.custom.easyui.loadingDelayTimer) {
        clearTimeout(loadingDelayTimer);
    }
    loadingDelayTimer = setTimeout(navTabShowLoading,500);
}
EOD;
            $htmlArray[] = Html::script($customScriptText);
        }

        function convertContentPart(&$content, &$result, $defaultWidth = '', $defaultHeight = '', $defaultTitle = '', $defaultHtmlOptions = []) {
            $result['title'] = $defaultTitle;
            $result['width'] = $defaultWidth;
            $result['height'] = $defaultHeight;
            $result['options'] = $defaultHtmlOptions;
            if (is_array($content)) {
                foreach (['title', 'width', 'height', 'options'] as $k) {
                    if (isset($content[$k])) {
                        $result[$k] = $content[$k];
                    }
                }
                if (isset($content['content'])) {
                    $content = $content['content'];
                }
                else {
                    $content = strval($content);
                }
            }
        }

        $regionInfo = [];
        if (!empty($headerPart)) {
            convertContentPart($headerPart, $regionInfo, '', '', '', ['style' => "background-color:#E8E8FF"]);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'north', $regionInfo['options']);
            $htmlArray[] = $headerPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($footerPart)) {
            convertContentPart($footerPart, $regionInfo, '', '', '', ['style' => "height:21px;text-align:center;vertical-align:center;padding:0 5px;overflow-y:hidden"]);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'south', $regionInfo['options']);
            $htmlArray[] = $footerPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($eastPart)) {
            convertContentPart($eastPart, $regionInfo, '200px', '', '', ['split'=>true, 'icon'=>'icon-ok']);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'east', $regionInfo['options']);
            $htmlArray[] = $eastPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($westPart)) {
            convertContentPart($westPart, $regionInfo, '200px', '', '', ['split'=>true, 'icon'=>'icon-ok']);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'west', $regionInfo['options']);
            $htmlArray[] = $westPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($containerPart)) {
            convertContentPart($containerPart, $regionInfo, '100%', '100%', '', []);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'center', $regionInfo['options']);
            $htmlArray[] = $containerPart;
            $htmlArray[] = self::tag('div', '', ['id'=>'loading', 'style'=>"position: absolute; z-index: 1000; top: 0px; left: 0px; width: 100%; height: 100%; background: #E8E8FF; text-align: center; padding-top: 20%;"]);
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        return implode("\n", $htmlArray);
    }
    
    /**
     * returns the html head part
     * @param string $title
     * @param boolean $closeHead default true, if close the html head tag
     * @return string html text
     */
    public static function headPart($title, $closeHead = true) {
        $lanLocale = Yii::$app->params['lan_locale'];
        $basePath = self::ASSETS_BASE_PATH;
        $easyuiFoler = self::ASSETS_EASYUI_FOLDER;
        $uiTheme = self::getUiTheme();
        $html = <<<EOD
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$title}</title>
    <link rel="stylesheet" type="text/css" href="{$basePath}/assets/{$easyuiFoler}/themes/{$uiTheme}/easyui.css">
    <link rel="stylesheet" type="text/css" href="{$basePath}/assets/{$easyuiFoler}/themes/icon.css">
    <link rel="stylesheet" type="text/css" href="{$basePath}/assets/css/easyui.custom.css">
    <link rel="stylesheet" type="text/css" href="{$basePath}/assets/css/icons.extension.css">

    <script type="text/javascript" src="{$basePath}/assets/{$easyuiFoler}/jquery.min.js"></script>
    <script type="text/javascript" src="{$basePath}/assets/{$easyuiFoler}/jquery.easyui.min.js"></script>
    <script type="text/javascript" src="{$basePath}/assets/{$easyuiFoler}/locale/easyui-lang-{$lanLocale}.js"></script>

    <script type="text/javascript" src="{$basePath}/assets/custom/js/common.custom.js"></script>
    <script type="text/javascript" src="{$basePath}/assets/custom/js/utils.custom.js"></script>
    <script type="text/javascript" src="{$basePath}/assets/custom/js/easyui.custom.js"></script>
    <script type="text/javascript" src="{$basePath}/assets/custom/js/locale/custom.messages-{$lanLocale}.js"></script>
EOD;
        $arrScripts = [];
        $arrScripts[] = "$(function(){ $.custom.uiframework = 'easyui';});";
        
        if (isset(Yii::$app->params['tabs_in_iframe'])) {
            if (Yii::$app->params['tabs_in_iframe']) {
                $arrScripts[] = "if ($.custom.easyui.config) { $.custom.easyui.config.openTabInIframe = true; }";
            }
        }

        $html .= "\n".Html::script(implode("\n", $arrScripts));
    
        if ($closeHead) {
            $html .= "\n</head>";
        }

        return $html;
    }

    public static function openBody($htmlOptions = []) {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'easyui-layout';
        }
        return BaseHtmlUI::beginTag('body', $htmlOptions);
    }
    
    public static function mergeHtmlOptionsToDataOptions($mergeKeys, &$htmlOptions, &$dataOptions, $conversion = []) {
        foreach ($mergeKeys as $_k) {
            if (isset($htmlOptions[$_k])) {
                if ($htmlOptions[$_k] !== '') {
                    if (isset($conversion[$_k])) {
                        $dataOptions[$conversion[$_k]] = $htmlOptions[$_k];
                    }
                    else {
                        $dataOptions[$_k] = $htmlOptions[$_k];
                    }
                }
                unset($htmlOptions[$_k]);
            }
        }
        
        $dataOptionsStr = '';
        if (isset($htmlOptions['data-options'])) {
            $_optionsStr = $htmlOptions['data-options'];
            if (is_string($_optionsStr)) {
                $dataOptionsStr = $_optionsStr;
            }
            else {
                foreach ($_optionsStr as $_k => $_v) {
                    $dataOptions[$_k] = $_v;
                }
            }
        }
        $tmpArr = [];
        foreach ($dataOptions as $_k => $_v) {
            $tmpArr[] = "{$_k}:{$_v}";
        }
        if (!empty($tmpArr)) {
            if (!empty($dataOptionsStr)) {
                $dataOptionsStr .= ',';
            }
            $dataOptionsStr .= implode(',', $tmpArr);
        }
        if (!empty($dataOptionsStr)) {
            $htmlOptions['data-options'] = $dataOptionsStr;
        }
    }
    
    /**
     * 
     * @param type $dataOrUrl if this field is array consider as data, or if string, consider as a url.
     * @param type $htmlOptions
     * @param type $selection
     * @return type
     */
    public static function comboTree($dataOrUrl, $htmlOptions = [], $selection = false) {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'easyui-combotree';
        }
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        
        $dataOptions = [];
        
        $_ID = isset($htmlOptions['id']) ? $htmlOptions['id'] : '';
        if ($selection !== false) {
            if (empty($_ID)) {
                $_ID = self::ID_PREFIX . 'combotree_' . self::genID();
                $htmlOptions['id'] = $_ID;
            }
            if (is_array($selection)) {
                $_sels = [];
                foreach ($selection as $_v) {
                    $_v = strval($_v);
                    if (preg_match('/^[0-9]*$/', $_v)) {
                        $_sels[] = intval($_v);
                    }
                    else {
                        $_sels[] = $_v;
                    }
                }
                $selection = json_encode($_sels);
                $dataOptions['onLoadSuccess'] = "function(){ $('#{$_ID}').combotree('setValues', {$selection}); }";
            }
            else {
                $selection = strval($selection);
                if (!preg_match('/^[0-9]*$/', $selection)) {
                    $selection = "'{$selection}'";
                }
                $dataOptions['onLoadSuccess'] = "function(){ $('#{$_ID}').combotree('setValue', {$selection}); }";
            }
        }
        
        $varDataName = '';
        if (is_string($dataOrUrl)) {
            $dataOptions['url'] = "'{$dataOrUrl}'";
            $dataOptions['method'] = "'get'";
        }
        else {
            $varDataName = 'combotree_data_'.self::genID();
            $dataOptions['data'] = $varDataName;
        }
        
        $_keys = ['onSelect', 'onCheck', 'onChange', 'onLoadSuccess'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions);
        
        $htmlArray = [];
        $htmlArray[] = BaseHtmlUI::tag('select', '', $htmlOptions);
        
        if (!empty($varDataName)) {
            $htmlArray[] = Html::script("var {$varDataName} = ".  json_encode($dataOrUrl));
        }
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param type $dataArray
     * @param type $title
     * @param type $htmlOptions
     * @param type $selectionIndex
     * @return type
     */
    public static function dataList($dataArray, $title = '', $htmlOptions = [], $selectionIndex = false) {
        $htmlArray = [];
        if (!empty($title)) {
            $htmlArray[] = self::beginPanel($title);
        }
        
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'easyui-datalist';
        }
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        
        $dataOptions = [];
        
        $_ID = isset($htmlOptions['id']) ? $htmlOptions['id'] : '';
        if ($selectionIndex !== false) {
            if (empty($_ID)) {
                $_ID = self::ID_PREFIX . 'datalist_' . self::genID();
                $htmlOptions['id'] = $_ID;
            }
            if (is_array($selectionIndex)) {
                $_sels = [];
                foreach ($selectionIndex as $_v) {
                    $_v = strval($_v);
                    if (preg_match('^[0-9]*$', $_v)) {
                        $_sels[] = "$('#{$_ID}').datalist('checkRow', {$_v});";
                    }
                }
                $selection = implode("\n", $_sels);
                $dataOptions['onLoadSuccess'] = "function(){ {$selection} }";
            }
            else {
                $selectionIndex = intval($selectionIndex);
                $dataOptions['onLoadSuccess'] = "function(){ $('#{$_ID}').datalist('checkRow', {$selectionIndex}); }";
            }
        }
        
        $content = false;
        if (is_string($dataArray)) {
            $dataOptions['url'] = "'{$dataOrUrl}'";
            $dataOptions['method'] = "'get'";
        }
        else {
            $_contentArray = [];
            
            $flag = false;
            if (array_keys($dataArray) === range(0, count($dataArray) - 1)) {
                foreach ($dataArray as $_k => $_v) {
                    if (is_array($_v)) {
                        $flag = true;
                        break;
                    }
                }
            }
            if ($flag) {
                foreach ($dataArray as $cfg) {
                    $_htmlOpt = [];
                    $_name = '';
                    if (is_array($cfg)) {
                        $_name = isset($cfg['name']) ? $cfg['name'] : '';
                        //unset($cfg['name']);
                        foreach ($cfg as $__k => $__v) {
                            $_htmlOpt[$__k] = $__v;
                        }
                    }
                    else {
                        $_name = strval($cfg);
                        $_htmlOpt['value'] = $_name;
                    }
                    $_contentArray[] = BaseHtmlUI::tag('li', $_name, $_htmlOpt);
                }
            }
            else {
                foreach ($dataArray as $_k => $_v) {
                    $_contentArray[] = BaseHtmlUI::tag('li', $_v, ['value' => $_k]);
                }
            }
            
            $content = "\n" . implode("\n", $_contentArray) . "\n";
        }
        
        $_keys = ['onSelect', 'onCheck', 'onLoadSuccess'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions);
        
        $htmlArray[] = BaseHtmlUI::tag('ul', $content, $htmlOptions);
        
        if (!empty($title)) {
            $htmlArray[] = self::endPanel();
        }
        return implode("\n", $htmlArray);
    }
    
    /**
     * 
     * @param type $btnText
     * @param type $htmlOptions
     */
    public static function linkButton($btnText, $htmlOptions = []) {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'easyui-linkbutton';
        }
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        if (!isset($htmlOptions['href'])) {
            $htmlOptions['href'] = 'javascript:void(0);';
        }
        
        $dataOptions = [];
        
        $_cvt = ['icon' => 'iconCls'];
        $_keys = ['icon', 'onClick', 'selected', 'disabled', 'toggle'];
        self::mergeHtmlOptionsToDataOptions($_keys, $htmlOptions, $dataOptions, $_cvt);
        
        return BaseHtmlUI::tag('a', $btnText, $htmlOptions);
    }
    
}