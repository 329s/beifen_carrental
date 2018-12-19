<?php

namespace common\models;

use Yii;

/**
 *
 * @property integer $id
 * @property integer $vehicle_id
 * @property string $serial 序列号
 * @property string $insurance_no 保单号码
 * @property integer $time 出险时间
 * @property string $address 出险地点
 * @property string $driver 驾驶人
 * @property integer $report_time 报案时间
 * @property integer $filing_time 立案时间
 * @property integer $closing_time 结案时间
 * @property integer $case_status 案件状态
 * @property integer $indemnity_amount 赔款金额
 * @property integer $created_at
 * @property integer $updated_at
 */
class Pro_vehicle_insurance_indemnity extends \common\helpers\ActiveRecordModel
{    
    
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
            'serial' => Yii::t('locale', 'Serial No.'),
            'insurance_no' => Yii::t('carrental', 'Insurance no'),
            'time' => Yii::t('carrental', 'Danger time'),
            'address' => Yii::t('carrental', 'Danger place'),
            'driver' => Yii::t('carrental', 'Driver'),
            'report_time' => Yii::t('carrental', 'Report time'),
            'filing_time' => Yii::t('carrental', 'Filing time'),
            'closing_time' => Yii::t('carrental', 'Closing time'),
            'case_status' => Yii::t('carrental', 'Case status'),
            'indemnity_amount' => Yii::t('carrental', 'Indemnity amount'),
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
            'serial' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'textbox'),
            'insurance_no' => array('width' => 140, 'sortable' => 'true',
                'editor' => 'textbox'),
            'time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'address' => array('width' => 180, 'sortable' => 'true',
                'editor' => 'textbox'),
            'driver' => array('width' => 100, 'sortable' => 'true',
                'editor' => 'textbox'),
            'report_time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'filing_time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'closing_time' => array('width' => 180, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}",
                'editor' => 'datetimebox'),
            'case_status' => array('width' => 100,
                'editor' => array(
                    'type'=>'combobox',
                    'options' => array('valueField'=>'id', 'textField'=>'text', 'data'=>\common\helpers\CEasyUI::convertComboboxDataToString(\common\components\OptionsModule::getInsuranceCaseStatusArray())),
                )
            ),
            'indemnity_amount' => array('width' => 100,
                'editor' => 'numberbox',
            ),
            'created_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'updated_at' => array('width' => 140, 'sortable' => 'true', 'formatter' => "function(value,row){return $.custom.utils.humanTime(value);}"),
            'operation' => array('width' => 60, 
                'buttons' => array(
                ),
            ),
        );
    }
    
    public function getAttributeValues()
    {
        return [
            'id' => $this->id,
            'vehicle_id' => $this->vehicle_id,
            'serial' => $this->serial,
            'insurance_no' => $this->insurance_no,
            'time' => $this->time,
            'address' => $this->address,
            'driver' => $this->driver,
            'report_time' => $this->report_time,
            'filing_time' => $this->filing_time,
            'closing_time' => $this->closing_time,
            'case_status' => $this->case_status,
            'indemnity_amount' => $this->indemnity_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
    
}
