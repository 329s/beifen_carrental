<?php

namespace backend\components;

/**
 * Description of OptionsService
 *
 * @author kevin
 */
class OptionsService extends BaseService
{
    
    public static function processFestivalEdit()
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
            
            $objFormData = new \backend\models\Form_pro_festival();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_festival::findOne(['name' => $objFormData->name])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>\Yii::t('locale', 'Festival name')]));
                }
                $objFesitval = new \common\models\Pro_festival();
            }
            else {
                $objFesitval = \common\models\Pro_festival::findById($objFormData->id);
                if (!$objFesitval) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
                }
            }
            
            if ($objFormData->save($objFesitval)) {
                $checkText = \common\components\OptionsModule::checkFestivalTime($objFesitval);
                if (!empty($checkText)) {
                    return self::errorResult($checkText);
                }
                else if ($objFesitval->save()) {
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
    
    public static function processVehicleValidationOptionsEdit()
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
            
            $objFormData = new \backend\models\Form_pro_vehicle_validation_config();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_vehicle_validation_config::findOne(['name' => $objFormData->name])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>\Yii::t('locale', 'Name')]), 300);
                }
                $objItem = new \common\models\Pro_vehicle_validation_config();
            }
            else {
                $objItem = \common\models\Pro_vehicle_validation_config::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Item')]));
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
    
}
