<?php
namespace common\models;

use Yii;

/**
 * Login form
 */
class Form_pub_user_login extends \common\helpers\ActiveFormModel
{
    public $account;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // account and password are both required
            [['account', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    public function getActiveRecordModel() {
        $model = new Pub_user_info();
        return $model;
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
            if (Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 7 : 0)) {
                $user->login_count++;
                $user->login_at = time();
                $user->save();
                return true;
            }
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Pub_user|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = Pub_user::findByUsername($this->account);
        }

        return $this->_user;
    }
}
