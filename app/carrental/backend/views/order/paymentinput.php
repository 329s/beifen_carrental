<?php

echo \backend\widgets\OrderPaymentInputWidget::widget([
    'orderId' => $orderId,
    'isRelet' => $isRelet,
    'isSettlement' => $isSettlement,
    'orderAction' => $orderAction,
]);
