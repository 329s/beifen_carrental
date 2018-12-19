<?php

namespace common\models;

/**
 * This is the model class for table "pro_splendid_idea".
 *
 * @property integer $id
 * @property string $type
 * @property integer $status
 * @property string $title
 * @property string $content
 * @property string $publisher
 * @property double $award_amount
 * @property string $image_info
 * @property string $attachment_info
 * @property integer $visits
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $approved_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $approved_at
 */
class Pro_splendid_idea extends \common\helpers\ActiveRecordModel
{
    const TYPE_ALL = 0;
    const TYPE_TEXT = 1;
    const TYPE_OPTIMIZATION = 2;
    const TYPE_INNOVATE = 3;

    const STATUS_APPROVAL_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_CLOSED = -10;

    private $_imagesArray = null;
    private $_filesArray = null;
    
    public $focus_count = 0;
    public $comment_count = 0;

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
            [['type', 'title', 'content', 'publisher'], 'required'],
            [['id', 'type', 'status', 'approved_by', 'approved_at', 'visits'], 'integer'],
            [['award_amount'], 'number'],
            [['title'], 'string', 'max' => 256],
            [['content'], 'string', 'max' => 1024],
            [['publisher'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('carrental', 'Idea')]),
            'status' => \Yii::t('locale', '{name} status', ['name'=>\Yii::t('carrental', 'Idea')]),
            'title' => \Yii::t('locale', 'Title'),
            'content' => \Yii::t('locale', 'Content'),
            'publisher' => \Yii::t('locale', 'Publisher'),
            'award_amount' => \Yii::t('carrental', 'Award amount'),
            'image_info' => \Yii::t('locale', 'Images'),
            'attachment_info' => \Yii::t('locale', 'Attachment'),
            'visits' => \Yii::t('locale', 'Visits'),
            'created_by' => \Yii::t('locale', '{name} account', ['name'=>\Yii::t('locale', 'Publish')]),
            'updated_by' => \Yii::t('locale', '{name} account', ['name'=>\Yii::t('locale', 'Update')]),
            'approved_by' => \Yii::t('locale', '{name} account', ['name'=>\Yii::t('locale', 'Approval')]),
            'created_at' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('locale', 'Publish')]),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'approved_at' => \Yii::t('locale', '{name} time', ['name'=>\Yii::t('locale', 'Approval')]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
        ];
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
            'type' => array('width' => 100,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getTypesArray())." }"),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'title' => array('width' => 120, 'sortable' => 'true'),
            'content' => array('width' => 200, 'sortable' => 'true'),
            'publisher' => array('width' => 120),
            'image_info' => array('width' => 100),
            'attachment_info' => array('width' => 100),
            'visits' => array('width' => 80),
            'created_by' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.publish_user_disp; }"),
            'approved_by' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.approval_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public static function getTypesArray()
    {
        return [
            static::TYPE_ALL => \Yii::t('locale', 'All types'),
            static::TYPE_TEXT => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Punctuation')]),
            static::TYPE_OPTIMIZATION => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Optimization')]),
            static::TYPE_INNOVATE => \Yii::t('locale', '{name} type', ['name'=>\Yii::t('locale', 'Innovation')]),
        ];
    }

    public static function getStatusArray($enableAll = false)
    {
        $arr = [
            static::STATUS_APPROVAL_PENDING => \Yii::t('carrental', 'Approval pending'),
            static::STATUS_APPROVED => \Yii::t('carrental', 'Approved'),
            static::STATUS_CLOSED => \Yii::t('locale', 'Closed'),
        ];
        if ($enableAll) {
            $arr[0] = \Yii::t('locale', 'All');
        }
        return $arr;
    }

    public function getImagesArray() {
        if ($this->_imagesArray === null) {
            $this->_imagesArray = [];
            
            $arr = explode(',', $this->image_info);
            $arrImageIds = [];
            foreach ($arr as $v0) {
                $arrImageIds[] = intval($v0);
            }
            
            if (!empty($arrImageIds)) {
                $cdb = Pro_image::find();
                $cdb->where(['id' => $arrImageIds]);
                $arrRows = $cdb->all();
                foreach ($arrRows as $row) {
                    $this->_imagesArray[$row->id] = $row;
                }
            }
        }
        
        return $this->_imagesArray;
    }
    
    public function setImageInfoArray($arr) {
        $this->_imagesArray = [];
        $arrElements = [];
        foreach ($arr as $k => $img) {
            $arrElements[] = $k;
            $this->_imagesArray[$k] = $img;
        }
        $this->image_info = implode(",", $arrElements);
    }
    
    public function getAttachmentsArray() {
        if ($this->_filesArray === null) {
            $this->_filesArray = [];
            
            $arr = explode(',', $this->attachment_info);
            $arrImageIds = [];
            foreach ($arr as $v0) {
                $arrImageIds[] = intval($v0);
            }
            
            if (!empty($arrImageIds)) {
                $cdb = Pro_image::find();
                $cdb->where(['id' => $arrImageIds]);
                $arrRows = $cdb->all();
                foreach ($arrRows as $row) {
                    $this->_filesArray[$row->id] = $row;
                }
            }
        }
        
        return $this->_filesArray;
    }
    
    public function setAttachmentInfoArray($arr) {
        $this->_filesArray = [];
        $arrElements = [];
        foreach ($arr as $k => $img) {
            $arrElements[] = $k;
            $this->_filesArray[$k] = $img;
        }
        $this->attachment_info = implode(",", $arrElements);
    }
    
    /**
     * 
     * @return \common\helpers\ExtendActiveDataProvider
     */
    public static function createDataProvider($config = []) {
        return parent::createDataProvider(\yii\helpers\ArrayHelper::merge([
            'formattingAttributes' => [
                'type' => static::getTypesArray(),
                'status' => static::getStatusArray(),
                'created_at,updated_at,approved_at' => 'datetime',
            ],
            'findAttributes' => [
                'created_by,updated_by,approved_by' => \backend\models\Rbac_admin::createFindIdNamesArrayConfig(),
                'focus_count' => ['class'=>Pro_splendid_idea_focus::className(), 'idField'=>'focus_id', 'textField'=>'count(*)', 'groupBy'=>'focus_id', 'findByPrimaryKeys'=>true],
                'comment_count' => ['class'=>Pro_splendid_idea_comments::className(), 'idField'=>'main_id', 'textField'=>'count(*)', 'groupBy'=>'main_id', 'findByPrimaryKeys'=>true],
            ],
        ], $config));
    }

    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'title'];
    }

}
