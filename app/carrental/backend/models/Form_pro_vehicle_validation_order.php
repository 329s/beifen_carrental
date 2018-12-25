<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_validation_order".
 */
class Form_pro_vehicle_validation_order extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $vehicle_id;
    public $order_id;
    public $oil;
    public $mileage;
    public $validator;
    public $validated_at;
    public $validate_summary;
    public $validate_info;
    public $image_info;
    public $tmp_images;
    
    public $_validateInfoArray;
    public $_validateImagesArray;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['vehicle_id', 'order_id', 'oil', 'mileage'], 'required'],
            [['id', 'vehicle_id', 'order_id', 'oil', 'mileage'], 'integer'],
            [['validator'], 'string', 'max' => 32],
            [['validate_summary'], 'string', 'max' => 128],
            
            [['validator', 'validate_summary'], 'filter', 'filter' => 'trim'],
            [['validated_at'], 'datetime'],
            [['validate_info'], \common\helpers\validators\PairsValidator::className(), 'rule' => ['integer']],
            [['image_info', 'tmp_images'], 'image', 'maxFiles' => 24],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_validation_order();
        return $model;
    }

    public function processFileArrayAttribute($filepaths = array(), $attribute = '') {
        $arrImages = [];
        foreach ($filepaths as $key => $imagePath) {
            $idKey = $this->parseArrayAttributeIndex($key, $attribute);
            if (!$idKey) {
                continue;
            }
            $fileKey = $this->parseArrayAttributeIndex(substr($key, strlen($idKey)+2), $attribute);
            $objImage = null;
            $_imgId = 0;
            if ($fileKey != 'addfiles') {
                $_imgId = intval($fileKey);
            }
            if ($_imgId) {
                $objImage = \common\models\Pro_image::findById($_imgId);
            }

            if (!$objImage || $objImage->bind_type != \common\models\Pro_image::BIND_TYPE_VEHICLE_VALIDATION || $objImage->bind_id != $this->vehicle_id) {
                $objImage = new \common\models\Pro_image();
                $objImage->bind_type = \common\models\Pro_image::BIND_TYPE_VEHICLE_VALIDATION;
                $objImage->bind_id = $this->vehicle_id;
                $objImage->status = \common\models\Pro_image::STATUS_ENABLED;
                $objImage->path = $imagePath;
                $objImage->save();
            }
            else {
                $objImage->path = $imagePath;
                $objImage->save();
            }
            
            if ($objImage) {
                \Yii::info("  inserted image:{$objImage->id} {$objImage->path}");
                if (isset($arrImages[$idKey])) {
                    $arrImages[$idKey][$objImage->id] = $objImage;
                }
                else {
                    $arrImages[$idKey] = [$objImage->id => $objImage];
                }
            }
        }
        
        if ($attribute == 'image_info') {
            $this->_validateImagesArray = $arrImages;
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
        if ($this->_validateImagesArray) {
            $model->setValidateImageInfoArray(\yii\helpers\ArrayHelper::merge($model->getValidateImagesArray(), $this->_validateImagesArray));
        }
    }
    
}