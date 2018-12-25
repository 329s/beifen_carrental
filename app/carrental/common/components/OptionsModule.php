<?php

namespace common\components;

class OptionsModule {
    
    static $_festivalsArray = null;
    
    public static function getCommonStatusArray() {
        return [
            \common\components\Consts::STATUS_ENABLED => \Yii::t('locale', 'Enabled'), 
            \common\components\Consts::STATUS_DISABLED => \Yii::t('locale', 'Disabled'), 
        ];
    }
    
    public static function getFestivalStatusArray() {
        return [
            \common\models\Pro_festival::STATUS_NORMAL => \Yii::t('locale', 'Enabled'), 
            \common\models\Pro_festival::STATUS_CLOSED => \Yii::t('locale', 'Disabled'), 
        ];
    }
    
    public static function getFestivalsArray() {
        if (static::$_festivalsArray === null) {
            $cdb = \common\models\Pro_festival::find();
            $cdb->where(['status' => \common\models\Pro_festival::STATUS_NORMAL]);
            $arrRows = $cdb->all();
            static::$_festivalsArray = [];
            foreach ($arrRows as $row) {
                static::$_festivalsArray[$row->id] = $row;
            }
        }
        return static::$_festivalsArray;
    }
    
    public static function checkFestivalTime($festival) {
        $arrFestivals = self::getFestivalsArray();
        foreach ($arrFestivals as $id => $obj) {
            if ($obj->id == $festival->id) {
                continue;
            }
            if ($obj->isTimeMatch($festival->start_time)) {
                return \Yii::t('locale', '{name} conflits with existing festival times!', ['name'=>$festival->getAttributeLabel('start_time')]);
            }
            if ($obj->isTimeMatch($festival->end_time)) {
                return \Yii::t('locale', '{name} conflits with existing festival times!', ['name'=>$festival->getAttributeLabel('end_time')]);
            }
        }
        return '';
    }
    
    public static function getOptionalServiceObjectsArray() {
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        $arrData = \common\models\Pro_service_price::findAllServicePrices($authOfficeId);
        return $arrData;
    }
    
    public static function getInsuranceTypesArray() {
        return [
            0 => \Yii::t('locale', 'None'),
            1 => \Yii::t('carrental', 'Force insurance'),
            2 => \Yii::t('carrental', 'Business insurance'),
        ];
    }
    
    public static function getInsuranceCaseStatusArray() {
        return [
            0 => \Yii::t('locale', 'Unknown'),
            1 => \Yii::t('carrental', 'Insurance case reported'),
            2 => \Yii::t('carrental', 'Insurance case filing'),
            3 => \Yii::t('carrental', 'Insurance case closed'),
        ];
    }
    
    public static function getActivityStatusArray() {
        return [
            \common\models\Pro_activity_image::STATUS_ENABLED => \Yii::t('locale', 'Enabled'), 
            \common\models\Pro_activity_image::STATUS_DISABLED => \Yii::t('locale', 'Disabled'), 
        ];
    }
    
    public static function getBirthdayPrice($officeId) {
        return 58;
    }
    
}