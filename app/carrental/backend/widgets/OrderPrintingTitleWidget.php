<?php

namespace backend\widgets;

use yii\helpers\Html;
use common\helpers\CMyHtml;

class OrderPrintingTitleWidget extends \yii\base\Widget
{
    public $title = '';
    public $subtitle = '';
    public $logoUrl = '';
    public $serial = '';
    
    public $titleOptions;
    public $subTitleOptions;
    
    private $_idPrefix;
    private $_autoId;
    
    /**
     * Initializes the detail view.
     * This method will initialize required property values.
     */
    public function init()
    {
        parent::init();
        
        $this->_autoId = CMyHtml::genID();
        $this->_idPrefix = self::$autoIdPrefix.'_'.CMyHtml::getIDPrefix();
        
        if (empty($this->logoUrl)) {
            $urlRoot = \common\helpers\Utils::getRootUrl();
            $this->logoUrl = "{$urlRoot}assets/images/logo/yikazc_black.png";
        }
        
        if (!isset($this->titleOptions) || empty($this->titleOptions)) {
            $this->titleOptions = ['style'=>"font-size:14px"];
        }
        if (!isset($this->subTitleOptions) || empty($this->subTitleOptions)) {
            $this->subTitleOptions = [];
        }
    }

    /**
     * Renders the detail view.
     * This is the main entry of the whole detail view rendering.
     */
    public function run()
    {
        $htmlArray = [];
        $htmlArray[] = Html::tag('h1', $this->title, array_merge(['class'=>'contract'], $this->titleOptions));
        $marginTop = -33;
        if (!empty($this->subtitle)) {
            $htmlArray[] = Html::tag('h2', $this->subtitle, array_merge(['class'=>'contract'], $this->subTitleOptions));
            $marginTop = -60;
        }
        
        $barCodeId = "{$this->_idPrefix}barcode_order_{$this->_autoId}";
        
        $htmlArray[] = Html::beginTag('div', ['style'=>"width:100%;height:66px;display:block;vertical-align:center;margin:{$marginTop}px 0px 0px 0px;float:left"]);
        $htmlArray[] = Html::tag('div', Html::img($this->logoUrl, ['style'=>"width:180px;vertical-align:center"]), ['style'=>"display:block;vertical-align:center;float:left;height:66px"]);
        $htmlArray[] = Html::beginTag('div', ['style'=>"display:block;align:center;float:center;text-align:center"]);
        $htmlArray[] = Html::endTag('div');
        $htmlArray[] = Html::tag('div', '', ['id'=>$barCodeId, 'style'=>"display:block;float:right;height:66px"]);
        $htmlArray[] = Html::endTag('div');
        
        $htmlArray[] = \common\helpers\BarcodeGenerator::widget([
            'elementId' => $barCodeId,
            'type' => 'code39',
            'value' => $this->serial,
        ]);
        
        return implode("\n", $htmlArray);
    }
}
