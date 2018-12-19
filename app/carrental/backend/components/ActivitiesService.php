<?php

namespace backend\components;

/**
 * Description of ActivitiesService
 *
 * @author kevin
 */
class ActivitiesService extends BaseService
{
    
    public static function processImageActivityEdit()
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
            
            $objFormData = new \backend\models\Form_pro_activity_image();
            if ($action == 'insert') {
                $objFormData->setScenario($action);
            }
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_activity_image::findOne(['name' => $objFormData->name])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>\Yii::t('locale', 'activity')]));
                }
                $objActivityImage = new \common\models\Pro_activity_image();
            }
            else {
                $objActivityImage = \common\models\Pro_activity_image::findById($objFormData->id);
                if (!$objActivityImage) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
                }
            }
            
            if ($objFormData->save($objActivityImage)) {
                if ($objActivityImage->save()) {
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
    
    public static function processTextActivityEdit()
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
            
            $objFormData = new \backend\models\Form_pro_activity_info();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_activity_info::findOne(['name' => $objFormData->name])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>\Yii::t('locale', 'activity')]));
                }
                $objActivityInfo = new \common\models\Pro_activity_info();
            }
            else {
                $objActivityInfo = \common\models\Pro_activity_info::findById($objFormData->id);
                if (!$objActivityInfo) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
                }
            }
            
            if ($objFormData->save($objActivityInfo)) {
                if ($objActivityInfo->save()) {
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
    
}
