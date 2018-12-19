<?php
namespace backend\models;

/**
 * Login form
 */
class Form_rbac_admin extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $username;
    public $email;
    public $password;
    public $password_repeat;
    public $belong_office_id;
    public $status;
    public $avatar;
    public $remark;
    public $authority_at;
    public $role_id;
    public $verification_code;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'belong_office_id', 'role_id', 'email', 'status'], 'required'],
            [['belong_office_id', 'status', 'role_id'], 'integer'],
            [['username'], 'string', 'min' => 2, 'max' => 64],
            [['remark'], 'string', 'max' => 255],
            [['email'], 'string', 'max' => 128],
            [['avatar'], 'image'],
            [['username'], 'unique', 'targetClass' => 'backend\models\Rbac_admin', 'filter'=>['<>', 'id', $this->id]],
            [['username', 'email', 'remark'], 'filter', 'filter' => 'trim'],
            [['authority_at'], 'datetime'],

            [['password', 'password_repeat'], 'required', 'on'=>['create']],
            [['password', 'password_repeat'], 'string', 'min' => 6, 'max' => 16],
            [['password_repeat'], 'compare', 'compareAttribute' => 'password', 'message'=> \Yii::t('locale', '{attribute} must be equal to {compareValueOrAttribute}.')],
            [['password'], 'compare', 'compareAttribute' => 'password_repeat', 'message'=> \Yii::t('locale', '{attribute} must be equal to {compareValueOrAttribute}.')],

            ['status', 'default', 'value' => Rbac_admin::STATUS_ACTIVE],
            ['status', 'in', 'range' => [Rbac_admin::STATUS_ACTIVE, Rbac_admin::STATUS_DELETED]],
            
            //['verification_code', 'captcha'],
        ];
    }

    public function getActiveRecordModel() {
        $model = new Rbac_admin();
        return $model;
    }
    
    public function savingFields() {
        return [
            'username',
            'email',
            'remark',
            'status',
            'avatar',
            'authority_at',
            'belong_office_id',
        ];
    }
    
    public function afterSaveToModel($model) {
        if ($this->password !== '') {
            $model->setPassword($this->password);
            $model->generateAuthKey();
        }
    }
    
    /**
     * Signs user up.
     *
     * @return Rbac_admin|null the saved model or null if saving fails
     */
    public function signup($appId)
    {
        if ($this->validate()) {
            $user = new Rbac_admin();
            $this->save($user);
            $user->app_id = $appId;
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }
}
