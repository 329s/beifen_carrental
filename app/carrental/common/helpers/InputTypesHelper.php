<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

/**
 * Description of InputTypesHelper
 *
 * @author kevin
 */
class InputTypesHelper extends \yii\base\Component
{
    const TYPE_TEXT = 1;
    const TYPE_INTEGER = 2;
    const TYPE_DOUBLE = 3;
    const TYPE_EMAIL = 4;
    const TYPE_PASSWORD = 5;
    const TYPE_TELEPHONE = 6;
    const TYPE_TEXTAREA = 7;
    const TYPE_DATE = 11;
    const TYPE_DATETIME = 12;
    const TYPE_TIME = 13;
    const TYPE_DROPDOWN_LIST = 21;
    const TYPE_DROPDOWN_TREE = 22;
    const TYPE_CHECKBOX = 23;
    const TYPE_CHECKBOX_LIST = 24;
    const TYPE_RADIO = 25;
    const TYPE_RADIO_LIST = 26;
    const TYPE_FILE = 31;
    const TYPE_IMAGE = 32;
    const TYPE_CAPTCHA = 41;
    const TYPE_URL = 42;
    const TYPE_IP = 43;
    
    const TYPE_NOP = -1;
    const TYPE_GROUP = -2;
    const TYPE_HIDDEN = -3;
    
    private static $builtInTypes = null;

    public function init() {
        parent::init();

    }
    
    public static function getBuiltInTypes() {
        if (!static::$builtInTypes) {
            static::initBuildInTypes();
        }
        return static::$builtInTypes;
    }
    
    protected static function initBuildInTypes() {
        static::$builtInTypes = [
            'boolean' => ['type'=>static::TYPE_RADIO_LIST, 'data'=>['1'=>\Yii::t('locale', 'Yes'), '0'=>\Yii::t('locale', 'No')]],
            'captcha' => static::TYPE_CAPTCHA,
            'date' => static::TYPE_DATE,
            'datetime' => static::TYPE_DATETIME,
            'time' => static::TYPE_TIME,
            'double' => static::TYPE_DOUBLE,
            'email' => static::TYPE_EMAIL,
            'file' => static::TYPE_FILE,
            'image' => static::TYPE_IMAGE,
            'integer' => static::TYPE_INTEGER,
            'number' => static::TYPE_DOUBLE,
            'string' => static::TYPE_TEXT,
            'url' => static::TYPE_URL,
            'ip' => static::TYPE_IP,
        ];
    }
    
}
