<?php
namespace frontend\controllers;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\db\Connection;
use yii\db\Query;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Site controller
 */
class WxappuserController extends \yii\web\Controller
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
                    'editinfo',
                    'get_verify_code',
                    'reset_password',
                    'signup',
                    'login_dynamic',
                ],
            ]
        ];
    }

    public function init(){
        header('Access-Control-Allow-Origin:http://m.yikazc.com');
        header('Access-Control-Allow-Origin:http://yikazc.com');
        header('Access-Control-Allow-Origin:*');
        $this->enableCsrfValidation = false;
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

    public function beforeAction1($action) {
        $preVerify = \common\components\SysmaintenanceService::verifyMaintenanceStatus($action);
        if (!$preVerify[0]) {
            echo json_encode(['result'=> \frontend\components\ApiModule::CODE_ON_MAINTENANCE, 'desc' => $preVerify[1]]);//4
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
        // echo "<pre>";
        // var_dump($arrVerifys);
        // echo "<hr>";
        // var_dump(implode("|", $arrVerifys),$mySign);
        // echo "<hr>";
        // var_dump($sign);
        // echo "</pre>";die;
        if ($mySign == $sign) {
            return true;
        }

        echo json_encode(['result'=> \frontend\components\ApiModule::CODE_INVALID_PACKAGE, 'desc' => \Yii::t('locale', 'Invalid access!')]);//4001
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
        // echo "1";die;
        do
        {
            // ini_set('session.gc_maxlifetime', 300);
            $params = \Yii::$app->request->post();
            // $params = array('phone'=>'18395947721','password'=>md5('s123456789'));
            // $params = array('phone'=>'15067083784','password'=>md5('1234567890'));
            $requiredFields = ['phone', 'password'];
            foreach ($requiredFields as $k) {
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {//0
                break;
            }
            // if (!\Yii::$app->user->isGuest) {
            //    break;
            // }
            
            $model = new \common\models\Form_pub_user_login();
            $model->account = $params['phone'];
            $model->password = $params['password'];
	            // echo "<pre>";
	            // print_r($model);
	            // echo "</pre>";die;
            if ($model->login()) {
                $arrData['user_id'] = \Yii::$app->user->id;
                $arrData['account'] = $model->account;
                $arrData['session_id'] = session_id();
                $arrData[\Yii::$app->request->csrfParam] = \Yii::$app->request->getCsrfToken();
            } else {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
                $arrData['desc'] = $model->getErrorDebugString();
            }
            
        }while(0);
        echo json_encode($arrData);
    }

     /**
     *@desc动态登陆
     *@param $phone
     *@param $code 手机发送的验证码
     */
    public function actionLogin_dynamic() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['phone', 'code'];
            // $arrData['params'] = $params;
            // $params = array('phone'=>'18395947721','code'=>'1235');
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
            
            // $user->setPassword($params['password']);
            $user->generateAuthKey();
           
            if (\Yii::$app->user->login($user,  3600 * 24 * 7)) {
                $arrData['user_id'] = $user->id;
                $arrData['account'] = $user->account;
                // $arrData['isg'] = \Yii::$app->user->isGuest;
                $arrData['session_id'] = session_id();
                $arrData[\Yii::$app->request->csrfParam] = \Yii::$app->request->getCsrfToken();
            }
            else {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_SET_PASSWORD_FAILED;
                $arrData['desc'] = \Yii::t('locale', 'Sorry, the operation failed!');
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
        \Yii::$app->user->logout();

        echo json_encode(['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."]);
    }

    public function actionLogoutwxapp()
    {

        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do{
            $se_id = \Yii::$app->request->post('id');
            // $se_id ='h633mjckln7vg2gvbulu4q2s64';
            if(!$se_id){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc'] = '参数不能为空';
                break;
            }else{
                $obj = \frontend\models\Yii_session::findOne($se_id);
                if($obj){

                    $obj->delete();
                }



            }
        }while (0);
        echo json_encode($arrData);
    }

    public function actionGetstatus($value='')
    {
        # code...
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do{
            $se_id = \Yii::$app->request->post('id');
            if(!$se_id){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc'] = '参数不能为空';
                break;
            }
            $loginStatus = \frontend\models\Yii_session::findOne(['id' => $se_id]); //查询admin_session表中是否有用户的登录记录
            if($loginStatus){
                $arrData['session_id'] = $loginStatus->id;
                $arrData['expire'] = $loginStatus->expire;
                $data = $loginStatus->data;
                $session_data = \frontend\components\CommonModule::unserialize_php($data);
                $arrData['id'] = $session_data['__id'];
                break;
            }else{
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc'] = '身份信息失效,请重新登陆';
                break;
            }
        }while (0);
        echo json_encode($arrData);
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
            /*if (!$verifyResult[0]) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_PHONE_CODE_INVALID;
                $arrData['desc'] = $verifyResult[1];
                break;
            }*/
            
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
    


    /**
     *@desc修改密码
     *@param $phone
     *@param $code 手机发送的验证码
     *@param $password
     */
    public function actionReset_password() {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['phone', 'code', 'password'];
            // $params = array('phone'=>'18395947721','password'=>'123456789','code'=>'1235');
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
    
    /**
     *@desc修改用户信息,认证
     *@param $phone 手机号码
     *@param $name 姓名
     *@param $card_type 1：身份证
     *@param $card_id 身份证号码
     *@param $email 邮件
     *@param $emergency_contact 紧急联系人
     *@param $emergency_telephone 紧急联系电话
     *@param $home_address 地址
     */
    public function actionEditinfo() {
        $arrData = ['result'=>\frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do
        {
            $params = \Yii::$app->request->post();
            $requiredFields = ['name', 'card_type', 'card_id', 'phone', 'email','sess_id'];
            foreach ($requiredFields as $k) {
                if (empty($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }
            }
            $seid_data = \Yii::$app->session->readSession($params['sess_id']);
            if(!$seid_data){
                $useid=0;
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');//当前是游客模式，请先登录。
                break;//sjj，先注释调，上线后取消注释
            }else{
                $session_data = \frontend\components\CommonModule::unserialize_php($seid_data);
                $useid = $session_data['__id'];
            }
            // $useid = '18988';


            $user = \common\models\Pub_user::findOne(['id' => $useid]);

            if (!$user) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = 'Not login.';
                break;
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

            // echo "<pre>";
            // print_r($objFormData);
            // echo "</pre>";
            // break;

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
            /*if (\Yii::$app->user->isGuest) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }*/
            $sess_id = \Yii::$app->request->post('sess_id');
            if(empty($sess_id)){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = '参数非法！';
                break;
            }
            $seid_data = \Yii::$app->session->readSession($sess_id);
            if(!$seid_data){
                $useid=0;
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');//当前是游客模式，请先登录。
                break;//sjj，先注释调，上线后取消注释
            }else{
                $session_data = \frontend\components\CommonModule::unserialize_php($seid_data);
                $useid = $session_data['__id'];
            }

            $cdb = \common\models\Pub_user::find();
            // $cdb->where(['id' => \Yii::$app->user->id]);
            $cdb->where(['id' => $useid]);
            // $cdb->where(['id' => 13436]);
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
            $arrData['emergency_contact'] = ($userInfo ? $userInfo->emergency_contact : null);
            $arrData['emergency_telephone'] = ($userInfo ? $userInfo->emergency_telephone : null);
            $arrData['home_address'] = ($userInfo ? $userInfo->home_address : null);
            $arrData['invite_code'] = $userInfo->getInviteCode();
            
        }while (0);
        echo json_encode($arrData);
    }


    /*
     *@desc 找回密码，获取验证码
     */
    public function actionGet_verify_code(){
        $arrData    = ['result'=> \frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do{
            $phone  = \Yii::$app->request->post('phone');
            // $phone  = '17858966569';
            // 配置项
            $api    = \Yii::$app->params['mob.sms.sendmsgurl'];
            $appkey = \Yii::$app->params['mob.sms.appkey'];
            $zone   = '86';
            $params = array(
                'appkey' => $appkey,
                'phone'  => $phone,
                'zone'   => $zone,
            );

            // 发送验证码
            // $response = \common\components\SmsComponent::postRequest($api, array( 'appkey' => $appkey, 'zone' =>'86', 'phone' => '18395947721') );
            $result = \common\helpers\Utils::queryUrlPost($api, $params);

            $errMsg = \Yii::t('locale', 'Unknown error');
            // $arrData['r'] = $result;
            if ($result[0] == 200) {
                $response = $result[1];
                $oResult = json_decode($response);
                if (isset($oResult->status) && $oResult->status == 200) {
                    $errMsg = \Yii::t('locale', 'Success');
                    // return [true, $errMsg];
                    $arrData['desc'] = $errMsg;
                    break;
                } else {
                    $errCodes = [
                        405 => 'AppKey为空',
                        406 => 'AppKey无效',
                        456 => '国家代码或手机号码为空',
                        457 => '手机号码格式错误',
                        466 => '请求校验的验证码为空',
                        467 => '请求校验验证码频繁',
                        468 => '验证码错误',
                        474 => '没有打开服务端验证开关',
                    ];
                    $errMsg = (isset($errCodes[$oResult->status]) ? $errCodes[$oResult->status] : '未知错误');

                    $arrData['result'] = $oResult->status;
                    $arrData['desc'] = $errMsg;
                    break;

                }
            } else {
                $errMsg = json_decode($result[1]);
                $arrData['result'] = $result[0];
                $arrData['desc'] = $errMsg->status;
                break;
            }

        }while (0);
        echo json_encode($arrData);
    }
    

    /*
     *@desc 判断用户是否登陆
     */
    public function actionIslogin(){
        // $arrData    = ['result'=> \frontend\components\ApiModule::CODE_SUCCESS, 'desc'=>\Yii::t('locale', 'Success')];
        do{
            if (\Yii::$app->user->isGuest) {
                $arrData['result'] = '1';
                $arrData['desc'] = '游客模式';
                $arrData['bool'] = \Yii::$app->user->isGuest;
                break;
            }else{
                $arrData['result'] = '0';
                $arrData['desc'] = '已登录';
                $arrData['bool'] = \Yii::$app->user->isGuest;
                break;
            }
        }while (0);
        echo json_encode($arrData);
    }


    /*根据经纬度得到地址*/
    public function actionGet_address_sid()
    {
        // $coordinate='119.653633,29.122915';
        $coordinate=trim(\Yii::$app->request->post('location'));
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];//0,成功
        do{
            // 根据经纬度得到城市信息
            $map = \common\components\MapApiGaode::create();
            $arrCoordinateResult = $map->getAddressByLocation($coordinate);
            $addresinfo=json_decode($arrCoordinateResult[1]);
            $arrData['city'] = $addresinfo->regeocode->addressComponent->city;
            $arrData['citycode'] = $addresinfo->regeocode->addressComponent->citycode;
            // 得到所有门店
            $AllshopAddress = \frontend\components\CommonModule::getAllShopInfo();
            $distance=0;
            foreach ($AllshopAddress as $key => $value) {
                $distanceResult = \common\components\DistanceService::getDistanceByCoordinates($coordinate, $value['xy']);

                if ($distanceResult[0] < 0) {
                    $arrResult['result'] = 1;
                    $arrResult['desc'] = $distanceResult[1];
                } else {
                    if($distance == 0){
                        $distance = $distanceResult[0];
                        $sid = $value['id'];
                    }
                    if($distance > $distanceResult[0]){
                        $distance = $distanceResult[0];
                        $sid = $value['id'];
                        $fullname = $value['fullname'];
                    }


                }
            }
            $arrData['distance'] = $distance;
            $arrData['sid'] = intval($sid);
            $arrData['fullname'] = $fullname;
        }while (0);

        echo json_encode($arrData);
    }

    /*小程序根据sid获取门店信息*/
    public function actionGet_office_by_id(){
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => "Success."];
        do{
            $sid = \Yii::$app->request->post('sid');
            if (empty($sid)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Invalid parameter!');
                break;
            }

            $cdb = \common\models\Pro_office::find();
            $cdb->where(['id' => $sid]);
            $arrRows = $cdb->asarray()->all();
            $arrData['info'] = $arrRows;
        }while (0);
        echo json_encode($arrData);
    }



}
