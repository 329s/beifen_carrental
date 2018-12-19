<?php

echo backend\widgets\VehicleFeePlanEditorWidget::widget([
    'vehicleModelId' => $vehicleModelId,
    'officeId' => $officeId,
    'feesBySources' => $arrFeesBySources,
    'submitUrl' => $saveUrl,
]);
