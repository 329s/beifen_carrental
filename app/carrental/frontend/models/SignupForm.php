<?php
namespace frontend\models;

use common\models\Pub_user;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends \common\helpers\ActiveFormModel
{
    public $account;
    public $email;
    public $password;
    public $invited_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'email', 'invited_code'], 'filter', 'filter' => 'trim'],
            [['account', 'password'], 'required'],
            ['account', 'unique', 'targetClass' => '\common\models\Pub_user', 'message' => \Yii::t('locale', 'The {field} has already been taken.', ['field'=>  \Yii::t('locale', 'account')])],
            ['account', 'string', 'min' => 2, 'max' => 255],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],

            ['password', 'string', 'min' => 6],
            
            ['invited_code', 'string', 'max' => 24],
        ];
    }

    /**
     * Signs user up.
     *
     * @return Pub_user|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $user = new Pub_user();
            $user->account = $this->account;
            $user->email = $this->email;
            $user->setPassword($this->password);
            $user->generateAuthKey();
            $user->login_count = 1;
            $user->login_at = time();
            if ($this->invited_code) {
                $user->invited_code = $this->invited_code;
            }
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }
}
