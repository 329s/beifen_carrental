<?php

namespace common\components;

class VehicleModule
{
    const VEHICLE_VIOLATION_ORDER_PROCESS_MAX_TIME = 1296000; // (86400*15);
    
    public static function getVehicleBrandsArray($hasNone = true) {
        $cdb = \common\models\Pro_vehicle_brand::find();
        $cdb->select(['id', 'name', 'flag']);
        $cdb->where(["belong_brand"=>0]);
        $cdb->andWhere(['flag' => \common\models\Pro_vehicle_brand::FLAG_ENABLED]);
        $arrRows = $cdb->all();
        
        $arrData = [];
        if ($hasNone) {
            $arrData[0] = \Yii::t('locale', 'None');
        }
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        
        return $arrData;
    }
    
    public static function getSubVehicleBrandsArray($brandId, $hasNone = true) {
        if (empty($brandId)) {
            return [];
        }
        $cdb = \common\models\Pro_vehicle_brand::find();
        $cdb->select(['id', 'name', 'flag']);
        $cdb->where("belong_brand = :id", [':id' => $brandId]);
        $cdb->andWhere('flag = :flag', [':flag' => \common\models\Pro_vehicle_brand::FLAG_ENABLED]);
        
        $arrRows = $cdb->all();
        
        $arrData = [];
        if ($hasNone) {
            $arrData[0] = \Yii::t('locale', 'None');
        }
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        
        return $arrData;
    }
    
    public static function getVehicleBrandFlagsArray() {
        return [
            \common\models\Pro_vehicle_brand::FLAG_ENABLED => \Yii::t('locale', 'Enabled'),
            \common\models\Pro_vehicle_brand::FLAG_DISABLED => \Yii::t('locale', 'Disabled')
        ];
    }
    
    public static function getCommonStatusArray() {
        return [
            Consts::STATUS_ENABLED => \Yii::t('locale', 'Enabled'),
            Consts::STATUS_DISABLED => \Yii::t('locale', 'Disabled')
        ];
    }
    
    public static function getVehicleCarriagesArray() {
        return [
           0 => \Yii::t('locale', 'No display'),
            2 => \Yii::t('carrental', 'two carriages'),
            3 => \Yii::t('locale', '{number} carriages', ['number' => \Yii::t('locale', 'three')]),
            4 => \Yii::t('locale', 'SUV'),
        ];
    }
    
    public static function getVehicleSeatsArray() {
        return [
            5 => \Yii::t('locale', '{number} seats', ['number' => 5]),
            7 => \Yii::t('locale', '{number} seats', ['number' => 7]),
            2 => \Yii::t('locale', '{number} seats', ['number' => 2]),
            10 => \Yii::t('locale', '{number} seats', ['number' => 10]),
            15 => \Yii::t('locale', '{number} seats', ['number' => 15]),
            9 => \Yii::t('locale', '{number} seats', ['number' => 9]),
            20 => \Yii::t('locale', '{number} seats', ['number' => 20]),
        ];
    }
    
    public static function getVehicleGearboxTypesArray() {
        $manualFlag = \common\models\Pro_vehicle_model::GEARBOX_MANUAL;//0x00000000 手动
        $autoFlag = \common\models\Pro_vehicle_model::GEARBOX_AUTO;//0x10000000;    // 自动
        return [
            $manualFlag => \Yii::t('locale', 'Manual'),
            $autoFlag => \Yii::t('locale', 'Auto'),
            $manualFlag | 0x05 => '5MT',
            $autoFlag | 0x05 => '5AT',
            $manualFlag | 0x06 => '6MT',
            $autoFlag | 0x06 => '6AT',
            $manualFlag | 0x07 => '7MT',
            $autoFlag | 0x07 => '7AT',
            $manualFlag | 0x08 => '8MT',
            $autoFlag | 0x08 => '8AT',
        ];
    }
    
    public static function getVehicleOilLabelsArray() {
        return [
            92 => '92',
            93 => '93',
            95 => '95',
            97 => '97',
            1  => '柴油'
        ];
    }
    
    public static function getVehicleStatusArray() {
        return [
            \common\models\Pro_vehicle::STATUS_NORMAL => \Yii::t('locale', 'For hire'),
            //\common\models\Pro_vehicle::STATUS_BOOKED => \Yii::t('locale', 'Booked'),
            //\common\models\Pro_vehicle::STATUS_RENTED => \Yii::t('locale', 'Rented'),
            \common\models\Pro_vehicle::STATUS_MAINTENANCE => \Yii::t('locale', 'Maintenance/upkeeping'),
            \common\models\Pro_vehicle::STATUS_SAILED => \Yii::t('carrental', 'Saled'),
        ];
    }
    
