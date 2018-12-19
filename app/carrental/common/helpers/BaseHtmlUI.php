<?php
namespace common\helpers;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class BaseHtmlUI
{
    const ASSETS_BASE_PATH = '../../../..';
    
	/**
	 * @var string the CSS class for displaying error summaries (see {@link errorSummary}).
	 */
	public static $errorSummaryCss='errorSummary';
	/**
	 * @var string the CSS class for displaying error messages (see {@link error}).
	 */
	public static $errorMessageCss='errorMessage';
	/**
	 * @var string the CSS class for highlighting error inputs. Form inputs will be appended
	 * with this CSS class if they have input errors.
	 */
	public static $errorCss='error';
	/**
	 * @var string the tag name for the error container tag. Defaults to 'div'.
	 * @since 1.1.13
	 */
	public static $errorContainerTag='div';
	/**
	 * @var string the CSS class for required labels. Defaults to 'required'.
	 * @see label
	 */
	public static $requiredCss='required';
	/**
	 * @var string the HTML code to be prepended to the required label.
	 * @see label
	 */
	public static $beforeRequiredLabel='';
	/**
	 * @var string the HTML code to be appended to the required label.
	 * @see label
	 */
	public static $afterRequiredLabel=' <span class="required">*</span>';
	/**
	 * @var integer the counter for generating automatic input field names.
	 */
	public static $count=0;
	/**
	 * Sets the default style for attaching jQuery event handlers.
	 *
	 * If set to true (default), event handlers are delegated.
	 * Event handlers are attached to the document body and can process events
	 * from descendant elements that are added to the document at a later time.
	 *
	 * If set to false, event handlers are directly bound.
	 * Event handlers are attached directly to the DOM element, that must already exist
	 * on the page. Elements injected into the page at a later time will not be processed.
	 *
	 * You can override this setting for a particular element by setting the htmlOptions delegate attribute
	 * (see {@link clientChange}).
	 *
	 * For more information about attaching jQuery event handler see {@link http://api.jquery.com/on/}
	 * @since 1.1.9
	 * @see clientChange
	 */
	public static $liveEvents=true;
	/**
	 * @var boolean whether to close single tags. Defaults to true. Can be set to false for HTML5.
	 * @since 1.1.13
	 */
	public static $closeSingleTags=true;
	/**
	 * @var boolean whether to render special attributes value. Defaults to true. Can be set to false for HTML5.
	 * @since 1.1.13
	 */
	public static $renderSpecialAttributesValue=true;

    public static $lastTagID = '';
    
    private static $_id = 0;

    /**
     * about ui component const
     */
    const DG_TOOL_APPEND = 0x00000001;  // append a item to datagrid
    const DG_TOOL_REMOVE = 0x00000002;  // remove the item from datagrid
    const DG_TOOL_ACCEPT = 0x00000004;  // accept the changes in datagrid cells
    const DG_TOOL_REJECT = 0x00000008;  // reject the changes in datagrid cells
    const DG_TOOL_GETCHANGES = 0x00000010;  // get changes in datagrid
    const DG_TOOL_EDIT = 0x00000020;  // get changes in datagrid
    const DG_TOOL_SINGLEMULTIPLE = 0x00000040; // single|multiple selection mode
    const DG_TOOL_MENU = 0x00000080;
    const DG_TOOL_BUTTON = 0x00000100;
    const DG_TOOL_CUSTOM = 0x00000200;  // custom tool

    const DG_TOOLS_DEFAULT = 0x0000007F;

    const DG_TOOL_SEARCH_TEXTBOX = 0x00001000;   // search text box
    const DG_TOOL_SEARCH_COMBOBOX = 0x00002000;  // search dropdown box
    const DG_TOOL_SEARCH_RADIO = 0x00004000;     // search radio button list
    const DG_TOOL_SEARCH_CHECKBOX = 0x00008000;  // search check box list
    const DG_TOOL_SEARCH_TEXTFIELD = 0x00010000; // search textfield
    const DG_TOOL_SEARCH_DATEBOX = 0x00020000;   // search date box
    const DG_TOOL_SEARCH_DATETIMEBOX = 0x00040000;   // search date box
    const DG_TOOL_SEARCH_BUTTON = 0x00080000;    // search button
    const DG_TOOL_SEARCH_COMBOTREE = 0x00100000; // search combo tree

    const DG_TOOL_SEARCH_TYPE_FLAG = 0x001FF000;  //

