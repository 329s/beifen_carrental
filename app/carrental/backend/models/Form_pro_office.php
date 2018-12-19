<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_office".
 */
class Form_pro_office extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $fullname;
    public $shortname;
    public $manager;
    public $telephone;
    public $shopowner_tel;
    public $open_time;
    public $close_time;
    public $address;
    public $geo_x;
    public $geo_y;
    public $status;
    public $city_id;
    public $area_id;
    public $parent_id;
    public $transit_route;
    public $landmark;
    public $image_info;

    public $_imagesArray;
    public $isonewayoffice;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fullname', 'shortname', 'status', 'isonewayoffice','city_id', 'area_id', 'parent_id'], 'required'],
            [['geo_x', 'geo_y'], 'number'],
            [['id', 'status', 'isonewayoffice','city_id', 'area_id', 'parent_id', 'landmark'], 'integer'],
            [['fullname'], 'string', 'min' => 2, 'max' => 255],
            [['shortname', 'manager', 'telephone', 'shopowner_tel','address'], 'string', 'max' => 255],
            [['open_time', 'close_time'], 'string', 'max' => 8],
            [['transit_route'], 'string', 'max' => 512],
            [['image_info'], 'image', 'maxSize'=>256000, 'maxFiles'=>4],
            [['fullname'], 'unique', 'targetClass' => 'common\models\Pro_office', 'filter'=>['<>', 'id', $this->id]],
            
            ['status', 'default', 'value' => \common\models\Pro_office::STATUS_NORMAL],
            ['status', 'in', 'range' => [\common\models\Pro_office::STATUS_NORMAL, \common\models\Pro_office::STATUS_CLOSED]],
            ['isonewayoffice', 'default', 'value' => \common\models\Pro_office::ONE_WAY_NO],
            ['isonewayoffice', 'in', 'range' => [\common\models\Pro_office::ONE_WAY_NO, \common\models\Pro_office::ONE_WAY_YES]],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_office();
        return $model;
    }

    public function load($data, $formName = null) {
        if (parent::load($data, $formName)) {
            $objCity = \common\models\Pro_city::findById($this->city_id);
            if (!$objCity) {
                $this->addError('city_id', \Yii::t('carrental', 'Could not find office belong city!'));
                return false;
            }
            $objCityArea = \common\models\Pro_city_area::findById($this->area_id);
            if (!$objCityArea) {
                $this->addError('area_id', \Yii::t('carrental', 'Could not find office belong area!'));
                return false;
            }
            return true;
        }
        return false;
    }
    
    public function processFileArrayAttribute($filepaths = array(), $attribute = '') {
        $arrImages = [];
        $officeId = $this->getModelId();
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

            if (!$objImage || $objImage->bind_type != \common\models\Pro_image::BIND_TYPE_OFFICE_PHOTO || $objImage->bind_id != $officeId) {
                $objImage = new \common\models\Pro_image();
                $objImage->bind_type = \common\models\Pro_image::BIND_TYPE_OFFICE_PHOTO;
                $objImage->bind_id = $officeId;
                $objImage->status = \common\models\Pro_image::STATUS_ENABLED;
                $objImage->path = $imagePath;
                $objImage->save();
            }
            else {
                $objImage->path = $imagePath;
                $objImage->save();
            }
            
            if ($objImage) {
                \Yii::error("  inserted image:{$objImage->id} {$objImage->path} {$_imgId}");
                $arrImages[$objImage->id] = $objImage;
            }
        }
        
        if ($attribute == 'image_info') {
            $this->_imagesArray = $arrImages;
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
    }
    
}