    public static function getVehicleStatusWithAllArray() {
        return [
            0 => \Yii::t('locale', 'None'),
            \common\models\Pro_vehicle::STATUS_NORMAL => \Yii::t('locale', 'For hire'),
            \common\models\Pro_vehicle::STATUS_BOOKED => \Yii::t('locale', 'Booked'),
            \common\models\Pro_vehicle::STATUS_RENTED => \Yii::t('locale', 'Rented'),
            \common\models\Pro_vehicle::STATUS_MAINTENANCE => \Yii::t('locale', 'Maintenance/upkeeping'),
            \common\models\Pro_vehicle::STATUS_SAILED => \Yii::t('carrental', 'Saled'),
        ];
    }
    
    public static function getVehicleColorsArray() {
        return [
            1 => \Yii::t('locale', 'Black'),
            2 => \Yii::t('locale', 'White'),
            3 => \Yii::t('locale', 'Red'),
            4 => \Yii::t('locale', 'Yellow'),
            5 => \Yii::t('locale', 'Silvery'),
            6 => \Yii::t('locale', 'Brown'),
            7 => \Yii::t('locale', 'Blue'),
            8 => \Yii::t('locale', 'Gray'),
            9 => \Yii::t('locale', 'Green'),
            10 => \Yii::t('locale', 'Champagne'),
            11 => \Yii::t('locale', 'Purple'),
            12 => \Yii::t('carrental', 'Golden'),
            13 => \Yii::t('carrental', 'Black-purple'),
            99 => \Yii::t('locale', 'Other'),
        ];
    }
    
    public static function getVehicleMileagePriceRangesArray() {
        return [0, 50, 100];
    }
    
    public static function getVehiclePropertiesArray() {
        return [
            \common\models\Pro_vehicle::PROPERTY_BAUGHT => \Yii::t('locale', 'Self baught vehicle'),
            \common\models\Pro_vehicle::PROPERTY_AFFILIATE => \Yii::t('locale', 'Affiliate vehicle'),
            \common\models\Pro_vehicle::PROPERTY_TRUSTTEE => \Yii::t('locale', 'Trusttee vehicle'),
        ];
    }
    
    public static function getVehicleMaintenanceCheckPointTypesArray() {
        return [
            \common\models\Pro_vehicle_maintenance_config_item::CHECKPOINT_TYPE_MILEAGE => \Yii::t('carrental', 'Maintenance by mileage'),
            \common\models\Pro_vehicle_maintenance_config_item::CHECKPOINT_TYPE_TIME => \Yii::t('carrental', 'Maintenance by time')
        ];
    }
    
    public static function getVehicleBrandNamesArrayByIds($arrBrandIds) {
        $arrData = [];
        $cdb2 = \common\models\Pro_vehicle_brand::find();
        $cdb2->select(['id', 'name']);
        if (is_array($arrBrandIds) && !empty($arrBrandIds)) {
            $cdb2->where(['id' => $arrBrandIds]);     
        }
        else {
            $cdb2->where(['id' => intval($arrBrandIds)]);
        }
        $arrRows = $cdb2->all();
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        return $arrData;
    }
    
    public static function getVehicleModelNamesArrayByIds($arrModelIds) {
        $arrData = [];
        $cdb2 = \common\models\Pro_vehicle_model::find();
        $cdb2->select(['id', 'vehicle_model']);
        if (is_array($arrModelIds) && !empty($arrModelIds)) {
            if (!isset($arrModelIds['all'])) {
                $cdb2->where(['id' => $arrModelIds]);                
            }
        }
        else {
            $cdb2->where(['id' => intval($arrModelIds)]);
        }
        $arrRows = $cdb2->asArray()->all();
        foreach ($arrRows as $row) {
            $arrData[$row['id']] = $row['vehicle_model'];
        }
        return $arrData;
    }
    
    public static function getVehicleModelNamesArray($options = []) {
        $arrData = [];
        $cdb2 = \common\models\Pro_vehicle_model::find();
        $cdb2->select(['id', 'vehicle_model']);
        if ($options) {
            if (isset($options['enableNone']) && $options['enableNone']) {
                $arrData[''] = \Yii::t('locale', 'None');
            }
        }
        $arrRows = $cdb2->asArray()->all();
        foreach ($arrRows as $row) {
            $arrData[$row['id']] = $row['vehicle_model'];
        }
        return $arrData;
    }
    
