<?php

namespace common\helpers;

/**
 * Description of BootstrapAdminHtml
 *
 * @author kevin
 */
class BootstrapAdminHtml extends BootstrapHtml
{
    
    public static function accordionList($accordions = [], $htmlOptions = [])
    {
        $htmlArray = [];
        $htmlArray[] = parent::beginTag('ul', ['class'=>'sidebar-menu']);
        if (isset($htmlOptions['title'])) {
            $htmlArray[] = parent::tag('li', $htmlOptions['title'], ['class'=>'header']);
            unset($htmlOptions['title']);
        }
        
        foreach ($accordions as $k => $row) {
            if (is_string($k)) {
                continue;
            }
            $title = (isset($row['title']) ? $row['title'] : (isset($row['name']) ? $row['name'] : '') );
            $content = isset($row['data']) ? $row['data'] : null;
            $htmlOptions = (isset($row['htmlOptions']) ? $row['htmlOptions'] : []);
            
            $exClass = '';
            $icon = (isset($row['icon']) ? $row['icon'] : 'fa-folder');
            $labels = (isset($row['label']) ? $row['label'] : null);
            if (isset($row['selected'])) {
                if (\common\helpers\Utils::boolvalue($row['selected'])) {
                    $exClass .= ' active';
                }
            }
            $htmlOptions['class'] = "treeview{$exClass}";
            $htmlOptions['encode'] = false;
            
            $href = '#';
            $hasContent = false;
            
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
                if (isset($content['url'])) {
                    $href = $content['url'];
                    unset($content['url']);
                }
                elseif (isset($content['href'])) {
                    $href = $content['href'];
                    unset($content['href']);
                }
                
                if (isset($content['data'])) {
                    $childData = $content['data'];
                    $content = $childData;
                }
                
                if ($type == 'accordion') {
                    $content = self::accordionList($content, $childHtmlOptions);
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
                    $content = self::accordionTreeList($tabsArr, $childHtmlOptions);
                }
                else {
                    $content = self::accordionTreeList($content, $childHtmlOptions);
                }
                
                if (!empty($content)) {
                    $hasContent = true;
                }
            }
            else if (!empty($content)) {
                $content = strval($content);
                $hasContent = true;
            }
            
            $htmlArray[] = parent::beginTag('li', $htmlOptions);
            $htmlArray[] = self::accordionLabelNodeElement($title, ['href'=>$href, 'icon'=>$icon, 'label'=>$labels, 'hasChildren'=>$hasContent]);
            
            if ($hasContent) {
                $htmlArray[] = $content;
            }
            
            $htmlArray[] = parent::endTag('li');
        }
        
        $htmlArray[] = parent::endTag('ul');
        
