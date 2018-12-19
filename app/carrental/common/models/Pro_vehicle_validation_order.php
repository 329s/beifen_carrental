<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property integer $order_id
 * @property integer $oil
 * @property integer $mileage
 * @property integer $validator
 * @property integer $validated_at
 * @property integer $validate_summary
 * @property string $validate_info
 * @property string $image_info
 * @property string $tmp_images
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_validation_order extends \common\helpers\ActiveRecordModel
{
    private $_validateInfoArray = null;
    private $_validateImagesArray = null;
    
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
            'vehicle_id' => Yii::t('locale', 'Vehicle'),
            'order_id' => \Yii::t('locale', 'Order'),
            'oil' => Yii::t('carrental', 'Oil volume'),
            'mileage' => Yii::t('carrental', 'Current mileage'),
            'validator' => Yii::t('carrental', 'Vehicle validator'),
            'validated_at' => Yii::t('carrental', 'Vehicle validated time'),
            'validate_summary' => Yii::t('carrental', 'Vehicle validated summary'),
            'validate_info' => Yii::t('locale', 'Vehicle validation info'),
            'image_info' => Yii::t('locale', 'Vehicle validation images'),
            'tmp_images' => Yii::t('locale', 'Uploaded images'),
            'created_at' => Yii::t('locale', 'Create time'),
            'updated_at' => Yii::t('locale', 'Update time'),
            'operation' => Yii::t('locale', 'Operation'),
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
            'vehicle_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.plate_number; }"),
            'order_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.order_serial; }"),
            'oil' => array('width' => 100, 'sortable' => 'true'),
            'mileage' => array('width' => 100, 'sortable' => 'true'),
            'validator' => array('width' => 100, 'sortable' => 'true'),
            'validated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'validate_info' => array('width' => 100, 'sortable' => 'true'),
            'image_info' => array('width' => 100, 'sortable' => 'true'),
            'tmp_images' => array('width' => 100, 'sortable' => 'true'),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public function getValidateInfoArray() {
        if ($this->_validateInfoArray === null) {
            $this->_validateInfoArray = [];
            
            $arr = explode(';', $this->validate_info);
            foreach ($arr as $v0) {
                $arr2 = explode(':', trim($v0));
                if (count($arr2) > 1) {
                    $_id = intval($arr2[0]);
                    $_val = intval($arr2[1]);
                    
                    $this->_validateInfoArray[$_id] = $_val;
                }
            }
        }
        
        return $this->_validateInfoArray;
    }
    
    public function setValidateInfoArray($arr) {
        $this->_validateInfoArray = [];
        $arrElements = [];
        foreach ($arr as $k => $v) {
            $arrElements[] = "{$k}:{$v}";
            $this->_validateInfoArray[$k] = $v;
        }
        $this->validate_info = implode(";", $arrElements);
    }
    
    public function getValueByValidationOptionsId($validationOptionsId) {
        $arr = $this->getValidateInfoArray();
        if (isset($arr[$validationOptionsId])) {
            return $arr[$validationOptionsId];
        }
        return 0;
    }
    
    public function getValidateImagesArray() {
        if ($this->_validateImagesArray === null) {
            $this->_validateImagesArray = [];
            
            $arr = explode(';', $this->image_info);
            $arrImageIds = [];
            $arrImageIdsByValidationId = [];
            foreach ($arr as $v0) {
                $arr2 = explode(':', trim($v0));
                if (count($arr2) > 1) {
                    $_id = intval($arr2[0]);
                    $_vals = trim($arr2[1]);
                    $arr3 = explode('|', $_vals);
                    $imgIds = [];
                    foreach ($arr3 as $_val) {
                        $arrImageIds[] = intval($_val);
                        $imgIds[] = intval($_val);
                    }
                    
                    $arrImageIdsByValidationId[$_id] = $imgIds;
                }
            }
            
            $arrImages = [];
            if (!empty($arrImageIds)) {
                $cdb = Pro_image::find();
                $cdb->where(['id' => $arrImageIds]);
                $arrRows = $cdb->all();
                foreach ($arrRows as $row) {
                    $arrImages[$row->id] = $row;
                }
            }
            
            foreach ($arrImageIdsByValidationId as $_id => $_imgIds) {
                $imgs = [];
                foreach ($_imgIds as $_imgId) {
                    if (isset($arrImages[$_imgId])) {
                        $imgs[] = $arrImages[$_imgId];
                    }
                }
                $this->_validateImagesArray[$_id] = $imgs;
            }
        }
        
        return $this->_validateImagesArray;
    }
    
    public function setValidateImageInfoArray($arr) {
        $this->_validateImagesArray = [];
        $arrElements = [];
        foreach ($arr as $k => $_imgs) {
            $arrImgIds = [];
            foreach ($_imgs as $_img) {
                $arrImgIds[] = $_img->id;
            }
            $arrElements[] = "{$k}:".implode("|", $arrImgIds);
            $this->_validateImagesArray[$k] = $_imgs;
        }
        $this->image_info = implode(";", $arrElements);
    }
    
    public function getValidationImagesByValidationOptionsId($validationId) {
        $arr = $this->getValidateImagesArray();
        return (isset($arr[$validationId]) ? $arr[$validationId] : []);
    }

}
