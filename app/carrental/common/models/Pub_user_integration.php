<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "pub_user_integration".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $integral
 * @property integer $type
 * @property integer $status
 * @property string $expire_at
 * @property string $created_at
 * @property string $updated_at
 */
class Pub_user_integration extends \common\helpers\ActiveRecordModel
{
    
    const STATUS_NORMAL = 0;
    const STATUS_USED = 1;
    const STATUS_EXPIRED = 10;
    
    const TYPE_RENTCAR = 1301;
    const TYPE_INVITE_REGISTER = 1311;
    const TYPE_INVITE_RENTCAR = 1312;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'integral', 'type'], 'required'],
            [['user_id', 'integral', 'type', 'status', 'expire_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '实名用户ID',
            'integral' => '积分',
            'type' => '积分来源',
            'status' => '积分状态',
            'expire_at' => '到期日',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
    
    /**
     * 
     * @param integer $userId
     * @param integer $intgral
     * @param integer $source
     * @return static
     */
    public static function create($userId, $intgral, $source)
    {
        $obj = new static();
        $obj->user_id = $userId;
        $obj->integral = $intgral;
        $obj->type = $source;
        $obj->status = static::STATUS_NORMAL;
        $obj->expire_at = time() + 86400 * 60;
        return $obj;
    }
    
}
