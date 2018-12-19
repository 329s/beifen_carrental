<?php
namespace backend\models;

/**
 * optional services form
 */
class Form_pro_optional_services extends \common\helpers\ActiveFormModel
{
    public $belongOfficeId = 0;
    
    public $servicePriceArray = [];
    public $serviceObjectsArray = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
        ];
    }
    
    public function getActiveRecordModel() {
        $model = new \common\models\Pro_service_price();
        return $model;
    }

    public function savingFields() {
        return [
        ];
    }
    
    public function load($data, $formName = null)
    {
        $scope = $formName === null ? $this->formName() : $formName;
        $formData = null;
        // this from load data must pasing scope
        if ($scope === '' && !empty($data)) {
            $formData = $data[$this->formName()];
        } elseif (isset($data[$scope])) {
            $formData = $data[$scope];
        }
        
        $formSelections = [];
        if ($formData && isset($formData['selections'])) {
            foreach ($formData['selections'] as $selId => $_tmp) {
                $formSelections[intval($selId)] = 1;
            }
        }
        
        if ($formData) {
            $arrServicePriceObjects = \common\models\Pro_service_price::findAllServicePrices($this->belongOfficeId);

            $this->servicePriceArray = [];
            $this->serviceObjectsArray = [];
            foreach ($arrServicePriceObjects as $objService) {
                if (isset($formSelections[$objService->id])) {
                    if (isset($formData[$objService->id])) {
                        $this->servicePriceArray[$objService->id] = floatval($formData[$objService->id]);
                        $this->serviceObjectsArray[$objService->id] = $objService;
                    }
                }
            }
            
            return true;
        }
        else {
            return false;
        }
    }
}