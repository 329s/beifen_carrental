<?php

echo \backend\widgets\OrderEditorWidget::widget([
    'action' => 'insert',
    'vehicleOrder' => null,
    'vehicleModelId' => null,
    'vehicleId' => null,
    'userId' => $userId,
]);
