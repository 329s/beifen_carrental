<?php

namespace common\widgets;

/**
 * Description of GridView
 *
 * @author kevin
 */
class GridView extends \yii\grid\GridView
{
    static $idPrefix = 'w_bs_grid_';
    
    public $tools = [];
    
    /**
     *
     * @var string the jquery selector
     */
    public $containerSelector = null;
    
    public $tableHeadOptions = ['style'=>''];
    
    public $tableBodyOptions = ['style'=>"overflow-y: scroll;overflow-x:hidden;"];
    
    public $toolbarOptions = ['class'=>'btn-group'];
    
    public $enableFulidLayout = false;
    
    public $filterInTable = true;
    
    public $filterMethod = 'get';
    
    public $layout = "{toolbar}{items}\n{summary}\n{pager}";
    
    public $linkTemplate;
    
    public $onclickOpenUrlTemplate;
    
    private $_tableContainerId;
    private $_autoId;

    public function init() {
        $this->_autoId = \common\helpers\BootstrapHtml::genID();
        if (!isset($this->options['id'])) {
            $this->options['id'] = static::$idPrefix.$this->_autoId;
        }
        if ($this->containerSelector == null) {
            $this->containerSelector = '#'.\common\helpers\BootstrapHtml::MAIN_CONTENT_ID;
        }
        if (!$this->dataColumnClass) {
            $this->dataColumnClass = DataColumnExtend::className();
        }
        $this->_tableContainerId = static::$idPrefix.'_wrapper'.$this->_autoId;
        $this->pager['class'] = LinkPager::className();
        $this->pager['containerSelector'] = $this->containerSelector;
        //$this->tableOptions['style'] = "overflow:hidden;";
        if (!isset($this->pager['options'])) {
            $this->pager['options'] = ['class'=>'pagination'];
        }
        if ($this->dataProvider instanceof \common\helpers\ExtendActiveDataProvider) {
            $filterParams = $this->dataProvider->getFilterParams();
            if (!empty($filterParams)) {
                $this->pager['filterParams'] = $filterParams;
            }
        }
        if (!$this->onclickOpenUrlTemplate) {
            $this->onclickOpenUrlTemplate = '$.custom.bootstrap.yiigridview.queryUrl('.($this->containerSelector?"'{$this->containerSelector}'":"undefined").', \'{url}\')';
        }
        if (!$this->linkTemplate) {
            $this->linkTemplate = '<a href="javascript:void(0)" onclick="'.$this->onclickOpenUrlTemplate.'">{label}</a>';
        }
        \yii\bootstrap\Html::addCssClass($this->summaryOptions, 'pull-left');
        \yii\bootstrap\Html::addCssClass($this->pager['options'], 'pull-right');
        if ($this->enableFulidLayout) {
            \yii\bootstrap\Html::addCssStyle($this->pager['options'], ['margin'=>'0px']);
        }
        parent::init();
        $this->dataProvider->setSort(['class'=> Sorter::className(), 'containerSelector'=>$this->containerSelector]);
    }
    
    public function run() {
        parent::run();
        $content = ob_get_clean();
        $id = $this->options['id'];
        $options = \yii\helpers\Json::htmlEncode($this->getClientOptions());
        $scripts = [];
        $scripts[] = "$(function () {";
        $scripts[] = "jQuery('#{$id}').yiiGridView({$options});";
        if ($this->containerSelector) {
            $filterMethod = (is_string($this->filterMethod) && !empty($this->filterMethod)) ? "'{$this->filterMethod}'" : 'undefined';
            $scripts[] = "jQuery('#{$id}').bind('beforeFilter', function(event) { $.custom.bootstrap.yiigridview.applyFilter($(this), event, '{$this->containerSelector}', {$filterMethod}); } );";
        }
        if ($this->enableFulidLayout) {
            $containerSelector = $this->containerSelector ? "'".$this->containerSelector."'" : 'window';
            
            $scripts[] = "$({$containerSelector}).bind('onResize', function(event) {";
            $scripts[] = "  var containerH = $({$containerSelector}).height();";
            $scripts[] = "  var innerElements = [";
            //$scripts[] = "    $('#{$id} .summary'),";
            $scripts[] = "    $('#{$id} .pagination'),";
            //$scripts[] = "    $('#{$id} .summary')";
            $scripts[] = "  ];";
            $scripts[] = "  var innerH = 0;";
            $scripts[] = "  for(var i in innerElements) { if (innerElements.length) { innerH += innerElements[i].outerHeight(true); } }";
            $scripts[] = "  $('#{$id}').css({height:containerH});";
            $scripts[] = "  $('#{$this->_tableContainerId}').css({height:containerH - innerH});";
            $scripts[] = "});";
            $scripts[] = "$({$containerSelector}).trigger($.Event('onResize'));";
        }
        $scripts[] = "});";
        
        $htmlArray = [];
        //$htmlArray[] = \common\helpers\BootstrapHtml::beginPanel('', ['style'=>"height:100%;"]);
        //$htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'table-responsive']);
        $htmlArray[] = $content;
        //$htmlArray[] = \yii\bootstrap\Html::endTag('div');
        //$htmlArray[] = \common\helpers\BootstrapHtml::endPanel();
        $htmlArray[] = \yii\bootstrap\Html::script(implode("\n", $scripts), ['type'=>"text/javascript"]);
        
        return implode("\n", $htmlArray); 
    }
    
