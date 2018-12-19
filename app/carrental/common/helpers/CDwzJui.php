<?php

namespace common\helpers;

use Yii;
use yii\helpers\Html;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CDwzJui extends \common\helpers\BaseHtmlUI
{
	const ID_PREFIX='yt_dwz_';


    /**
     * generate one of datagrid column config with properties
     * @param string $fieldName name for field mapped to datagrid data column key
     * @param string $columnName string that displaied in table header column
     * @param integer $width culumn display width
     * @param array $otherOptions other column data-options like ['checkbox'=>'true']
     * @param array|string $editor if the datagrid allows editing, this field specifies the field editor type and operation values.
     * @param function $formatter if the editor data need convert display value, define the function(value, row) {}
     * @return array the column config array for format datagrid tag
     */
    public static function formatDatagridColumnConfig($fieldName, $columnName,
            $width = null, $otherOptions = null,
            $editor = null, $formatter = null)
    {
        static $_nonStringKey = ['required' => 1, 'multiple' => 1, 'columns' => 1];
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

        if ($formatter) {
            $arrOptions['formatter'] = "{$formatter}";
        }

        $arrColumn['options'] = $arrOptions;

        return $arrColumn;
    }

    private static function _convertDatagridToolFunctionConfigToOptions($datagridId, $cfg, $title, &$hasDialog, &$hasWindow, $defaultFuncName = 'undefined') {
        $options = [];
        if (is_array($cfg)) {
            $needSelect = 'false';
            $needReload = 'false';
            if (isset($cfg['needSelect'])) {
                if ($cfg['needSelect'] == true || $cfg['neesSelect'] == 'true') {
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
                $options['href'] = $url;
                $options['target'] = 'dialog';
                $hasDialog = true;
            }
            elseif (isset($cfg['window'])) {
                $url = $cfg['window'];
                $options['href'] = $url;
                $options['target'] = 'window';
                $hasWindow = true;
            }
            elseif (isset ($cfg['tab'])) {
                $url = $cfg['tab'];
                $options['href'] = $url;
                $options['target'] = 'navTab';
            }
            elseif (isset ($cfg['_blank'])) {
                $url = $cfg['_blank'];
                $options['href'] = $url;
                $options['target'] = '_blank';
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
                $options['href'] = $url;
                $options['target'] = 'ajaxTodo';
                if (!empty($prompt)) {
                    $options['title'] = $prompt;
                }
            }
            else {
                $options['href'] = 'javascript:void(0);';
            }
        }
        return $options;
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
            if (empty($iconName)) { $iconName = 'add'; }
        }
        elseif ($type & self::DG_TOOL_REMOVE) {
            if (empty($name)) { $name = Yii::t('locale', 'Delete'); }
            if (empty($iconName)) { $iconName = 'delete'; }
        }
        elseif ($type & self::DG_TOOL_EDIT) {
            if (empty($name)) { $name = Yii::t('locale', 'Edit'); }
            if (empty($iconName)) { $iconName = 'edit'; }
        }
        elseif ($type & self::DG_TOOL_ACCEPT) {
            if (empty($name)) { $name = Yii::t('locale', 'Save'); }
            if (empty($iconName)) { $iconName = 'icon'; }
        }
        elseif ($type & self::DG_TOOL_REJECT) {
            if (empty($name)) { $name = Yii::t('locale', 'Cancel'); }
            if (empty($iconName)) { $iconName = 'icon'; }
        }
        elseif ($type & self::DG_TOOL_GETCHANGES) {
            if (empty($name)) { $name = Yii::t('locale', 'GetChanges'); }
            if (empty($iconName)) { $iconName = 'icon'; }
        }
        elseif ($type & self::DG_TOOL_SINGLEMULTIPLE) {
            return null;
        }
        elseif ($type & self::DG_TOOL_MENU) {
            if (empty($name)) { $name = '  '; }
            if (empty($iconName)) { $iconName = 'icon'; }
            /*
            self::$_count ++;
            $menuId = $datagridId . '_menu_' . self::$_count;
            $endFix = '';
            if (is_array($func)) {
                $menuHtmlArray = [];
                $menuHtmlArray[] = Html::beginTag('div', ['id' => $menuId, 'style' => 'width:160px;']);
                $menuButtonArray = self::_genDatagridMenuButton($datagridId, $func, $hasDialog, $hasWindow);
                $menuHtmlArray[] = implode("\n", $menuButtonArray);
                $menuHtmlArray[] = Html::endTag('div');
                $endFix = "\n".implode("\n", $menuHtmlArray);
            }
            else {
                $endFix = "\n".strval($func);
            }
            
            return Html::tag('a', $name, ['href'=>'javascript:void(0)',
                    'class' => 'easyui-menubutton',
                    'encode' => false,
                    'data-options' => "iconCls:'{$iconName}',menu:'#{$menuId}'",
                ]
            ) . $endFix;
            */
            return null;
        }
        else {
            if (empty($func)) {
                return null;
            }
            elseif (is_array($func)) {
                if (isset($func['_blank'])) {
                    $url = $func['_blank'];
                    $htmlOptions = ['href'=>$url,
                        'encode' => false,
                        'target' => '_blank'
                    ];
                    if (isset($func['icon'])) {
                        $iconName = $func['icon'];
                    }
                    if (isset($func['onclick'])) {

                    }
                    if (!empty($iconName)) {
                        $htmlOptions['class'] = $iconName;
                    }
                    $html = Html::tag('a', Html::tag('span', $name), $htmlOptions);
                    return $html;
                }
            }
        }

        $htmlOptions = [];
        if (is_array($func)) {
            $htmlOptions = self::_convertDatagridToolFunctionConfigToOptions($datagridId, $func, $name, $hasDialog, $hasWindow);
        }
        else {
            $func = strval($func);
            if (strpos($func, 'function') === false && strpos($func, '(') === false) {
                $htmlOptions['href'] = 'javascript:void(0)';
                $htmlOptions['onclick'] = $func;
            }
            else {
                $htmlOptions['href'] = $func;
            }
        }

        if (!empty($iconName)) {
            $htmlOptions['class'] = $iconName;
        }
        $html = Html::tag('a', Html::tag('span', $name), $htmlOptions);
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
    public static function genDatagridSearchAreaTool($datagridId, $dgParamVarName, $dgSearchOnChange, $defaultTargetType, $type, $name, $prompt, $param, $htmlOptions, $selected = '') {
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
                $searchOnChangeExec = "setTimeout(function(){{$dgSearchOnChange}}, 100);";
            }
            unset($htmlOptions['searchOnChange']);
        }

        $prefixPrompt = $prompt;
        $tagName = '';
        $content = false;
        if ($type & self::DG_TOOL_SEARCH_TEXTBOX) {
            $tagName = 'input';
            if (!empty($param)) {
                $htmlOptions['value'] = $param;
            }
        }
        elseif ($type & self::DG_TOOL_SEARCH_COMBOBOX) {
            $dataOptionsArray = [];

            $hasMultiple = false;
            $hasEvents = false;
            $hasDataoptions = false;
            if (isset($htmlOptions['data-options'])) {
                $hasDataoptions = true;
                $val = $htmlOptions['data-options'];
                $dataOptionsArray[] = $val;
                $pos = strpos($val, 'multiple');
                if ($pos !== false) {
                    $pos2 = strpos($val, ',', $pos);
                    if ($pos2 === false) {
                        $pos2 = null;
                    }
                    else {
                        $pos2 = $pos2 - $pos;
                    }
                    $v2 = substr($val, $pos, $pos2);
                    if (strpos($v2, 'true')) {
                        $hasMultiple = true;
                    }
                }

                if (strpos($val, 'onSelect')) {
                    $hasEvents = true;
                }

                unset($htmlOptions['data-options']);
            }
            else {
                $booleanKeys = ['multiple', 'multiline'];
                foreach ($booleanKeys as $k) {
                    if (isset($htmlOptions[$k])) {
                        if ($k == 'multiple') {
                            $hasMultiple = true;
                        }
                        $val = $htmlOptions[$k];
                        if (is_string($val) && stripos($val, 'true') !== false) {
                            $dataOptionsArray[] = "{$k}:true";
                        }
                        elseif (is_bool($val) && $val) {
                            $dataOptionsArray[] = "{$k}:true";
                        }
                        unset($htmlOptions[$k]);
                    }
                }

                $dataOptionsArray[] = "prompt:'{$prompt}'";
            }
            
            if ($hasMultiple) {
                $htmlOptions['multiple'] = 'multiple';
            }

            if (isset($htmlOptions['reloadUrlOnSelect'])) {
                $reloadUrlOnSelect = $htmlOptions['reloadUrlOnSelect'];
                unset($htmlOptions['reloadUrlOnSelect']);

                if (empty($dgParamVarName)) {
                    $reloadFunc = "dwzPageBreak({targetType:'{$defaultTargetType}'})";
                }
                else {
                    $reloadFunc = "$.custom.dwz.table.reloadWithUrl('{$datagridId}', '{$reloadUrlOnSelect}', '{$defaultTargetType}', '{$dgParamVarName}')";
                }
                $htmlOptions['onchange'] = $reloadFunc;
            }

            //$htmlOptions['class'] = 'combox';
            $comboEvents = '';

            if (is_array($param)) {
                $tagName = 'select';
                if (!isset($param[''])) {
                    $param[''] = '';
                }
                return Html::label($prefixPrompt, false). Html::dropDownList($name, ((!isset($selected) || empty($selected)) ? '' : $selected), $param, $htmlOptions);
            }
            elseif (is_string($param)) {    // consider as url for get combobox option list
                return null;
            }
        }
        elseif ($type & self::DG_TOOL_SEARCH_RADIO) {
            if (!isset($htmlOptions['onchange'])) {
                $htmlOptions['onchange'] = "{$searchOnChangeExec}";
            }
            $htmlOptions['separator'] = '';
            $htmlOptions['container'] = '';
            $htmlOptions['template'] = '{beginLabel}{input}{labelTitle}{endLabel}';

            if (!empty($prompt)) {
                $prefixPrompt = "{$prompt}:";
            }
            return Html::label($prefixPrompt, false). Html::radioList($name, (empty($selected) ? '' : $selected), $param, $htmlOptions);
        }
        elseif ($type & self::DG_TOOL_SEARCH_CHECKBOX) {
            if (!isset($htmlOptions['onchange'])) {
                $htmlOptions['onchange'] = "{$searchOnChangeExec}";
            }
            $htmlOptions['separator'] = '';
            $htmlOptions['container'] = '';
            $htmlOptions['template'] = '{beginLabel}{input}{labelTitle}{endLabel}';
            if (!empty($prompt)) {
                $prefixPrompt = "{$prompt}:";
            }
            $content = Html::label($prefixPrompt, false). Html::checkBoxList($name, (empty($selected) ? '' : $selected), $param, $htmlOptions);
            return $content;
        }
        elseif ($type & self::DG_TOOL_SEARCH_TEXTFIELD) {
            $tagName = 'input';
            if (!empty($param)) {
                $htmlOptions['value'] = $param;
            }
        }
        elseif ($type & self::DG_TOOL_SEARCH_DATEBOX) {
            $tagName = 'input';
            $htmlOptions['class'] = 'date';
            $htmlOptions['name'] = $name;
            $htmlOptions['dateFmt'] = 'yyyy-MM-dd';

            if (!empty($prompt)) {
                $prefixPrompt = "{$prompt}:";
            }

            if (!empty($param)) {
                $htmlOptions['value'] = $param;
            }

            return Html::label($prefixPrompt, false). Html::tag($tagName, '', $htmlOptions);
        }
        elseif ($type & self::DG_TOOL_SEARCH_DATETIMEBOX) {
            $tagName = 'input';
            $htmlOptions['class'] = 'date';
            $htmlOptions['name'] = $name;
            $htmlOptions['dateFmt'] = 'yyyy-MM-dd HH:mm:ss';

            if (!empty($prompt)) {
                $prefixPrompt = "{$prompt}:";
            }

            if (!empty($param)) {
                $htmlOptions['value'] = $param;
            }

            return Html::label($prefixPrompt, false). Html::tag($tagName, '', $htmlOptions);
        }
        elseif ($type & self::DG_TOOL_SEARCH_BUTTON) {
            return null;
        }

        if (!isset($htmlOptions['name'])) {
            $htmlOptions['name'] = $name;
        }
        if (!empty($tagName)) {
            return Html::label($prefixPrompt, false). Html::tag($tagName, $content, $htmlOptions);
        }
        return null;
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
            $columnWidth = null;
            $columnDataOptions = null;
            $columnEditor = null;
            $columnFormatter = null;
            if (isset($columnCustomTypesArray[$fieldName])) {
                $columnCustomType = $columnCustomTypesArray[$fieldName];
                if (isset($columnCustomType['data-options'])) {
                    $columnDataOptions = $columnCustomType['data-options'];
                    unset($columnCustomType['data-options']);
                }
                if (isset($columnCustomType['width'])) {
                    $columnWidth = $columnCustomType['width'];
                    unset($columnCustomType['width']);
                }
                if (isset($columnCustomType['editor'])) {
                    $columnEditor = $columnCustomType['editor'];
                    unset($columnCustomType['width']);
                }
                if (isset($columnCustomType['formatter'])) {
                    $columnFormatter = $columnCustomType['formatter'];
                    unset($columnCustomType['width']);
                }
                if (isset($columnCustomType['key'])) {
                    if (empty($idField) && $columnCustomType['key'] === true) {
                        $idField = $fieldName;
                    }
                    unset($columnCustomType['key']);
                }
                
                if ($columnDataOptions === null) {
                    $columnDataOptions = [];
                }
                foreach ($columnCustomType as $k => $v) {
                    if (!isset($columnDataOptions[$k])) {
                        $columnDataOptions[$k] = $v;
                    }
                }
            }
            
            $columnsConfigArray[] = self::formatDatagridColumnConfig($fieldName, $columnName, $columnWidth, $columnDataOptions, $columnEditor, $columnFormatter);
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
        
        return self::datagrid2($title, $columnsConfigArray, $dataArray, $width, $height, $dataOptions, $htmlOptions, $toolbarArray, $frozenColumnIndex, $frozenRowIndex);
    }

    /**
     *
     * @param string $title
     * @param array $columns
     * @param array $dataArray data array
     * @param string $width
     * @param string $height
     * @param array $datagridDataOptions data-options for datagrid
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
        // generate datagrid id
        $currentID = self::genID();
        $dgID = self::ID_PREFIX . 'dg_' . $currentID;
        self::$lastTagID = $dgID;
        if ($htmlOptions && isset($htmlOptions['id'])) {
            $dgID = $htmlOptions['id'];
            unset($htmlOptions['id']);
        }
        else {
            if (!is_array($htmlOptions)) { $htmlOptions = []; }
            //$htmlOptions['id'] = $dgID;
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

        $isDefaultLayoutH = true;
        $layoutH = 75;
        if (!isset($htmlOptions['layoutH'])) {
            $htmlOptions['layoutH'] = $layoutH;
        }
        else {
            $isDefaultLayoutH = false;
            $layoutH = intval($htmlOptions['layoutH']);
        }
        
        $arrScripts = [];

        $url = '';
        $method = '';
        if (isset($datagridDataOptions['url'])) {
            $url = $datagridDataOptions['url'];
            if (isset($datagridDataOptions['method'])) {
                $method = $datagridDataOptions['method'];
            }
            if (empty($method)) {
                $method = 'get';
            }
        }

        $numPerPage = 20;
        $pageNumShown = 10;

        $showPagination = true;
        $showHeader = true;
        if (isset($htmlOptions['data-options'])) {
            $_tmpDataOptions = $htmlOptions['data-options'];
            if (is_array($_tmpDataOptions)) {
                if (isset($_tmpDataOptions['pagination'])) {
                    if (!$_tmpDataOptions['pagination'] || !\common\helpers\Utils::boolvalue($_tmpDataOptions['pagination'])) {
                        $showPagination = false;
                    }
                }
                if (isset($_tmpDataOptions['showHeader'])) {
                    if (!$_tmpDataOptions['showHeader'] || !\common\helpers\Utils::boolvalue($_tmpDataOptions['showHeader'])) {
                        $showHeader = false;
                    }
                }
                if (isset($_tmpDataOptions['pageSize'])) {
                    if (!empty($_tmpDataOptions['pageSize'])) {
                        $numPerPage = intval($_tmpDataOptions['pageSize']);
                        if ($numPerPage == 0) {
                            $numPerPage = 20;
                        }
                    }
                }
            }
        }
        
        // pagination info
        $defaultTargetType = (isset($htmlOptions['targetType']) ? $htmlOptions['targetType'] : 'navTab');
        $paginationOptions = ['class'=>'pagination', 'targetType'=>$defaultTargetType, 
            'totalCount'=>'100', 'numPerPage'=>$numPerPage, 'pageNumShown'=>$pageNumShown, 'currentPage'=>'1'];
        $paginationOnChange = "dwzPageBreak({targetType:'{$paginationOptions['targetType']}',numPerPage:this.value})";
        $dgSearchOnChange = "dwzPageBreak({targetType:'{$paginationOptions['targetType']}'})";
        $dgParamVarName = '';
        if (!empty($url)) {
            $dgParamVarName = $dgID.'_search_param';
            $paginationOptions['targetType'] = "ajaxCustom";
            $paginationOptions['rel'] = "{url:'{$url}',method:'{$method}',tableId:'{$dgID}',numPerPage:{$numPerPage},pageNumShown:{$pageNumShown},searchParamName:'{$dgParamVarName}'}";
            $paginationOptsStr = "targetType:'{$paginationOptions['targetType']}',rel:{$paginationOptions['rel']}";
            $paginationOnChange = "dwzPageBreak({{$paginationOptsStr},numPerPage:this.value})";
            $dgSearchOnChange = "$.custom.dwz.table.doSearch('{$dgID}', '{$url}', {{$paginationOptsStr}});";
            $arrScripts[] = <<<EOD
var {$dgParamVarName} = {cacheData:{currentPage:1, numPerPage:${numPerPage}, pageNumShown:${pageNumShown}}, params:{}};
$(function(){
    $.custom.dwz.table.asynLoadData('{$dgID}', '{$url}', '{$method}', {{$paginationOptsStr},numPerPage:{$numPerPage},pageNumShown:{$pageNumShown}});
});
EOD;
        }

        $hasDialog = false;
        $hasWindow = false;
        
        // generate datagrid tools
        $toolbarHtmlArray = [];
        $toolbarSearchAreaHtmlArray = [];
        $toolbarSearchButtonName = false;
        if (is_array($toolbarArray)) {
            foreach ($toolbarArray as $cfg) {
                $toolType = $cfg[0];
                if ($toolType & self::DG_TOOL_SEARCH_TYPE_FLAG) {
                    if ($toolType & self::DG_TOOL_SEARCH_BUTTON) {
                        $toolbarSearchButtonName = $cfg[1];
                    }
                    else {
                        $toolHtml = self::genDatagridSearchAreaTool($dgID, $dgParamVarName, $dgSearchOnChange, $defaultTargetType, $toolType, $cfg[1], $cfg[2], $cfg[3], $cfg[4], $cfg[5]);
                        if ($toolHtml) {
                            $toolbarSearchAreaHtmlArray[] = $toolHtml;
                        }
                    }
                }
                else {
                    $toolHtml = self::genDatagridTool($dgID, $toolType, $cfg[1], $cfg[2], $cfg[3], $hasDialog, $hasWindow);
                    if ($toolHtml) {
                        $toolbarHtmlArray[] = $toolHtml;
                    }
                }
            }
        }

        // if specified frozen row
        if ($frozenRowIndex > 0 && $frozenRowIndex <= 10) {
        }
        
        // columns
        $arrColumns = [];
        $arrColumnFields = [];
        $arrDetailedColumns = [];
        $arrDetailedFields = [];
        $sortColumnName = false;
        
        $colOptionsKeys = ['width', 'align', 'field', 'formatter'];
        foreach ($columns as $col) {
            $colOptions = $col['options'];
            
            // if the column displays buttons
            if (isset($colOptions['buttons'])) {
                $scriptFuncName = '';
                $scriptContent = self::scriptDatagridCellButtons($dgID, $colOptions['buttons'], $scriptFuncName, $hasDialog, $hasWindow);
                if (!empty($scriptFuncName) && (!isset($colOptions['formatter']) || empty($colOptions['formatter']))) {
                    $arrScripts[] = $scriptContent;
                    unset($colOptions['buttons']);
                    $colOptions['formatter'] = "function(value,row){ return {$scriptFuncName}(value,row); }";
                }
            }
            
            $_columnOptions = [];
            foreach ($colOptionsKeys as $_k) {
                if (isset($colOptions[$_k])) {
                    $_columnOptions[$_k] = $colOptions[$_k];
                }
            }
            if (!isset($_columnOptions['width'])) {
                $_columnOptions['width'] = 20;
            }
            if (isset($colOptions['sortable'])) {
                if (\common\helpers\Utils::boolvalue($colOptions['sortable']) && isset($colOptions['field']) && $colOptions['field']) {
                    $_columnOptions['orderfield'] = $colOptions['field'];
                }
            }

            if ($sortColumnName === false && isset($colOptions['sortable'])) {
                if (\common\helpers\Utils::boolvalue($colOptions['sortable']) && isset($colOptions['field']) && $colOptions['field']) {
                    $sortColumnName = $colOptions['field'];
                    if (!isset($_columnOptions['class'])) {
                        $_columnOptions['class'] = 'asc';
                    }
                }
            }

            $_columnCfg = [
                'name' => $col['name'],
                'options' => $_columnOptions,
            ];
            $_fieldName = isset($colOptions['field']) ? $colOptions['field'] : '';
            
            if (isset($colOptions['detailed']) && $colOptions['detailed']) {
                //$arrDetailedColumns[] = $_columnCfg;
                //$arrDetailedFields[] = $_fieldName;
                // TODO
                $arrColumns[] = $_columnCfg;
                $arrColumnFields[] = $_fieldName;
            }
            else {
                $arrColumns[] = $_columnCfg;
                $arrColumnFields[] = $_fieldName;
            }
            
        }
        
        if (!empty($arrDetailedColumns)) {
            $detailUrl = '';
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
        }
        
        // table html attribute options
        $tblOptions = [
            //'title' => $title,
            'class' => 'table',
            'width' => '100%',
            //'data-options' => self::_renderDataOptions($tblDefaultDataOptions, $datagridDataOptions),
            'encode' => false,  // do not convert html charactors
        ];
        foreach ($htmlOptions as $k => $v) {
            $tblOptions[$k] = $v;
        }

        $htmlArray = [];
        
        if (!empty($arrDetailedColumns)) {
            //$htmlArray[] = Html::tag('script','', ['type' => 'text/javascript', 'src' => self::ASSETS_BASE_PATH . '/assets/'.self::ASSETS_EASYUI_FOLDER.'/extension/jquery.easyui.datagrid-detailview.js']);
        }
        
        // if spesified table size, wrap up a div to make sure datagrid display correctily
        $tableParentOptions = ['id'=>$dgID, 'class'=>'pageContent', 'encode'=>false];
        if (!empty($tableParentSize)) {
            $tableParentOptions['style'] = implode(';', $tableParentSize);
        }

        $htmlArray[] = Html::beginTag('div', $tableParentOptions);

        // table title
        $titleBarHtmlArray = [];
        if (!empty($title) && !preg_match("/^\s*$/", $title)) {
            $titleBarHtmlArray[] = Html::beginTag('div', ['class'=>'panelBar']);
            $titleBarHtmlArray[] = Html::tag('span', $title, ['style'=>'padding:5px 0px 0px 5px; line-height:25px;']);
            $titleBarHtmlArray[] = Html::endTag('div');

            if ($isDefaultLayoutH) {
                $layoutH = (intval($layoutH) + 27);
                $htmlOptions['layoutH'] = $layoutH;
                $tblOptions['layoutH'] = $layoutH;
            }
        }

        // tool bars
        if (!empty($toolbarHtmlArray) || !empty($toolbarSearchAreaHtmlArray)) {
            if (!empty($toolbarSearchAreaHtmlArray)) {
                $htmlArray[] = Html::beginTag('div', ['class'=>'pageHeader']);
                $htmlArray[] = Html::beginTag('form', ['onsubmit'=>$dgSearchOnChange, 'action'=>'javascript:void(0);', 'method'=>'post']);
                $htmlArray[] = Html::beginTag('div', ['class'=>'searchBar']);
                $htmlArray[] = Html::beginTag('ul', ['class'=>'searchContent']);
                foreach ($toolbarSearchAreaHtmlArray as $_toolHtml) {
                    $htmlArray[] = Html::beginTag('li');
                    $htmlArray[] = $_toolHtml;
                    $htmlArray[] = Html::endTag('li');
                }
                $htmlArray[] = Html::endTag('ul');

                $htmlArray[] = Html::beginTag('div', ['class'=>'subBar']);
                $htmlArray[] = Html::beginTag('ul', []);
                if (empty($toolbarSearchButtonName)) {
                    $toolbarSearchButtonName = Yii::t('locale', 'Retrieval');
                }
                $htmlArray[] = Html::tag('li', Html::tag('div', Html::tag('div', Html::tag('button', $toolbarSearchButtonName, ['type'=>'submit']), ['class'=>'buttonContent']), ['class'=>'buttonActive']));
                $htmlArray[] = Html::endTag('ul');
                $htmlArray[] = Html::endTag('div');

                $htmlArray[] = Html::endTag('div');
                $htmlArray[] = Html::endTag('form');
                $htmlArray[] = Html::endTag('div');

                if ($isDefaultLayoutH) {
                    $layoutH = (intval($layoutH) + 61);
                    $htmlOptions['layoutH'] = $layoutH;
                    $tblOptions['layoutH'] = $layoutH;
                }
            }

            if (!empty($titleBarHtmlArray)) {
                $htmlArray[] = implode("\n", $titleBarHtmlArray);
                $titleBarHtmlArray = false;
            }

            $htmlArray[] = Html::beginTag('div', ['class'=>'panelBar']);
            $htmlArray[] = Html::beginTag('ul', ['class'=>'toolBar']);
            if (!empty($toolbarHtmlArray)) {
                foreach ($toolbarHtmlArray as $_html) {
                    $htmlArray[] = Html::tag('li', $_html);
                }
            }
            $htmlArray[] = Html::endTag('ul');
            $htmlArray[] = Html::endTag('div');
        }

        if (!empty($titleBarHtmlArray)) {
            $htmlArray[] = implode("\n", $titleBarHtmlArray);
            $titleBarHtmlArray = false;
        }

        // format table htmls
        if (!empty($url)) {
            if (isset($tblOptions['layoutH'])) {
                $tblOptions['tlayoutH'] = $tblOptions['layoutH'];
                unset($tblOptions['layoutH']);
            }
            $tblOptions['class'] = 'table-template';
            $_style = (isset($tblOptions['style']) ? $tblOptions['style'] : '');
            $_style .= (empty($_style) ? '' : ';') . 'display:none';
            $tblOptions['style'] = $_style;
        }
        else {
            if (!$showPagination && $isDefaultLayoutH) {
                unset($tblOptions['layoutH']);
            }
        }
        $tableHtmlArray = [];
        $tableHtmlArray[] = Html::beginTag('table', $tblOptions);
        $columnCount = count($arrColumns);
        $theadArray = [];
        // check if has specified frozen columns
        if ($frozenColumnIndex > 0 && $frozenColumnIndex < $columnCount - 2) {
            for ($i = 0; $i <= $frozenColumnIndex; $i++) {
                $col = $arrColumns[$i];
                $theadArray[] = Html::tag('th', $col['name'], $col['options']);
            }

            for ($i = $frozenColumnIndex + 1; $i < $columnCount; $i++) {
                $col = $arrColumns[$i];
                $theadArray[] = Html::tag('th', $col['name'], $col['options']);
            }
        }
        else {
            // format each column into normal columns
            foreach ($arrColumns as $col) {
                $theadArray[] = Html::tag('th', $col['name'], $col['options']);
            }
        }

        // normal datagrid head
        $tableHtmlArray[] = Html::beginTag('thead');
        $tableHtmlArray[] = Html::beginTag('tr');
        $tableHtmlArray[] = implode("\n", $theadArray);
        $tableHtmlArray[] = Html::endTag('tr');
        $tableHtmlArray[] = Html::endTag('thead');

        if (empty($url) && !empty($dataArray)) {
            $rowHtmlArray = [];
            foreach ($dataArray as $row) {
                $cellHtmlArray = [];
                foreach($arrColumnFields as $_field) {
                    $cellHtmlArray[] = '<td>'.(isset($row[$_field]) ? $row[$_field] : '').'</td>';
                }
                $rowHtmlArray[] = Html::tag('tr', implode('', $cellHtmlArray));
            }
            $tableHtmlArray[] = Html::beginTag('tbody');
            $tableHtmlArray[] = '  '.implode("\n  ", $rowHtmlArray);
            $tableHtmlArray[] = Html::endTag('tbody');
        }
        else {
            $tableHtmlArray[] = Html::beginTag('tbody');
            $tableHtmlArray[] = Html::endTag('tbody');
        }
        // close datagrid
        $tableHtmlArray[] = Html::endTag('table');

        if (empty($url)) {
            $htmlArray[] = implode("\n", $tableHtmlArray);
        }
        else {
            $htmlArray[] = Html::tag('div', '', ['class'=>'t-fill-position', 'layoutH'=>(intval($layoutH) - 22)]);
        }

        // pagination bar
        if ($showPagination) {
            $htmlArray[] = Html::beginTag('div', ['class'=>'panelBar', 'id'=>$dgID.'_pagination']);
            $htmlArray[] = Html::beginTag('div', ['class'=>'pages']);
            $htmlArray[] = Html::tag('span', Yii::t('locale', 'Display'));
            $htmlArray[] = Html::dropDownList('numPerPage', '', ['20'=>'20', '30'=>'30', '50'=>'50', '100'=>'100'], ['class'=>'combox', 'onchange'=>$paginationOnChange]);
            $htmlArray[] = Html::tag('span', Yii::t('locale', 'item(s),total') . Yii::t('locale', 'item(s)'));
            $htmlArray[] = Html::endTag('div');
            $htmlArray[] = Html::tag('div', '', $paginationOptions);
            $htmlArray[] = Html::endTag('div');
        }

        if (!empty($url)) {
            $htmlArray[] = implode("\n", $tableHtmlArray);
        }

        // close the wrapped datagrid div if generated
        if (!empty($tableParentSize)) {
            $htmlArray[] = Html::endTag('div');
        }
        else {
            $htmlArray[] = Html::endTag('div');
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
    public static function scriptDatagridCellButtons($datagridId, $buttons, &$scriptFuncName, &$hasDialog, &$hasWindow) {
        $pushBtnArray = [];
        foreach ($buttons as $btnConfig) {
            $title = isset($btnConfig['title']) ? $btnConfig['title'] : '';
            $name = isset($btnConfig['name']) ? $btnConfig['name'] : '';
            $url = isset($btnConfig['url']) ? $btnConfig['url'] : '';
            $type = isset($btnConfig['type']) ? $btnConfig['type'] : '';
            $paramField = isset($btnConfig['paramField']) ? $btnConfig['paramField'] : '';
            $icon = isset($btnConfig['icon']) ? $btnConfig['icon'] : '';
            $condition = isset($btnConfig['condition']) ? $btnConfig['condition'] : '';
            $needReload = isset($btnConfig['needReload']) ? $btnConfig['needReload'] : false;
            
            $target = '';
            $scriptFuncPrefix = '';
            $scriptParamEndfix = '';
            if ($type == 'dialog') {
                $hasDialog = true;
                $target = 'dialog';
            }
            else if ($type == 'window') {
                $hasWindow = true;
                $target = 'dialog';
            }
            else if ($type == 'ajax') {
                $target = 'ajaxTodo';
                if ($needReload == true || $needReload == 'true') {
                    //$scriptParamEndfix = ", \\'get\\', function(){ $(\\'#{$datagridId}\\').datagrid(\\'reload\\'); }";
                }
            }
            else if ($type == '_blank') {
                $target = '_blank';
            }
            else {
                $target = 'navTab';
            }
            
            $urlEndfix = '';
            if (!empty($paramField)) {
                $urlEndfix = "' + row.{$paramField} + '";
            }
            $classExtra = '';
            if (!empty($icon)) {
                $classExtra .= "{$icon}";
            }
            
            $curBtnHtml = '';
            if (empty($icon)) {
                $curBtnHtml = "'<a href=\"{$url}{$urlEndfix}\" target=\"{$target}\" title=\"{$title}\" style=\"display:block;float:left;margin: 0px 4px 0px 0px\">{$name}</a>'";
            }
            else {
                if (preg_match("/^btn[A-Z][a-z]+/", $icon)) {
                    $curBtnHtml = "'<a href=\"{$url}{$urlEndfix}\" class=\"{$classExtra}\" target=\"{$target}\" title=\"{$title}\" >{$name}</a>'";
                }
                else {
                    $curBtnHtml = "'<a href=\"{$url}{$urlEndfix}\" class=\"{$classExtra}\" target=\"{$target}\" title=\"{$title}\" style=\"display:block;width:16px;height:16px;float:left;margin: 0px 4px 0px 0px\" ></a>'";
                }
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
        $scriptContent = "function {$scriptFuncName}(value,row) {\n    var a = new Array();\n    " . implode("\n    ", $pushBtnArray) . "\n    return '' + a.join('') + '';\n}\n";
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
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'dlg_' . $currentID;
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            $htmlOptions['id'] = $id;
        }
        self::$lastTagID = $id;
        
        return Html::tag('div', $content, $htmlOptions);
    }

    /**
     * 
     * @param string $title
     * @param array $htmlOptions
     * @param string $content default false, the window content
     * @return string html text
     */
    public static function window($title, $htmlOptions = [], $content = false) {
        $currentID = self::genID();
        $id = self::ID_PREFIX . 'wnd_' . $currentID;
        if (isset($htmlOptions['id'])) {
            $id = $htmlOptions['id'];
        }
        else {
            $htmlOptions['id'] = $id;
        }
        self::$lastTagID = $id;
        
        return Html::tag('div', $content, $htmlOptions);
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
            Html::beginTag('div', ['style' => '']),
            Html::label($label, $name),
            Html::textInput($name, $value, $htmlOptions),
            Html::endTag('div')
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
        $className = '';
        $exClassName = '';
        $tailHtml = '';
        if (isset($htmlOptions['data-options'])) {
            unset($htmlOptions['data-options']);
        }
        if (isset($htmlOptions['required'])) {
            $isRequired = $htmlOptions['required'];
            if ($isRequired == true || $isRequired == 'true') {
                $exClassName .= ' required';
            }
            unset($htmlOptions['required']);
        }
        if (!empty($prompt)) {
            if ($prompt != strip_tags($prompt)) {
                $tailHtml = $prompt;
            }
            else {
                $tailHtml = Html::label($prompt);
            }
        }
        
        $htmlOptions['name'] = $name;
        $htmlOptions['value'] = $value;
        
        if (self::INPUT_TEXTBOX == $type) {
            $htmlOptions['type'] = 'text';
        }
        elseif (self::INPUT_NUMBERBOX == $type) {
            $htmlOptions['type'] = 'number';
        }
        elseif (self::INPUT_EMAIL == $type) {
            $htmlOptions['type'] = 'email';
        }
        elseif (self::INPUT_PASSWORD == $type) {
            $htmlOptions['type'] = 'password';
        }
        elseif (self::INPUT_TELEPHONE == $type) {
            $htmlOptions['type'] = 'tel';
        }
        elseif (self::INPUT_DATEBOX == $type) {
            $htmlOptions['type'] = 'text';
            $htmlOptions['class'] = 'date';
            if (!isset($htmlOptions['dateFmt'])) {
                $htmlOptions['dateFmt'] = 'yyyy-MM-dd';
            }
        }
        elseif (self::INPUT_DATETIMEBOX == $type) {
            $htmlOptions['type'] = 'text';
            $htmlOptions['class'] = 'date';
            if (!isset($htmlOptions['dateFmt'])) {
                $htmlOptions['dateFmt'] = 'yyyy-MM-dd HH:mm:ss';
            }
        }
        elseif (self::INPUT_TEXTAREA == $type) {
            return Html::textArea($name, $value, $htmlOptions) . $tailHtml;
        }
        elseif (self::INPUT_COMBOBOX == $type) {
            $htmlOptions['class'] = 'combox'.$exClassName;
            return Html::dropDownList($name, $value, $data, $htmlOptions) . $tailHtml;
        }
        elseif (self::INPUT_CHECKBOXLIST == $type) {
            return Html::checkBoxList($name, $value, $data, $htmlOptions) . $tailHtml;
        }
        elseif (self::INPUT_RATIOBUTTONLIST == $type) {
            return Html::radioList($name, $value, $data, $htmlOptions) . $tailHtml;
        }
        elseif (self::INPUT_CHECKBOXDATAGRID == $type) {
            // TODO
            return '';
        }
        else {
            return '';
        }
        
        $htmlOptions['class'] = $className.$exClassName;
        
        return Html::tag($tag, '', $htmlOptions) . $tailHtml;
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
        
        $isInDialog = false;
        if (isset($htmlOptions['window'])) {
            $isInDialog = \common\helpers\Utils::boolvalue($htmlOptions['window']);
            unset($htmlOptions['window']);
        }
        if (isset($htmlOptions['dialog'])) {
            $isInDialog = \common\helpers\Utils::boolvalue($htmlOptions['dialog']);
            unset($htmlOptions['dialog']);
        }
        if (!isset($htmlOptions['onsubmit'])) {
            $htmlOptions['onsubmit'] = 'return validateCallback(this,'.($isInDialog ? 'dialogAjaxDone' :'navTabAjaxDone').');';
        }
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'pageForm required-validate';
        }
        
        // wrapper
        $htmlArray[] = Html::beginTag('div', ['class' => 'pageContent']);
        $htmlArray[] = Html::tag('div', Html::tag('span', $title), ['class' => 'panelBar']);
        
        $htmlArray[] = Html::beginForm($action, $method, $htmlOptions);
        
        $htmlArray[] = Html::beginTag('div', ['class' => 'pageFormContent', 'layoutH' => '158']);
        foreach ($inputs as $o) {
            if (is_string($o)) {
                $htmlArray[] = Html::beginTag('div', ['class' => 'unit']);
                $htmlArray[] = $o;
                $htmlArray[] = Html::endTag('div');
            }
            else {
                $type = isset($o['type']) ? $o['type'] : self::INPUT_TEXTBOX;
                $name = isset($o['name']) ? $o['name'] : '';
                $value = isset($o['value']) ? $o['value'] : '';
                $label = isset($o['label']) ? $o['label'] : '';
                $data = isset($o['data']) ? $o['data'] : [];
                $options = isset($o['htmlOptions']) ? $o['htmlOptions'] : [];
                $prompt = isset($o['prompt']) ? $o['prompt'] : '';

                $htmlArray[] = Html::beginTag('div', ['class' => 'unit']);
                $htmlArray[] = Html::label($label, $name);
                $htmlArray[] = self::inputField($type, $name, $value, $data, $options, $prompt);
                $htmlArray[] = Html::endTag('div');
            }
        }
        $htmlArray[] = Html::endTag('div');
        
        foreach ($hiddenFields as $k => $v) {
            $htmlArray[] = Html::hiddenInput($k, $v);
        }
        
        if (!empty($buttons)) {
            $htmlArray[] = Html::beginTag('div', ['class' => "formBar"]);
            $htmlArray[] = Html::beginTag('ul');
            
            if (isset($buttons['submit'])) {
                $htmlArray[] = '<li><div class="buttonActive"><div class="buttonContent"><button type="submit">'.$buttons['submit'].'</button></div></div></li>';
            }
            if (isset($buttons['reset'])) {
                $htmlArray[] = '<li><div class="button"><div class="buttonContent"><button type="button" class="close">'.$buttons['reset'].'</button></div></div></li>';
            }
            if (isset($buttons['close'])) {
                $htmlArray[] = '<li><div class="button"><div class="buttonContent"><button type="button" class="close">'.$buttons['close'].'</button></div></div></li>';
            }
            
            $htmlArray[] = Html::endTag('ul');
            $htmlArray[] = Html::endTag('div');
        }
        
        $htmlArray[] = Html::endForm();
        
        
        $htmlArray[] = Html::endTag('div');
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
        $iconCls = '';
        if (isset($htmlOptions['icon'])) {
            $iconCls = ' '. $htmlOptions['icon'];
            unset($htmlOptions['icon']);
        }
        $htmlOptions['class'] = "accordion{$iconCls}";
        $htmlOptions['encode'] = false;
        
        $htmlArray = [];
        $htmlArray[] = Html::beginTag('div', $htmlOptions);
        
        foreach ($accordions as $k => $row) {
            if (is_string($k)) {
                continue;
            }
            $title = $row['title'];
            $content = $row['data'];
            $htmlOptions = $row['htmlOptions'];
            
            $iconCls = '';
            if (isset($row['icon'])) {
                $iconCls = ' '. $row['icon'];
            }
            if (isset($row['selected'])) {
                if (\common\helpers\Utils::boolvalue($row['selected'])) {
                    //
                }
            }
            if (isset($row['tools'])) {
                $tools = $row['tools'];
                /*
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
                */
            }
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
                
                // test
                if ($type == 'accordion') {
                    $type = 'tabs';
                }
                //$type = $type . '-test';

                if ($type == 'accordion') {
                    $arr2 = [];
                    $_leftsideID = self::ID_PREFIX. 'leftside_'.self::genID();
                    $_tmpID = self::ID_PREFIX. 'sidebar_'.self::genID();
                    $childHtmlOptions['fillSpace'] = $_tmpID;
                    //$childHtmlOptions['style'] = "margin:0px 0px 0px 10px;height:100%;";
                    //$childHtmlOptions['style'] = "width:200px; position:absolute; top:0; left:5px; z-index:20; overflow:hidden;";

                    //$arr2[] = Html::beginTag('div', ['id'=> $_tmpID, 'style'=>"width:100%;height:99.9%;"]);
                    $arr2[] = Html::beginTag('div', ['id'=> $_leftsideID, 'style'=>"position:absolute; top:55px; left:0; z-index:20; height:100%;width:100%"]);

                    $arr2[] = Html::beginTag('div', ['id'=>$_tmpID.'_s', 'style'=>"display:none; width:24px; position:absolute; top:0; left:0; z-index:21;"]);
                    $arr2[] = Html::beginTag('div', ['class'=>'collapse']);
                    $arr2[] = Html::tag('div', Html::tag('div'), ['class'=>'toggleCollapse']);
                    $arr2[] = Html::endTag('div');
                    $arr2[] = Html::endTag('div');

                    $arr2[] = Html::beginTag('div', ['id'=> $_tmpID, 'style'=>"margin:0px 0px 0px 10px; position:absolute; z-index:20; overflow:hidden;"]);
                    $arr2[] = self::accordionList($content, $childHtmlOptions);
                    $arr2[] = Html::endTag('div');

                    $_script = <<<EOD
$(function(){
    setTimeout(function() {
        var iH = $('#{$_leftsideID}').parent('.accordionContent').height();
        $('#{$_leftsideID}').height(iH);
        $('#{$_tmpID}').height($('#{$_leftsideID}').height() - 5);
        $('#{$_tmpID}').width($('#{$_leftsideID}').width() - 10);
        $('.collapse', $('#{$_tmpID}_s')).height($('#{$_tmpID}').height());
    }, 100);
});
EOD;
                    $arr2[] = Html::script($_script);

                    $arr2[] = Html::endTag('div');
                    //$arr2[] = Html::endTag('div');
                    $content = implode("\n", $arr2);
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
                    //$arr2[] = self::beginTabs(['id'=>self::ID_PREFIX.'tabs_'.self::genID(), 'tabs'=>$tabsArr, 'style'=>'width:100%;height:100%' /*, 'left'=>false, 'right'=>false*/]);
                    $arr2[] = self::beginTabs(['tabs'=>$tabsArr]);
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
            
            $htmlArray[] = Html::beginTag('div', ['class'=>'accordionHeader']);
            $htmlArray[] = Html::tag('h2', Html::tag('span', 'Folder').$title);
            $htmlArray[] = Html::endTag('div');
            $htmlArray[] = Html::beginTag('div', ['class'=>'accordionContent']);
            $htmlArray[] = $content;
            $htmlArray[] = Html::endTag('div');
        }
        
        $htmlArray[] = Html::endTag('div');
        
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
        $treeCls = 'treeFolder';
        if (isset($htmlOptions['icon'])) {
            $treeCls .= " ".$htmlOptions['icon'];
            unset($htmlOptions['icon']);
        }
        
        $htmlOptions['class'] = "tree {$treeCls}";
        $htmlOptions['encode'] = false;
        
        $htmlArray = [];
        $htmlArray[] = Html::beginTag('ul', $htmlOptions);
        
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
                    $htmlOptions['icon'] = $iconName;
                }
                
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            else {
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            
            $htmlArray[] = $content;
        }
        
        $htmlArray[] = Html::endTag('ul');
        
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
                        $name = Html::tag('a', $name);
                    }
                }
                
                if ($isClose) {
                    //$htmlArray[] = Html::beginTag('li', ['data-options' => "state:'closed'", 'encode' => false]);
                    $htmlArray[] = Html::beginTag('li');
                }
                else {
                    $htmlArray[] = Html::beginTag('li');
                }
                $htmlArray[] = $name;
                
                if (isset($htmlOptions['icon'])) {
                    $origionCls = isset($htmlOptions['class']) ? $htmlOptions['class'] : '';
                    $origionCls .= " ".$htmlOptions['icon'];
                    $htmlOptions['class'] = $origionCls;
                    unset($htmlOptions['icon']);
                }

                $htmlArray[] = Html::beginTag('ul', $htmlOptions);
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
                
                $htmlArray[] = Html::endTag('ul');
                $htmlArray[] = Html::endTag('li');
                $content = implode("\n", $htmlArray);
            }
            else {
                $iconName = isset($treeList['icon']) ? $treeList['icon'] : '';

                if (!empty($iconName)) {
                    //$htmlOptions['data-options'] = "iconCls:'{$iconName}'";
                }
                $htmlOptions['encode'] = false;

                $type = isset($treeList['type']) ? $treeList['type'] : '';
                $url = isset($treeList['url']) ? $treeList['url'] : '';
                $target = isset($treeList['target']) ? $treeList['target'] : '';
                
                if ($type == 'navTab') {
                    $tabPanelId = isset($treeList['tabPanelId']) ? $treeList['tabPanelId'] : '';
                    $isIframe = isset($treeList['isIframe']) ? \common\helpers\Utils::boolvalue($treeList['isIframe']) : false;
                    
                    $btnHtmlOptions = [
                        'href' => $url,
                        'rel' => "page{$tabPanelId}",
                        'target' => $target,
                        'encode' => false,
                    ];
                    if ($isIframe) {
                        $btnHtmlOptions['rel'] = 'external';
                        $btnHtmlOptions['external'] = 'true';
                    }

                    $innerHtml = Html::tag('a', $name, $btnHtmlOptions);
                    
                    $content = Html::tag('li', $innerHtml, $htmlOptions);
                }
                else {
                    if (empty($url)) {
                        $content = Html::tag('li', $name, $htmlOptions);
                    }
                    else {
                        $content = Html::tag('li', Html::tag('a', $name, ['href' => $url, 'target' => $target]), $htmlOptions);
                    }
                }
            }
        }
        else {
            $content = strval($treeList);
            if ($content == strip_tags($content)) {
                $content = Html::tag('li', $content);
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
        $styleOptions = [];
        if (isset($htmlOptions['fit'])) {
            if (\common\helpers\Utils::boolvalue($htmlOptions['fit'])) {
                if (!isset($htmlOptions['width']) && !isset($htmlOptions['style'])) {
                    $htmlOptions['width'] = '100%';
                }
                if (!isset($htmlOptions['height']) && !isset($htmlOptions['style'])) {
                    $htmlOptions['height'] = '100%';
                }
            }
            unset($htmlOptions['fit']);
        }
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
        
        //$htmlOptions['class'] = 'layout';
        $htmlOptions['encode'] = false;
        return Html::beginTag('div', $htmlOptions);
    }
    
    public static function endLayout() {
        return Html::endTag('div');
    }
    
    /**
     * 
     * @param string $width
     * @param string $height
     * @param string $title
     * @param string $region
     * @return string html text
     */
    public static function beginLayoutRegion($width='', $height='', $title='', $region='', $htmlOptions = []) {
        if (isset($htmlOptions['style'])) {
            $_style = $htmlOptions['style'];
            $_arr = explode(';', $_style);
            foreach ($_arr as $_e) {
                $_arr2 = explode(':', $_e);
                if (isset($_arr2[1])) {
                    $_k = trim($_arr2[0]);
                    $_v = trim($_arr2[1]);

                    if ($_k == 'width' && empty($width)) {
                        $width = $_v;
                    }
                    elseif ($_k == 'height' && empty($height)) {
                        $height = $_v;
                    }
                }
            }
            $styleArray[] = $_style;
        }

        $htmlArray = [];
        $arrScripts = [];

        if ($region == 'west' || $region == 'east') {
            $_issplit = false;
            if (isset($htmlOptions['split'])) {
                if ($htmlOptions['split'] == true || $htmlOptions['split'] == 'true') {
                    $_issplit = true;
                }
            }

            if (empty($width)) {
                $width = '200px';
            }
            $prefixID = self::ID_PREFIX.self::genID().'_';
            $layoutID = $prefixID.$region;
            $sidebarID = $prefixID.'sidebar';
            $sidebar_sID = $prefixID.'sidebar_s';
            $splitBarID = $prefixID.'splitBar';
            $splitBarProxyID = $prefixID.'splitBarProxy';
            $htmlArray[] = Html::beginTag('div', ['id'=>$layoutID, 'class'=>'dwz-layout-sidebar-p', 'style'=>"width:{$width}; position:absolute; left:0; z-index:20;"]);
            if ($_issplit) {
                $htmlArray[] = Html::beginTag('div', ['id'=>$sidebar_sID, 'class'=>"dwz-layout-sidebar_s"]);
                $htmlArray[] = Html::beginTag('div', ['class'=>'collapse']);
                $htmlArray[] = Html::tag('div', Html::tag('div', '', ['class'=>'toggleCollapse']));
                $htmlArray[] = Html::endTag('div');
                $htmlArray[] = Html::endTag('div');
            }
            $htmlArray[] = Html::beginTag('div', ['id'=>$sidebarID, 'class'=>'dwz-layout-sidebar-c', 'style'=>'width:{$width}; position:absolute; top:0; left:5px; z-index:20; overflow:hidden']);
            $_titleEndfix = '';
            if ($_issplit) {
                $_titleEndfix = Html::tag('div');
            }
            $htmlArray[] = Html::tag('div', Html::tag('h2',$title).$_titleEndfix, ['class'=>'toggleCollapse']);

            $htmlArray[] = Html::tag('div', '', ['id'=>$splitBarID, 'style'=>'display:block; overflow:hidden; width:5px; cursor:col-resize; position:absolute; top:0px; left:{$width}; z-index:20; background:#e5edef']);
            $htmlArray[] = Html::tag('div', '', ['id'=>$splitBarProxyID, 
                'style'=>'display:none; overflow:hidden; width:3px; border-style:solid; border-width:1px; cursor:col-resize; position:absolute; top:0px; left:{$width}; z-index:20; border-color:#c0c0c0; background:#CCC']);
            
            $arrScripts[] = <<<EOD
$(function(){
    var objParent = $('#{$layoutID}').parent();
    if (objParent.size() == 0) {
        return;
    }
    var iLayoutH = objParent.height();
    var iLayoutW = objParent.width();
    if (iLayoutH == 0 || iLayoutW == 0) {
        if (objParent.parent()[0].className.match(/\s*page\s+unitBox\s*/)) {
            var objPage = objParent.parent().parent();
            iLayoutW = objPage.width();
            iLayoutH = objPage.height();
            objParent.width(iLayoutW);
            objParent.height(iLayoutH);
        }
    }

    var objSidebar = $('#{$sidebarID}');
    var objSplitBarParent = objParent;
    if (objSidebar.size() > 0 && objSplitBarParent.children('#{$splitBarID}').size() == 0) {
        var objSplitBar = objSidebar.children('#{$splitBarID}');
        var objSplitBarProxy = objSidebar.children('#{$splitBarProxyID}');
        if (objSplitBar.size() > 0) {
            objSplitBarParent.append(objSplitBar.prop('outerHTML'));
            objSplitBarParent.append(objSplitBarProxy.prop('outerHTML'));

            objSplitBar.remove();
            objSplitBarProxy.remove();
        }
    }

    var objContainer = objParent.children('.dwz-layout-center-t:first');
    var containerId = '#tempcontainerxxx';
    if (objContainer.size() > 0) {
        containerId = '#' + objContainer[0].id;
    }
    $('#{$layoutID}').jBar({minW:150, maxW:700, container:containerId, sideBar:'#{$sidebarID}', sideBar2:'#{$sidebar_sID}', splitBar:'#{$splitBarID}', splitBar2:'#{$splitBarProxyID}'});

    var iSidebarW = objSidebar.width();
    if (iSidebarW > 0) {
        $('#{$splitBarID}', objSplitBarParent).css({left:iSidebarW+5});
        $('#{$splitBarProxyID}', objSplitBarParent).css({left:iSidebarW+5});
    }

    setTimeout(function(){
        var objParent = $('#{$layoutID}').parent();
        if (objParent.size() == 0) {
            return;
        }
        var iHeaderH = 0;
        var objHeader = objParent.children('.dwz-layout-header-t');
        if (objHeader.size() > 0) {
            iHeaderH += objHeader.height();
        }
        var objFooter = objParent.children('.dwz-layout-footer-t');
        if (objFooter.size() > 0) {
            iHeaderH += objFooter.height();
        }
        var iContentH = objParent.height() - iHeaderH - 0;

        if (iContentH > 0) {
            $("#{$sidebarID}, #{$sidebar_sID} .collapse, #{$splitBarID}, #{$splitBarProxyID}").height(iContentH - 5);
        }

    }, 20);
});
EOD;
        }
        elseif ($region == 'north') {
            if (empty($height)) {
                $height = '50px';
            }
            $htmlOptions['class'] = 'dwz-layout-header-t';
            //$htmlOptions['id'] = self::ID_PREFIX.self::genID().'_header';
            $htmlArray[] = Html::beginTag('div', ['style'=>"display:block; overflow:hidden; height:{$height}; z-index:30"]);
            $htmlArray[] = Html::beginTag('div', ['class'=>'headerNav', 'style'=>"height:{$height}; background-repeat:no-repeat; background-position:100% -50px"]);
        }
        elseif ($region == 'south') {
            if (empty($height)) {
                $height = '50px';
            }
            $htmlOptions['class'] = 'dwz-layout-footer-t';
            //$htmlOptions['id'] = self::ID_PREFIX.self::genID().'_header';
            $htmlArray[] = Html::beginTag('div', ['style'=>"display:block; overflow:hidden; height:{$height}; z-index:30"]);
            $htmlArray[] = Html::beginTag('div');
        }
        else {
            $containerID = self::ID_PREFIX.self::genID().'_container';
            $htmlOptions['class'] = 'dwz-layout-center-t';
            $htmlOptions['id'] = $containerID;
            if (!isset($htmlOptions['layoutH'])) {
                $htmlOptions['layoutH'] = 0;
            }
            $htmlArray[] = Html::beginTag('div', $htmlOptions);
            $htmlArray[] = Html::beginTag('div');

            $arrScripts[] = <<<EOD
$(function(){
setTimeout(function(){
    var objParent = $('#{$containerID}').parent();
    if (objParent.size() == 0) {
        return;
    }
    var iLayoutH = objParent.height();
    var iLayoutW = objParent.width();
    if (iLayoutH == 0 || iLayoutW == 0) {
        if (objParent.parent()[0].className.match(/\s*page\s+unitBox\s*/)) {
            var objPage = objParent.parent().parent();
            iLayoutW = objPage.width();
            iLayoutH = objPage.height();
            objParent.width(iLayoutW);
            objParent.height(iLayoutH);
        }
    }
    var iHeaderH = 0;
    var iSidebarW = 0;
    var objHeader = objParent.children('.dwz-layout-header-t');
    if (objHeader.size() > 0) {
        iHeaderH += objHeader.height();
    }
    var objFooter = objParent.children('.dwz-layout-footer-t');
    if (objFooter.size() > 0) {
        iHeaderH += objFooter.height();
    }
    var objSidebar = objParent.children('.dwz-layout-sidebar-p');
    if (objSidebar.size() > 0) {
        iSidebarW = objSidebar.children('.dwz-layout-sidebar-c').width() + 10;
    }
    var iContentW = iLayoutW - iSidebarW - 5;
    var iContentH = iLayoutH - iHeaderH - 0;

    $('#{$containerID}').width(iContentW);
    $('#{$containerID}').height(iContentH);
    $('#{$containerID}').css({position:'absolute', left:iSidebarW});
    if (iContentH > 0) {
        $('#{$containerID} .tabsPageContent').height(iContentH - 34).find("[layoutH]").layoutH();
    }
}, 20);
});
EOD;
        }

        if (!empty($arrScripts)) {
            $htmlArray[] = Html::script(implode("\n", $arrScripts));
        }

        return implode("\n", $htmlArray);
    }
    
    public static function endLayoutRegion() {
        return Html::endTag('div')."\n".Html::endTag('div');
    }
    
    public static function beginPanel($title = ' ', $htmlOptions = []) {
        $htmlArray = [];
        $htmlArray[] = Html::beginTag('div', $htmlOptions);
        if (!empty($title)) {
            $htmlArray[] = Html::beginTag('div', ['class'=>'panelBar dwz-panel-title-bar']);
            $htmlArray[] = Html::tag('span', $title, ['style'=>'padding:5px 0px 0px 5px; line-height:25px;']);
            $htmlArray[] = Html::endTag('div');
        }
        $htmlArray[] = Html::beginTag('div', ['class'=>'dwz-panel-content']);
        return implode("\n", $htmlArray);
    }

    public static function endPanel() {
        return Html::endTag('div')."\n".Html::endTag('div');
    }

    public static function beginTabs($htmlOptions = []) {
        $htmlOptions['class'] = 'tabs';
        $clsTabsLeft = '';
        $clsTabsRight = '';
        $moreContent = '';
        if (isset($htmlOptions['left'])) {
            if ($htmlOptions['left']) {
                $clsTabsLeft .= 'tabsLeft';
            }
            unset($htmlOptions['left']);
        }
        if (isset($htmlOptions['right'])) {
            if ($htmlOptions['right']) {
                $clsTabsRight .= 'tabsRight';
            }
            unset($htmlOptions['right']);
        }
        if (isset($htmlOptions['more'])) {
            $moreContent = $htmlOptions['more'];
            unset($htmlOptions['more']);
        }
        $tabId = isset($htmlOptions['id']) ? $htmlOptions['id'] : '';
        $tabs = [];
        $tabsContent = [];
        if (isset($htmlOptions['tabs'])) {
            $tabs = $htmlOptions['tabs'];
            unset($htmlOptions['tabs']);
        }

        if (!isset($htmlOptions['currentIndex'])) {
            $htmlOptions['currentIndex'] = '0';
        }
        if (!isset($htmlOptions['eventType'])) {
            $htmlOptions['eventType'] = 'click';
        }

        $htmlArray = [];
        $htmlArray[] = Html::beginTag('div', $htmlOptions);
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsHeader']);
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsHeaderContent']);
        $htmlArray[] = Html::beginTag('ul', []);
        foreach ($tabs as $_k) {
            $_title = false;
            $_href = 'javascript:void(0);';
            if (is_array($_k)) {
                if (isset($_k['title'])) {
                    $_title = strval($_k['title']);
                }
                elseif (isset($_k['name'])) {
                    $_title = strval($_k['name']);
                }

                if ($_title) {
                    if (isset($_k['content'])) {
                        $tabsContent[] = strval($_k['content']);
                    }
                    elseif (isset($_k['data'])) {
                        $tabsContent[] = strval($_k['data']);
                    }
                }
                if (isset($_k['href'])) {
                    $_href = $_k['href'];
                }
            }
            else {
                $_title = strval($_k);
            }

            if ($_title) {
                $htmlArray[] = Html::tag('li', Html::tag('a', Html::tag('span', $_title), ['href'=>$_href, 'title'=>$_title]));
            }
        }
        $htmlArray[] = Html::endTag('ul');
        $htmlArray[] = Html::endTag('div');
        if (!empty($clsTabsLeft)) {
            $htmlArray[] = Html::tag('div', 'left', ['class'=>$clsTabsLeft]);
        }
        if (!empty($clsTabsRight)) {
            $htmlArray[] = Html::tag('div', 'right', ['class'=>$clsTabsRight]);
        }
        if (!empty($moreContent)) {
            $htmlArray[] = Html::tag('div', 'more', ['class'=>'tabsMore']);
        }
        $htmlArray[] = Html::endTag('div');

        if (!empty($moreContent)) {
            $htmlArray[] = Html::beginTag('ul', ['class'=>'tabsMoreList']);
            if (is_array($moreContent)) {
                foreach ($moreContent as $_v) {
                    $htmlArray[] = Html::tag('li', $_v);
                }
            }
            else {
                $htmlArray[] = Html::tag('li', $moreContent);
            }
            $htmlArray[] = Html::endTag('ul');
        }

        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsContent']);

        foreach ($tabsContent as $_c) {
            $htmlArray[] = self::beginTabsChild('');
            //$htmlArray[] = Html::beginTag('div');
            $htmlArray[] = strval($_c);
            //$htmlArray[] = Html::endTag('div');
            $htmlArray[] = self::endTabsChild();
        }

        return implode("\n", $htmlArray);
    }
    
    public static function endTabs() {
        $htmlArray = [];
        $htmlArray[] = Html::endTag('div');
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsFooter']);
        $htmlArray[] = Html::tag('div', '', ['class'=>'tabsFooterContent']);
        $htmlArray[] = Html::endTag('div');
        $htmlArray[] = Html::endTag('div');

        return implode("\n", $htmlArray);
    }

    public static function beginTabsChild($title = '', $htmlOptions = []) {
        return Html::beginTag('div', $htmlOptions);
    }

    public static function endTabsChild() {
        return Html::endTag('div');
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
        $styleArray = [];
        if (!empty($width)) {
            $styleArray[] = "width:{$width}";
        }
        if (!empty($height)) {
            $styleArray[] = "height:{$height}";
        }

        if (isset($htmlOptions['style'])) {
            $_style = $htmlOptions['style'];
            $styleArray[] = $_style;
        }
        
        if (!empty($styleArray)) {
            $htmlOptions['style'] = implode(";", $styleArray);
        }

        $htmlArray = [];

        if (!empty($region)) {
            if ($region == 'west' || $region == 'east') {
                $_issplit = false;
                if (isset($htmlOptions['split'])) {
                    if ($htmlOptions['split'] == true || $htmlOptions['split'] == 'true') {
                        $_issplit = true;
                    }
                }

                $htmlArray[] = Html::beginTag('div', ['id'=>'leftside']);
                if ($_issplit) {
                    $htmlArray[] = Html::beginTag('div', ['id'=>'sidebar_s']);
                    $htmlArray[] = Html::beginTag('div', ['class'=>'collapse']);
                    $htmlArray[] = Html::tag('div', Html::tag('div'), ['class'=>'toggleCollapse']);
                    $htmlArray[] = Html::endTag('div');
                    $htmlArray[] = Html::endTag('div');
                }
                $htmlArray[] = Html::beginTag('div', ['id'=>'sidebar']);
                $_titleEndfix = '';
                if ($_issplit) {
                    $_titleEndfix = Html::tag('div');
                }
                $htmlArray[] = Html::tag('div', Html::tag('h2',$title).$_titleEndfix, ['class'=>'toggleCollapse']);
            }
            elseif ($region == 'north') {
                $htmlArray[] = Html::beginTag('div', ['id'=>'header']);
                $htmlArray[] = Html::beginTag('div', ['class'=>'headerNav']);
            }
            elseif ($region == 'south') {
                $htmlArray[] = Html::beginTag('div', ['id'=>'footer']);
                $htmlArray[] = Html::beginTag('div');
            }
            else {
                $htmlArray[] = Html::beginTag('div', ['id'=>'container']);
                $htmlArray[] = Html::beginTag('div');
            }
        }
        else {
            $htmlArray[] = Html::beginTag('div', ['id'=>'container']);
                $htmlArray[] = Html::beginTag('div');
        }

        return implode("\n", $htmlArray);
    }
    
    public static function endMainPageLayoutRegion() {
        return Html::endTag('div')."\n".Html::endTag('div');
    }
    
    public static function beginMainPageTabs($htmlOptions = []) {
        $htmlOptions['class'] = 'tabsPage';
        $clsTabsLeft = 'tabsLeft';
        $clsTabsRight = 'tabsRight';
        $moreContent = '';
        if (isset($htmlOptions['left'])) {
            if (!$htmlOptions['left']) {
                $clsTabsLeft .= ' tabsLeftDisabled';
            }
            unset($htmlOptions['left']);
        }
        if (isset($htmlOptions['right'])) {
            if (!$htmlOptions['right']) {
                $clsTabsRight .= ' tabsRightDisabled';
            }
            unset($htmlOptions['right']);
        }
        if (isset($htmlOptions['more'])) {
            $moreContent = $htmlOptions['more'];
            unset($htmlOptions['more']);
        }
        $tabId = isset($htmlOptions['id']) ? $htmlOptions['id'] : '';
        $tabs = [];
        $tabsContent = [];
        if (isset($htmlOptions['tabs'])) {
            $tabs = $htmlOptions['tabs'];
            unset($htmlOptions['tabs']);
        }

        $htmlArray = [];
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsPage', 'id'=>$tabId]);
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsPageHeader']);
        $htmlArray[] = Html::beginTag('div', ['class'=>'tabsPageHeaderContent']);
        $htmlArray[] = Html::beginTag('ul', ['class'=>'navTab-tab']);
        foreach ($tabs as $_k) {
            $_title = false;
            if (is_array($_k)) {
                if (isset($_k['title'])) {
                    $_title = strval($_k['title']);
                }
                elseif (isset($_k['name'])) {
                    $_title = strval($_k['name']);
                }

                if ($_title) {
                    if (isset($_k['content'])) {
                        $tabsContent[] = strval($_k['content']);
                    }
                    elseif (isset($_k['data'])) {
                        $tabsContent[] = strval($_k['data']);
                    }
                }
            }
            else {
                $_title = strval($_k);
            }

            if ($_title) {
                $htmlArray[] = Html::tag('li', Html::tag('a', Html::tag('span',Html::tag('span', $_title,['class'=>'home_icon'])), ['href'=>'javascript:void(0);', 'title'=>$_title]), ['class'=>'main', 'tabid'=>'main']);
            }
        }
        $htmlArray[] = Html::endTag('ul');
        $htmlArray[] = Html::endTag('div');
        $htmlArray[] = Html::tag('div', 'left', ['class'=>$clsTabsLeft]);
        $htmlArray[] = Html::tag('div', 'right', ['class'=>$clsTabsRight]);
        if (!empty($moreContent)) {
            $htmlArray[] = Html::tag('div', 'more', ['class'=>'tabsMore']);
        }
        $htmlArray[] = Html::endTag('div');

        if (!empty($moreContent)) {
            $htmlArray[] = Html::beginTag('ul', ['class'=>'tabsMoreList']);
            if (is_array($moreContent)) {
                foreach ($moreContent as $_v) {
                    $htmlArray[] = Html::tag('li', $_v);
                }
            }
            else {
                $htmlArray[] = Html::tag('li', $moreContent);
            }
            $htmlArray[] = Html::endTag('ul');
        }

        $htmlArray[] = Html::beginTag('div', ['class'=>'navTab-panel tabsPageContent layoutBox']);

        foreach ($tabsContent as $_c) {
            $htmlArray[] = self::beginTabsChild('', ['class'=>'page unitBox']);
            //$htmlArray[] = Html::beginTag('div');
            $htmlArray[] = strval($_c);
            //$htmlArray[] = Html::endTag('div');
            $htmlArray[] = self::endTabsChild();
        }

        return implode("\n", $htmlArray);
    }
    
    public static function endMainPageTabs() {
        return Html::endTag('div')."\n".Html::endTag('div');
    }

    public static function mainPageHeaderContent($content = []) {
        $htmlArray = [];
        $htmlArray[] = self::tag('a', 'Flag', ['class'=>'logo', 'href'=>'javascript:void(0);']);
        $htmlArray[] = self::beginTag('ul', ['class'=>'nav']);
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
        
        $htmlArray[] = self::beginTag('ul', ['class'=>'themeList', 'id'=>'themeList']);
        $htmlArray[] = self::tag('li', self::tag('div', '蓝色'), ['theme'=>'default']);
        $htmlArray[] = self::tag('li', self::tag('div', '绿色'), ['theme'=>'green']);
        $htmlArray[] = self::tag('li', self::tag('div', '紫色'), ['theme'=>'purple']);
        $htmlArray[] = self::tag('li', self::tag('div', '银色'), ['theme'=>'silver']);
        $htmlArray[] = self::tag('li', self::tag('div', '天蓝'), ['theme'=>'azure']);
        $htmlArray[] = self::endTag('ul');
        
        return implode("\n", $htmlArray);
    }
    
    public static function mainPageLayout($headerPart = '', $westPart = '', $containerPart = '', $footerPart = '', $eastPart = '') {
        $htmlArray = [];
        
        function convertContentPart(&$content, &$result, $defaultWidth = '', $defaultHeight = '', $defaultTitle = '', $defaultHtmlOptions = []) {
            $result['title'] = $defaultTitle;
            $result['width'] = $defaultWidth;
            $result['height'] = $defaultHeight;
            $result['options'] = $defaultHtmlOptions;
            if (is_array($content)) {
                foreach (array('title', 'width', 'height', 'options') as $k) {
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
        $htmlArray[] = CHtml::openTag('div', ['id'=>'layout']);
        if (!empty($headerPart)) {
            convertContentPart($headerPart, $regionInfo, '', '', '', []);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'north', $regionInfo['options']);
            $htmlArray[] = $headerPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($westPart)) {
            convertContentPart($westPart, $regionInfo, '', '', '', ['split'=>true]);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'west', $regionInfo['options']);
            $htmlArray[] = $westPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        if (!empty($containerPart)) {
            convertContentPart($containerPart, $regionInfo, '', '', '', []);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'center', $regionInfo['options']);
            $htmlArray[] = $containerPart;
            $htmlArray[] = self::endMainPageLayoutRegion();
        }
        $htmlArray[] = CHtml::closeTag('div');
        if (!empty($footerPart)) {
            convertContentPart($footerPart, $regionInfo, '', '', '', []);
            $htmlArray[] = self::beginMainPageLayoutRegion($regionInfo['width'], $regionInfo['height'], $regionInfo['title'], 'south', $regionInfo['options']);
            $htmlArray[] = $footerPart;
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
        $uiTheme = "default";
        $lanLocale = Yii::$app->params['lan_locale'];
        $basePath = self::ASSETS_BASE_PATH;
        $html = <<<EOD
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$title}</title>

<link href="{$basePath}/assets/dwz/themes/default/style.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="{$basePath}/assets/dwz/themes/css/core.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="{$basePath}/assets/dwz/themes/css/print.css" rel="stylesheet" type="text/css" media="print"/>
<link href="{$basePath}/assets/dwz/uploadify/css/uploadify.css" rel="stylesheet" type="text/css" media="screen"/>

<link href="{$basePath}/assets/css/dwz.custom.css" rel="stylesheet" type="text/css">
<link href="{$basePath}/assets/css/icons.extension.css" rel="stylesheet" type="text/css">

<!--[if IE]>
<link href="{$basePath}/assets/dwz/themes/css/ieHack.css" rel="stylesheet" type="text/css" media="screen"/>
<![endif]-->

<!--[if lt IE 9]><script src="{$basePath}/assets/dwz/js/speedup.js" type="text/javascript"></script><script src="{$basePath}/assets/dwz/js/jquery-1.11.3.min.js" type="text/javascript"></script><![endif]-->
<!--[if gte IE 9]><!--><script src="{$basePath}/assets/dwz/js/jquery-2.1.4.min.js" type="text/javascript"></script><!--<![endif]-->

<script src="{$basePath}/assets/dwz/js/jquery.cookie.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/jquery.validate.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/jquery.bgiframe.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/xheditor/xheditor-1.2.2.min.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/xheditor/xheditor_lang/zh-cn.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/uploadify/scripts/jquery.uploadify.js" type="text/javascript"></script>

<!-- svg图表  supports Firefox 3.0+, Safari 3.0+, Chrome 5.0+, Opera 9.5+ and Internet Explorer 6.0+ -->
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/raphael.js"></script>
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/g.raphael.js"></script>
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/g.bar.js"></script>
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/g.line.js"></script>
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/g.pie.js"></script>
<script type="text/javascript" src="{$basePath}/assets/dwz/chart/g.dot.js"></script>

<script src="{$basePath}/assets/dwz/js/dwz.core.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.util.date.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.validate.method.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.barDrag.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.drag.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.tree.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.accordion.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.ui.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.theme.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.switchEnv.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.alertMsg.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.contextmenu.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.navTab.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.tab.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.resize.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.dialog.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.dialogDrag.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.sortDrag.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.cssTable.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.stable.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.taskBar.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.ajax.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.pagination.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.database.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.datepicker.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.effects.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.panel.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.checkbox.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.history.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.combox.js" type="text/javascript"></script>
<script src="{$basePath}/assets/dwz/js/dwz.print.js" type="text/javascript"></script>

<!-- 可以用dwz.min.js替换前面全部dwz.*.js (注意：替换是下面dwz.regional.zh.js还需要引入)
<script src="{$basePath}/assets/dwz/bin/dwz.min.js" type="text/javascript"></script>
-->
<script src="{$basePath}/assets/dwz/js/dwz.regional.zh.js" type="text/javascript"></script>

<script type="text/javascript" src="{$basePath}/assets/custom/js/common.custom.js"></script>
<script type="text/javascript" src="{$basePath}/assets/custom/js/utils.custom.js"></script>
<script type="text/javascript" src="{$basePath}/assets/custom/js/dwz.custom.js"></script>
<script type="text/javascript" src="{$basePath}/assets/custom/js/locale/custom.messages-{$lanLocale}.js"></script>

<script type="text/javascript">
$(function(){
	DWZ.init("{$basePath}/assets/dwz/dwz.frag.xml", {
		loginUrl:"{$basePath}/assets/dwz/login_dialog.html", loginTitle:"登录",	// 弹出登录对话框
//		loginUrl:"login.html",	// 跳到登录页面
		statusCode:{ok:200, error:300, timeout:301}, //【可选】
		pageInfo:{pageNum:"pageNum", numPerPage:"numPerPage", orderField:"orderField", orderDirection:"orderDirection"}, //【可选】
		keys: {statusCode:"statusCode", message:"message"}, //【可选】
		ui:{hideMode:'offsets'}, //【可选】hideMode:navTab组件切换的隐藏方式，支持的值有’display’，’offsets’负数偏移位置的值，默认值为’display’
		debug:false,	// 调试模式 【true|false】
		callback:function(){
			initEnv();
			$("#themeList").theme({themeBase:"{$basePath}/assets/dwz/themes"}); // themeBase 相对于index页面的主题base路径
		}
	});
});

</script>
EOD;
        $arrScripts = [];
        $arrScripts[] = "$(function(){ $.custom.uiframework = 'dwz';});";
        $html .= "\n".Html::script(implode("\n", $arrScripts));

        if ($closeHead) {
            $html .= "\n</head>";
        }

        return $html;
    }
    
    public static function openBody($htmlOptions = []) {
        return Html::beginTag('body', $htmlOptions);
    }
    
    public static function convertFuncStringJS($funcStr, $paramsContent = '', $wrapped = false) {
        if (preg_match('/\s*function\s*\([A-Za-z0-9_ ,]*\)\s*\{/', $funcStr)) {
            if ($wrapped) {
                $funcStr = "javascript:{var func=new Function('return {$funcStr}')(); func({$paramsContent});}";
            }
            else {
                $funcStr = "var func=new Function('return {$funcStr}')();\nfunc({$paramsContent});";
            }
        }
        return $funcStr;
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
            //$htmlOptions['class'] = '';
        }
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        
        $arrOptions = [];
        if (is_array($dataOrUrl)) {
            $arrOptions = self::_genComboTreeChildren('', $dataOrUrl);
        }

        if (isset($htmlOptions['onChange'])) {
            if (!isset($htmlOptions['onchange'])) {
                $htmlOptions['onchange'] = self::convertFuncStringJS($htmlOptions['onChange'], 'this.value', true);
            }
            unset($htmlOptions['onChange']);
        }

        $htmlArray = [];
        $htmlArray[] = Html::dropDownList('', $selection, $arrOptions, $htmlOptions);
        //$htmlArray[] = Html::tag('select', '', $htmlOptions);
        
        return implode("\n", $htmlArray);
    }
    
    private static function _genComboTreeChildren($prefix, $arr) {
        $arrOptions = [];
        foreach ($arr as $item) {
            if (isset($item['children'])) {
                $_newPrefix = $prefix . (isset($item['text']) ? $item['text'] . '&gt;' : '&nbsp;&gt;');
                $arrOptions[$_newPrefix] = self::_genComboTreeChildren($_newPrefix, $item['children']);
            }
            elseif (isset($item['id']) && isset($item['text'])) {
                $arrOptions[$item['id']] = $item['text'];
            }
        }
        return $arrOptions;
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
        $arrScripts = [];
        if (!empty($title)) {
            $htmlArray[] = self::beginPanel($title);
        }
        
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'datalist';
        }
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        
        $_ID = isset($htmlOptions['id']) ? $htmlOptions['id'] : '';
        $selectedIdArr = [];
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
                        $selectedIdArr[intval($_v)] = 1;
                        //$_sels[] = "$('#{$_ID}').datalist('checkRow', {$_v});";
                    }
                }
                $selection = implode("\n", $_sels);
                //$dataOptions['onLoadSuccess'] = "function(){ {$selection} }";
            }
            else {
                $selectionIndex = intval($selectionIndex);
                $selectedIdArr[$selectionIndex] = 1;
                //$dataOptions['onLoadSuccess'] = "function(){ $('#{$_ID}').datalist('checkRow', {$selectionIndex}); }";
            }
        }
        
        if (isset($htmlOptions['onSelect'])) {
            if (!isset($htmlOptions['callback'])) {
                $htmlOptions['callback'] = $htmlOptions['onSelect'];
            }
            unset($htmlOptions['onSelect']);

            $arrScripts[] = "$(function(){ $.custom.dwz.datalist.bindOnClickEvent('{$_ID}'); });";
        }

        $content = false;
        if (is_string($dataArray)) {
        }
        else {
            $_contentArray = [];

            $_idx = 0;
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
                    if (isset($selectedIdArr[$_idx])) {
                        $_htmlOpt['class'] = 'selected';
                    }
                    $_contentArray[] = Html::tag('li', $_name, $_htmlOpt);
                    $_idx++;
                }
            }
            else {
                foreach ($dataArray as $_k => $_v) {
                    if (isset($selectedIdArr[$_idx])) {
                        $_contentArray[] = Html::tag('li', $_v, ['value' => $_k, 'class'=>'selected']);
                    }
                    else {
                        $_contentArray[] = Html::tag('li', $_v, ['value' => $_k]);
                    }
                    $_idx++;
                }
            }
            
            $content = "\n" . implode("\n", $_contentArray) . "\n";
        }
        $htmlArray[] = Html::tag('ul', $content, $htmlOptions);
        
        if (!empty($arrScripts)) {
            $htmlArray[] = Html::script(implode("\n", $arrScripts));
        }

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
        $htmlOptions['class'] = 'button';
        if (!isset($htmlOptions['encode'])) {
            $htmlOptions['encode'] = false;
        }
        if (!isset($htmlOptions['href'])) {
            $htmlOptions['href'] = 'javascript:void(0);';
        }
        if (isset($htmlOptions['onClick'])) {
            if (!isset($htmlOptions['onclick'])) {
                $htmlOptions['onclick'] = self::convertFuncStringJS($htmlOptions['onClick'], 'this', true);
                unset($htmlOptions['onClick']);
            }
        }
        if (isset($htmlOptions['selected'])) {
            if (\common\helpers\Utils::boolvalue($htmlOptions['selected'])) {
                $htmlOptions['class'] = 'buttonActive';
            }
        }
        if (isset($htmlOptions['disabled'])) {
            if (\common\helpers\Utils::boolvalue($htmlOptions['disabled'])) {
                $htmlOptions['class'] = 'buttonDisabled';
            }
        }
        return Html::tag('a', Html::tag('span', $btnText), $htmlOptions);
    }
    
}
