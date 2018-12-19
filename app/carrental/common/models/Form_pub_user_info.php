<?php

namespace common\models;

/**
 * This is the active form model class for table "pub_user_info".
 */
class Form_pub_user_info extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $gender;
    public $identity_type;
    public $identity_id;
    public $telephone;
    public $fixedphone;
    public $email;
    public $vip_level;
    public $nationality;
    public $birthday;
    public $identity_start_time;
    public $identity_end_time;
    public $residence_address;
    public $issuing_unit;
    public $user_type;
    public $home_address;
    public $post_code;
    public $qq;
    public $msn;
    public $emergency_contact;
    public $emergency_telephone;
    public $driver_license;
    public $driver_license_type;
    public $driver_license_time;
    public $driver_license_expire_time;
    public $driver_license_issuing_unit;
    public $driver_license_image;
    public $member_id;
    public $credit_card_no;
    public $credit_card_deposit;
    public $credit_card_lines;
    public $credit_card_type;
    public $credit_card_expire_time;
    public $bank_card_no;
    public $bank_card_name;
    public $bank_card_deposit;
    public $total_consumption;
    public $cur_integration;
    public $used_integration;
    public $invite_code;
    public $invited_code;
    public $credit_level;
    public $love_car_level;
    public $max_renting_cars;
    public $blacklist_reason;
    public $violation_score;
    public $violation_penalty;
    public $accident_serious;
    public $accident_moderate;
    public $accident_minor;
    public $company_name;
    public $company_address;
    public $company_license;
    public $organization_code;
    public $company_telephone;
    public $company_postcode;
    public $finger_no;
    public $finger_info;
    public $belong_office_id;
    public $unfreeze_at;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'identity_id', 'telephone'], 'required'],
            [['id', 'gender', 'identity_type', 'vip_level', 'user_type', 'qq', 'driver_license_type', 'member_id', 
                'credit_card_lines', 'credit_card_type', 'cur_integration', 'used_integration', 'credit_level', 
                'love_car_level', 'max_renting_cars', 'violation_score', 'violation_penalty', 
                'accident_serious', 'accident_moderate', 'accident_minor', 'finger_no', 'belong_office_id'], 'integer'],
            [['total_consumption'], 'number'],
            [['msn', 'driver_license_issuing_unit'], 'string', 'max' => 64],
            [['telephone', 'fixedphone', 'nationality', 'emergency_contact', 'emergency_telephone', 'driver_license', 
                'credit_card_no', 'bank_card_no', 'bank_card_name', 'company_license', 
                'organization_code', 'company_telephone'], 'string', 'max' => 32],
            [['email', 'residence_address', 'issuing_unit', 'home_address', 'credit_card_deposit', 
                'bank_card_deposit', 'company_name', 'company_address', 'finger_info'], 'string', 'max' => 255],
            [['post_code', 'company_postcode'], 'string', 'max' => 16],
            [['invite_code', 'invited_code'], 'string', 'max' => 24],
            [['blacklist_reason'], 'string', 'max' => 128],
            [['identity_id'], 'unique', 'targetClass' => 'common\models\Pub_user_info', 'filter'=>['<>', 'id', $this->id]],
            
            [['name', 'identity_id', 'msn', 'telephone', 'fixedphone', 'nationality', 'emergency_contact', 'emergency_telephone', 
                'driver_license', 'credit_card_no', 'bank_card_no', 'bank_card_name', 'company_license', 'organization_code', 
                'company_telephone', 'email', 'residence_address', 'issuing_unit', 'home_address', 
                'credit_card_deposit', 'bank_card_deposit', 'company_name', 'company_address', 'finger_info', 'post_code', 'company_postcode',
                'invite_code', 'invited_code', 'blacklist_reason'], 'filter', 'filter' => 'trim'],
            
            ['name', 'string', 'min' => 2, 'max' => 64],
            ['identity_id', 'string', 'min' => 2, 'max' => 32],
            
            ['gender', 'default', 'value' => \common\models\Pub_user_info::GENDER_UNKNOWN],
            ['gender', 'in', 'range' => [\common\models\Pub_user_info::GENDER_UNKNOWN, \common\models\Pub_user_info::GENDER_MALE, \common\models\Pub_user_info::GENDER_FEMALE]],
            
            ['identity_type', 'default', 'value' => \common\components\Consts::ID_TYPE_IDENTITY],
            ['identity_type', 'in', 'range' => array_keys(Pub_user_info::getIdentityTypesArray())],
            
            [['driver_license_image'], 'image', 'maxSize'=>512000],
            

            // sjj 下方代码是后台会员编辑的时候日期的验证并转换时间戳，但是app接口中是时间戳的形式，为了保证app的运行先把日期规则注释
            //[['birthday', 'identity_start_time', 'driver_license_time', 'driver_license_expire_time'], \common\helpers\validators\DateValidator::className()],
            //[['identity_end_time', 'credit_card_expire_time'], \common\helpers\validators\DateValidator::className(), 'defaultTimepart'=> '23:59:59'],
            //[['unfreeze_at'], \common\helpers\validators\DatetimeValidator::className()],

            [['max_renting_cars'], 'default', 'value'=>1],
        ];
    }

   

    public function getActiveRecordModel() {
        $model = new \common\models\Pub_user_info();
        return $model;
    }

    public function loadFromModel($model) {
        $fields = $this->savingFields();
        if (empty($fields)) {
            $fields = [];
            foreach ($model->attributes() as $k) {
                if (!in_array($k, $model->primaryKey())) {
                    $fields[] = $k;
                }
            }
        }
        foreach ($fields as $k => $k2) {
            $k1 = $k;
            if (is_integer($k1)) {
                $k1 = $k2;
            }
            if ($this->hasProperty($k1)) {
                $this->$k1 = $model->$k2;
            }
        }
        if ($model->id) {
            $this->id = $model->id;
        }
    }
    
}
