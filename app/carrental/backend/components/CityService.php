<?php

namespace backend\components;

/**
 * Description of CityService
 *
 * @author kevin
 */
class CityService extends BaseService
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
            
            $objFormData = new \backend\models\Form_pro_city();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (false && $objFormData->type >= \common\models\Pro_city::TYPE_CITY) {
                    $objCity = \common\models\Pro_city::findOne(['name' => $objFormData->name]);
                    if ($objCity) {
                        self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>$objFormData->name]), 300);
                        return;
                    }
                }
                $objItem = new \common\models\Pro_city();
            }
            else {
                $objItem = \common\models\Pro_city::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
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
    
    public static function processEditArea()
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
            
            $objFormData = new \backend\models\Form_pro_city_area();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                $objItem = new \common\models\Pro_city_area();
            }
            else {
                $objItem = \common\models\Pro_city_area::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', 'Data does not exist!'));
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
    
}