    /**
     * Renders the data models for the grid view.
     */
    public function renderItems()
    {
        $caption = $this->renderCaption();
        $columnGroup = $this->renderColumnGroup();
        $tableHeader = $this->showHeader ? $this->renderTableHeader() : false;
        $tableBody = $this->renderTableBody();
        $tableFooter = $this->showFooter ? $this->renderTableFooter() : false;
        $content = array_filter([
            $caption,
            $columnGroup,
            $tableHeader,
            $tableFooter,
            $tableBody,
        ]);
        
        $tableContent =  \yii\bootstrap\Html::tag('table', implode("\n", $content), $this->tableOptions);
        //return \common\helpers\BootstrapHtml::panel($this->renderSearches(), ['id'=> $this->_tableContainerId, 'style'=>"overflow:auto"], $tableContent);
        return \yii\bootstrap\Html::tag('div', $tableContent,['id'=> $this->_tableContainerId, 'class'=>'table-responsive', 'style'=>"overflow:auto;"]);
    }
    
    public function renderSearches()
    {
        if (!$this->filterInTable && $this->filterModel !== null) {
            $cells = [];
            $emptyCell = \yii\bootstrap\Html::tag('td', $this->emptyCell);
            foreach ($this->columns as $column) {
                /* @var $column \commen\widgets\DataColumnExtend */
                $cell = $column->renderFilterCell();
                if ($cell != $emptyCell) {
                    $cells[] = \yii\bootstrap\Html::tag('td', ($column->label !== '' ? $column->label : $column->getHeaderCellLabel()), []);
                    $cells[] = $cell;
                }
                
            }
            
            if (!empty($cells)) {
                $htmlArray = [];
                $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'panel']);
                $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'pull-right']);
                $htmlArray[] = \yii\bootstrap\Html::beginTag('table', ['class'=>'table']);
                $htmlArray[] = \yii\bootstrap\Html::tag('tbody', \yii\bootstrap\Html::tag('tr', implode("\n", $cells)), []);
                $htmlArray[] = \yii\bootstrap\Html::endTag('table');
                $htmlArray[] = \yii\bootstrap\Html::endTag('div');
                $htmlArray[] = \yii\bootstrap\Html::endTag('div');
                
                return implode("\n", $htmlArray);
            }
            //return Html::tag('tr', implode('', $cells), $this->filterRowOptions);
        }
        return '';
    }
    
    public function renderToolbar()
    {
        $buttons = [];
        foreach ($this->tools as $button) {
            if (is_array($button)) {
                $visible = \yii\helpers\ArrayHelper::remove($button, 'visible', true);
                if ($visible === false) {
                    continue;
                }

                $button['view'] = $this->getView();
                if (!isset($button['encodeLabel'])) {
                    $button['encodeLabel'] = $this->encodeLabels;
                }
                
                if (!isset($button['class'])) {
                    if (isset($button['dropdown'])) {
                        $button['class'] = \yii\bootstrap\ButtonDropdown::className();
                    }
                    elseif (isset($button['items'])) {
                        $button['class'] = \yii\widgets\Menu::className();
                        if (!isset($button['linkTemplate'])) {
                            $button['linkTemplate'] = $this->linkTemplate;
                        }
                    }
                    else {
                        $button['class'] = \yii\bootstrap\Button::className();
                    }
                }
                $buttons[] = '';
            } else {
                $buttons[] = $button;
            }
        }
        return '';
    }
    
    /**
     * @inheritdoc
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{searches}':
                return $this->renderSearches();
            case '{toolbar}':
                return $this->renderToolbar();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * Renders the table header.
     * @return string the rendering result.
     */
    public function renderTableHeader()
    {
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content = \yii\bootstrap\Html::tag('tr', implode('', $cells), $this->headerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_HEADER) {
            $content = $this->renderFilters() . $content;
        } elseif ($this->filterPosition === self::FILTER_POS_BODY) {
            $content .= $this->renderFilters();
        }
        
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::beginTag('thead', $this->tableHeadOptions);
        $htmlArray[] = $content;
        $htmlArray[] = \yii\bootstrap\Html::endTag('thead');
        return implode("\n", $htmlArray);
    }
    
    public function renderTableBody() {
        $models = array_values($this->dataProvider->getModels());
        $keys = $this->dataProvider->getKeys();
        $rows = [];
        foreach ($models as $index => $model) {
            $key = $keys[$index];
            if ($this->beforeRow !== null) {
                $row = call_user_func($this->beforeRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }

            $rows[] = $this->renderTableRow($model, $key, $index);

            if ($this->afterRow !== null) {
                $row = call_user_func($this->afterRow, $model, $key, $index, $this);
                if (!empty($row)) {
                    $rows[] = $row;
                }
            }
        }

        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::beginTag('tbody', $this->tableBodyOptions);
        if (empty($rows)) {
            $colspan = count($this->columns);
            $htmlArray[] = \yii\bootstrap\Html::tag('tr', \yii\bootstrap\Html::tag('td', $this->renderEmpty(), ['colspan'=>$colspan]));
        } else {
            $htmlArray[] = implode("\n", $rows);
        }
        $htmlArray[] = \yii\bootstrap\Html::endTag('tbody');
        
        return implode("\n", $htmlArray);
    }
    
    /**
     * Renders the filter.
     * @return string the rendering result.
     */
    public function renderFilters()
    {
        if ($this->filterInTable) {
            return parent::renderFilters();
        }
        return '';
    }

}
