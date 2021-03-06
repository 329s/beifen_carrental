<?php

namespace common\models;

/**
 *
 * @property integer $id
 * @property integer $office_id
 * @property integer $user_id
 * @property string $comment
 * @property string $image_info
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_office_comments extends \common\helpers\ActiveRecordModel
{
    const STATUS_NORMAL = 0;
    const STATUS_PROCESSED = 1;
    
    private $_imagesArray = null;

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
            'office_id' => \Yii::t('locale', 'Office'),
            'user_id' => \Yii::t('locale', 'User Name'),
            'comment' => \Yii::t('locale', 'Comment'),
            'image_info' => \Yii::t('locale', 'Images'),
            'status' => \Yii::t('locale', 'Status'),
            'created_at' => \Yii::t('locale', 'Create time'),
            'updated_at' => \Yii::t('locale', 'Update time'),
            'operation' => \Yii::t('locale', 'Operation'),
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
            'office_id' => array('width' => 120, 'sortable' => 'true'),
            'user_id' => array('width' => 120, 'sortable' => 'true'),
            'comment' => array('width' => 120),
            'image_info' => array('width' => 100),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public static function getStatusArray() {
        return [
            static::STATUS_NORMAL => \Yii::t('locale', 'Normal'), 
            static::STATUS_PROCESSED => \Yii::t('locale', 'Processed'), 
        ];
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
}

