<?php

echo \backend\widgets\OrderEditorEasyuiWidget::widget([
    'action' => (isset($action) ? $action : null),
    'vehicleOrder' => (isset($objVehicleOrder) ? $objVehicleOrder : null),
    'vehicleModelId' => (isset($vehicleModelId) ? $vehicleModelId : null),
    'vehicleId' => (isset($vehicleId) ? $vehicleId : null),
]);
