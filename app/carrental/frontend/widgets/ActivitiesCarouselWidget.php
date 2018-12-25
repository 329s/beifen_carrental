<?php

namespace frontend\widgets;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class ActivitiesCarouselWidget extends \yii\base\Widget
{
    
    private $_carouselConfig;
    
    public function init() {
        parent::init();
        
        $arrActivityImageItems = [];
        $arrActivities = \common\models\Pro_activity_image::findAll(['type'=>\common\models\Pro_activity_image::TYPE_WEB_HOME_IMAGES]);
        foreach ($arrActivities as $row) {
            $arrActivityImageItems[] = [
                'content' => \yii\helpers\Html::img($row->getImageUrl(), []),
            ];
        }

        if (empty($arrActivityImageItems)) {
            $arrActivityImageItems[] = [
                'content' => \yii\helpers\Html::img('data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==', []),
                'caption' => '<h4>Exsample 1</h4><p>Test a exsample.</p>',
            ];
            $arrActivityImageItems[] = [
                'content' => \yii\helpers\Html::img('data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==', []),
                'caption' => '<h4>Exsample 2</h4><p>Test a exsample.</p>',
            ];
            $arrActivityImageItems[] = [
                'content' => \yii\helpers\Html::img('data:image/gif;base64,R0lGODlhAQABAIAAAHd3dwAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==', []),
                'caption' => '<h4>Exsample 3</h4><p>Test a exsample.</p>',
            ];
        }

        $this->_carouselConfig = [
            'items' => $arrActivityImageItems,
            //'options' => ['style'=>"width:100%"],
            'controls' => [
                \yii\bootstrap\Html::tag('span', '', ['class'=>'glyphicon glyphicon-chevron-left', 'aria-hidden'=>'true']).\yii\bootstrap\Html::tag('span', '&lsaquo;', ['class'=>'sr-only']),
                \yii\bootstrap\Html::tag('span', '', ['class'=>'glyphicon glyphicon-chevron-right', 'aria-hidden'=>'true']).\yii\bootstrap\Html::tag('span', '&rsaquo;', ['class'=>'sr-only'])
            ],
        ];
    }
    
    public function run() {
        $urlRoot = \common\helpers\Utils::getRootUrl();
        
        $htmlArray = [];
        $htmlArray[] = \yii\helpers\Html::cssFile("{$urlRoot}assets/ui/bootstrap/extensions/carousel.css");
        $htmlArray[] = \yii\bootstrap\Carousel::widget($this->_carouselConfig);
        return implode("\n", $htmlArray);
    }
    
}
