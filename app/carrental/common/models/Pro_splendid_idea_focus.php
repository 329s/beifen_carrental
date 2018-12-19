<?php

namespace common\models;

/**
 * This is the model class for table "pro_splendid_idea_focus".
 *
 * @property string $id
 * @property string $focus_id
 * @property string $user_id
 * @property string $created_at
 */
class Pro_splendid_idea_focus extends \common\helpers\ActiveRecordModel
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['focus_id', 'user_id'], 'required'],
            [['focus_id', 'user_id'], 'integer'],
            [['focus_id', 'user_id'], 'unique', 'targetAttribute' => ['focus_id', 'user_id'], 'message' => \Yii::t('locale', 'The item has already been focused')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'focus_id' => 'Focus ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
        ];
    }
    
}
