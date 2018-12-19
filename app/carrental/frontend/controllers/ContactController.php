<?php
namespace frontend\controllers;

/**
 * Contact controller
 */
class ContactController extends \yii\web\Controller
{
    private $actionKey = \frontend\components\ApiModule::KEY;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
        ];
    }
    
    public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
        //return true;
        $arrParams = [];
        $sign = '';
        $params = \Yii::$app->request->post();
        foreach ($params as $k => $v) {
            if ($k == 'sign') {
                $sign = $v;
            }
            else {
                $arrParams[$k] = $v;
            }
        }
        
        $arrVerifys = [];
        ksort($arrParams);
        foreach ($arrParams as $k => $v) {
            $k = strval($k);
            $v = strval($v);
            $arrVerifys[] = "{$k}={$v}";
        }
        $arrVerifys[] = $this->actionKey;
        
        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }
        
        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;
    }
    
    public function actionShop_comment() {
        $shopId = intval(\Yii::$app->request->post('sid'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $message = \Yii::$app->request->post('message');
            
            if (empty($shopId) || empty($message)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }

            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            
            $objUser = \common\models\Pub_user::findIdentity(\Yii::$app->user->id);
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            
            $objShop = \common\models\Pro_office::findById($shopId);
            if (!$objShop) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objItem = new \common\models\Pro_office_comments();
            $objItem->office_id = $shopId;
            $objItem->user_id = $objUser->id;
            $objItem->comment = $message;
            $objItem->status = \common\models\Pro_office_comments::STATUS_NORMAL;
            
            $objItem->save();
            
        }while (0);

        echo json_encode($arrData);
    }
    
    public function actionFeedback() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $message = \Yii::$app->request->post('message');
            
            if (empty($message)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }

            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            
            $objUser = \common\models\Pub_user::findIdentity(\Yii::$app->user->id);
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            
            $objItem = new \common\models\Pro_feedback();
            $objItem->user_id = $objUserInfo->id;
            $objItem->message = $message;
            $objItem->status = \common\models\Pro_feedback::STATUS_NORMAL;
            
            $objItem->save();
            
        }while (0);

        echo json_encode($arrData);
    }
    
    public function actionJoin_application() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $name = \Yii::$app->request->post('name');
            $phone = \Yii::$app->request->post('phone');
            $address = \Yii::$app->request->post('address');
            $mail = \Yii::$app->request->post('mail');
            $message = \Yii::$app->request->post('message');
            
            if (empty($message) || empty($name) || empty($phone) || empty($address)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objUser = null;
            
            if (\Yii::$app->user->getIsGuest()) {
                if (false) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                    $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                    break;
                }
            }
            else {
                $objUser = \common\models\Pub_user::findIdentity(\Yii::$app->user->id);
                //if (!$objUser) {
                //    $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                //    $arrData['desc'] = \Yii::t('locale', 'Login required.');
                //    break;
                //}

                //$objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
                //if (!$objUserInfo) {
                //    $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                //    $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                //    break;
                //}
            }
            
            
            $objItem = new \common\models\Pro_join_applying();
            $objItem->user_id = $objUser ? $objUser->id : 0;
            $objItem->name = $name;
            $objItem->phone = $phone;
            $objItem->address = $address;
            $objItem->mail = $mail;
            $objItem->message = $message;
            $objItem->status = \common\models\Pro_join_applying::STATUS_NORMAL;
            
            $objItem->save();
            
        }while (0);

        echo json_encode($arrData);
    }
    
    public function actionLongrent_application() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $name = \Yii::$app->request->post('name');
            $phone = \Yii::$app->request->post('phone');
            $company = \Yii::$app->request->post('company');
            $mail = \Yii::$app->request->post('mail');
            $message = \Yii::$app->request->post('message');
            $officeIdTakeCar = intval(\Yii::$app->request->post('sid'));
            $startTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->post('start_time'));
            $endTime = \common\helpers\Utils::toTimestamp(\Yii::$app->request->post('end_time'));
            
            if (empty($officeIdTakeCar) || empty($message) || empty($name) || empty($phone) || empty($company)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $curTime = time();
            if ($startTime < $curTime) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            if ($endTime < $startTime) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objUser = null;
            
            if (\Yii::$app->user->getIsGuest()) {
                if (false) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                    $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                    break;
                }
            }
            else {
                $objUser = \common\models\Pub_user::findIdentity(\Yii::$app->user->id);
                //if (!$objUser) {
                //    $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                //    $arrData['desc'] = \Yii::t('locale', 'Login required.');
                //    break;
                //}

                //$objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
                //if (!$objUserInfo) {
                //    $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                //    $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                //    break;
                //}
            }
            
            $objShop = \common\models\Pro_office::findById($officeIdTakeCar);
            if (!$objShop) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }
            
            $objItem = new \common\models\Pro_long_rent_applying();
            $objItem->user_id = $objUser ? $objUser->id : 0;
            $objItem->name = $name;
            $objItem->phone = $phone;
            $objItem->company = $company;
            $objItem->mail = $mail;
            $objItem->message = $message;
            $objItem->office_id_take_car = $officeIdTakeCar;
            $objItem->start_time = $startTime;
            $objItem->end_time = $endTime;
            $objItem->status = \common\models\Pro_long_rent_applying::STATUS_NORMAL;
            
            $objItem->save();
            
        }while (0);

        echo json_encode($arrData);
    }
    
}