    public static function getVehicleModelNamesWithPriceArray($options = []) {
        $cdb = \common\models\Pro_vehicle_model::find();
        $cdb->select(['id', 'vehicle_model', 'poundage', 'basic_insurance', 'rent_deposit', 'overtime_price_personal']);
        
        if (isset($options['brand']) && $options['brand']) {
            $cdb->andWhere(['brand'=>$options['brand']]);
        }
        if (isset($options['series']) && $options['series']) {
            $cdb->andWhere(['model_series'=>$options['series']]);
        }
        
        $arrRows = $cdb->asArray()->all();
        //$arrData = [['id'=>'', 'text'=> \Yii::t('locale', 'Please select...')]];
        $arrData = [];
        if (isset($options['enableall']) && $options['enableall']) {
            $arrData[] = ['id'=>0, 'text'=>  \Yii::t('locale', 'All')];
        }
        elseif (isset($options['enablenone']) && $options['enablenone']) {
            $arrData[] = ['id'=>0, 'text'=>  \Yii::t('locale', 'None')];
        }
        foreach ($arrRows as $row) {
            $arrData[] = ['id'=>$row['id'], 'text' => $row['vehicle_model'], 'poundage'=>$row['poundage'],
                'basic_insurance'=>$row['basic_insurance'], 
                'rent_deposit'=>$row['rent_deposit'], 
                'overtime_price_personal'=>$row['overtime_price_personal']
            ];
        }
        if (isset($options['enableadd']) && $options['enableadd']) {
            $arrData[] = ['id'=>-128, 'text'=>  \Yii::t('locale', '{operation} vehicle model', ['operation' => \Yii::t('locale', 'Add')]).'...'];
        }
        
        return $arrData;
        
    }
    
    public static function getVehicleModelImageUrl($imagePath) {
        if (empty($imagePath)) {
            return '';
        }
        $host = \Yii::$app->request->getHostInfo();
        $uri = \Yii::$app->request->getBaseUrl();
        $pos = strpos($uri, 'app/carrental');
        $relpath = '';
        if ($pos > 1) {
            $relpath = substr($uri, 0, $pos - 1);
        }
        return $host.$relpath.$imagePath;
    }

    public static function getVehicleEmissionDisplayValue($emission) {
        return round(floatval($emission) / 1000, 1);
    }
    
    public static function getFeePlanObjects($arrVehicleModelIds, $officeId = -1) {
        $cdb = \common\models\Pro_vehicle_fee_plan::find();
        if (is_array($arrVehicleModelIds) && !empty($arrVehicleModelIds)) {
            $cdb->where(['vehicle_model_id' => $arrVehicleModelIds]);
        }
        else {
            $cdb->where(['vehicle_model_id' => intval($arrVehicleModelIds)]);
        }
        if ($officeId >= 0) {
            if ($officeId > 0) {
                $officeId = [$officeId, 0];
            }
            $cdb->andWhere(['office_id' => $officeId]);
        }
        $arrRows = $cdb->all();
        // $sql=$cdb->createCommand()->getRawSql();
        $arrFeePlans = [];
        foreach($arrRows as $row) {
            if (!isset($arrFeePlans[$row->source])) {
                $arrFeePlans[$row->source] = [];
            }
            if (!isset($arrFeePlans[$row->source][$row->office_id])) {
                $arrFeePlans[$row->source][$row->office_id] = [];
            }
            $arrFeePlans[$row->source][$row->office_id][$row->vehicle_model_id] = $row;
        }
        
        return $arrFeePlans;
        // return $sql;
    }
    /*sjj*/
    public static function getFeePlanObjectss($arrVehicleModelIds, $officeId = -1) {
        $cdb = \common\models\Pro_vehicle_fee_plan::find();
        if (is_array($arrVehicleModelIds) && !empty($arrVehicleModelIds)) {
            $cdb->where(['vehicle_model_id' => $arrVehicleModelIds]);
        }
        else {
            $cdb->where(['vehicle_model_id' => intval($arrVehicleModelIds)]);
        }
        if ($officeId >= 0) {
            if ($officeId > 0) {
                $officeId = [$officeId, 0];
            }
            $cdb->andWhere(['office_id' => $officeId]);
        }
            $cdb->andWhere(['source' => 0]);
        $arrRows = $cdb->all();
        // $sql=$cdb->createCommand()->getRawSql()
        
        $arrFeePlans = [];
        foreach($arrRows as $row) {
            if (!isset($arrFeePlans[$row->source])) {
                $arrFeePlans[$row->source] = [];
            }
            if (!isset($arrFeePlans[$row->source][$row->office_id])) {
                $arrFeePlans[$row->source][$row->office_id] = [];
            }
            $arrFeePlans[$row->source][$row->office_id][$row->vehicle_model_id] = $row;
        }
        
        return $arrFeePlans;
    }
    /*sjj*/
    
