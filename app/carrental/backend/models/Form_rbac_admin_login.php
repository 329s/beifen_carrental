<?php
namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class Form_rbac_admin_login extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function attributeLabels()
    {
        $model = new Rbac_admin();
        if ($model) {
            return $model->attributeLabels();
        }
        return [];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('locale', 'Incorrect username or password.'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $expireDuration = 86400;
            if ($user->authority_at) {
                $leftDuration = $user->authority_at - time();
                if ($leftDuration <= 0) {
                    $this->addError('', \Yii::t('locale', 'Sorry, your account had already been expired.'));
                    return false;
                }
                else if ($expireDuration > $leftDuration) {
                    $expireDuration = $leftDuration;
                }
            }
            if (Yii::$app->user->login($user, $this->rememberMe ? $expireDuration : 0))
            {
                $user->login_count++;
                $user->login_at = time();
                $user->save();
                
                $id = $user->id;
                $username = $this->username;
                $ip = \Yii::$app->request->getUserIP();
                $token = md5(sprintf("%s&%s&%s",$user->login_at,$id,$ip));
                
                $session = \Yii::$app->session;
                $session->set(md5(sprintf("%s&%s",$id,$username)),$token);
                
                $this->saveSession($id, $token);
                
                return true;
            }
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Rbac_admin|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Rbac_admin::findByUsername($this->username);
        }

        return $this->_user;
    }
    
    public function saveSession($id, $sessionToken)  
    {  
        $loginAdmin = Rbac_admin_session::findOne(['id' => $id]); //查询admin_session表中是否有用户的登录记录  
        if(!$loginAdmin){ //如果没有记录则新建此记录  
            $sessionModel = new Rbac_admin_session();  
            $sessionModel->id = $id;  
            $sessionModel->session_token = $sessionToken;  
            $result = $sessionModel->save();  
        }else{          //如果存在记录（则说明用户之前登录过）则更新用户登录token  
            $loginAdmin->session_token = $sessionToken;  
            $result = $loginAdmin->update();  
        }  
        return $result;  
    }  

}