    const INPUT_TYPE_GROUP = 0x00000010;
    const INPUT_TYPE_SUBGROUP = 0x00000020;
    const INPUT_TYPE_APPENDELEMENTBUTTON = 0x00000040;
    const INPUT_TYPE_HTML = 0x00000080;
    const INPUT_TEXTBOX = 0x00000100;
    const INPUT_NUMBERBOX = 0x00000200;
    const INPUT_EMAIL = 0x00000400;
    const INPUT_PASSWORD = 0x00000800;
    const INPUT_TELEPHONE = 0x00001000;
    const INPUT_DATEBOX = 0x00002000;
    const INPUT_DATETIMEBOX = 0x00004000;
    const INPUT_TIMEBOX = 0x00008000;
    const INPUT_TEXTAREA = 0x00010000;
    const INPUT_COMBOBOX = 0x00020000;
    const INPUT_CHECKBOXLIST = 0x00040000;
    const INPUT_RATIOBUTTONLIST = 0x00080000;
    const INPUT_CHECKBOXDATAGRID = 0x00100000;
    const INPUT_FILEFIELD = 0x00200000;
    const INPUT_IMAGEFIELD = 0x00400000;
    const INPUT_COMBOTREE = 0x00800000;
    const INPUT_COMBOGRID = 0x01000000;
    
    public static function genID() {
        if (empty(self::$_id)) {
            $t = time();
            self::$_id = ($t % 1000000) * 1000 + rand(0, 999);
        }
        self::$_id++;
        return self::$_id;
    }

	/**
	 * Appends {@link errorCss} to the 'class' attribute.
	 * @param array $htmlOptions HTML options to be modified
	 */
	protected static function addErrorCss(&$htmlOptions)
	{
		if(empty(self::$errorCss))
			return;

		if(isset($htmlOptions['class']))
			$htmlOptions['class'].=' '.self::$errorCss;
		else
			$htmlOptions['class']=self::$errorCss;
	}

	/**
	 * Generates a valid HTML ID based on name.
	 * @param string $name name from which to generate HTML ID
	 * @return string the ID generated based on name.
	 */
	public static function getIdByName($name)
	{
		return str_replace(['[]','][','[',']',' '],['','_','_','','_'],$name);
	}

    /**
     * format datagrid toolbar tool config array
     * @param integer $type tool type
     * @param string $name display name
     * @param string $func function string or function name
     * @param string $iconName icon class name
     * @return array toolbar tool config data
     */
    public static function formatDatagridToolConfig($type, $name = '', $func = '', $iconName = '') {
        return [$type, $name, $func, $iconName];
    }

    /**
     * format datagrid toolbar tool config array
     * @param integer $type tool type
     * @param string $name search field name
     * @param string $prompt Description
     * @param string $param prompt string or function string or function name
     * @param array $htmlOptions
     * @return array toolbar tool config data
     */
    public static function formatDatagridSearchAreaToolConfig($type, $name, $prompt = '', $param = '', $htmlOptions = [], $selected = '') {
        return [$type, $name, $prompt, $param, $htmlOptions, $selected];
    }

    public static function convertInputFieldType($typeName) {
        static $typeNameArray = [
            'textbox' => self::INPUT_TEXTBOX,
            'number' => self::INPUT_NUMBERBOX,
            'email' => self::INPUT_EMAIL,
            'password' => self::INPUT_PASSWORD,
            'telephone' => self::INPUT_TELEPHONE,
            'datebox' => self::INPUT_DATEBOX,
            'datetimebox' => self::INPUT_DATETIMEBOX,
            'timebox' => self::INPUT_TIMEBOX,
            'textarea' => self::INPUT_TEXTAREA,
            'combobox' => self::INPUT_COMBOBOX,
            'checkBoxList' => self::INPUT_CHECKBOXLIST,
            'radioButtonList' => self::INPUT_RATIOBUTTONLIST,
            'checkBoxDatagrid' => self::INPUT_CHECKBOXDATAGRID,
            'combotree' => self::INPUT_COMBOTREE,
            'combogrid' => self::INPUT_COMBOGRID,
        ];
        
        if (isset($typeNameArray[$typeName])) {
            return $typeNameArray[$typeName];
        }
        
        return 0;
    }
    
    /**
     * Generates a complete HTML tag.
     * @param string|boolean|null $name the tag name. If $name is `null` or `false`, the corresponding content will be rendered without any tag.
     * @param string $content the content to be enclosed between the start and end tags. It will not be HTML-encoded.
     * If this is coming from end users, you should consider [[encode()]] it to prevent XSS attacks.
     * @param array $options the HTML tag attributes (HTML options) in terms of name-value pairs.
     * These will be rendered as the attributes of the resulting tag. The values will be HTML-encoded using [[encode()]].
     * If a value is null, the corresponding attribute will not be rendered.
     *
     * For example when using `['class' => 'my-class', 'target' => '_blank', 'value' => null]` it will result in the
     * html attributes rendered like this: `class="my-class" target="_blank"`.
     *
     * See [[renderTagAttributes()]] for details on how attributes are being rendered.
     *
     * @return string the generated HTML tag
     * @see beginTag()
     * @see endTag()
     */
    public static function tag($name, $content = '', $options = [])
    {
        if ($name === null || $name === false) {
            return $content;
        }
        $html = "<$name" . static::renderTagAttributes($options) . '>';
        return isset(\yii\helpers\Html::$voidElements[strtolower($name)]) ? $html : "$html$content</$name>";
    }

    public static function beginTag($name, $options = []) {
        if ($name === null || $name === false) {
            return '';
        }
        return "<$name" . static::renderTagAttributes($options) . '>';
    }
    