    /**
     * @param array $arrFeePlans
     * @param integer $orderSource
     * @param integer $officeId
     * @param integer $vehicleModelId
     * @return \common\models\Pro_vehicle_fee_plan
     */
    public static function getFeePlanObjectFromArray($arrFeePlans, $orderSource, $officeId, $vehicleModelId) {
        if ($orderSource) {
            if (!isset($arrFeePlans[$orderSource])) {
                $orderSource = 0;
            }
        }
        if (!isset($arrFeePlans[$orderSource])) {
            return null;
        }
        if ($officeId) {
            if (!isset($arrFeePlans[$orderSource][$officeId])) {
                $officeId = 0;
            }
        }
        if (!isset($arrFeePlans[$orderSource][$officeId])) {
            return null;
        }
        if (!isset($arrFeePlans[$orderSource][$officeId][$vehicleModelId])) {
            return null;
        }
        
        return $arrFeePlans[$orderSource][$officeId][$vehicleModelId];
    }

    public static function getVehicleObjects($vehicleIdArray) {
        $cdb = \common\models\Pro_vehicle::find();
        if (is_array($vehicleIdArray) && !empty($vehicleIdArray)) {
            $cdb->where(['id' => $vehicleIdArray]);
        }
        else {
            $cdb->where(['id' => intval($vehicleIdArray)]);
        }
        $arrRows = $cdb->all();
        
        $arrData = [];
        foreach($arrRows as $row) {
            $arrData[$row->id] = $row;
        }
        
        return $arrData;
    }
    
    public static function getVehicleModelObjects($vehicleModelIdArray) {
        $cdb = \common\models\Pro_vehicle_model::find();
        if (is_array($vehicleModelIdArray) && !empty($vehicleModelIdArray)) {
            $cdb->where(['id' => $vehicleModelIdArray]);
        }
        else {
            $cdb->where(['id' => intval($vehicleModelIdArray)]);
        }
        $arrRows = $cdb->all();
        
        $arrData = [];
        foreach($arrRows as $row) {
            $arrData[$row->id] = $row;
        }
        
        return $arrData;
    }
    
    public static function getVehicleModelById($vehicleModelId) {
        $cdb = \common\models\Pro_vehicle_model::find();
        $cdb->where(['id' => intval($vehicleModelId)]);
        return $cdb->one();
    }
    
