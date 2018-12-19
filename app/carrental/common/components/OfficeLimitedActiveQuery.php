<?php

namespace common\components;

class OfficeLimitedActiveQuery extends \yii\db\ActiveQuery
{
    public $attribute = 'belong_office_id';
    public $enableAreaLimit = false;
    
    private $_hasVerifiedOfficeLimitation = false;

    /**
     * @inheritdoc
     */
    public function prepare($builder) {
        if (!$this->_hasVerifiedOfficeLimitation) {
            $this->_hasVerifiedOfficeLimitation = true;
            $this->verifyOfficeLimitation();
        }
        return parent::prepare($builder);
    }
    
    public function verifyOfficeLimitation() {
        $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
        
        if ($officeId != OfficeModule::HEAD_OFFICE_ID) {
            $this->applyOfficeLimitation($officeId);
        }
    }

    public function applyOfficeLimitation($officeId) {
        $tablePart = '';
        if (!empty($this->join)) {
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
            $tablePart = $tableName.'.';
        }
        $arrConditionInfo = null;
        $officeCondition = \common\models\Pro_office::getOfficeIdsForOfficeLimitedCondition($officeId, $this->enableAreaLimit);
        if (is_array($this->attribute)) {
            $arrConditionInfo = ['or'];
            $count = 0;
            foreach ($this->attribute as $attr) {
                $arrConditionInfo[] = [$tablePart.$attr => $officeCondition];
                $count++;
            }
            if ($count) {
                $this->andWhere($arrConditionInfo);
            }
        }
        else {
            $arrConditionInfo = [$tablePart.$this->attribute => $officeCondition];
            $this->andWhere($arrConditionInfo);
        }
        return $arrConditionInfo;
    }
}
