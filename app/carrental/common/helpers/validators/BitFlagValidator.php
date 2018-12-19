<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers\validators;

/**
 * Description of BitFlagValidator
 *
 * @author kevin
 */
class BitFlagValidator extends \yii\validators\Validator
{
    
    public $list;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = \Yii::t('yii', '{attribute} is invalid.');
        }
        if ($this->list === null) {
            throw new \yii\base\InvalidParamException("Missing parameter 'list'.");
        }
        elseif (!is_array($this->list)) {
            throw new \yii\base\InvalidParamException("The parameter 'list' should be array type.");
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if (is_string($value)) {
            $value = explode(",", $value);
        }
        $values = [];
        foreach ((array)$value as $i => $v) {
            if (!is_integer($v) && !preg_match('/^\d+$/', $v)) {
                $this->addError($model, $attribute, $this->message, []);
                break;
            }
            elseif (!isset($this->list[intval($v)])) {
                $this->addError($model, $attribute, $this->message, []);
                break;
            }
            $values[] = intval($v);
        }

        $model->$attribute = $values;
    }

}
