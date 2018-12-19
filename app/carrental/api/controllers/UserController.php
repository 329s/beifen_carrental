<?php
namespace frontend\controllers;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * Site controller
 */
class UserController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'csrf' => [
                'class' => \common\helpers\NoCsrfBehavior::className(),
                'controller' => $this,
                'actions' => [
                    'login',
                    'logout',
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function beforeAction($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);
            return false;
        }
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
        $arrVerifys[] = \frontend\components\ApiModule::KEY;

        $mySign = md5(implode("|", $arrVerifys));
        if ($mySign == $sign) {
            return true;
        }

        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);
        return false;
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['phone', 'password'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            // if (!\Yii::$app->user->isGuest) {
            //    break;
            // }
            
            $model = new \common\models\Form_pub_user_login();
            $model->account = $params['phone'];
            $model->password = $params['password'];
            
            if ($model->login()) {
                $arrData['user_id'] = \Yii::$app->user->id;
                $arrData['account'] = $model->account;
                $arrData[\Yii::$app->request->csrfParam] = \Yii::$app->request->getCsrfToken();
            } else {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = $model->getErrorDebugString();
            }
            
        }while(0);
        echo json_encode($arrData);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        echo json_encode(['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['phone', 'code', 'password'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $phone = $params['phone'];
            $code = $params['code'];
            $zone = (isset($params['zone']) ? $params['zone'] : '86');
            
            $verifyResult = \common\components\UserModule::verifyUserPhoneSmsCode($phone, $code, $zone);
            if (!$verifyResult[0]) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_PHONE_CODE_INVALID;
                $arrData['desc'] = $verifyResult[1];
                break;
            }
            
            $model = new \frontend\models\SignupForm();
            $model->account = $phone;
            $model->email = "";
            $model->password = $params['password'];
            $model->invited_code = \yii\helpers\ArrayHelper::getValue($params, 'invite_code', '');
            
            $user = $model->signup();
            if ($user) {
                \Yii::$app->getUser()->login($user);
                $arrData['user_id'] = \Yii::$app->user->id;
                $arrData['account'] = $user->account;
                $arrData[\Yii::$app->request->csrfParam] = \Yii::$app->request->getCsrfToken();
                
                \common\components\SmsComponent::send($phone, \common\components\Consts::KEY_SMS_USER_SIGNUP, ['CNAME'=>$user->account]);
            } else {
                $errText = $model->getErrorDebugString();
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_SIGNUP_FAILED;
                $arrData['desc'] = empty($errText) ? \Yii::t('locale', 'Telephone No. already registered') : $errText;
            }
            
        }while(0);
        echo json_encode($arrData);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new \frontend\models\PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new \frontend\models\ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    
    public function actionReset_password() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['phone', 'code', 'password'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $phone = $params['phone'];
            $code = $params['code'];
            $zone = (isset($params['zone']) ? $params['zone'] : '86');
            
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['account' => $phone]);
            $user = $cdb->one();

            if (!$user) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_NOT_EXISTS;
                $arrData['desc'] = \Yii::t('locale', 'Telephone No. not registered');
                break;
            }
            
            $verifyResult = \common\components\UserModule::verifyUserPhoneSmsCode($phone, $code, $zone);
            if (!$verifyResult[0]) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_PHONE_CODE_INVALID;
                $arrData['desc'] = $verifyResult[1];
                break;
            }
            
            $user->setPassword($params['password']);
            $user->generateAuthKey();
            if ($user->save()) {
                $arrData['user_id'] = $user->id;
                $arrData['account'] = $user->account;
                $arrData[\Yii::$app->request->csrfParam] = \Yii::$app->request->getCsrfToken();
            }
            else {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_SET_PASSWORD_FAILED;
                $arrData['desc'] = \Yii::t('locale', 'Sorry, the operation failed!');
            }
            
        }while(0);
        echo json_encode($arrData);
    }
    
    public function actionEditinfo() {
        $arrData = ['result'=>\frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do
        {
            if (\Yii::$app->user->isGuest) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = 'Not login.';
                break;
            }
            
            $user = \common\models\Pub_user::findOne(['id' => \Yii::$app->user->id]);

            if (!$user) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = 'Not login.';
                break;
            }
            
            $params = \Yii::$app->request->post();
            $requiredFields = ['name', 'card_type', 'card_id', 'phone', 'email'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            
            $userInfo = null;
            if ($user->info_id > 0) {
                $cdb = \common\models\Pub_user_info::find();
                $cdb->where(['id'=>$user->info_id]);
                $userInfo = $cdb->one();
            }
            
            $objFormData = new \common\models\Form_pub_user_info();
            if ($userInfo) {
                $objFormData->loadFromModel($userInfo);
            }
            else {
                $objFormData->user_type = \common\models\Pub_user_info::USER_TYPE_PERSONAL;
                $objFormData->vip_level = \common\models\Pub_user_info::VIP_LEVEL_NORMAL;
                $objFormData->driver_license_type = \common\models\Pub_user_info::DRIVER_LICENSE_TYPE_C1;
            }
            $objFormData->name = $params['name'];
            $objFormData->identity_type = $params['card_type'];
            $objFormData->identity_id = $params['card_id'];
            $objFormData->telephone = $params['phone'];
            $objFormData->email = $params['email'];
            
            if (!\common\helpers\Utils::validatePhoneno($objFormData->telephone)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid telephone No.!');
                break;
            }
            
            $verifyResult = \common\components\UserModule::validateIdentity($objFormData->identity_type, $objFormData->identity_id);
            if ($verifyResult[0] < 0) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = $verifyResult[1];
                break;
            }
            
            if (!$userInfo) {
                $objUserInfo = \common\models\Pub_user_info::findOne(['identity_id'=>$objFormData->identity_id]);
                if ($objUserInfo) {
                    // if the user info has no other user binded, bind it anyway.
                    if (\common\models\Pub_user::findOne(['info_id'=>$objUserInfo->id])) {
                        $arrData['result'] = \frontend\components\ApiModule::CODE_USER_IDENTITY_ALREADY_BINDED;
                        $arrData['desc'] = \Yii::t('locale', 'The identity information had already been bind.');
                        break;
                    }
                    
                    if ($objUserInfo->telephone != $objFormData->telephone
                        || $objUserInfo->name != $objFormData->name) {
                        $arrData['result'] = \frontend\components\ApiModule::CODE_USER_IDENTITY_ALREADY_BINDED;
                        $arrData['desc'] = \Yii::t('locale', 'The information not true.');
                        break;
                    }
                    
                    // ....
                    $userInfo = $objUserInfo;
                }
            }
            
            if (!$objFormData->validate()) {
                $user->info_id = $userInfo->id;
                $user->save();
                $errText = $objFormData->getErrorDebugString();
                \Yii::error("user:{$user->id} edit real name info failed with error:{$errText}", 'user');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                // $arrData['desc'] = $errText;
                $arrData['desc'] = \Yii::t('locale', 'The information already relation');
                break;
            }
            
            $isAdd = false;
            if (!$userInfo) {
                $userInfo = new \common\models\Pub_user_info();
                $isAdd = true;
            }
            
            if (!$objFormData->save($userInfo)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Cannot load the data.');
                break;
            }
            
            $checkInviteAward = false;
            if (empty($userInfo->invited_code) && $user->invited_code) {
                $userInfo->invite_code = $user->invited_code;
                $checkInviteAward = true;
            }
            
            if (!$userInfo->save()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_IDENTITY_INFO_ALREADY_EXISTS;
                $arrData['desc'] = \Yii::t('locale', 'There is a user info already registered.');
                break;
            }
            
            if ($checkInviteAward) {
                \common\components\UserModule::onInvitedUser($userInfo->invited_code);
            }
            
            if ($isAdd) {
                $user->info_id = $userInfo->id;
                $user->save();
            }
        }while (0);
        
        echo json_encode($arrData);
    }
    
    public function actionGetinfo() {
        $arrData = ['result'=> \frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do
        {
            if (\Yii::$app->user->isGuest) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => \Yii::$app->user->id]);
            $user = $cdb->one();

            if (!$user) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            $userInfo = \common\models\Pub_user_info::findById($user->info_id);
            if (!$userInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            
            $orderCount = 0;
            if ($userInfo) {
                $cdb = \common\models\Pro_vehicle_order::find(true);
                $cdb->where(['user_id' => $userInfo->id]);
                $orderCount = $cdb->count();
            }
            
            $arrData['name'] = ($userInfo ? $userInfo->name : null);
            $arrData['card_type'] = ($userInfo ? $userInfo->identity_type : null);
            $arrData['card_id'] = ($userInfo ? $userInfo->identity_id : null);
            $arrData['phone'] = ($userInfo ? $userInfo->telephone : null);
            $arrData['email'] = ($userInfo ? $userInfo->email : null);
            $arrData['card_count'] = (empty($userInfo) || $userInfo->bank_card_no ? 1 : 0);
            $arrData['order_count'] = $orderCount;
            $arrData['invite_code'] = $userInfo->getInviteCode();
            
        }while (0);
        echo json_encode($arrData);
    }
    
}