    public static function endTag($name) {
        if ($name === null || $name === false) {
            return '';
        }
        return "</$name>";
    }
    
    /**
     * Renders the HTML tag attributes.
     *
     * Attributes whose values are of boolean type will be treated as
     * [boolean attributes](http://www.w3.org/TR/html5/infrastructure.html#boolean-attributes).
     *
     * Attributes whose values are null will not be rendered.
     *
     * The values of attributes will be HTML-encoded using [[encode()]].
     *
     * The "data" attribute is specially handled when it is receiving an array value. In this case,
     * the array will be "expanded" and a list data attributes will be rendered. For example,
     * if `'data' => ['id' => 1, 'name' => 'yii']`, then this will be rendered:
     * `data-id="1" data-name="yii"`.
     * Additionally `'data' => ['params' => ['id' => 1, 'name' => 'yii'], 'status' => 'ok']` will be rendered as:
     * `data-params='{"id":1,"name":"yii"}' data-status="ok"`.
     *
     * @param array $attributes attributes to be rendered. The attribute values will be HTML-encoded using [[encode()]].
     * @return string the rendering result. If the attributes are not empty, they will be rendered
     * into a string with a leading white space (so that it can be directly appended to the tag name
     * in a tag. If there is no attribute, an empty string will be returned.
     */
    public static function renderTagAttributes($attributes)
    {
        if (count($attributes) > 1) {
            $sorted = [];
            foreach (\yii\helpers\Html::$attributeOrder as $name) {
                if (isset($attributes[$name])) {
                    $sorted[$name] = $attributes[$name];
                }
            }
            $attributes = array_merge($sorted, $attributes);
        }
        $isEncode = true;
        if (isset($attributes['encode'])) {
            $isEncode = \common\helpers\Utils::boolvalue($attributes['encode']);
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            if (is_bool($value)) {
                if ($value) {
                    $html .= " $name";
                }
            } elseif (is_array($value)) {
                if (in_array($name, \yii\helpers\Html::$dataAttributes)) {
                    foreach ($value as $n => $v) {
                        if (is_array($v)) {
                            $html .= " $name-$n='" . \yii\helpers\Json::htmlEncode($v) . "'";
                        } else {
                            $html .= " $name-$n=\"" . ($isEncode ? \yii\helpers\Html::encode($v) : $v) . '"';
                        }
                    }
                } elseif ($name === 'class') {
                    if (empty($value)) {
                        continue;
                    }
                    $_v = implode(' ', $value);
                    $html .= " $name=\"" . ($isEncode ? \yii\helpers\Html::encode($_v) : $_v) . '"';
                } elseif ($name === 'style') {
                    if (empty($value)) {
                        continue;
                    }
                    $_v = \yii\helpers\Html::cssStyleFromArray($value);
                    $html .= " $name=\"" . ($isEncode ? \yii\helpers\Html::encode($_v) : $_v) . '"';
                } else {
                    $html .= " $name='" . \yii\helpers\Json::htmlEncode($value) . "'";
                }
            } elseif ($value !== null) {
                $html .= " $name=\"" . ($isEncode ? \yii\helpers\Html::encode($value) : $value) . '"';
            }
        }

        return $html;
    }
    
    public static function popHtmlOptionsStyleAttribute(&$htmlOptions, $attribute, $defaultValue = '') {
        $value = $defaultValue;
        if (isset($htmlOptions[$attribute])) {
            $value = $htmlOptions[$attribute];
            unset($htmlOptions[$attribute]);
        }
        if (isset($htmlOptions['style'])) {
            $styleContent = $htmlOptions['style'];
            $arr = explode(";", $styleContent);
            $arrOptions = [];
            foreach ($arr as $_v0) {
                $arr2 = explode(":", $_v0);
                if (count($arr2) > 1) {
                    $arrOptions[trim($arr2[0])] = trim($arr2[1]);
                }
            }
            
            if (isset($arrOptions['$attribute'])) {
                $value = $arrOptions['$attribute'];
                unset($arrOptions['$attribute']);
            }
            
            $arrNewStyles = [];
            foreach ($arrOptions as $k => $v) {
                $arrNewStyles[] = "{$k}:{$v}";
            }
            $htmlOptions['style'] = implode(";", $arrNewStyles);
        }
        
        return $value;
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
    
    public static function getFixLayoutJs($containerId, $triggerElement = 'window') {
        $scripts = [];
        $scripts[] = "$({$triggerElement}).resize(function(e){";
        $scripts[] = "    var h = $({$triggerElement}).height()";
        $scripts[] = "    $('#{$containerId}').css({height:h,'min-height':h});";
        //$scripts[] = "    $('#{$containerId}').trigger($.Event('onResize'));";
        $scripts[] = "});";
        $scripts[] = "$(function () {";
        $scripts[] = "    $({$triggerElement}).resize();";
        $scripts[] = "});";
        
        return implode("\n", $scripts);
    }
    
}