        return implode("\n", $htmlArray);
    }
    
    public static function accordionLabelNodeElement($title, $options = [])
    {
        $htmlArray = [];
        
        $href = isset($options['href']) ? $options['href'] : '#';
        $icon = isset($options['icon']) ? $options['icon'] : 'fa-folder';
        $labels = isset($options['label']) ? $options['label'] : null;
        $hasChildren = isset($options['hasChildren']) ? $options['hasChildren'] : false;
        
        if (substr($icon, 0, 3) != 'fa-') {
            $icon = ' fa-folder';
        }
        
        $htmlArray[] = parent::beginTag('a', ['href'=>$href]);
        $htmlArray[] = parent::tag('i', '', ['class'=>"fa {$icon}"]);
        $htmlArray[] = parent::tag('span', $title);
        if (!empty($labels) || $hasChildren) {
            $htmlArray[] = parent::beginTag('span', ['class'=>'pull-right-container']);
            if (!empty($labels)) {
                if (!is_array($labels)) {
                    $labels = ['value'=>strval($labels), 'color'=>'default'];
                }
                elseif (!isset($labels[0])) {
                    $labels = [$labels];
                }
                foreach($labels as $o) {
                    $lbl = '';
                    $color = 'default';
                    if (is_array($o)) {
                        $lbl = isset($o['value']) ? $o['value'] : '';
                        if (isset($o['color'])) { $color = $o['color']; }
                    }
                    else {
                        $lbl = strval($o);
                    }
                    $htmlArray[] = parent::tag('small', $lbl, ['class'=>"label pull-right bg-{$color}"]);
                }
            }
            if ($hasChildren) {
                $htmlArray[] = parent::tag('i', '', ['class'=>'fa fa-angle-left pull-right']);
            }
            $htmlArray[] = parent::endTag('span');
        }
        $htmlArray[] = parent::endTag('a');
        
        return implode("\n", $htmlArray);
    }
    
    public static function accordionTreeList($treeArray, $htmlOptions)
    {
        $htmlOptions['class'] = "treeview-menu";
        $htmlOptions['encode'] = false;
        
        $htmlArray = [];
        $htmlArray[] = parent::beginTag('ul', $htmlOptions);
        
        foreach ($treeArray as $k => $row) {
            if (is_string($k)) {
                continue;
            }
            $htmlOptions = [];
            
            if (is_array($row)) {
                if (isset($row['htmlOptions'])) {
                    $htmlOptions = $row['htmlOptions'];
                    unset($row['htmlOptions']);
                }
                
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            else {
                $content = self::_genTreeChildren($row, $htmlOptions);
            }
            
            $htmlArray[] = $content;
        }
        
        $htmlArray[] = parent::endTag('ul');
        
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
                
                $htmlArray[] = parent::beginTag('li');
                $htmlArray[] = self::accordionLabelNodeElement($name, ['href'=>isset($treeList['url'])?$treeList['url']:'#', 
                    'icon'=>isset($treeList['icon'])?$treeList['icon']:'fa-folder', 
                    'label'=>isset($treeList['tip'])?$treeList['tip']:null, 
                    'hasChildren'=>true]);
                
                $htmlArray[] = parent::beginTag('ul', ['class'=>'treeview-menu']);
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
                
                $htmlArray[] = parent::endTag('ul');
                $htmlArray[] = parent::endTag('li');
                $content = implode("\n", $htmlArray);
                //return self::accordionList($treeList, $htmlOptions);
            }
            else {
                $type = isset($treeList['type']) ? $treeList['type'] : '';
                $iconName = isset($treeList['icon']) ? $treeList['icon'] : 'fa-circle-o';
                $htmlOptions['encode'] = false;
                $url = isset($treeList['url']) ? $treeList['url'] : '';
                $target = isset($treeList['target']) ? $treeList['target'] : '';
                
                if (substr($iconName, 0, 3) != 'fa-') {
                    $iconName = 'fa-circle-o';
                }
                
                if ($type == 'navTab') {
                    $tabPanelId = isset($treeList['tabPanelId']) ? $treeList['tabPanelId'] : '';
                    //$isIframe = isset($treeList['isIframe']) ? \common\helpers\Utils::boolvalue($treeList['isIframe']) : false;
                    
                    $funcName = 'easyuiFuncNavTabAddHref';
                    //if ($isIframe) {
                    //    $funcName = 'easyuiFuncNavTabAddIframe';
                    //}
                    
                    if (empty($target)) {
                        $htmlOptions['href'] = $url;
                    }
                    else {
                        $htmlOptions['href'] = "javascript:void(0);";
                        $htmlOptions['onclick'] = "$.custom.bootstrap.loadElement('#{$target}', '{$url}');";
                    }
                    //$htmlOptions['href'] = 'javascript:void(0);';
                    //$htmlOptions['onclick'] = "{$funcName}('#{$target}', '{$name}', '{$url}', '{$tabPanelId}');";
                    $htmlOptions['encode'] = false;
                    
                    // for debug
                    $_debug = isset(\Yii::$app->params['debugHrefContent']) ? \Yii::$app->params['debugHrefContent'] : false;
                    if ($_debug) {
                        $htmlOptions['ondblclick'] = "easyuiFuncNavTabAddIframe('#{$target}', '{$name}', '{$url}', '{$tabPanelId}');";
                    }
                }
                else {
                    if (empty($url)) {
                        $htmlOptions['href'] = '#';
                    }
                    else {
                        $htmlOptions['href'] = $url;
                    }
                    if (!empty($target)) {
                        $htmlOptions['target'] = $target;
                    }
                }
                
                $content = parent::tag('li', parent::tag('a', parent::tag('i', '', ['class'=>"fa {$iconName}"]).$name, $htmlOptions));
            }
        }
        else {
            $content = strval($treeList);
            if ($content == strip_tags($content)) {
                $content = parent::tag('li', $content);
            }
        }
        return $content;
    }
    
    public static function beginSidebar($htmlOptions = []) 
    {
        if (!isset($htmlOptions['class'])) {
            $htmlOptions['class'] = 'main-sidebar';
        }
        $options1 = ['class'=>isset($htmlOptions['class'])?$htmlOptions['class']:'main-sidebar'];
        $options2 = ['class'=>isset($htmlOptions['sectionClass'])?$htmlOptions['sectionClass'] : 'sidebar'];
        $htmlArray = [
            parent::beginTag('aside', $options1),
            parent::beginTag('section', $options2),
        ];
        return implode("\n", $htmlArray);
    }
    
    public static function endSidebar()
    {
        $htmlArray = [
            parent::endTag('section'),
            parent::endTag('aside'),
        ];
        return implode("\n", $htmlArray);
    }
    
    public static function renderNotificationMessageElement($options = [])
    {
        $tagText = \yii\helpers\ArrayHelper::remove($options, 'tag', '');
        $liId = \yii\helpers\ArrayHelper::remove($options, 'id', '');
        $isHide = \yii\helpers\ArrayHelper::remove($options, 'hide', false);
        $liOptions = [];
        if (!empty($liId)) {
            $liOptions['id'] = $liId;
        }
        if ($isHide) {
            $liOptions['style'] = "display:none";
        }
        $htmlArray = [];
        $htmlArray[] = parent::beginTag('li', $liOptions);
        $htmlArray[] = parent::beginTag('a', array_merge(['href'=>'#'], isset($options['linkOptions'])?$options['linkOptions']:[]));
        if (isset($options['image'])) {
            $img = $options['image'];
            $imgOptions = ['class'=>'img-circle'];
            if (is_array($img)) {
                if (isset($img['options'])) {
                    $imgOptions = array_merge($imgOptions, $img['options']);
                }
                $img = isset($img['src']) ? $img['src'] : (isset($img['url']) ? $img['url'] : '');
            }
            else {
                $img = strval($img);
            }
            $htmlArray[] = parent::tag('div', \yii\helpers\Html::img($img, $imgOptions), ['class'=>'pull-left']);
        }
        if (isset($options['title']) && isset($options['time']) && isset($options['description'])) {
            $htmlArray[] = parent::beginTag('h4');
            $htmlArray[] = $options['title'].parent::tag('small', parent::tag('i', '', ['class'=>'fa fa-clock-o']).$options['time']);
            $htmlArray[] = parent::endTag('h4');
            $htmlArray[] = parent::tag('p', $options['description']);
        }
        elseif (isset($options['message'])) {
            $icon = isset($options['icon']) ? $options['icon'] : 'fa-users text-aqua';
            $htmlArray[] = parent::tag('i', '', ['class'=>"fa {$icon}"]).$options['message'];
        }
        $htmlArray[] = parent::tag('span', empty($tagText) ? '' : $tagText, ['class'=>'badge']);
        $htmlArray[] = parent::endTag('a');
        $htmlArray[] = parent::endTag('li');
        
        return implode("\n", $htmlArray);
    }
    
    public static function progressBar($percent, $options = []) {
        $size = isset($options['size']) ? $options['size'] : 'xs';
        $color = isset($options['color']) ? $options['color'] : 'aqua';
        $text = isset($options['text']) ? $options['text'] : '';
        $htmlArray = [];
        $htmlArray[] = parent::beginTag('div', ['class'=>"progress $size"]);
        $htmlArray[] = parent::beginTag('div', ['class'=>"progress-bar progress-bar-{$color}", 'role'=>'progressbar', 'style'=>"width:{$percent}%", 'aria-valuenow'=>"{$percent}", 'aria-valuemin'=>'0', 'aria-valuemax'=>'100']);
        $htmlArray[] = parent::tag('span', $percent.' '.$text, ['class'=>'sr-only']);
        $htmlArray[] = parent::endTag('div');
        $htmlArray[] = parent::endTag('div');
        return implode("\n", $htmlArray);
    }
    
    public static function getRegisterFixFluidWidowLayoutJs($containerId) {
        $containerDefaultOffset = 80;
        
        $scripts = [];
        $scripts[] = "$(window).resize(function(e){";
        $scripts[] = "    var _offset_ = {$containerDefaultOffset};";
        $scripts[] = "    var h = $(window).height()-_offset_;";
        $scripts[] = "    $('#{$containerId}').css({height:h,'min-height':h});";
        $scripts[] = "    $('#{$containerId}').trigger($.Event('onResize'));";
        $scripts[] = "});";
        $scripts[] = "$(function () {";
        $scripts[] = "    $(window).resize();";
        $scripts[] = "});";
        
        return implode("\n", $scripts);
    }
    
}
