<?php

namespace common\helpers;

use yii\helpers\Html;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class CMyHtml extends \common\helpers\BaseHtmlUI {
    const UI_FRAMEWORK_EASYUI = 1;
    const UI_FRAMEWORK_DWZ = 2;
    
    //public static $uiFramework = self::UI_FRAMEWORK_DWZ;
    //public static $uiClass = '\common\helpers\CDwzJui';
    public static $uiFramework = self::UI_FRAMEWORK_EASYUI;
    public static $uiClass = '\common\helpers\CEasyUI';
    
    public static function getIDPrefix() {
        $cls = self::$uiClass;
        return $cls::ID_PREFIX;
    }
    
    public static function openBody($htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::openBody($htmlOptions);
    }   

    public static function closeBody() {
        return Html::endTag('body');
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
        
        $cls = self::$uiClass;
        return $cls::datagrid($title, $model, $columns, $dataArray, $width, $height, $htmlOptions, $urlsArray, $toolbarArray, $frozenColumnIndex, $frozenRowIndex);
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
        $cls = self::$uiClass;
        return $cls::textFieldWithLabel($name, $value, $label, $htmlOptions);
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
        $cls = self::$uiClass;
        return $cls::form($title, $action, $method, $htmlOptions, $inputs, $buttons, $hiddenFields);
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
        $cls = self::$uiClass;
        return $cls::accordionList($accordions, $htmlOptions);
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
        $cls = self::$uiClass;
        return $cls::treeList($treeArray, $htmlOptions);
    }

    /**
     * 
     * @return string html text
     */
    public static function beginLayout($htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::beginLayout($htmlOptions);
    }
    
    public static function endLayout() {
        $cls = self::$uiClass;
        return $cls::endLayout();
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
        $cls = self::$uiClass;
        return $cls::beginLayoutRegion($width, $height, $title, $region, $htmlOptions);
    }
    
    public static function endLayoutRegion() {
        $cls = self::$uiClass;
        return $cls::endLayoutRegion();
    }
    
    public static function beginPanel($title = ' ', $htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::beginPanel($title, $htmlOptions);
    }

    public static function endPanel() {
        $cls = self::$uiClass;
        return $cls::endPanel();
    }

    public static function beginTabs($htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::beginTabs($htmlOptions);
    }
    
    public static function endTabs() {
        $cls = self::$uiClass;
        return $cls::endTabs();
    }

    public static function beginTabsChild($title = '', $htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::beginTabsChild($title, $htmlOptions);
    }

    public static function endTabsChild() {
        $cls = self::$uiClass;
        return $cls::endTabsChild();
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
        $cls = self::$uiClass;
        return $cls::beginMainPageLayoutRegion($width, $height, $title, $region, $htmlOptions);
    }
    
    public static function endMainPageLayoutRegion() {
        $cls = self::$uiClass;
        return $cls::endMainPageLayoutRegion();
    }
    
    public static function beginMainPageTabs($htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::beginMainPageTabs($htmlOptions);
    }
    
    public static function endMainPageTabs() {
        $cls = self::$uiClass;
        return $cls::endMainPageTabs();
    }

    public static function mainPageHeaderContent($content = []) {
        $cls = self::$uiClass;
        return $cls::mainPageHeaderContent($content);
    }
    
    public static function mainPageLayout($headerPart = '', $westPart = '', $containerPart = '', $footerPart = '', $eastPart = '') {
        $cls = self::$uiClass;
        return $cls::mainPageLayout($headerPart, $westPart, $containerPart, $footerPart, $eastPart);
    }
    
    public static function htmlHead($title, $closeHead = true) {
        $cls = self::$uiClass;
        return $cls::headPart($title, $closeHead);
    }
    
    public static function checkBox($name, $label, $checked = false, $htmlOptions = []) {
        $labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:[];
        unset($htmlOptions['labelOptions']);
        return Html::beginTag('label', $labelOptions) . Html::checkbox($name, $checked, $htmlOptions) . $label . Html::endTag('label');
    }
    
    /**
     * 
     * @param type $dataOrUrl if this field is array consider as data, or if string, consider as a url.
     * @param type $htmlOptions
     * @param type $selection
     * @return type
     */
    public static function comboTree($data, $htmlOptions = [], $selection = false) {
        $cls = self::$uiClass;
        return $cls::comboTree($data, $htmlOptions, $selection);
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
        $cls = self::$uiClass;
        return $cls::dataList($dataArray, $title, $htmlOptions, $selectionIndex);
    }
    
    /**
     * 
     * @param type $btnText
     * @param type $htmlOptions
     */
    public static function linkButton($btnText, $htmlOptions = []) {
        $cls = self::$uiClass;
        return $cls::linkButton($btnText, $htmlOptions);
    }
    
}
