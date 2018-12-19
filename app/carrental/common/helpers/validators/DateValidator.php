<?php

namespace common\helpers\validators;

/**
 * Description of DateValidator
 *
 * @author kevin
 */
class DateValidator extends \yii\validators\DateValidator
{
    
    public $defaultTimepart;
    
    public $adaptTimestamp = false;
    
    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute) {
        if ($this->adaptTimestamp) {
            $this->timestampAttribute = $attribute;
        }
        return parent::validateAttribute($model, $attribute);
    }
    
}
