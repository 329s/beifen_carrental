<?php

namespace common\models;

/**
 * Office model
 *
 * @property integer $id
 * @property string $fullname
 * @property string $shortname
 * @property string $manager
 * @property string $telephone
 * @property string $open_time
 * @property string $close_time
 * @property string $address
 * @property double $geo_x
 * @property double $geo_y
 * @property integer $city_id
 * @property integer $area_id
 * @property integer $parent_id
 * @property integer $status
 * @property string $transit_route
 * @property integer $landmark
 * @property string $image_info
 * @property integer $edit_user_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_office extends \common\helpers\ActiveRecordModel
{
    const REGION_TYPE_CITY = 3;
    const REGION_TYPE_SUB = 2;
    const REGION_TYPE_OFFICE = 1;
    
    const LANDMARK_NEAR_AIR_PORT = 0x01;
    const LANDMARK_NEAR_TRAIN_STATION = 0x02;
    const LANDMARK_NEAR_BUS_STATION = 0x04;
    const LANDMARK_NEAR_SUBWAY = 0x08;
    
    const STATUS_NORMAL = 0;
    const STATUS_CLOSED = -10;

    const ONE_WAY_NO = 0;
    const ONE_WAY_YES = 1;
    
    private $_imagesArray = null;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            \yii\behaviors\TimestampBehavior::className(),
            \common\helpers\behaviors\EditorBehavior::className(),
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
            'fullname' => \Yii::t('locale', 'Region/Office full name'),
            'shortname' => \Yii::t('locale', 'Short name'),
            'manager' => \Yii::t('locale', 'Charger'),
            'telephone' => \Yii::t('locale', 'Contact number'),
            'shopowner_tel' => \Yii::t('locale', 'Shopowner Tel'),
            'open_time' => \Yii::t('locale', 'Office open time'),
            'close_time' => \Yii::t('locale', 'Office close time'),
            'address' => \Yii::t('locale', 'Region/Office address'),
            'geo_coordinates' => \Yii::t('locale', 'Geographic coordinates'),
            'geo_x' => \Yii::t('locale', 'Longitude'),
            'geo_y' => \Yii::t('locale', 'Latitude'),
            'city_id' => \Yii::t('locale', 'Belong city'),
            'area_id' => \Yii::t('locale', 'Office region'),
            'parent_id' => \Yii::t('carrental', 'Superior store'),
            'status' => \Yii::t('locale', 'Status'),
            'isonewayoffice' => \Yii::t('locale', 'Isonewayoffice'),
            'transit_route' => \Yii::t('locale', 'Transit route'),
            'landmark' => \Yii::t('locale', 'Landmark'),
            'image_info' => \Yii::t('locale', '{name} photos', ['name'=>\Yii::t('locale', 'Office')]),
            'edit_user_id' => \Yii::t('locale', 'Edit user'),
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
            'fullname' => array('width' => 120, 'sortable' => 'true'),
            'shortname' => array('width' => 120, 'sortable' => 'true'),
            'manager' => array('width' => 100, 'sortable' => 'true'),
            'telephone' => array('width' => 120),
            'shopowner_tel' => array('width' => 120),
            'open_time' => array('width' => 100, 'sortable' => 'true'),
            'close_time' => array('width' => 100, 'sortable' => 'true'),
            'address' => array('width' => 100),
            'geo_coordinates' => array('width' => 100, 'formatter'=>"function(value,row) { if (row.geo_x || row.geo_y) { return ''+row.geo_x+','+row.geo_y; } return ''; }"),
            'geo_x' => array('width' => 100),
            'geo_y' => array('width' => 100),
            'city_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row) { return row.city_disp; }"),
            'area_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row) { return row.area_disp; }"),
            'parent_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row) { return row.parent_office; }"),
            'status' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getStatusArray())." }"),
            'isonewayoffice' => array('width' => 60,
                'formatter' => "function(value,row){ ".\common\helpers\CEasyUI::convertComboboxDataToFormatterFunc(static::getOneWayArray())." }"),
            'landmark' => array('width' => 140,
                'formatter' => "function(value,row){\n    var t = new Array();\n".
                "    if (value & ".self::LANDMARK_NEAR_AIR_PORT.") { t.push($.custom.lan.defaults.office.nearAirPort); }\n".
                "    if (value & ".self::LANDMARK_NEAR_TRAIN_STATION.") { t.push($.custom.lan.defaults.office.nearTrainStation); }\n".
                "    if (value & ".self::LANDMARK_NEAR_BUS_STATION.") { t.push($.custom.lan.defaults.office.nearBusStation); }\n".
                "    if (value & ".self::LANDMARK_NEAR_SUBWAY.") { t.push($.custom.lan.defaults.office.nearSubway); }\n".
                "    return t.join(',');\n".
                "}"),
            'transit_route' => array('width' => 200),
            'image_info' => array('width' => 100),
            'edit_user_id' => array('width' => 100, 'sortable' => 'true', 'formatter' => "function(value,row){ return row.edit_user_disp; }"),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                    \Yii::$app->user->can('office/edit') ? array('type' => 'dialog', 'url' => \yii\helpers\Url::to(['office/edit', 'id'=>'']), 'name' => \Yii::t('locale', 'Edit'), 'title' => \Yii::t('locale', 'Edit'), 'paramField' => 'id', 'icon' => 'icon-edit') : null,
                    \Yii::$app->user->can('office/delete') ? array('type' => 'ajax', 'url' => \yii\helpers\Url::to(['office/delete', 'id'=>'']), 'name' => \Yii::t('locale', 'Delete'), 'title' => \Yii::t('locale', 'Are you sure to delete these records?'), 'paramField' => 'id', 'icon' => 'icon-delete') : null,
                ),
            ),
        );
    }
    
    public static function getStatusArray() {
        return [
            \common\models\Pro_office::STATUS_NORMAL => \Yii::t('locale', 'Opening'), 
            \common\models\Pro_office::STATUS_CLOSED => \Yii::t('locale', 'Closed'), 
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
                    $this->_imagesArray[intval($row->id)] = $row;
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
    
    public function getCoordinate() {
        return $this->geo_x.','.$this->geo_y;
    }
    
    public static function getOfficeIdsForOfficeLimitedCondition($officeId, $enableAreaLimit=false) {
        if ($enableAreaLimit) {
            $objOffice = \common\models\Pro_office::findById($officeId);
            if ($objOffice && $objOffice->area_id) {
                $arrRows = \common\models\Pro_office::findAll(['area_id'=>$objOffice->area_id]);
                $arrOfficeIds = [];
                foreach ($arrRows as $row) {
                    $arrOfficeIds[] = $row->id;
                }
                if (!empty($arrOfficeIds)) {
                    $officeId = $arrOfficeIds;
                }
            }
        }
        
        $cdb = static::find(true);
        $cdb->where(['or', ['id'=>$officeId], ['parent_id'=>$officeId]]);
        $cdb->andWhere(['status'=> static::STATUS_NORMAL]);
        $arrRows = $cdb->all();
        
        $arrOfficeIds = [];
        $count = 0;
        foreach($arrRows as $row) {
            $arrOfficeIds[] = $row->id;
            $count++;
        }
        
        if ($count <= 1) {
            return $officeId;
        }
        return $arrOfficeIds;
    }
    
    public static function createFindIdNamesArrayConfig($config = array()) {
        return ['class'=> static::className(), 'idField'=>'id', 'textField'=>'shortname', 'extend'=>[\common\components\OfficeModule::HEAD_OFFICE_ID=>\Yii::t('locale', 'Head office')]];
    }


    public static function getOneWayArray() {
        return [
            \common\models\Pro_office::ONE_WAY_YES => \Yii::t('locale', 'One way yes'), 
            \common\models\Pro_office::ONE_WAY_NO => \Yii::t('locale', 'One way no'), 
        ];
    }

}
