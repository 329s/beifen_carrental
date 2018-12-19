<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\widgets;

/**
 * Description of ActiveFieldWithoutGroupWrapper
 *
 * @author kevin
 */
class ActiveFieldWithoutGroupWrapper extends \yii\bootstrap\ActiveField
{
    //put your code here
    
    public function begin() {
        parent::begin();
        return '';
    }
    
    public function end() {
        parent::end();
        return '';
    }
    
}
