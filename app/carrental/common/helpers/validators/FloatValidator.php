<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers\validators;

/**
 * Description of FloatValidator
 *
 * @author kevin
 */
class FloatValidator extends \yii\validators\NumberValidator
{
    
    public $integerOnly = false;
    
    public $factor = 1000;
    
}
