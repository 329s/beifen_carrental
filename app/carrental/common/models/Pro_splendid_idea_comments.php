<?php

namespace common\models;

/**
 * This is the model class for table "pro_splendid_idea_comments".
 *
 * @property integer $id
 * @property integer $main_id
 * @property integer $comment_to
 * @property string $content
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_splendid_idea_comments extends \common\helpers\ActiveRecordModel
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \yii\behaviors\BlameableBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['main_id'], 'required'],
            [['main_id', 'comment_to'], 'integer'],
            [['content'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'main_id' => 'Main ID',
            'comment_to' => 'Comment To',
            'content' => \Yii::t('locale', '{name} content', ['name'=>\Yii::t('locale', 'Comment')]),
            'created_by' => \Yii::t('locale', '{name} account', ['name'=>\Yii::t('locale', 'Comment')]),
            'updated_by' => \Yii::t('locale', '{name} account', ['name'=>\Yii::t('locale', 'Update')]),
            'created_at' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('locale', 'Comment')]),
            'updated_at' => 'Updated At',
        ];
    }
    
    /**
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = array()) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'created_at,updated_at' => 'datetime',
            ],
            'findAttributes' => [
                'comment_to' => static::createFindIdNamesArrayConfig(),
                'created_by,updated_by' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
            ],
        ], $config));
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'content'];
    }

}
