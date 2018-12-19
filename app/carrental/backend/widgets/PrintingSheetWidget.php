<?php

namespace backend\widgets;

use yii\helpers\Html;

class PrintingSheetWidget  extends \yii\base\Widget
{
    public $data = [];
    
    public $nameAlign = 'left';
    public $valueAlign = 'left';
    
    public $rowHeight = null;
    
    public $tableOptions = ['class'=>'contract', 'border'=>'1', 'cellspacing'=>0, 'cellpadding'=>0, 'style'=>"table-layout:fixed;width:100%"];
    
    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $htmlArray = [];
        $htmlArray[] = $this->beginSheet();
        $htmlArray[] = $this->genDataRowsHtml($data);
        $htmlArray[] = $this->endSheet();
        return implode("\n", $htmlArray);
    }
    
    public function beginSheet($tableOptions = []) {
        $htmlArray = [];
        $htmlOptions = array_merge($this->tableOptions, $tableOptions);
        $htmlArray[] = Html::beginTag('table', $htmlOptions);
        $htmlArray[] = Html::beginTag('tbody');
        return implode("\n", $htmlArray);
    }
    
    public function endSheet() {
        $htmlArray = [];
        $htmlArray[] = Html::endTag('tbody');
        $htmlArray[] = Html::endTag('table');
        return implode("\n", $htmlArray);
    }
    
    public function genDataRowsHtml($data) {
        $htmlArray = [];
        foreach ($data as $row) {
            $htmlArray[] = Html::beginTag('tr');
            foreach ($row as $col) {
                $colName = Html::tag('span', ($col['name'] ? $col['name'] : '&nbsp;'), ['style'=>'font-size:12px;']);
                $colValue = null;
                $nameAlign = $this->nameAlign;
                $valueAlign = $this->valueAlign;
                if (isset($col['value'])) {
                    $colValue = ($col['value'] === '' ? '&nbsp;' : $col['value']);
                }
                if (isset($col['namealign'])) {
                    $nameAlign = $col['namealign'];
                }
                if (isset($col['valuealign'])) {
                    $valueAlign = $col['valuealign'];
                }
                $nameExtraStyle = '';
                $valueExtraStyle = '';
                if (isset($col['bold'])) {
                    $nameExtraStyle = "font-weight:bold;";
                }
                $rowHeight = $this->rowHeight;
                if (isset($col['height'])) {
                    $rowHeight = $col['height'];
                }
                if ($rowHeight) {
                    $nameExtraStyle .= "height:{$rowHeight};";
                }
                if (isset($col['nameverticalalign'])) {
                    $nameExtraStyle .= "vertical-align:{$col['nameverticalalign']};";
                }
                if (isset($col['valueverticalalign'])) {
                    $valueExtraStyle .= "vertical-align:{$col['valueverticalalign']};";
                }
                if (isset($col['valuestyle'])) {
                    $valueExtraStyle .= $col['valuestyle'].';';
                }
                if (isset($col['allowwrap'])) {
                    if ($colValue) {
                        $valueExtraStyle .= "word-break:break-all;word-wrap:break-word;white-space:normal;";
                    }
                    else {
                        $nameExtraStyle .= "word-break:break-all;word-wrap:break-word;white-space:normal;";
                    }
                }
                $nameOptions = ['style'=>"text-align:{$nameAlign};nowrap:nowrap;{$nameExtraStyle}"];
                $valueOptions = ['style'=>"text-align:{$valueAlign};{$valueExtraStyle}"];
                if (isset($col['colspan'])) {
                    if ($colValue !== null) {
                        $valueOptions['colspan'] = $col['colspan'];
                    }
                    else {
                        $nameOptions['colspan'] = $col['colspan'];
                    }
                }
                if (isset($col['namespan'])) {
                    $nameOptions['colspan'] = $col['namespan'];
                }
                if (isset($col['rowspan'])) {
                    if ($colValue !== null) {
                        $valueOptions['rowspan'] = $col['rowspan'];
                    }
                    else {
                        $nameOptions['rowspan'] = $col['rowspan'];
                    }
                }
                if (isset($col['namerowspan'])) {
                    $nameOptions['rowspan'] = $col['namerowspan'];
                }
                if (isset($col['namewidth'])) {
                    $nameOptions['width'] = $col['namewidth'];
                }
                if (isset($col['valuewidth'])) {
                    $valueOptions['width'] = $col['valuewidth'];
                }

                $htmlArray[] = Html::tag('td', $colName, $nameOptions);
                if ($colValue !== null) {
                    $htmlArray[] = Html::tag('td', Html::tag('span', $colValue, ['style'=>"font-size:12px;"]), $valueOptions);
                }
            }
            $htmlArray[] = Html::endTag('tr');
        }
        
        return implode("\n", $htmlArray);
    }
    
}
