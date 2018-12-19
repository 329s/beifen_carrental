<?php

namespace backend\components;

/**
 * Description of OfficeService
 *
 * @author kevin
 */
class OfficeService extends BaseService
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
            
            $objFormData = new \backend\models\Form_pro_office();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (\common\models\Pro_office::findOne(['fullname' => $objFormData->fullname])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['name'=>\Yii::t('carrental', 'office')]));
                }
                $objItem = new \common\models\Pro_office();
            }
            else {
                $objItem = \common\models\Pro_office::findById($objFormData->id);
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
    
}
