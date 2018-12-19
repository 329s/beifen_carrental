<?php

namespace backend\components;

/**
 * Description of InternalServiceService
 *
 * @author kevin
 */
class InternalServiceService extends BaseService
{
    
    public static function processApplyingEdit() {
        $arrResult = [false, 'Unknown error'];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            $itemId = intval(\Yii::$app->request->getParam('id'));
            $objItem = $itemId ? \backend\models\Pro_inner_applying::findById($itemId) : null;
            $objFormData = new \backend\models\Form_pro_inner_applying();

            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                $arrResult[1] = (empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText);
                break;
            }

            $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            $authoration = \backend\components\AdminModule::getCurRoleAuthoration();

            $objVehicle = null;
            if ($objFormData->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_BELONG_OFFICE
                    || $objFormData->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_STOP_OFFICE
                    || $objFormData->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_INNER_USE) {
                $objVehicle = \common\models\Pro_vehicle::findById($objFormData->plate_number, 'plate_number');
                if (!$objVehicle) {
                    $arrResult[1] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                    break;
                }
            }

            if ($action == 'insert') {
                $objItem = new \backend\models\Pro_inner_applying();
                $objFormData->save($objItem);
                $objItem->status = \backend\models\Pro_inner_applying::STATUS_APPLYING;
                if ($objVehicle) {
                    $objItem->approval_office_id = $objVehicle->belong_office_id;
                }
            }
            else if ($action == 'update') {
                if (!$objItem) {
                    $arrResult[1] = \Yii::t('locale', 'ID should not be empty!');
                    break;
                }
                else {
                    if ($objItem->status >= \backend\models\Pro_inner_applying::STATUS_APPROVED) {
                        if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER
                                || ($authOfficeId != \common\components\OfficeModule::HEAD_OFFICE_ID && $authOfficeId != $objItem->approval_office_id)) {
                            $arrResult[1] = \Yii::t('locale', 'Sorry, no operating privileges for current user!');
                            break;
                        }
                    }
                    else {
                        if (\Yii::$app->user->id != $objItem->created_by 
                                && $authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER
                                && $authOfficeId != \common\components\OfficeModule::HEAD_OFFICE_ID
                                && $authOfficeId != $objItem->office_id) {
                            $arrResult[1] = \Yii::t('locale', 'Sorry, no operating privileges for current user!');
                            break;
                        }
                    }
                    $objFormData->save($objItem);
                }
            }
            else {
                $arrResult[1] = \Yii::t('locale', 'Sorry, the operation failed!');
                break;
            }

            if ($objItem->save()) {
                $arrResult[0] = true;
                $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            } else {
                $arrResult[1] = \Yii::t('locale', 'Sorry, the operation fails, please re-submit!');
                break;
            }
        }while(0);
        return $arrResult;
    }
    
    public static function processApplyingApproval() {
        $arrResult = [false, 'Unknown error'];
        do
        {
            $itemId = intval(\Yii::$app->request->getParam('id'));
            if (!$itemId) {
                $arrResult[1] = \Yii::t('locale', 'ID should not be empty!');
                break;
            }
            $objItem = \backend\models\Pro_inner_applying::findById($itemId);
            $objFormData = new \backend\models\Form_pro_inner_applying();

            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                $arrResult[1] = (empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText);
                break;
            }

            $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            $authoration = \backend\components\AdminModule::getCurRoleAuthoration();

            if (!$objItem) {
                $arrResult[1] = \Yii::t('locale', 'ID should not be empty!');
                break;
            }
            else {
                if ($objItem->status != \backend\models\Pro_inner_applying::STATUS_APPLYING) {
                    $arrResult[1] = \Yii::t('carrental', 'The status were not {status}, the operation could not be completed.', ['status'=> \backend\models\Pro_inner_applying::getStatusArray()[\backend\models\Pro_inner_applying::STATUS_APPLYING]]);
                    break;
                }
                if ($objFormData->status != \backend\models\Pro_inner_applying::STATUS_APPROVED
                        && $objFormData->status != \backend\models\Pro_inner_applying::STATUS_REJECTED) {
                    $arrResult[1] = \Yii::t('carrental', 'Approval status not supported.');
                    break;
                }
                $objFormData->save($objItem);
            }

            if ($authOfficeId != \common\components\OfficeModule::HEAD_OFFICE_ID
                && $authOfficeId != $objItem->approval_office_id) {
                $arrResult[1] = \Yii::t('locale', 'Sorry, no operating privileges for current user!');
                break;
            }
            if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                $arrResult[1] = \Yii::t('locale', 'Sorry, current user could not execute the approve opearation.');
                break;
            }

            $arrExtraSavingObjects = [];
            if ($objItem->status == \backend\models\Pro_inner_applying::STATUS_APPROVED) {
                $objItem->approved_at = time();
                if ($objItem->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_STOP_OFFICE
                        || $objItem->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_BELONG_OFFICE) {
                    $objVehicle = \common\models\Pro_vehicle::findById($objItem->plate_number, 'plate_number');
                    if (!$objVehicle) {
                         $arrResult[1] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                         break;
                    }
                    if (!\common\models\Pro_office::findById($objItem->office_id)) {
                         $arrResult[1] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Office')]);
                         break;
                    }

                    // 车辆是否正在出租
                    //$timeStart = time();
                    //$timeEnd = $timeStart+86400;
                    if (\common\components\OrderModule::hasVehicleRented($objVehicle->id, 0, 0)) {
                         $arrResult[1] = \Yii::t('locale', 'Sorry, vehicle already rented!');
                         break;
                    }
                    if ($objItem->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_STOP_OFFICE) {
                        // check vehicle

                        $objVehicle->stop_office_id = $objItem->office_id;
                        $arrExtraSavingObjects[$objVehicle->id] = $objVehicle;
                    }
                    elseif ($objItem->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_BELONG_OFFICE) {
                        // check vehicle
                       // if ($authoration < \backend\models\Rbac_role::AUTHORITY_DOMAIN_MANAGER) {
                            // $arrResult[1] = \Yii::t('locale', 'Sorry, current user could not execute the approve opearation.');
                            // break;
                        // }
						if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                            $arrResult[1] = \Yii::t('locale', 'Sorry, current user could not execute the approve opearation.');
                            break;
                        }

                        $objVehicle->belong_office_id = $objItem->office_id;
                        $objVehicle->stop_office_id = $objItem->office_id;
                        $arrExtraSavingObjects[$objVehicle->id] = $objVehicle;
                    }
                    if ($objItem->vehicle_inbound_mileage && $objItem->vehicle_inbound_mileage > $objVehicle->cur_kilometers) {
                        $objVehicle->cur_kilometers = $objItem->vehicle_inbound_mileage;
                        $arrExtraSavingObjects[$objVehicle->id] = $objVehicle;
                    }
                    if ($objItem->vehicle_outbound_mileage && $objItem->vehicle_outbound_mileage > $objVehicle->cur_kilometers) {
                        $objVehicle->cur_kilometers = $objItem->vehicle_outbound_mileage;
                        $arrExtraSavingObjects[$objVehicle->id] = $objVehicle;
                    }
                }
                elseif ($objItem->type == \backend\models\Pro_inner_applying::TYPE_VEHICLE_INNER_USE) {
                    $objVehicle = \common\models\Pro_vehicle::findById($objItem->plate_number, 'plate_number');
                    if (!$objVehicle) {
                        $arrResult[1] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                        break;
                    }

                    $objVehicle->status = \common\models\Pro_vehicle::STATUS_MAINTENANCE;
                    if ($objItem->vehicle_inbound_mileage && $objItem->vehicle_inbound_mileage > $objVehicle->cur_kilometers) {
                        $objVehicle->cur_kilometers = $objItem->vehicle_inbound_mileage;
                    }
                    if ($objItem->vehicle_outbound_mileage && $objItem->vehicle_outbound_mileage > $objVehicle->cur_kilometers) {
                        $objVehicle->cur_kilometers = $objItem->vehicle_outbound_mileage;
                    }
                    $arrExtraSavingObjects[] = $objVehicle;
                }
            }

            if ($objItem->save()) {
                foreach ($arrExtraSavingObjects as $obj) {
                    $obj->save();
                }
                $arrResult[0] = true;
                $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            } else {
                $arrResult[1] = \Yii::t('locale', 'Sorry, the operation fails, please re-submit!');
                break;
            }
        }while(0);
        return $arrResult;
    }
    
    public static function processApplyingDelete() {
        $arrResult = [false, 'Unknown error'];
        do
        {
            $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            if ($authOfficeId != \common\components\OfficeModule::HEAD_OFFICE_ID) {
                $arrResult[1] = \Yii::t('locale', 'Sorry, no operating privileges for current user!');
                break;
            }
            $intID = intval(\Yii::$app->request->getParam('id'));
            if (!$intID) {
                $arrResult[1] = \Yii::t('locale', 'ID should not be empty!');
                break;
            }

            $objData = \backend\models\Pro_inner_applying::findById($intID);

            if (!$objData) {
                $arrResult[1] = \Yii::t('locale', 'Data does not exist!');
                break;
            }

            if ($objData->status != \backend\models\Pro_inner_applying::STATUS_APPLYING) {
                $arrResult[1] = \Yii::t('carrental', 'The status were not {status}, the operation could not be completed.', ['status'=> \backend\models\Pro_inner_applying::getStatusArray()[\backend\models\Pro_inner_applying::STATUS_APPLYING]]);
                break;
            }

            $objData->delete();
            
            $arrResult[0] = true;
            $arrResult[1] = \Yii::t('locale', 'Deleted successfully!');
        }while(0);
        return $arrResult;
    }
    
}
