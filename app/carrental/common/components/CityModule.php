<?php

namespace common\components;

class CityModule
{
    public static function getRegionTypesArray() {
        return [
            \common\models\Pro_city::TYPE_PROVINCE => \Yii::t('locale', 'Province'), 
            \common\models\Pro_city::TYPE_CITY => \Yii::t('locale', 'City'), 
            \common\models\Pro_city::TYPE_SUB => \Yii::t('locale', 'Borough'),
        ];
    }
    public static function getCityTypesArray() {
        return [
            \common\models\Pro_city::TYPE_CITY => \Yii::t('locale', 'City'), 
            \common\models\Pro_city::TYPE_SUB => \Yii::t('locale', 'Borough'),
        ];
    }
    public static function getCityStatusArray() {
        return [
            \common\models\Pro_city::STATUS_NORMAL => \Yii::t('locale', 'Enabled'), 
            \common\models\Pro_city::STATUS_DISABLED => \Yii::t('locale', 'Disabled'),
        ];
    }
    public static function getCityFlagsArray() {
        return [
            \common\models\Pro_city::FLAG_NORMAL => \Yii::t('locale', 'None'), 
            \common\models\Pro_city::FLAG_HOT => \Yii::t('locale', 'Hot city'),
        ];
    }
    
    public static function getProvincesArray() {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'type', 'status']);
        $cdb->andWhere(['>=', 'type', \common\models\Pro_city::TYPE_PROVINCE]);
        $cdb->andWhere(['>=', 'status', \common\models\Pro_city::STATUS_NORMAL]);
        $arrRows = $cdb->all();
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        
        return $arrData;
    }
    
    public static function getAllProvincesArray($addNone = false) {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'type', 'status']);
        $cdb->andWhere(['>=', 'type', \common\models\Pro_city::TYPE_PROVINCE]);
        $arrRows = $cdb->all();
        
        $arrData = [];
        if ($addNone) {
            $arrData[''] = \Yii::t('locale', 'None');
        }
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        
        return $arrData;
    }
    
    protected static function _convertCityDataToTreeData($arrRows) {
        $arrTmp = [];
        foreach ($arrRows as $row) {
            $_o = ['name' => $row->name, 'status' => $row->status, 'belong_id' => $row->belong_id];
            if (\common\models\Pro_city::TYPE_PROVINCE == $row->type) {
                $arrTmp[$row->id] = array_merge($_o, ['children' => []]);
            }
            elseif (\common\models\Pro_city::TYPE_CITY == $row->type) {
                if (isset($arrTmp[$row->belong_id])) {
                    $arrTmp[$row->belong_id]['children'][$row->id] = array_merge($_o, ['children' => []]);
                }
            }
            elseif (\common\models\Pro_city::TYPE_SUB == $row->type) {
                foreach ($arrTmp as $__k => $__v) {
                    if (isset($__v['children'][$row->belong_id])) {
                        $arrTmp[$__k]['children'][$row->belong_id]['children'][$row->id] = $_o;
                        break;
                    }
                }
            }
        }
        
        return $arrTmp;
    }

    public static function getCityTreeData()
    {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'type', 'status', 'belong_id']);
        $cdb->andWhere(['>=', 'type', \common\models\Pro_city::TYPE_SUB]);
        $cdb->orderBy('type desc');
        $arrRows = $cdb->all();
        
        return self::_convertCityDataToTreeData($arrRows);
    }
    
    public static function _genCityTreeChildrenData($key, $value, $hasCheckBox = false) {
        $retArr = [
            'id' => $key,
            'text' => $value['name'],
        ];
        if ($hasCheckBox) {
            $retArr['checked'] = ($value['status'] >= \common\models\Pro_city::STATUS_NORMAL ? true: false);
        }

        if (isset($value['children']) && !empty($value['children'])) {
            $retArr['children'] = [];
            foreach ($value['children'] as $_k => $_v) {
                $retArr['children'][] = self::_genCityTreeChildrenData($_k, $_v, $hasCheckBox);
            }
        }

        return $retArr;
    }

    public static function getCityComboTreeData($skipSubCity = false, $options=[]) {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'type', 'status', 'belong_id']);
        $cdb->andWhere([($skipSubCity ? '>' : '>='), 'type', \common\models\Pro_city::TYPE_SUB]);
        $cdb->andWhere(['>=', 'status', \common\models\Pro_city::STATUS_NORMAL]);
        $cdb->orderBy('type desc');
        $arrRows = $cdb->all();
        
        $cityData = self::_convertCityDataToTreeData($arrRows);
        
        $arrData = [];
        
        if (isset($options['showUniversal']) && $options['showUniversal']) {
            $txt = \Yii::t('locale', 'Universal');
            if (is_string($options['showUniversal']) && !empty($options['showUniversal'])) {
                $txt = $options['showUniversal'];
            }
            $arrData[] = [
                'id' => 0,
                'text' => $txt,
            ];
        }
        
        foreach ($cityData as $key => $value) {
            $arrData[] = self::_genCityTreeChildrenData($key, $value);
        }
        
        return $arrData;
    }
    
    protected static function _convertCityAreaDataToTreeData($arrRows) {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'belong_id', 'type', 'status']);
        $cdb->where(['>=', 'status', \common\models\Pro_city::STATUS_NORMAL]);
        $cdb->orderBy('type desc');
        $arrCityRows = $cdb->all();
        
        $state = '';
        
        $arrCities = [];
        $arrTreeCities = [];
        foreach ($arrCityRows as $city) {
            $arrCities[$city->id] = $city;
            
            $_o = ['name'=>$city->name, 'flag'=>0, 'children'=>[]];
            
            if (\common\models\Pro_city::TYPE_PROVINCE == $city->type) {
                $arrTreeCities[$city->id] = array_merge($_o, ['children'=>[], 'checkable'=>false]);
            }
            elseif (\common\models\Pro_city::TYPE_CITY == $city->type) {
                $__o = array_merge($_o, ['children'=>[], 'checkable'=>false]);
                if (isset($arrTreeCities[$city->belong_id])) {
                    $arrTreeCities[$city->belong_id]['children'][$city->id] = $__o;
                }
            }
        }
        
        foreach ($arrRows as $row) {
            $city = (isset($arrCities[$row->city_id]) ? $arrCities[$row->city_id] : null);
            if ($city) {
                $_o = ['name' => $row->name, 'id'=>$row->id, 'flag'=>1];
                if (\common\models\Pro_city::TYPE_PROVINCE == $city->type) {
                    if (isset($arrTreeCities[$city->id])) {
                        if ($state == '') {
                            $state = 'closed';
                        }
                        else {
                            $arrTreeCities[$city->id]['state'] = $state;
                        }
                
                        $arrTreeCities[$city->id]['flag'] = 1;
                        $arrTreeCities[$city->id]['children'][$row->id] = $_o;
                    }
                }
                elseif (\common\models\Pro_city::TYPE_CITY == $city->type) {
                    if (isset($arrTreeCities[$city->belong_id])) {
                        $arrTreeCities[$city->belong_id]['flag'] = 1;
                        if (isset($arrTreeCities[$city->belong_id]['children'][$city->id])) {
                            $arrTreeCities[$city->belong_id]['children'][$city->id]['flag'] = 1;
                            $arrTreeCities[$city->belong_id]['children'][$city->id]['children'][$row->id] = $_o;
                        }
                    }
                }
            }
        }
        
        return $arrTreeCities;
    }
    
    public static function getCityAreaComboTreeData() {
        $cdb = \common\models\Pro_city_area::find();
        $cdb->select(['id', 'name', 'status', 'city_id']);
        $cdb->andWhere(['>=', 'status', \common\models\Pro_city_area::STATUS_NORMAL]);
        $arrRows = $cdb->all();
        
        $arrTreeData = self::_convertCityAreaDataToTreeData($arrRows);
        
        $arrData = [];
        $arrTemp = OfficeModule::_convertTreeDataToComboTreeData($arrTreeData, 'id', 'text');
        foreach ($arrTemp as $o) {
            $arrData[] = $o;
        }
        
        return $arrData;
    }
    
    public static function getSubCityIdsByCityId($cityId) {
        $cdb2 = \common\models\Pro_city::find();
        $objCity = $cdb2->where("`id`={$cityId}")->one();
        $arrCityId = [];
        if ($objCity) {
            if ($objCity->type > \common\models\Pro_city::TYPE_SUB) {
                $arrObjs = $cdb2->where("city_id={$objCity->id}")->all();
                foreach ($arrObjs as $row) {
                    if ($row->type > \common\models\Pro_city::TYPE_SUB) {
                        $arrCityId = array_merge($arrCityId, self::getSubCityIdsByCityId($row->id));
                    }
                    else {
                        $arrCityId[] = $row->id;
                    }
                }
            }
            else {
                $arrCityId[] = $objCity->id;
            }
        }
        return $arrCityId;
    }
    
    public function getCityFlagDisplayText($flag) {
        $txt = '';
        if (($flag & \common\models\Pro_city::FLAG_HOT)) {
            $txt = \Yii::t('locale', 'Hot city');
        }
        return $txt;
    }
    
    public static function getCityNamesArray($arrCityIds) {
        $arrData = [];
        $cdb2 = \common\models\Pro_city::find();
        $cdb2->select(['id', 'name']);
        if (is_array($arrCityIds) && !empty($arrCityIds)) {
            $cdb2->where(['id' => $arrCityIds]);
        }
        else {
            $cdb2->where(['id' => intval($arrCityIds)]);
        }
        $arrRows = $cdb2->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        return $arrData;
    }
    
    public static function getCityAreaNamesArray($arrAreaIds) {
        $arrData = [];
        $cdb2 = \common\models\Pro_city_area::find();
        $cdb2->select(['id', 'name']);
        if (is_array($arrAreaIds) && !empty($arrAreaIds)) {
            $cdb2->where(['id' => $arrAreaIds]);
        }
        else {
            $cdb2->where(['id' => intval($arrAreaIds)]);
        }
        $arrRows = $cdb2->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        return $arrData;
    }
    
}
