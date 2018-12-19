<?php

namespace backend\models;

/**
 * This is the active form model class for table "pro_vehicle_validation_config".
 */
class Form_pro_vehicle_validation_config extends \common\helpers\ActiveFormModel
{
    public $id = 0;
    public $name;
    public $type;
    public $belong_id;
    public $status;
    public $value_flag;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type', 'belong_id'], 'required'],
            [['id', 'type', 'belong_id', 'status'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique', 'targetClass' => 'common\models\Pro_vehicle_validation_config', 'filter'=>['<>', 'id', $this->id]],
            
            [['name'], 'filter', 'filter' => 'trim'],
            [['value_flag'], \common\helpers\validators\BitFlagValidator::className(), 'list' => \common\models\Pro_vehicle_validation_config::getValueFlagsArray()],
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_vehicle_validation_config();
        return $model;
    }

    public function load2($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        $formData = null;
        if ($scope === '' && !empty($data)) {
            $formData = $data;
        } elseif (isset($data[$scope])) {
            $formData = $data[$scope];
        }
        
        if ($formData) {
            // process
            $arrDateFields = [];
            foreach ($arrDateFields as $_field) {
                if (isset($formData[$_field])) {
                    $formData[$_field] = \common\helpers\Utils::toTimestamp($formData[$_field]);
                }
            }
            
            if (isset($formData['value_flag'])) {
                if (is_array($formData['value_flag'])) {
                    $val = 0;
                    foreach ($formData['value_flag'] as $v) {
                        $val += intval($v);
                    }
                    $formData['value_flag'] = $val;
                }
            }
            
            $this->setAttributes($formData);
            
            $this->edit_user_id = \Yii::$app->getUser()->id;
            return $this->validate();
        }
        else {
            return false;
        }
    }
}