    public static function formatVehicleDatagridDataArray($arrRows, $showVehicleModelDetail = false, $startTime = 0, $endTime = 0, $priceType = \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE) {
        $arrModelIds = [];
        $arrVehicleIds = [];
        $arrModelIdByVehicleIds = [];
        $arrSourceModels = [];
        foreach ($arrRows as $row) {
            $arrSourceModels[$row['id']] = $row;
            $arrModelIdByVehicleIds[$row['id']] = $row['model_id'];
            if (!isset($arrModelIds[$row['model_id']])) {
                $arrModelIds[$row['model_id']] = 1;
            }
            if (!isset($arrVehicleIds[$row['id']])) {
                $arrVehicleIds[$row['id']] = 1;
            }
        }
        $arrVehicleStatus = OrderModule::getVehicleStatusByVehicleIds(array_keys($arrVehicleIds));
        $arrModelObjects = self::getVehicleModelObjects(array_keys($arrModelIds));
        $arrFeePlans = self::getFeePlanObjects(array_keys($arrModelObjects));
        $feeOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        if ($feeOfficeId < 0) {
            $feeOfficeId = 0;
        }
        $dataProvider = \common\models\Pro_vehicle::createDataProvider([
            'prepareDatas' => null,
        ]);
        $dataProvider->setModels($arrSourceModels);
        $dataProvider->manualFormatModelValues();
        $arrModels = $dataProvider->getModels();
        $arrOriginModelDatas = $dataProvider->originModelDatas;
        
        $arrData = [];
        foreach ($arrModels as $_i => $oVehicle) {
            $objVehicleModel = (isset($arrModelObjects[$arrModelIdByVehicleIds[$oVehicle['id']]]) ? $arrModelObjects[$arrModelIdByVehicleIds[$oVehicle['id']]] : null);
            $statusInfo = (isset($arrVehicleStatus[$oVehicle['id']]) ? $arrVehicleStatus[$oVehicle['id']] : null);
            $status = ($statusInfo ? $statusInfo['status'] : $oVehicle['status']);
            $o = $oVehicle->getAttributes();
            foreach ($dataProvider->formattingAttributes as $_attr => $_formatter) {
                if (isset($arrOriginModelDatas[$_i][$_attr])) {
                    $o['o_'.$_attr] = $arrOriginModelDatas[$_i][$_attr];
                }
            }
            $o['status'] = (isset($arrStatus[$status]) ? $arrStatus[$status] : '');
            
            if (empty($o['vehicle_image'])) {
                if ($objVehicleModel) {
                    $o['vehicle_image'] = \common\helpers\Utils::toFileUri($objVehicleModel['image_0']);
                }
            }
            
            if ($statusInfo) {
                $o['rent_start_time'] = $statusInfo['start_time'];
                $o['rent_end_time'] = $statusInfo['end_time'];
            }
            
            if ($showVehicleModelDetail) {
                $feeOnline = null;
                $feeOffice = null;
                $feeDefault = null;
                $arrPriceData = null;
                if ($objVehicleModel) {
                    $feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $feeOfficeId, $objVehicleModel['id']);
                    $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $feeOfficeId, $objVehicleModel['id']);
                    $feeDefault = $feeOffice ? $feeOffice : null/* $feeOnline */;
                    if ($feeDefault) {
                        $arrPriceData = $feeDefault->getPriceForDuration($startTime, $endTime, $priceType);
                    }
                }
                
                $o['vehicle_model_id'] = ($objVehicleModel ? $objVehicleModel['id'] : 0);
                $o['vehicle_type'] = ($objVehicleModel ? $objVehicleModel['vehicle_type'] : '');
                $o['vehicle_seat'] = ($objVehicleModel ? $objVehicleModel['seat'] : '');
                $o['vehicle_emission'] = ($objVehicleModel ? $objVehicleModel->vehicleEmissionHumanText() : '');
                $o['vehicle_oil_label'] = ($objVehicleModel ? $objVehicleModel['oil_label'] : '');
                $o['vehicle_oil_capacity'] = ($objVehicleModel ? $objVehicleModel['oil_capacity'].'L' : '');
                $o['vehicle_driving_mode'] = ($objVehicleModel ? $objVehicleModel['driving_mode'] : '');
                $o['vehicle_air_intake_mode'] = ($objVehicleModel ? $objVehicleModel['air_intake_mode'] : '');
                $o['vehicle_gearbox'] = ($objVehicleModel ? $objVehicleModel['gearbox'] : '');
                $o['vehicle_poundage'] = ($objVehicleModel ? $objVehicleModel['poundage'] : '');
                $o['vehicle_basic_insurance'] = ($objVehicleModel ? $objVehicleModel['basic_insurance'] : '');
                $o['vehicle_rent_deposit'] = ($objVehicleModel ? $objVehicleModel['rent_deposit'] : '');
                $o['vehicle_designated_driving_price'] = ($objVehicleModel ? $objVehicleModel['designated_driving_price'] : '');
                $o['vehicle_overtime_price_personal'] = ($objVehicleModel ? $objVehicleModel['overtime_price_personal'] : '');
                $o['vehicle_overtime_price_designated'] = ($objVehicleModel ? $objVehicleModel['overtime_price_designated'] : '');
                $o['vehicle_overmileage_price_personal'] = ($objVehicleModel ? $objVehicleModel['overmileage_price_personal'] : '');
                $o['vehicle_overmileage_price_designated'] = ($objVehicleModel ? $objVehicleModel['overmileage_price_designated'] : '');
                $o['vehicle_price_office'] = $feeOffice ? $feeOffice['price_default'] : 0;
                $o['vehicle_price_online'] = $feeOnline ? $feeOnline['price_default'] : 0;
                $o['vehicle_rent_per_day'] = $arrPriceData ? $arrPriceData['price'] : 0;
                $o['vehicle_price_rent'] = $arrPriceData ? $arrPriceData['price'] : 0;
            }
            $arrData[] = $o;
        }
        
        return $arrData;
    }
    
    public static function getVehicleValidationOptionsValueFlagsArray() {
        return [
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_NONE => \Yii::t('carrental', 'None'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_GOOD => \Yii::t('carrental', 'Good'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_BROKEN => \Yii::t('carrental', 'Broken'),
            \common\models\Pro_vehicle_validation_config::VALUE_FLAG_LOST => \Yii::t('carrental', 'Lost'),
        ];
    }
    
    public static function getVehicleValidationOptionsTypesArray() {
        return [
            \common\models\Pro_vehicle_validation_config::TYPE_ROOT => \Yii::t('carrental', 'Label'),
            \common\models\Pro_vehicle_validation_config::TYPE_OPTIONS => \Yii::t('carrental', 'Options'),
            \common\models\Pro_vehicle_validation_config::TYPE_IMAGES => \Yii::t('carrental', 'Photos'),
        ];
    }
    
    public static function getVehecleValidationOptionsWithTypeLabelsArray($hasRoot = false) {
        $cdb = \common\models\Pro_vehicle_validation_config::find();
        $cdb->where(['belong_id'=>0]);
        $arrRows = $cdb->all();
        $arrData = [];
        if ($hasRoot) {
            $arrData[0] = \Yii::t('carrental', 'Label');
        }
        foreach ($arrRows as $row) {
            $arrData[$row->id] = $row->name;
        }
        return $arrData;
    }
    
    public static function getVehicleValidationOptionsArray() {
        $arrRows = \common\models\Pro_vehicle_validation_config::find()->all();
        $arrData = [];
        foreach ($arrRows as $row) {
            if ($row->belong_id) {
                if (isset($arrData[$row->belong_id])) {
                    $arr = $arrData[$row->belong_id];
                    if (isset($arr['children'])) {
                        $arrData[$row->belong_id]['children'][] = $row;
                    }
                    else {
                        $arrData[$row->belong_id]['children'] = [$row];
                    }
                }
                else {
                    $arrData[$row->belong_id] = ['children'=>[$row]];
                }
            }
            else {
                $o = [
                    'name' => $row->name,
                    'type' => $row->type,
                ];
                $arrData[$row->id] = $o;
            }
        }
        return $arrData;
    }
    
    public static function getOilVolumeLevesArray() {
        return [
            1 => \Yii::t('locale', '{num} fifth', ['num' => \Yii::t('locale', 'One')]),
            2 => \Yii::t('locale', '{num} fifth', ['num' => \Yii::t('locale', 'Two')]),
            3 => \Yii::t('locale', '{num} fifth', ['num' => \Yii::t('locale', 'Three')]),
            4 => \Yii::t('locale', '{num} fifth', ['num' => \Yii::t('locale', 'Four')]),
            5 => \Yii::t('carrental', 'Full oil'),
        ];
    }
    
    public static function getVehicleValidationSummaryArray() {
        $arr = [
            \Yii::t('carrental', 'Excellent without damage'),
            \Yii::t('carrental', 'Minor damage'),
        ];
        $arrData = [];
        foreach ($arr as $v) {
            $arrData[$v] = $v;
        }
        return $arrData;
    }
    
    public static function getVehicleViolationStatusArray() {
        return [
            \common\models\Pro_vehicle_violation::STATUS_UNPROCESSED => \Yii::t('locale', 'Un-processed'),
            \common\models\Pro_vehicle_violation::STATUS_PROCESSED => \Yii::t('locale', 'Processed'),
        ];
    }
    
    public static function getVehicleExpenditureTypesArray() {
        return \common\models\Pro_vehicle_cost::getTypesArray();
    }
    
    public static function getVehicleSettlementStatusArray() {
        return [
            0 => '全部',
            1 => '还车终结',
            2 => '分期结算',
            3 => '还车挂账',
            4 => '无效订单',
        ];
    }

    /*是否可供单程租车*/
    public static function getVehicleIsOneWayArray(){
        return [
            0 => '否',
            1 => '是',
        ];
    }

    /**
     *@desc   通过flag标签查询车辆或者车型，是否可供单程租车
     *@param  $flag 8:舒适型 16：经济型 64 商务型
     *@return $array
     */
    public function getOneWayVehicleModel($shopId=20, $takeCarTime, $returnCarTime, $flag = 16){
        $arrLeftCountByVehicleModel = \common\components\OrderModule::getVehicleLeftCountByTimeRegion($shopId, $takeCarTime, $returnCarTime);
        return $arrLeftCountByVehicleModel;
    }
}