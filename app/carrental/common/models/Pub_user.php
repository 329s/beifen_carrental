<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * Pub_user model
 *
 * @property integer $id
 * @property string $account
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $info_id
 * @property string $auth_key
 * @property integer $status
 * @property integer $login_count
 * @property string $invited_code
 * @property integer $login_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class Pub_user extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account', 'email'], 'filter', 'filter' => 'trim'],
            ['account', 'required'],
            ['account', 'string', 'min' => 2, 'max' => 255],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],

            [['status', 'info_id', 'unfreeze_at'], 'integer'],
            
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            
            ['info_id', 'default', 'value' => 0],

            ['invited_code', 'string', 'max'=>24],
        ];
    }

    /**
     * Returns the attribute labels.
     * Attribute labels are mainly used in error messages of validation.
     * By default an attribute label is generated using {@link generateAttributeLabel}.
     * This method allows you to explicitly specify attribute labels.
     *
     * Note, in order to inherit labels defined in the parent class, a child class needs to
     * merge the parent labels with child labels using functions like array_merge().
     *
     * @return array attribute labels (name=>label)
     * @see generateAttributeLabel
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'account' => Yii::t('locale', 'Account'),
            'email' => Yii::t('locale', 'Email'),
            'info_id' => Yii::t('locale', '{name} info', ['user'=> Yii::t('locale', 'User')]),
            'login_count' => Yii::t('locale', 'Login count'),
            'invited_code' => Yii::t('locale', 'Invited code'),
            'login_at' => Yii::t('locale', 'Last login time'),
            'created_at' => Yii::t('locale', 'Signup time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'status' => Yii::t('locale', 'Status'),
            'unfreeze_at' => Yii::t('locale', 'Unfreeze time'),
            'operation' => Yii::t('locale', 'Operation'),
            'user_info' => Yii::t('locale', '{name} info', ['user'=> Yii::t('locale', 'User')]),

            'real_name_authenticated' => \Yii::t('locale', 'Is real name authenticated'),
        );
    }
    
    /**
     * Returns the attribute custom types.
     * Attribute custom types is used for display model data in datagrid specified display options.
     * @return array attribute custom types (name=>array('width'=>null, 'data-options'=>null, 'editor'=>null,'formatter'=>null,'sortable'=>true))
     */
    public function attributeCustomTypes()
    {
        return array(
            'id' => array('data-options' => array('checkbox'=>'true'), 'key' => true),
            'account' => array('width' => 100, 'sortable' => 'true', 
                //'formatter' => "function(value,row){ return '<a href=\'edit/id/' + row.id + '\'>' + value + '</a>'; }"
                ),
            'email' => array('width' => 120, 'sortable' => 'true'),
            'info_id' => array('width' => 100),
            'real_name_authenticated' => array('width'=>80, 'sortable' => 'true', 'formatter'=>"function(value,row){ if (row.info_id > 0) { return '".\Yii::t('locale', 'Yes')."'; } return ''; }"),
            'login_count' => array('width' => 100, 'sortable' => 'true'),
            'invited_code' => array('width' => 100, 'sortable' => 'true'),
            'login_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return ''; } return $.custom.utils.humanTime(value);}"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return ''; } return $.custom.utils.humanTime(value);}"),
            'unfreeze_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return ''; } return $.custom.utils.humanTime(value);}"),
            'status' => array('width' => 60, 'sortable' => 'true', 'formatter' => <<<EOD
function(value,row){ 
    if (value == 0) {
        return '<font color=\'red\' style=\'vertical-align:center;\'>X</font>';
    } else {
        return '<font color=\'green\' style=\'vertical-align:center;\'>√</font>';
    }
}
EOD
                ),
            'operation' => array('width' => 130, 
                'buttons' => array(
                    \Yii::$app->user->can('user/account_realname_match') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['user/account_realname_match', 'id'=>'']), 'condition'=>array('{field} > 0', array('{field}'=>'has_user_info')), 'name' => Yii::t('carrental', 'Match real name authentication information'), 'title' => Yii::t('carrental', 'Match real name authentication information'), 'paramField' => 'id', 'icon' => 'icon-report', 'showText'=>true) : null,
                ),
                ),
            'user_info' => array('detailed' => true),
        );
    }
    
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by account
     *
     * @param string $account
     * @return static|null
     */
    public static function findByUsername($account)
    {
        return static::findOne(['account' => $account, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'account'];
    }

}