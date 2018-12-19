<?php

namespace common\models;

/**
 * User info model
 * 
 * @property integer $id
 * @property string $name
 * @property integer $gender
 * @property integer $identity_type
 * @property string $identity_id
 * @property string $telephone
 * @property string $fixedphone
 * @property string $email
 * @property integer $vip_level
 * @property string $nationality
 * @property integer $birthday
 * @property integer $identity_start_time
 * @property integer $identity_end_time
 * @property string $residence_address
 * @property string $issuing_unit
 * @property integer $user_type
 * @property string $home_address
 * @property string $post_code
 * @property integer $qq
 * @property integer $msn
 * @property string $emergency_contact
 * @property string $emergency_telephone
 * @property string $driver_license
 * @property integer $driver_license_type
 * @property integer $driver_license_time
 * @property integer $driver_license_expire_time
 * @property string $driver_license_issuing_unit
 * @property string $driver_license_image
 * @property integer $member_id
 * @property string $credit_card_no
 * @property string $credit_card_deposit
 * @property integer $credit_card_lines
 * @property integer $credit_card_type
 * @property integer $credit_card_expire_time
 * @property string $bank_card_no
 * @property string $bank_card_name
 * @property string $bank_card_deposit
 * @property integer $total_consumption
 * @property integer $cur_integration
 * @property integer $used_integration
 * @property string $invite_code
 * @property string $invited_code
 * @property integer $credit_level
 * @property integer $love_car_level
 * @property integer $max_renting_cars
 * @property string $blacklist_reason
 * @property integer $violation_score
 * @property integer $violation_penalty
 * @property integer $accident_serious
 * @property integer $accident_moderate
 * @property integer $accident_minor
 * @property string $company_name
 * @property string $company_address
 * @property string $company_license
 * @property string $organization_code
 * @property string $company_telephone
 * @property string $company_postcode
 * @property integer $finger_no
 * @property string $finger_info
 * @property integer $belong_office_id
 * @property integer $unfreeze_at
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pub_user_info extends \common\helpers\ActiveRecordModel
{
    const VIP_LEVEL_NORMAL = 0;     // 普通会员
    const VIP_LEVEL_SILVER = 1;     // 白银会员
    const VIP_LEVEL_GOLDEN = 2;     // 黄金会员
    const VIP_LEVEL_DIAMOND = 3;    // 钻石会员
    
    const GENDER_UNKNOWN = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    const CREDIT_CARD_TYPE_NORMAL = 1;
    const CREDIT_CARD_TYPE_SILVER = 2;
    const CREDIT_CARD_TYPE_GOLDEN = 3;
    const CREDIT_CARD_TYPE_DIAMOND = 4;

    const CREDIT_LEVEL_STAR_1 = 1;      // 1星
    const CREDIT_LEVEL_STAR_2 = 2;
    const CREDIT_LEVEL_STAR_3 = 3;
    const CREDIT_LEVEL_STAR_4 = 4;      // 4星
    const CREDIT_LEVEL_WARNING = -1;    // 警示
    const CREDIT_LEVEL_FORBIDEN = -10;  // 禁租

    const LOVE_CAR_LEVEL_NEGATIVE = -1;
    const LOVE_CAR_LEVEL_0 = 0;
    const LOVE_CAR_LEVEL_1 = 1;
    const LOVE_CAR_LEVEL_2 = 2;
    const LOVE_CAR_LEVEL_3 = 3;
    const LOVE_CAR_LEVEL_4 = 4;

    const USER_TYPE_PERSONAL = 1;
    const USER_TYPE_ENTERPRISE = 2;

    const DRIVER_LICENSE_TYPE_UNKNOWN = 0;
    const DRIVER_LICENSE_TYPE_C2 = 1;
    const DRIVER_LICENSE_TYPE_C1 = 2;
    const DRIVER_LICENSE_TYPE_B2 = 3;
    const DRIVER_LICENSE_TYPE_B1 = 4;
    const DRIVER_LICENSE_TYPE_A3 = 5;
    const DRIVER_LICENSE_TYPE_A2 = 6;
    const DRIVER_LICENSE_TYPE_A1 = 7;
    
    private $_birthday = null;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \common\helpers\behaviors\EditorBehavior::className(),
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
            'name' => \Yii::t('locale', 'Customer name'),
            'gender' => \Yii::t('locale', 'Gender'),
            'identity_type' => \Yii::t('locale', 'ID type'),
            'identity_id' => \Yii::t('locale', 'ID number'),
            'telephone' => \Yii::t('locale', 'Contact number'),
            'fixedphone' => \Yii::t('locale', 'Fixed phone number'),
            'email' => \Yii::t('locale', 'Email'),
            'vip_level' => \Yii::t('locale', 'Member type'),
            'nationality' => \Yii::t('locale', 'Nationality'),
            'birthday' => \Yii::t('locale', 'Birthday'),
            'identity_start_time' => \Yii::t('locale', 'Get identity date'),
            'identity_end_time' => \Yii::t('locale', 'Expires date'),
            'residence_address' => \Yii::t('locale', 'Residence address'),
            'issuing_unit' => \Yii::t('locale', 'Issuing unit'),
            'user_type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Customer')]),
            'home_address' => \Yii::t('locale', 'Home address'),
            'post_code' => \Yii::t('locale', 'Postcode'),
            'qq' => \Yii::t('locale', '{name} No.', ['name'=>'QQ']),
            'msn' => \Yii::t('locale', '{name} No.', ['name'=>'MSN']),
            'emergency_contact' => \Yii::t('locale', 'Emergency contact'),
            'emergency_telephone' => \Yii::t('locale', 'Contact number'),
            'driver_license' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Driver license')]),
            'driver_license_type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Driver license')]),
            'driver_license_time' => \Yii::t('carrental', 'Got driving license time'),
            'driver_license_expire_time' => \Yii::t('carrental', 'Driver license expire date'),
            'driver_license_issuing_unit' => \Yii::t('locale', 'Issuing unit'),
            'driver_license_image' => \Yii::t('locale', 'Driver license photo'),
            'member_id' => \Yii::t('locale', 'Member card no'),
            'credit_card_no' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Credit card')]),
            'credit_card_deposit' => \Yii::t('locale', 'Bank of deposit'),
            'credit_card_lines' => \Yii::t('locale', 'Credit lines'),
            'credit_card_type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Credit card')]),
            'credit_card_expire_time' => \Yii::t('locale', 'Expires at'),
            'bank_card_no' => \Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Bank card')]),
            'bank_card_name' => \Yii::t('locale', '{name} name', ['name'=>\Yii::t('locale', 'Account')]),
            'bank_card_deposit' => \Yii::t('locale', 'Bank of deposit'),
            'total_consumption' => \Yii::t('locale', 'Historical consumption'),
            'cur_integration' => \Yii::t('locale', 'Current integration'),
            'used_integration' => \Yii::t('locale', 'Used integration'),
            'invite_code' => \Yii::t('locale', 'Invite code'),
            'invited_code' => \Yii::t('locale', 'Invited code'),
            'credit_level' => \Yii::t('locale', 'Credit level'),
            'love_car_level' => \Yii::t('carrental', 'Loving car level'),
            'max_renting_cars' => \Yii::t('carrental', 'Max allowed renting cars'),
            'blacklist_reason' => \Yii::t('carrental', 'Blacklist reason'),
            'violation_score' => \Yii::t('carrental', 'Violation score'),
            'violation_penalty' => \Yii::t('carrental', 'Violation penalty'),
            'accident_serious' => \Yii::t('carrental', '{type} accident', ['type'=>\Yii::t('carrental', 'Serious')]),
            'accident_moderate' => \Yii::t('carrental', '{type} accident', ['type'=>\Yii::t('carrental', 'Moderate')]),
            'accident_minor' => \Yii::t('carrental', '{type} accident', ['type'=>\Yii::t('carrental', 'Minor')]),
            'company_name' => \Yii::t('carrental', 'Company name'),
            'company_address' => \Yii::t('carrental', 'Company address'),
            'company_license' => \Yii::t('carrental', 'Company license'),
            'organization_code' => \Yii::t('carrental', 'Organization code'),
            'company_telephone' => \Yii::t('carrental', 'Company telephone'),
            'company_postcode' => \Yii::t('carrental', 'Company postcode'),
            'finger_no' => \Yii::t('locale', 'Finger ID'),
            'finger_info' => \Yii::t('locale', 'Fingerprint information'),
            'belong_office_id' => \Yii::t('locale', 'Belong office'),
            'unfreeze_at' => \Yii::t('locale', 'Unfreeze time'),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation' => \Yii::t('locale', 'Operation'),

            'member_card_amount' => \Yii::t('locale', 'Card left amount'),
            'violation_records' => \Yii::t('carrental', 'Violation records'),
            'accident_records' => \Yii::t('carrental', 'Accident records'),
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
            'name' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ var txt = '<div style=\'text-align:center\'><span>'+value+'</span><br /><span style=\'color:orange\'>'; if (row.belong_office_id) { txt += '['+row.belong_office_id+']'; } else { txt += '&nbsp;'; } txt += '</span></div>'; return txt; }"),
            'gender' => array('width' => 60),
            'identity_type' => array('width' => 100),
            'identity_id' => array('width' => 100),
            'telephone' => array('width' => 100),
            'fixedphone' => array('width' => 100),
            'email' => array('width' => 100),
            'vip_level' => array('width' => 80),
            'email' => array('width' => 100),
            'nationality' => array('width' => 100),
            'birthday' => array('width' => 100),
            'identity_start_time' => array('width' => 100),
            'identity_end_time' => array('width' => 100),
            'residence_address' => array('width' => 100),
            'issuing_unit' => array('width' => 100),
            'user_type' => array('width' => 100),
            'home_address' => array('width' => 100),
            'post_code' => array('width' => 100),
            'qq' => array('width' => 100),
            'msn' => array('width' => 100),
            'emergency_contact' => array('width' => 100),
            'emergency_telephone' => array('width' => 100),
            'driver_license' => array('width' => 100),
            'driver_license_type' => array('width' => 100),
            'driver_license_time' => array('width' => 100),
            'driver_license_expire_time' => array('width' => 100),
            'driver_license_issuing_unit' => array('width' => 100),
            'driver_license_image' => array('width' => 100),
            'member_id' => array('width' => 100),
            'credit_card_no' => array('width' => 100),
            'credit_card_deposit' => array('width' => 100),
            'credit_card_lines' => array('width' => 100),
            'credit_card_type' => array('width' => 100),
            'credit_card_expire_time' => array('width' => 100),
            'bank_card_no' => array('width' => 100),
            'bank_card_name' => array('width' => 100),
            'bank_card_deposit' => array('width' => 100),
            'total_consumption' => array('width' => 100),
            'cur_integration' => array('width' => 100),
            'used_integration' => array('width' => 100),
            'invite_code' => array('width' => 100),
            'invited_code' => array('width' => 100),
            'credit_level' => array('width' => 100),
            'love_car_level' => array('width' => 100),
            'blacklist_reason' => array('width' => 100),
            'violation_score' => array('width' => 100),
            'violation_penalty' => array('width' => 100),
            'accident_serious' => array('width' => 100),
            'accident_moderate' => array('width' => 100),
            'accident_minor' => array('width' => 100),
            'company_name' => array('width' => 100),
            'company_address' => array('width' => 100),
            'company_license' => array('width' => 100),
            'organization_code' => array('width' => 100),
            'company_telephone' => array('width' => 100),
            'company_postcode' => array('width' => 100),
            'finger_no' => array('width' => 100),
            'finger_info' => array('width' => 100),
            'belong_office_id' => array('width' => 100, 'sortable' => 'true'),
            'unfreeze_at' => array('width' => 140, 'sortable' => 'true'),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true'),
            'created_at' => array('width' => 140, 'sortable' => 'true'),
            'updated_at' => array('width' => 140, 'sortable' => 'true'),
            'operation' => array('width' => 160, 
                'buttons' => array(
                    \Yii::$app->user->can('user/book_vehicle') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['user/book_vehicle', 'id'=>'']), 'name' => \Yii::t('carrental', 'Book'), 'title' => \Yii::t('carrental', 'Book vehicle'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                    \Yii::$app->user->can('order/userrentlist_index') ? array('type' => 'tab', 'url' => \yii\helpers\Url::to(['order/userrentlist_index', 'user_id'=>'']), 'name' => \Yii::t('carrental', 'Customer rent history'), 'title' => \Yii::t('carrental', 'Customer rent history'), 'paramField' => 'id', 'icon' => '', 'showText'=>true) : null,
                ),
            ),

            'member_card_amount' => array('width' => 100),
            'violation_records' => array('width' => 180, 
                'formatter'=>"function(value,row) { return '-'+row.violation_score+'".\Yii::t('locale', 'scores')." -'+row.violation_penalty+'".\Yii::t('locale', 'RMB Yuan')."' ; }",
            ),
            'accident_records' => array('width' => 180, 
                'formatter'=>"function(value,row) { return '<a href=\'javascript:void(0);\' class=\'icon-exclamation\' title=\'{$this->getAttributeLabel('accident_serious')}\' style=\'display:block;width:16px;height:16px;float:left;margin: 0px 0px 0px 4px\' />'".
                    " + '<a href=\'javascript:void(0);\' style=\'display:block;height:16px;float:left;margin: 0px 4px 0px 0px\'>'+row.accident_serious+'</a>'".
                    " + '<a href=\'javascript:void(0);\' class=\'icon-warnning\' title=\'{$this->getAttributeLabel('accident_moderate')}\' style=\'display:block;width:16px;height:16px;float:left;margin: 0px 0px 0px 4px\' />'".
                    " + '<a href=\'javascript:void(0);\' style=\'display:block;height:16px;float:left;margin: 0px 4px 0px 0px\'>'+row.accident_moderate+'</a>'".
                    " + '<a href=\'javascript:void(0);\' class=\'icon-information\' title=\'{$this->getAttributeLabel('accident_minor')}\' style=\'display:block;width:16px;height:16px;float:left;margin: 0px 0px 0px 4px\' />'".
                    " + '<a href=\'javascript:void(0);\' style=\'display:block;height:16px;float:left;margin: 0px 4px 0px 0px\'>'+row.accident_minor+'</a>'; }",
            ),
        );
    }
    
    public function beforeSave($insert) {
        if (empty($this->invite_code)) {
            $this->getInviteCode();
        }
        return parent::beforeSave($insert);
    }
    
    public static function getGendersArray() {
        return [
            static::GENDER_MALE => \Yii::t('locale', 'Male'),
            static::GENDER_FEMALE => \Yii::t('locale', 'Female'),
        ];
    }

    public static function getVipLevelsArray() {
        return [
            static::VIP_LEVEL_NORMAL => \Yii::t('locale', 'Normal member'),
            static::VIP_LEVEL_SILVER => \Yii::t('locale', 'Silver member'),
            static::VIP_LEVEL_GOLDEN => \Yii::t('locale', 'Golden member'),
            static::VIP_LEVEL_DIAMOND => \Yii::t('locale', 'Diamond member'),
        ];
    }

    public static function getCreditCardTypesArray() {
        return [
            static::CREDIT_CARD_TYPE_NORMAL => \Yii::t('locale', '{name} card', ['name'=>\Yii::t('locale', 'Normal')]),
            static::CREDIT_CARD_TYPE_SILVER => \Yii::t('locale', '{name} card', ['name'=>\Yii::t('locale', 'Silver')]),
            static::CREDIT_CARD_TYPE_GOLDEN => \Yii::t('locale', '{name} card', ['name'=>\Yii::t('locale', 'Golden')]),
            static::CREDIT_CARD_TYPE_DIAMOND => \Yii::t('locale', '{name} card', ['name'=>\Yii::t('locale', 'Diamond')]),
        ];
    }

    public static function getCreditLevelsArray() {
        return [
            static::CREDIT_LEVEL_STAR_1 => \Yii::t('locale', '{name} star', ['name'=>\Yii::t('locale', 'One')]),
            static::CREDIT_LEVEL_STAR_2 => \Yii::t('locale', '{name} star', ['name'=>\Yii::t('locale', 'Two')]),
            static::CREDIT_LEVEL_STAR_3 => \Yii::t('locale', '{name} star', ['name'=>\Yii::t('locale', 'Three')]),
            static::CREDIT_LEVEL_STAR_4 => \Yii::t('locale', '{name} star', ['name'=>\Yii::t('locale', 'Four')]),
            static::CREDIT_LEVEL_WARNING => \Yii::t('carrental', 'Warning(black list)'),
            static::CREDIT_LEVEL_FORBIDEN => \Yii::t('carrental', 'Forbidden(black list)'),
        ];
    }

    public static function getLoveCarLevelsArray() {
        return [
            static::LOVE_CAR_LEVEL_NEGATIVE => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'Negative')]),
            static::LOVE_CAR_LEVEL_0 => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'Zero')]),
            static::LOVE_CAR_LEVEL_1 => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'One')]),
            static::LOVE_CAR_LEVEL_2 => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'Two')]),
            static::LOVE_CAR_LEVEL_3 => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'Three')]),
            static::LOVE_CAR_LEVEL_4 => \Yii::t('locale', '{name} level', ['name'=>\Yii::t('locale', 'Four')]),
        ];
    }

    public static function getUserTypesArray() {
        return [
            static::USER_TYPE_PERSONAL => \Yii::t('locale', 'Personal'),
            static::USER_TYPE_ENTERPRISE => \Yii::t('locale', 'Enterprise'),
        ];
    }

    public static function getIdentityTypesArray() {
        return [
            \common\components\Consts::ID_TYPE_IDENTITY => \Yii::t('locale', 'Identity Card'),
            \common\components\Consts::ID_TYPE_PASSPORT => \Yii::t('locale', 'Passport'),
            \common\components\Consts::ID_TYPE_HK_MACAO => \Yii::t('locale', 'Hong Kong and Macao Residents Traveling to Mainland Pass'),
            \common\components\Consts::ID_TYPE_TAIWAN   => \Yii::t('locale','Taiwan Residents Traveling to Mainland Pass'),
        ];
    }

    public static function getDriverLicenseTypesArray() {
        return [
            static::DRIVER_LICENSE_TYPE_C2 => 'C2',
            static::DRIVER_LICENSE_TYPE_C1 => 'C1',
            static::DRIVER_LICENSE_TYPE_B2 => 'B2',
            static::DRIVER_LICENSE_TYPE_B1 => 'B1',
            static::DRIVER_LICENSE_TYPE_A3 => 'A3',
            static::DRIVER_LICENSE_TYPE_A2 => 'A2',
            static::DRIVER_LICENSE_TYPE_A1 => 'A1',
        ];
    }
    
    public function getViolationRecordsText() {
        return "-{$this->violation_score}".\Yii::t('locale', 'scores')." -{$this->violation_penalty}".\Yii::t('locale', 'RMB Yuan');
    }
    
    public function getAccidentRecordsHtml() {
        $iconOptions = ['href'=>"javascript:void(0);", 'style'=>"display:block;width:16px;height:16px;float:left;margin: 0px 0px 0px 4px", 'encode'=>false];
        $textOptions = ['href'=>"javascript:void(0);", 'style'=>"display:block;height:16px;float:left;margin: 0px 4px 0px 0px", 'encode'=>false];
        
        $htmlArray = [
            //\common\helpers\CMyHtml::beginTag('div', ['style'=>"height:24px;vertical-align:center"]),
            \common\helpers\CMyHtml::tag('a', '', array_merge($iconOptions, ['class'=>'icon-exclamation', 'title'=>$this->getAttributeLabel('accident_serious')])),
            \common\helpers\CMyHtml::tag('a', $this->accident_serious, $textOptions),
            \common\helpers\CMyHtml::tag('a', '', array_merge($iconOptions, ['class'=>'icon-warnning', 'title'=>$this->getAttributeLabel('accident_moderate')])),
            \common\helpers\CMyHtml::tag('a', $this->accident_moderate, $textOptions),
            \common\helpers\CMyHtml::tag('a', '', array_merge($iconOptions, ['class'=>'icon-information', 'title'=>$this->getAttributeLabel('accident_minor')])),
            \common\helpers\CMyHtml::tag('a', $this->accident_minor, $textOptions),
            //\common\helpers\CMyHtml::endTag('div'),
        ];
        
        return implode("\n", $htmlArray);
    }
    
    public function getMemberTypeText() {
        $arr = self::getVipLevelsArray();
        return (isset($arr[$this->vip_level]) ? $arr[$this->vip_level] : '');
    }
    
    public function getIdentityTypeText() {
        $arr = self::getIdentityTypesArray();
        return (isset($arr[$this->identity_type]) ? $arr[$this->identity_type] : '');
    }
    
    public function onConsumeAmount($ammount) {
        $this->total_consumption += $ammount;
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(array_merge([
            'formattingAttributes' => [
                'gender' => static::getGendersArray(),
                'identity_type' => static::getIdentityTypesArray(),
                'vip_level' => static::getVipLevelsArray(),
                'user_type' => static::getUserTypesArray(),
                'driver_license_type' => static::getDriverLicenseTypesArray(),
                'credit_card_type' => static::getCreditCardTypesArray(),
                'credit_level' => static::getCreditLevelsArray(),
                'love_car_level' => static::getLoveCarLevelsArray(),
                'birthday,identity_start_time,driver_license_time,driver_license_expire_time,identity_end_time,credit_card_expire_time' => 'date',
                'unfreeze_at,created_at,updated_at' => 'datetime',
            ],
            'findAttributes' => [
                'member_id' => Pro_member_card::createFindIdNamesArrayConfig(),
                'belong_office_id' => Pro_office::createFindIdNamesArrayConfig(),
                'edit_user_id' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'name'];
    }
    
    public function getBirthday() {
        if ($this->_birthday === null) {
            if (!empty($this->birthday)) {
                $this->_birthday = date('m-d', $this->birthday);
            }
            elseif ($this->identity_type == \common\components\Consts::ID_TYPE_IDENTITY && !empty($this->identity_id)) {
                $t = substr($this->identity_id, 6, 8);
                if (strlen($t) == 8) {
                    $this->_birthday = substr($t, 4, 2).'-'.substr($t, 6, 2);
                }
                else {
                    $this->_birthday = false;
                }
            }
            else {
                $this->_birthday = false;
            }
        }
        return $this->_birthday;
    }
    
    public function isBirthday($time) {
        return date('m-d', $time) == $this->getBirthday();
    }
    
    public function getInviteCode() {
        if (empty($this->invite_code)) {
            $this->invite_code = strval($this->id);
        }
        return $this->invite_code;
    }
    

    public function checkdate(){

    }
}
