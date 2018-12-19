<?php
namespace backend\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;

/**
 * Rbac_admin model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $app_id
 * @property integer $belong_office_id
 * @property string $auth_key
 * @property string $avatar
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $authority_at
 * @property string $password write-only password
 */
class Rbac_admin extends \common\helpers\ActiveRecordModel implements IdentityInterface
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
            [['username'], 'required'],
            [['belong_office_id', 'status', 'login_count', 'login_at', 'authority_at'], 'integer'],
            [['username'], 'string', 'min' => 2, 'max' => 64],
            [['auth_key'], 'string', 'max' => 64],
            [['email'], 'string', 'max' => 128],
            [['avatar', 'remark'], 'string', 'max' => 255],
            [['username'], 'unique', 'targetClass' => 'backend\models\Rbac_admin'],
            [['username', 'email'], 'filter', 'filter' => 'trim'],

            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
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
            'username' => Yii::t('locale', 'Account'),
            'email' => Yii::t('locale', 'Email'),
            'password' => Yii::t('locale', 'Password'),
            'password_repeat' => Yii::t('locale', 'Confirm Password'),
            'remark' => Yii::t('locale', 'Remarks'),
            'login_count' => Yii::t('locale', 'Login count'),
            'login_at' => Yii::t('locale', 'Last login time'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'authority_at' => \Yii::t('locale', 'Authority time'),
            'status' => \Yii::t('locale', 'Status'),
            'avatar' => \Yii::t('locale', 'Avatar'),
            'belong_office_id' => \Yii::t('locale', 'Belong office'),
            'operation' => Yii::t('locale', 'Operation'),
            
            'rememberMe' => Yii::t('locale', 'Remember me'),
            'verification_code' => \Yii::t('locale', 'Verification code'),
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
            'username' => array('width' => 100, 'sortable' => 'true', 
                //'formatter' => "function(value,row){ return '<a href=\'edit/id/' + row.id + '\'>' + value + '</a>'; }"
                ),
            'email' => array('width' => 120, 'sortable' => 'true'),
            'remark' => array('width' => 120, 'sortable' => 'true'),
            'login_count' => array('width' => 100, 'sortable' => 'true'),
            'login_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return ''; } return $.custom.utils.humanTime(value);}"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return '';  } return $.custom.utils.humanTime(value);}"),
            'authority_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){ if (value == 0) { return ''; } return $.custom.utils.humanTime(value);}"),
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
            'belong_office_id' => array('width'=>100, 'formatter'=>"function(value,row){ return row.belong_office_disp; }"),
            'operation' => array('width' => 260, 
                'buttons' => array(
                    \Yii::$app->user->can('rbac2/admin_edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['rbac2/admin_edit', 'id'=>'']), 'name' => Yii::t('locale', 'Edit'), 'title' => Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('rbac2/admin_direct') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/admin_direct', 'act'=>'active']).'&id=', 'condition'=>array("{field} != ".static::STATUS_ACTIVE, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Activate'), 'title' => '', 'paramField' => 'id', 'icon' => 'icon-lock_open') : null,
                    \Yii::$app->user->can('rbac2/admin_direct') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/admin_direct', 'act'=>'lock']).'&id=', 'condition'=>array("{field} == ".static::STATUS_ACTIVE, array('{field}'=>'status')), 'name' => Yii::t('locale', 'Lock'), 'title' => '', 'paramField' => 'id', 'icon' => 'icon-lock') : null,
                    \Yii::$app->user->can('rbac2/admin_delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['rbac2/admin_delete', 'id'=>'']), 'name' => Yii::t('locale', 'Delete'), 'title' => Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
                ),
        );
    }
    
    /**
     * @inheritdoc
     * @return static
     */
    public static function findIdentity($id)
    {
        if (empty($id)) {
            return null;
        }
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
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
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
    
    public function isAdministrator() {
        $name = substr($this->username, 0, 5);
        if (strtolower($name) == 'admin') {
            return true;
        }
        return false;
    }
    
    public function getAdmistratorNamePrefix() {
        return 'admin';
    }
    
    public function getRoleId() {
        $authRoles = \Yii::$app->authManager->getRolesByUser($this->id);
        $arrRoleIds = [];
        foreach ($authRoles as $authRole) {
            $arrRoleIds[] = $authRole->data->role_id;
        }
        $n = count($arrRoleIds);
        if ($n > 1) {
            return $arrRoleIds;
        }
        elseif ($n == 1) {
            return $arrRoleIds[0];
        }
        return 0;
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'username'];
    }

}
