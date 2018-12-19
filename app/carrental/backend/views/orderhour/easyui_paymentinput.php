<?php

echo \backend\widgets\OrderhourPaymentInputWidget::widget([
    'orderId' => $orderId,
    'isRelet' => $isRelet,
    'isSettlement' => $isSettlement,
    'orderAction' => $orderAction,
]);
