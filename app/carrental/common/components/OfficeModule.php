<?php

namespace common\components;

class OfficeModule
{
    const HEAD_OFFICE_ID = -99129;
    
    static public function getRegionTypesArray() {
        return [
            \common\models\Pro_office::REGION_TYPE_CITY => \Yii::t('locale', 'Root region'), 
            \common\models\Pro_office::REGION_TYPE_SUB => \Yii::t('locale', 'Sub region'), 
            \common\models\Pro_office::REGION_TYPE_OFFICE => \Yii::t('locale', 'Office')
        ];
    }
    
    static public function getOfficeStatusArray() {
        return [
            \common\models\Pro_office::STATUS_NORMAL => \Yii::t('locale', 'Opening'), 
            \common\models\Pro_office::STATUS_CLOSED => \Yii::t('locale', 'Closed'), 
        ];
    }
    
    static public function getOfficeOneWayArray() {
        return [
            \common\models\Pro_office::ONE_WAY_YES => \Yii::t('locale', 'One way yes'), 
            \common\models\Pro_office::ONE_WAY_NO => \Yii::t('locale', 'One way no'), 
        ];
    }
    protected static function _convertOfficeDataToTreeData($arrRows) {
        $cdb = \common\models\Pro_city::find();
        $cdb->select(['id', 'name', 'belong_id', 'type', 'status']);
        $cdb->where(['>=', 'status', \common\models\Pro_city::STATUS_NORMAL]);
        $cdb->orderBy('type desc');
        $arrCityRows = $cdb->asArray()->all();
        
        $state = '';
        $showSubCity = false;
        
        $arrCities = [];
        $arrTreeCities = [];
        foreach ($arrCityRows as $city) {
            $arrCities[$city['id']] = $city;
            
            $_o = ['name'=>$city['name'], 'flag'=>0, 'children'=>[]];
            
            if (\common\models\Pro_city::TYPE_PROVINCE == $city['type']) {
                $arrTreeCities[$city['id']] = array_merge($_o, ['children'=>[], 'checkable'=>false]);
            }
            elseif (\common\models\Pro_city::TYPE_CITY == $city['type']) {
                $__o = array_merge($_o, ['children'=>[], 'checkable'=>false]);
                if (isset($arrTreeCities[$city['belong_id']])) {
                    $arrTreeCities[$city['belong_id']]['children'][$city['id']] = $__o;
                }
            }
            elseif ($showSubCity && \common\models\Pro_city::TYPE_SUB == $city['type']) {
                $___o = array_merge($_o, ['children'=>[], 'checkable'=>false]);
                
                $parentCity = (isset($arrCities[$city['belong_id']]) ? $arrCities[$city['belong_id']] : null);
                if ($parentCity) {
                    if (isset($arrTreeCities[$parentCity['belong_id']])) {
                        if (isset($arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']])) {
                            $arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['children'][$city['id']] = $___o;
                        }
                    }
                }
            }
        }
        
        foreach ($arrRows as $row) {
            $city = (isset($arrCities[$row['city_id']]) ? $arrCities[$row['city_id']] : null);
            if ($city) {
                $_o = ['name' => $row['fullname'], 'id'=>$row['id'], 'flag'=>1];
                if (\common\models\Pro_city::TYPE_PROVINCE == $city['type']) {
                    if (isset($arrTreeCities[$city['id']])) {
                        if ($state == '') {
                            $state = 'closed';
                        }
                        else {
                            $arrTreeCities[$city['id']]['state'] = $state;
                        }
                
                        $arrTreeCities[$city['id']]['flag'] = 1;
                        $arrTreeCities[$city['id']]['children'][$row['id']] = $_o;
                    }
                }
                elseif (\common\models\Pro_city::TYPE_CITY == $city['type']) {
                    if (isset($arrTreeCities[$city['belong_id']])) {
                        $arrTreeCities[$city['belong_id']]['flag'] = 1;
                        if (isset($arrTreeCities[$city['belong_id']]['children'][$city['id']])) {
                            $arrTreeCities[$city['belong_id']]['children'][$city['id']]['flag'] = 1;
                            $arrTreeCities[$city['belong_id']]['children'][$city['id']]['children'][$row['id']] = $_o;
                        }
                    }
                }
                elseif (\common\models\Pro_city::TYPE_SUB == $city['type']) {
                    $parentCity = (isset($arrCities[$city['belong_id']]) ? $arrCities[$city['belong_id']] : null);
                    if ($parentCity) {
                        if (isset($arrTreeCities[$parentCity['belong_id']])) {
                            $arrTreeCities[$parentCity['belong_id']]['flag'] = 1;
                            if (isset($arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']])) {
                                $arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['flag'] = 1;
                                if ($showSubCity) {
                                    if (isset($arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['children'][$city['id']])) {
                                        $arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['children'][$city['id']]['flag'] = 1;
                                        $arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['children'][$city['id']]['children'][$row['id']] = $_o;
                                    }
                                }
                                else {
                                    $arrTreeCities[$parentCity['belong_id']]['children'][$city['belong_id']]['children'][$row['id']] = $_o;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        return $arrTreeCities;
    }
    
    public static function getAuthedOfficeIdArrayByOfficeId($officeId) {
        $arrOfficeId = [];
        $objOffice = \common\models\Pro_office::findById($officeId);
        if ($objOffice) {
            if ($objOffice->area_id > 0) {
                $arrRows = \common\models\Pro_office::find()->where(['area_id' => $objOffice->area_id])->asArray()->all();
                if (empty($arrRows)) {
                    $arrOfficeId[] = $officeId;
                }
                else {
                    foreach ($arrRows as $row) {
                        $arrOfficeId[] = $row['id'];
                    }
                }
            }
            else {
                $arrOfficeId[] = $officeId;
            }
        }
        return $arrOfficeId;
    }
    
    public static function getOfficeTreeData($showAllOffices = false)
    {
        $cdb = \common\models\Pro_office::find($showAllOffices);
        $cdb->select(['id', 'fullname', 'city_id']);
        $cdb->andWhere(['>=', 'status', \common\models\Pro_office::STATUS_NORMAL]);
        $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if (!$showAllOffices && $officeId != self::HEAD_OFFICE_ID) {
            $arrOfficeIds = self::getAuthedOfficeIdArrayByOfficeId($officeId);
            $cdb->andWhere(['id' => $arrOfficeIds]);
        }
        $arrRows = $cdb->asArray()->all();
        
        return self::_convertOfficeDataToTreeData($arrRows);
    }
    
    public static function _convertTreeDataToComboTreeData($arrTreeData, $idField = 'id', $valueField = 'text') {
        $arrData = [];
        foreach ($arrTreeData as $k => $row) {
            $_o = [];
            if (is_array($row)) {
                if (isset($row['flag'])) {
                    if (!$row['flag']) {
                        continue;
                    }
                }
                foreach ($row as $_k => $_v) {
                    $_o[$_k] = $_v;
                }
                
                $_o[$valueField] = $row['name'];
                
                if (isset($row['children'])) {
                    //$_o[$idField] = '';
                    $_o['children'] = self::_convertTreeDataToComboTreeData($row['children'], $idField, $valueField);
                }
                else {
                    $_o[$idField] = $k;
                }
            }
            else {
                if (is_string($row)) {
                    $row = "'{$row}'";
                }
                elseif (is_bool($row)) {
                    $row = ($row ? 'true' : 'false');
                }
                else {
                    $row = strval($row);
                }
                $_o[$idField] = $k;
                $_o[$valueField] = $row;
            }
            
            $arrData[] = $_o;
        }
        
        return $arrData;
    }

    public static function getOfficeComboTreeData($options = [])
    {
        $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        $idField = 'id';
        $valueField = 'text';
        if (isset($options['valueField'])) {
            $idField = $options['valueField'];
        }
        if (isset($options['textField'])) {
            $valueField = $options['textField'];
        }
        
        $arrData = [];
        if (isset($options['showUniversal']) && $options['showUniversal']) {
            $txt = \Yii::t('locale', 'Universal');
            if (is_string($options['showUniversal']) && !empty($options['showUniversal'])) {
                $txt = $options['showUniversal'];
            }
            $arrData[] = [
                $idField => 0,
                $valueField => $txt,
            ];
        }
        elseif ($officeId == self::HEAD_OFFICE_ID) {
            $arrData[] = [
                $idField => self::HEAD_OFFICE_ID,
                $valueField => \Yii::t('locale', 'Head office')
            ];
        }
        
        $showAllOffices = false;
        if (isset($options['showAll']) && $options['showAll']) {
            $showAllOffices = true;
        }
        
        $arrTreeData = self::getOfficeTreeData($showAllOffices);
        $arrTemp = self::_convertTreeDataToComboTreeData($arrTreeData, $idField, $valueField);
        foreach ($arrTemp as $o) {
            $arrData[] = $o;
        }
        
        return $arrData;
    }
    
    public static function getOfficeComboData($options = [])
    {
        $cdb = \common\models\Pro_office::find();
        $cdb->select(['id', 'fullname', 'city_id']);
        $cdb->andWhere("status>=".\common\models\Pro_office::STATUS_NORMAL);
        $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($officeId != OfficeModule::HEAD_OFFICE_ID) {
            $cdb->andWhere(['id' => $officeId]);
        }
        $arrRows = $cdb->asArray()->all();
        
        $idField = 'id';
        $valueField = 'text';
        if (isset($options['valueField'])) {
            $idField = $options['valueField'];
        }
        if (isset($options['textField'])) {
            $valueField = $options['textField'];
        }
        
        $arrData = [];
        foreach ($arrRows as $row) {
            $arrData[] = [
                $idField => $row['id'],
                $valueField => $row['fullname'],
            ];
        }
        
        return $arrData;
    }
    
    public static function getOfficeLandmarksArray() {
        return [
            0 => \Yii::t('locale', 'None'),
            \common\models\Pro_office::LANDMARK_NEAR_AIR_PORT => \Yii::t('locale', 'Near {place}', ['place' => \Yii::t('locale', 'air port')]),
            \common\models\Pro_office::LANDMARK_NEAR_TRAIN_STATION => \Yii::t('locale', 'Near {place}', ['place' => \Yii::t('locale', 'train station')]),
            \common\models\Pro_office::LANDMARK_NEAR_BUS_STATION => \Yii::t('locale', 'Near {place}', ['place' => \Yii::t('locale', 'bus station')]),
            \common\models\Pro_office::LANDMARK_NEAR_SUBWAY => \Yii::t('locale', 'Near {place}', ['place' => \Yii::t('locale', 'subway')]),
        ];
    }
    
    public static function getOfficeNamesArrayByOfficeIds($arrOfficeIds) {
        $arrData = [];
        $cdb2 = \common\models\Pro_office::find(true);
        $cdb2->select(['id', 'shortname']);
        if (is_array($arrOfficeIds) && !empty($arrOfficeIds)) {
            $cdb2->where(['id' => $arrOfficeIds]);
        }
        else {
            $cdb2->where(['id' => intval($arrOfficeIds)]);
        }
        //$officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        //if ($officeId != OfficeModule::HEAD_OFFICE_ID) {
        //    $cdb2->andWhere(['id' => $officeId]);
        //}
        $arrRows = $cdb2->asArray()->all();
        foreach ($arrRows as $row) {
            $arrData[$row['id']] = $row['shortname'];
        }
        $arrData[self::HEAD_OFFICE_ID] = \Yii::t('locale', 'Head office');
        return $arrData;
    }
    
    public static function isOfficeIdAuthorized($officeId) {
        $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($authOfficeId == self::HEAD_OFFICE_ID || $authOfficeId == $officeId) {
            return true;
        }
        elseif (empty($officeId)) {
            return false;
        }
        $arrOfficeIds = static::getAuthedOfficeIdArrayByOfficeId($authOfficeId);
        foreach ($arrOfficeIds as $_id) {
            if ($officeId == $_id) {
                return true;
            }
        }
        return false;
    }
    
}
