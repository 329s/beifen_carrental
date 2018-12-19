<?php

$inputs = [
    
];

$htmlArray = [];

$htmlArray[] = \backend\widgets\OrderEditorEasyuiWidget::widget([
    'action' => 'settlement',
    'vehicleOrder' => (isset($objVehicleOrder) ? $objVehicleOrder : null),
    'vehicleModelId' => (isset($vehicleModelId) ? $vehicleModelId : null),
    'vehicleId' => (isset($vehicleId) ? $vehicleId : null),
    'orderReadonly' => true,
    'customerReadonly' => true,
]);

echo implode("\n", $htmlArray);
