<?php

namespace common\widgets;
/* 
 * Carousel widget
 */
class CarouselWidget extends \yii\bootstrap\Widget
{
    
    /**
     *
     * @var array
     *   item contains:
     *     image: image url
     *     alt: image alt
     *     container: content above the image
     */
    public $items = [];
    
    public function init()
    {
        parent::init();
        
        if (empty($this->items)) {
            $this->items = [
                'image' => "data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==",
            ];
        }
    }
    
    public function run()
    {
        $id = 'w_carousel_'.$this->getId();
        $this->setId($id);
        
        $indicatorsHtmlArray = [];
        $boxHtmlArray = [];
        
        $i = 0;
        foreach ($this->items as $item) {
            $indicatorOptions = ['data-target'=>"#{$id}", 'data-slide-to'=>$i];
            $itemOptions = ['class'=>'item'.($i==0 ? ' active' : '')];
            if ($i == 0) {
                $indicatorOptions['class'] = 'active';
            }
            $indicatorsHtmlArray[] = \yii\bootstrap\Html::tag('li', '', $indicatorOptions);
            
            $itemHtmlArray = [];
            $itemHtmlArray[] = \yii\bootstrap\Html::beginTag('div', $itemOptions);
            $imgSrc = (isset($item['image']) ? $item['image'] : "data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==");
            $imgAlt = (isset($item['alt']) ? $item['alt'] : '');
            $imgOptions = ['alt'=>$imgAlt];
            $itemHtmlArray[] = \yii\bootstrap\Html::img($imgSrc, $imgOptions);
            $itemHtmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'container']);
            $itemHtmlArray[] = \yii\bootstrap\Html::beginTag('div', ['class'=>'carousel-caption']);
            if (isset($item['container'])) {
                $itemHtmlArray[] = $item['container'];
            }
            $itemHtmlArray[] = \yii\bootstrap\Html::endTag('div');
            $itemHtmlArray[] = \yii\bootstrap\Html::endTag('div');
            $itemHtmlArray[] = \yii\bootstrap\Html::endTag('div');
            
            $boxHtmlArray[] = implode("\n", $itemHtmlArray);
            
            $i++;
        }
        
        $htmlArray = [];
        $htmlArray[] = \yii\bootstrap\Html::beginTag('div', ['id'=>$id, 'class'=>'carousel slide', 'data-ride'=>'carousel']);
        $htmlArray[] = \yii\bootstrap\Html::tag('ol', implode("\n", $indicatorsHtmlArray, ['class'=>'carousel-indicators']));
        $htmlArray[] = \yii\bootstrap\Html::tag('div', implode("\n", $boxHtmlArray, ['class'=>'carousel-inner', 'role'=>'listbox']));
        $htmlArray[] = \yii\bootstrap\Html::beginTag('a', ['href'=>"#{$id}", 'class'=>'left carousel-control', 'role'=>'button', 'data-slide'=>'prev']);
        $htmlArray[] = \yii\bootstrap\Html::tag('span', '', ['class'=>'glyphicon glyphicon-chevron-left', 'aria-hidden'=>'true']);
        $htmlArray[] = \yii\bootstrap\Html::tag('span', 'Previous', ['class'=>'sr-only']);
        $htmlArray[] = \yii\bootstrap\Html::endTag('a');
        $htmlArray[] = \yii\bootstrap\Html::beginTag('a', ['href'=>"#{$id}", 'class'=>'right carousel-control', 'role'=>'button', 'data-slide'=>'next']);
        $htmlArray[] = \yii\bootstrap\Html::tag('span', '', ['class'=>'glyphicon glyphicon-chevron-right', 'aria-hidden'=>'true']);
        $htmlArray[] = \yii\bootstrap\Html::tag('span', 'Next', ['class'=>'sr-only']);
        $htmlArray[] = \yii\bootstrap\Html::endTag('a');
        
        $htmlArray[] = \yii\bootstrap\Html::endTag('div');
        
        echo implode("\n", $htmlArray);
    }
    
}
