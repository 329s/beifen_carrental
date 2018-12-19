<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers\validators;

/**
 * Description of DatetimeValidator
 *
 * @author kevin
 */
class DatetimeValidator extends DateValidator
{
    
    public $type = \yii\validators\DateValidator::TYPE_DATETIME;
    
}
