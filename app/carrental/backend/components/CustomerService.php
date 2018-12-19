<?php

namespace backend\components;

/**
 * Description of CustomerService
 *
 * @author kevin
 */
class CustomerService extends BaseService
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
            $objUserInfo = null;
            
            $objFormData = new \common\models\Form_pub_user_info();
            $objFormCardData = new \backend\models\Form_pro_member_card_useredit();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            if (!$objFormCardData->load(\Yii::$app->request->post())) {
                $errText = $objFormCardData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
           
            // 驾照时间改时间戳
            $objFormData->birthday        			 = strtotime($objFormData->birthday);
            $objFormData->identity_start_time        = strtotime($objFormData->identity_start_time);
            $objFormData->driver_license_time        = strtotime($objFormData->driver_license_time);
            $objFormData->driver_license_expire_time = strtotime($objFormData->driver_license_expire_time);
            $objFormData->identity_end_time          = strtotime($objFormData->identity_end_time .' 23:59:59');
            $objFormData->credit_card_expire_time    = strtotime($objFormData->credit_card_expire_time.' 23:59:59');
            $objFormData->unfreeze_at       		 = strtotime($objFormData->unfreeze_at);

            $identityVerifyText = false;
            $verifyResult = \common\components\UserModule::verifyUserIdentityCardNo($objFormData->identity_id);
            if ($verifyResult[0] < 0) {
                return self::errorResult($verifyResult[1]);
            }
            elseif ($verifyResult[0] > 0) {
                $identityVerifyText = $verifyResult[1]."<br />".\Yii::t('locale', 'Please check if the identity number is correct.');
            }
            
            if ($action == 'insert') {
                if (\common\models\Pub_user_info::findOne(['identity_id' => $objFormData->identity_id])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>\Yii::t('locale', 'Identity card').$objFormData->identity_id]));
                }
                
                $objUserInfo = new \common\models\Pub_user_info();
                $objFormData->save($objUserInfo);
            }
            elseif ($action == 'update') {
                $objUserInfo = \common\models\Pub_user_info::findById($objFormData->id);
                if (!$objUserInfo) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
                }
                $objFormData->save($objUserInfo);
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
            }
            
            if (!empty($objFormCardData->card_no)) {
                $objMemberCard = \common\models\Pro_member_card::findOne(['card_no'=>$objFormCardData->card_no]);
                if (!$objMemberCard) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Member card')]));
                }
                if ($objMemberCard->status == \common\models\Pro_member_card::STATUS_DISABLED) {
                    return self::errorResult(\Yii::t('carrental', 'Current member card were disabled!'));
                }
                
                $userId = 0;
                if ($objUserInfo->id) {
                    $userId = $objUserInfo->id;
                }
                if (\common\models\Pub_user_info::find()->where(['and', ['<>', 'id', $userId], ['member_id'=>$objMemberCard->id]])->exists()) {
                    return self::errorResult(\Yii::t('carrental', 'Current member card had already been used!'));
                }
                
                $isUpdateMemberCard = false;
                if ($objFormCardData->card_name != $objMemberCard->card_name) {
                    //$objMemberCard->card_name = $objFormCardData->card_name;
                    //$isUpdateMemberCard = true;
                }
                if ($objMemberCard->status == \common\models\Pro_member_card::STATUS_LOCKED) {
                    $objMemberCard->activated_at = $objMemberCard->activated_at;
                    $objMemberCard->status = \common\models\Pro_member_card::STATUS_ACTIVITED;
                    $isUpdateMemberCard = true;
                }
                if ($isUpdateMemberCard) {
                    $objMemberCard->edit_user_id = \Yii::$app->user->id;
                    $objMemberCard->save();
                }
                
                $objUserInfo->member_id = $objMemberCard->id;
            }
            else {
                $objUserInfo->member_id = 0;
            }
            
            if ($objUserInfo->save()) {
                $arrResult[0] = Consts::CODE_OK;
                $arrResult['navTabId'] = 'page601001';
                $arrResult['callbackType'] = 'refreshCurrentX';
                if ($identityVerifyText) {
                    $arrResult[1] = $identityVerifyText;
                }
                else {
                    $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
                }
            } else {
                $errText = $objUserInfo->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation fails, please re-submit!') : $errText));
            }
            
        }while(0);
        return $arrResult;
    }
    
    public static function processDelete()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            }

            $intID = intval(\Yii::$app->request->getParam('id'));
            if (!$intID) {
                return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
            }

            $objData = \common\models\Pub_user_info::findById($intID);

            if (!$objData) {
                return self::errorResult(\Yii::t('locale', 'Data does not exist!'));
            }

            \common\models\Pub_user::updateAll(['info_id'=>0], ['info_id'=>$intID]);

            $objData->delete();

            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Deleted successfully!');
            $arrResult['callbackType'] = 'refreshCurrent';
        }while(0);
        return $arrResult;
    }
    
    public static function processConsultEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $objFormData = new \backend\models\Form_pro_user_consult();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            $objItem = null;
            if ($action == 'insert') {               
                $objItem = new \backend\models\Pro_user_consult();
                $objFormData->save($objItem);
            }
            else if ($action == 'update') {
                if (!$objFormData->id) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                $objItem = \backend\models\Pro_user_consult::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                else {
                    $objFormData->save($objItem);
                }
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
            }
            
            if ($objItem->save()) {
                $arrResult[0] = Consts::CODE_OK;
                $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
                $arrResult['callbackType'] = 'refreshCurrent';
            } else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
        }while(0);
        return $arrResult;
    }
    
    public static function processSmsEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $objFormData = new \backend\models\Form_pub_user_sms();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            $objItem = null;
            if ($action == 'insert') {               
                $objItem = new \common\models\Pub_user_sms();
                $objFormData->save($objItem);
            }
            else if ($action == 'update') {
                if (!$objFormData->id) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                $objItem = \common\models\Pub_user_sms::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                else {
                    $objFormData->save($objItem);
                }
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
            }
            
            if ($objItem->save()) {
                $arrResult[0] = Consts::CODE_OK;
                $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
                $arrResult['callbackType'] = 'refreshCurrent';
            } else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
        }while(0);
        return $arrResult;
    }
    
    public static function processSmsDelete()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
            }

            $intID = intval(\Yii::$app->request->getParam('id'));
            if (!$intID) {
                return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
            }

            $objItem = \common\models\Pub_user_sms::findById($intID);

            if (!$objItem) {
                return self::errorResult(\Yii::t('locale', 'Data does not exist!'));
            }

            $objItem->delete();

            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Deleted successfully!');
            $arrResult['callbackType'] = 'refreshCurrent';
        }while(0);
        return $arrResult;
    }
    
    public static function processMembercardEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $objFormData = new \backend\models\Form_pro_member_card();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            if ($action == 'insert') {
                if (!\backend\components\AdminModule::isAuthorizedHeadOffice()) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
                }
                if (\common\models\Pro_member_card::findOne(['card_no' => $objFormData->card_no])) {
                    return self::errorResult(\Yii::t('locale', 'Sorry, this {name} already exists!', ['{name}'=>\Yii::t('locale', 'Name')]), 300);
                }
                $objItem = new \common\models\Pro_member_card();
            }
            elseif ($action == 'update') {
                $objItem = \common\models\Pro_member_card::findById($objFormData->id);
                if (!$objItem) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Item')]));
                }
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
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
