<?php

namespace backend\components;

/**
 * Description of VehicleService
 *
 * @author kevin
 */
class VehicleService extends BaseService
{
    
    public static function processEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            }
            
            $objFormData = new \backend\models\Form_pro_vehicle();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                $objItem = new \common\models\Pro_vehicle();
            }
            else {
                $objItem = \common\models\Pro_vehicle::findById($objFormData->id);
                // 车辆所属门店变更登记
                // print_r($objItem->belong_office_id);
                // echo "<hr>";
                // print_r($objFormData->belong_office_id);die;
                if($objItem->belong_office_id != $objFormData->belong_office_id){
                    $now = time();
                    $objVehicleChange   = new \common\models\Pro_vehicle_office_change();
                    $objVehicleChange->vehicle_id           = $objFormData->id;
                    $objVehicleChange->belong_office_id     = $objItem->belong_office_id;
                    $objVehicleChange->new_belong_office_id = $objFormData->belong_office_id;
                    $objVehicleChange->updated_at           = $now;
                    $objVehicleChange->created_at           = $now;
                    $objVehicleChange->save();
                }

                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]));
                }
                
                if ($objFormData->status != \common\models\Pro_vehicle::STATUS_NORMAL) {
                    $cdb2 = \common\models\Pro_vehicle_order::find(true);
                    $cdb2->where(['vehicle_id' => $objItem->id]);
                    if ($objFormData->status == \common\models\Pro_vehicle::STATUS_DELETED) {
                        $cdb2->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING]);
                        if ($cdb2->one()) {
                            return self::errorResult(\Yii::t('carrental', 'The car has order processed history, please do not delete the car!'));
                        }
                    }
                    elseif ($objFormData->status == \common\models\Pro_vehicle::STATUS_MAINTENANCE) {
                        $cdb2->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
                        if ($cdb2->one()) {
                            return self::errorResult(\Yii::t('carrental', 'The car has order processing, please do not maintenance the car!'));
                        }
                    }
                    elseif ($objFormData->status == \common\models\Pro_vehicle::STATUS_SAILED) {
                        $cdb2->andWhere(['<=', 'status', \common\models\Pro_vehicle_order::STATUS_RENTING]);
                        if ($cdb2->one()) {
                            return self::errorResult(\Yii::t('carrental', 'The car has order processing, please do not sale the car!'));
                        }
                    }
                }
            }
            
            if ($objFormData->save($objItem)) {
                if ($objItem->save()) {
                    $arrResult[0] = Consts::CODE_OK;
                    $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
                    $arrResult['callbackType'] = 'refreshCurrentX';
                } else {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
                }
            }
            else {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
        
        }while(0);
        return $arrResult;
    }
    
    public static function processExpenditureEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $type = intval(\Yii::$app->request->getParam('type'));
            $objFormData = null;
            $modelClass = null;
            $expenditureType = \common\models\Pro_expenditure_order::TYPE_VEHICLE;
            if ($type == \common\models\Pro_vehicle_cost::TYPE_RENEWAL) {
                $objFormData = new \backend\models\Form_pro_vehicle_insurance();
                $modelClass = \common\models\Pro_vehicle_insurance::className();
                $expenditureType = \common\models\Pro_expenditure_order::TYPE_VEHICLE_INSURANCE;
            }
            elseif ($type == \common\models\Pro_vehicle_cost::TYPE_DESIGNATING) {
                $objFormData = new \backend\models\Form_pro_vehicle_designating_cost();
                $modelClass = \common\models\Pro_vehicle_designating_cost::className();
                $expenditureType = \common\models\Pro_expenditure_order::TYPE_VEHICLE_DESIGNATING;
            }
            elseif ($type == \common\models\Pro_vehicle_cost::TYPE_OIL) {
                $objFormData = new \backend\models\Form_pro_vehicle_oil_cost();
                $modelClass = \common\models\Pro_vehicle_oil_cost::className();
                $expenditureType = \common\models\Pro_expenditure_order::TYPE_VEHICLE_OIL;
            }
            else {
                $objFormData = new \backend\models\Form_pro_vehicle_cost();
                $modelClass = \common\models\Pro_vehicle_cost::className();
            }
            $expenditureType += \common\models\Pro_expenditure_order::SUB_TYPE_VEHICLE_COST_FACTOR;
            
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            if (empty($objFormData->vehicle_id)) {
                return self::errorResult(\Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle')]));
            }
            
            if ($action == 'insert') {
                $objItem = new $modelClass;
            }
            else {
                if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
                }
                
                $objItem = $modelClass::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]));
                }
            }
            
            if ($type == \common\models\Pro_vehicle_cost::TYPE_UPKEEP) {
                $objVehicle = \common\models\Pro_vehicle::findById($objFormData->vehicle_id);
                if (!$objVehicle) {
                    return self::errorResult(\Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle')]));
                }
            }
            
            if (!$objFormData->save($objItem)) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            if (!$objItem->save()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
            
            if ($type == \common\models\Pro_vehicle_cost::TYPE_UPKEEP) {
                if ($objVehicle) {
                    $lastUpkeepMileage = $objVehicle->last_upkeep_mileage;
                    if ($lastUpkeepMileage < $objItem->bind_id) {
                        $lastUpkeepMileage = $objItem->bind_id;
                    }
                    if ($objVehicle->cur_kilometers < $lastUpkeepMileage) {
                        $objVehicle->cur_kilometers = $lastUpkeepMileage;
                    }
                    elseif ($objVehicle->cur_kilometers > $lastUpkeepMileage) {
                        $lastUpkeepMileage = $objVehicle->cur_kilometers;
                    }
                    $objVehicle->last_upkeep_mileage = $lastUpkeepMileage;
                    if ($objItem->cost_time > $objVehicle->last_upkeep_time) {
                        $objVehicle->last_upkeep_time = $objItem->cost_time;
                    }

                    $objVehicle->updateNextMaintenanceCheckPoint();
                    $objVehicle->save();
                }
            }

            $objExpenditureOrder = \common\models\Pro_expenditure_order::findByTypeAndBindId($expenditureType, $objItem->id);
            if ($objExpenditureOrder) {
                $objExpenditureOrder->updateWithCostOrder($objItem);
            }
            else {
                $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
                if ($officeId <= 0) {
                    $officeId = $objItem->getVehicleBelongOfficeId();
                }
                $objExpenditureOrder = \common\models\Pro_expenditure_order::createWithCostOrder($objItem, $expenditureType, $officeId);
            }
            $objExpenditureOrder->save();

            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = 'refreshCurrentX';
            
        }while(0);
        return $arrResult;
    }
    
    public static function processModelEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            }
            
            $objFormData = new \backend\models\Form_pro_vehicle_model();
            // echo "<pre>";
            // var_dump(\Yii::$app->request->post());
            // echo "</pre>";exit;
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                $objItem = new \common\models\Pro_vehicle_model();
            }
            else {
                $objItem = \common\models\Pro_vehicle_model::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]));
                }
            }
            
            if (!$objFormData->save($objItem)) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if (!$objItem->save()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = 'refreshCurrentX';
        
        }while(0);
        return $arrResult;
    }
    
    public static function processBrandEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            }
            
            $objFormData = new \backend\models\Form_pro_vehicle_brand();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_vehicle_brand::findOne(['name' => $objFormData->name])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>\Yii::t('locale', 'brand')]), 300);
                }
                $objItem = new \common\models\Pro_vehicle_brand();
            }
            else {
                $objItem = \common\models\Pro_vehicle_brand::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('carrental', 'Brand')]));
                }
            }
            
            if (!$objFormData->save($objItem)) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if (!$objItem->save()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = intval(\Yii::$app->request->getParam('skiprefresh')) ? '' : 'refreshCurrentX';
        
        }while(0);
        return $arrResult;
    }
    
    public static function processFeeplanEdit()
    {
        $yiiRequester = \Yii::$app->request;
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = $yiiRequester->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            $arrSources = \common\models\Pro_vehicle_fee_plan::getSourceTypesArray();
            $tmpFormData = new \backend\models\Form_pro_vehicle_fee_plan();
            $formName = $tmpFormData->formName();
            $arrSavingObjects = [];
            
            $vehicleModelId = intval($yiiRequester->post('vehicle_model_id'));
            $officeId = intval($yiiRequester->post('office_id'));
            if ($authOfficeId >= 0) {
                if ($officeId == 0 || $officeId != $authOfficeId) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
                }
            }
            if (!\common\models\Pro_vehicle_model::findById($vehicleModelId)) {
                return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle model')]));
            }
            if ($officeId > 0 && !\common\models\Pro_office::findById($officeId)) {
                return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Office')]));
            }
            
            foreach ($arrSources as $source => $sourceText) {
                $scope = $formName.$source;
                if ($yiiRequester->post($scope)) {
                    $objFormData = new \backend\models\Form_pro_vehicle_fee_plan();
                    $objFormData->source = $source;
                    $objFormData->vehicle_model_id = $vehicleModelId;
                    $objFormData->office_id = $officeId;
                    $objFormData->status = \common\models\Pro_vehicle_fee_plan::STATUS_NORMAL;
                    if (!$objFormData->load($yiiRequester->post(), $scope)) {
                        $errText = $objFormData->getErrorAsHtml();
                        return self::errorResult(\Yii::t('carrental', 'Saving {name} vehicle fee plan failed!', ['name'=>$sourceText]).'<br />'.(empty($errText) ? '' : $errText));
                    }
                    if (empty($objFormData->price_default)) {
                        if ($source == \common\models\Pro_vehicle_fee_plan::DEFAULT_SOURCE) {
                            return self::errorResult(\Yii::t('carrental', '{field} is required in {name} fee plan!', ['name'=>$sourceText, 'field'=>$objFormData->getActiveRecordModel()->getAttributeLabel('price_default')]));
                        }
                        continue;
                    }
                    $objItem = \common\models\Pro_vehicle_fee_plan::findOne(['source'=>$objFormData->source, 'vehicle_model_id'=> $objFormData->vehicle_model_id, 'office_id'=> $objFormData->office_id]);
                    if (!$objItem) {
                        $objItem = new \common\models\Pro_vehicle_fee_plan();
                    }

                    if (!$objFormData->save($objItem)) {
                        $errText = $objFormData->getErrorAsHtml();
                        return self::errorResult(\Yii::t('carrental', 'Saving {name} vehicle fee plan failed!', ['name'=>$sourceText]).'<br />'.(empty($errText) ? '' : $errText));
                    }

                    $arrSavingObjects[] = $objItem;
                }
            }
            
            foreach ($arrSavingObjects as $row) {
                if (!$row->save()) {
                    return self::errorResult(\Yii::t('carrental', 'Saving {name} vehicle fee plan failed!', ['name'=>$arrSources[$row->source]]));
                }
            }
            
            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = 'refreshCurrentX';
        
        }while(0);
        return $arrResult;
    }
    
    public static function processViolationEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $vehicleId = intval(\Yii::$app->request->getParam('vehicle_id'));
            $orderId = intval(\Yii::$app->request->getParam('order_id'));
            $inquiryId = intval(\Yii::$app->request->getParam('inquiryId'));
			
            if (!$vehicleId) {
                return self::errorResult(\Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
            }
            $objVehicle = \common\models\Pro_vehicle::findById($vehicleId);
            if (!$objVehicle) {
                return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]), 300);
            }
            
            $objFormData = new \backend\models\Form_pro_vehicle_violation();
            $objFormData->vehicle_id = $vehicleId;
            $objFormData->order_id = $orderId;
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                if ($errText) {
                    Yii::error($errText, 'order');
                }
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText) , 300);
            }
            
            $curTime = time();
            $objViolation = null;
            if ($action == 'insert') {
                $objViolation = new \common\models\Pro_vehicle_violation();
                $objFormData->save($objViolation);
                $objViolation->vehicle_id = $vehicleId;
                $objViolation->order_id = $orderId;
            }
            else if ($action == 'update') {
                $violationId = $objFormData->id; //intval(\Yii::$app->request->getParam('id'));
                $objViolation = \common\models\Pro_vehicle_violation::findById($violationId);
                if (!$objViolation) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'), 300);
                }
                else {
                    $objFormData->save($objViolation);
                }
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
            }
			
			if($inquiryId > 0){
				$objInquiry = \common\models\Pro_violation_inquiry::find()->where(['id'=>$inquiryId])->one();
				if(isset($objInquiry) && $objInquiry->status == 0){
					\common\models\Pro_violation_inquiry::updateAll(['status' => 1,'orderid'=>$orderId],[ 'id'=>$inquiryId]); 
				}
			}
			
            if ($objFormData->hasErrors()) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if (!$objViolation->save()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = 'refreshCurrent';
        
        }while(0);
        return $arrResult;
    }
    
}
