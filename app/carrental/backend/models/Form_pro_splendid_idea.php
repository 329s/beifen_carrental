<?php

namespace backend\models;

/**
 * Description of Form_pro_splendid_idea
 *
 * @author kevin
 */
class Form_pro_splendid_idea extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $type;
    public $status;
    public $title;
    public $content;
    public $publisher;
    public $award_amount;
    public $image_info;
    public $attachment_info;
    public $publish_user_id;
    public $approval_user_id;
    
    private $_imagesArray;
    private $_filesArray;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'title', 'content', 'publisher'], 'required'],
            [['id', 'type', 'status', 'publish_user_id', 'approval_user_id'], 'integer'],
            [['award_amount'], 'number'],
            [['title'], 'string', 'max' => 256],
            [['content'], 'string', 'max' => 1024],
            [['publisher'], 'string', 'max' => 45],
            
            ['image_info', 'image', 'maxSize'=>256000, 'maxFiles'=>3],
            ['attachment_info', 'file', 'maxFiles'=>3],
        ];
    }

    public function getActiveRecordModel() {
        $model = new \common\models\Pro_splendid_idea();
        return $model;
    }
    
    public function processFileArrayAttribute($filepaths = array(), $attribute = '') {
        $arrImages = [];
        $ideaId = $this->getModelId();
        foreach ($filepaths as $key => $imagePath) {
            $fileKey = $this->parseArrayAttributeIndex($key, $attribute);
            $objImage = null;
            $_imgId = 0;
            if ($fileKey != 'addfiles') {
                $_imgId = intval($fileKey);
            }
            if ($_imgId) {
                $objImage = \common\models\Pro_image::findById($_imgId);
            }

            if (!$objImage || $objImage->bind_type != \common\models\Pro_image::BIND_TYPE_SPLENDID_IDEA_ATTACHMENT || $objImage->bind_id != $ideaId) {
                $objImage = new \common\models\Pro_image();
                $objImage->bind_type = \common\models\Pro_image::BIND_TYPE_SPLENDID_IDEA_ATTACHMENT;
                $objImage->bind_id = $ideaId;
                $objImage->status = \common\models\Pro_image::STATUS_ENABLED;
                $objImage->path = $imagePath;
                $objImage->save();
            }
            else {
                $objImage->path = $imagePath;
                $objImage->save();
            }
            
            if ($objImage) {
                \Yii::error("  inserted image:{$objImage->id} {$objImage->path}");
                $arrImages[$objImage->id] = $objImage;
            }
        }
        
        if ($attribute == 'image_info') {
            $this->_imagesArray = $arrImages;
        }
        elseif ($attribute == 'attachment_info') {
            $this->_filesArray = $arrImages;
        }
        
        return null;
    }
    
    public function getFileArrayAttributePaths($attribute, $value = '') {
        if (empty($value) && isset($this->$attribute)) {
            $value = $this->$attribute;
        }
        $arr = explode(',', $value);
        $arrImageIds = [];
        foreach ($arr as $v0) {
            $arrImageIds[] = intval($v0);
        }
        
        $arrFilePaths = [];
        if (!empty($arrImageIds)) {
            $cdb = \common\models\Pro_image::find();
            $cdb->where(['id' => $arrImageIds]);
            $arrRows = $cdb->all();
            foreach ($arrRows as $row) {
                $arrFilePaths[$row->id] = $row->path;
            }
        }
        return $arrFilePaths;
    }
    
    protected function afterSaveToModel($model) {
        if ($this->_imagesArray) {
            $model->setImageInfoArray(\yii\helpers\ArrayHelper::merge($model->getImagesArray(), $this->_imagesArray));
        }
        if ($this->_filesArray) {
            $model->setAttachmentInfoArray(\yii\helpers\ArrayHelper::merge($model->getAttachmentsArray(), $this->_filesArray));
        }
    }
    
}
