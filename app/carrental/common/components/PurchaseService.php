<?php

namespace common\components;

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class PurchaseService
{
    
    public static function doPurchase($channelType, $arrData) {
        $arrResult = [false, 'Unknown error'];
        do
        {
            $orderType = \common\models\Pro_purchase_order::TYPE_VEHICLE_ORDER;
            $channelTradeNo = $arrData['channel_trade_no'];
            
            if (empty($channelTradeNo)) {
                $arrResult[1] = Yii::t('locale', '{name} should not be empty!', ['name'=>Yii::t('locale', '{name} No.', ['name'=>Yii::t('locale', 'Order')])]);
                \Yii::error("purchase service do purchase could not get myself trade no!", 'order');
                break;
            }
            if (substr($channelTradeNo, 0, 1) == Consts::RELET_TRADE_NO_PREFIX) {
                $orderType = \common\models\Pro_purchase_order::TYPE_VEHICLE_RELET;
            }
            
            $objPaymentOrder = \common\models\Pro_purchase_order::findByPaymentChannelTradeNo($channelType, $channelTradeNo);
            if ($objPaymentOrder) {
                if ($objPaymentOrder->status == \common\models\Pro_purchase_order::STATUS_SUCCEES) {
                    $arrResult[0] = true;
                    $arrResult[1] = 'Already processed';
                    \Yii::warning("purchase service do purchase with trade_no:{$channelTradeNo} while the purchase_order:{$objPaymentOrder->serial} already processed!", 'order');
                    break;
                }
            }
            else {
                $objPaymentOrder = new \common\models\Pro_purchase_order();
                
                $objPaymentOrder->channel_type = $channelType;
                $objPaymentOrder->channel_trade_no = $channelTradeNo;
                $objPaymentOrder->type = $orderType;
                $objPaymentOrder->sub_type = \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT;
            }
            
            $objPaymentOrder->status = $arrData['status'];
            $objPaymentOrder->purchase_code = $arrData['purchase_code'];
            $objPaymentOrder->purchase_msg = $arrData['purchase_msg'];
            $objPaymentOrder->tried_count += 1;
            $objPaymentOrder->amount = $arrData['amount'];
            if (isset($arrData['receipt_amount'])) {
                $objPaymentOrder->receipt_amount = $arrData['receipt_amount'];
            }
            else {
                $objPaymentOrder->receipt_amount = $objPaymentOrder->amount;
            }
            
            $objPaymentOrder->purchased_at = $arrData['purchased_at'];
            $objPaymentOrder->extra_info = $arrData['extra_info'];
            if (isset($arrData['pay_source']) && !empty($arrData['pay_source'])) {
                $objPaymentOrder->pay_source = $arrData['pay_source'];
            }
            
            $arrExtraInfo = json_decode($objPaymentOrder->extra_info, true);
            $userId = intval(isset($arrExtraInfo['userid']) ? $arrExtraInfo['userid'] : 0);
            if ($channelType == \common\models\Pro_purchase_order::CHANNEL_TYPE_ALIPAY) {
                if ($orderType == \common\models\Pro_purchase_order::TYPE_VEHICLE_ORDER) {
                    // should not use default findOne because the order default filtered belong office
                    $cdb = \common\models\Pro_vehicle_order::find(true);
                    $cdb->where(['serial'=>$arrData['serial']]);
                    $objVehicleOrder = $cdb->one();
                    if ($objVehicleOrder) {
                        $userId = $objVehicleOrder->user_id;
                    }
                }
            }
            
            $arrExtraSaveObjects = [];
            
            $objUserInfo = \common\models\Pub_user_info::findById($userId);
            if (!$objUserInfo) {
                \Yii::error("process payment while could not find user_info:{$userId}", 'order');
                $arrResult[1] = 'Attach invalid';
                $objPaymentOrder->status = \common\models\Pro_purchase_order::STATUS_FAILED;
            }
            
            $objPaymentOrder->user_id = $userId;
            
            if ($orderType == \common\models\Pro_purchase_order::TYPE_VEHICLE_ORDER) {
                // should not use default findOne because the order default filtered belong office
                $cdb = \common\models\Pro_vehicle_order::find(true);
                $cdb->where(['serial'=>$arrData['serial']]);
                $objVehicleOrder = $cdb->one();
                if ($objVehicleOrder !== false) {
                    $objPaymentOrder->bind_id = $objVehicleOrder->id;
                    $objPaymentOrder->belong_office_id = $objVehicleOrder->belong_office_id;
                    if (empty($objPaymentOrder->user_id)) {
                        $objPaymentOrder->user_id = $objVehicleOrder->user_id;
                    }
                    
                    if (empty($objPaymentOrder->pay_source)) {
                        $objPaymentOrder->pay_source = $objVehicleOrder->pay_source;
                    }
                    else {
                        $objVehicleOrder->pay_source = $objPaymentOrder->pay_source;
                    }

                    if ($objVehicleOrder->user_id != $objPaymentOrder->user_id) {
                        $arrResult[1] = 'Payment user id not match';
                        $objPaymentOrder->status = \common\models\Pro_purchase_order::STATUS_FAILED;
                        //break;
                    }
                    elseif ($objPaymentOrder->status == \common\models\Pro_purchase_order::STATUS_SUCCEES) {
                        $objVehicleOrder->paid_amount += $objPaymentOrder->amount;
                        $arrExtraSaveObjects[] = $objVehicleOrder;
                        
                        $objPaidDetail = \common\models\Pro_vehicle_order_price_detail::createPaidObjectByPurchaseOrder($objPaymentOrder, $objVehicleOrder);
                        $arrExtraSaveObjects[] = $objPaidDetail;
                    }
                    
                    $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicleOrder->vehicle_model_id);
                    $objOffice = \common\models\Pro_office::findById($objVehicleOrder->office_id_rent);
                    \common\components\SmsComponent::send($objVehicleOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_BOOKED_PAID, [
                        'CNAME'=>$objVehicleOrder->customer_name, 
                        'AUTOMODEL'=>$objVehicleModel ? $objVehicleModel->getHumanDisplayText() : '',
                        'USETIME'=>  date('Y-m-d H:i', $objVehicleOrder->start_time),
                        'SHOPADDRESS'=>$objVehicleOrder->getTakeCarAddressText(),
                        'SHOPTELEPHONE'=>$objOffice ? $objOffice->telephone : '',
                        'ORDERID'=>$objVehicleOrder->serial,
                    ]);
                }
                else {
                    \Yii::error("process payment while could not find vehicle rental order:{$arrData['serial']}", 'order');
                    $arrResult[1] = 'Invalid out_trade_no';
                    $objPaymentOrder->bind_id = 0;
                    break;
                }
            }
            else if ($orderType == \common\models\Pro_purchase_order::TYPE_VEHICLE_RELET) {
                $objVehicleRelet = \common\models\Pro_vehicle_order_relet::findOne(['serial'=>$arrData['serial']]);
                if ($objVehicleRelet) {
                    $objVehicleOrder = $objVehicleRelet->getMainOrder();
                    if (!$objVehicleOrder) {
                        \Yii::error("process payment while could not find vehicle rental order:{$objVehicleRelet->order_id} by relet order:{$arrData['serial']}", 'order');
                        $arrResult[1] = 'Invalid out_trade_no';
                        $objPaymentOrder->bind_id = 0;
                        break;
                    }
                    
                    $objPaymentOrder->bind_id = $objVehicleOrder->id;
                    $objPaymentOrder->belong_office_id = $objVehicleOrder->belong_office_id;
                    
                    if (empty($objPaymentOrder->pay_source)) {
                        $objPaymentOrder->pay_source = $objVehicleRelet->pay_source;
                    }
                    else {
                        $objVehicleRelet->pay_source = $objPaymentOrder->pay_source;
                    }

                    if ($objVehicleOrder->user_id != $objPaymentOrder->user_id) {
                        $arrResult[1] = 'Payment user id not match';
                        $objPaymentOrder->status = \common\models\Pro_purchase_order::STATUS_FAILED;
                        //break;
                    }
                    elseif ($objPaymentOrder->status == \common\models\Pro_purchase_order::STATUS_SUCCEES) {
                        $objVehicleRelet->paid_amount += $objPaymentOrder->amount;
                        $arrExtraSaveObjects[] = $objVehicleRelet;
                        
                        $objVehicleOrder->paid_amount += $objPaymentOrder->amount;
                        $arrExtraSaveObjects[] = $objVehicleOrder;
                        
                        $objPaidDetail = \common\models\Pro_vehicle_order_price_detail::createPaidObjectByPurchaseOrder($objPaymentOrder, $objVehicleOrder);
                        $arrExtraSaveObjects[] = $objPaidDetail;
                    }
                }
                else {
                    \Yii::error("process payment while could not find vehicle rental relet order:{$arrData['serial']}", 'order');
                    $arrResult[1] = 'Invalid out_trade_no';
                    $objPaymentOrder->bind_id = 0;
                    break;
                }
            }
            
            /*if ($objUserInfo) {
                foreach ($arrExtraSaveObjects as $obj) {
                    if ($obj instanceof \common\models\Pro_vehicle_order_price_detail) {
                        if (floatval($obj->price_rent)) {
                            $integralLog = UserModule::onUserConsumeByRent($objUserInfo, $obj->price_rent, false);
                            if ($integralLog) {
                                $arrExtraSaveObjects[] = $integralLog;
                            }
                        }
                    }
                }
                
                $objUserInfo->onConsumeAmount($objPaymentOrder->amount);
                $arrExtraSaveObjects[] = $objUserInfo;
            }*/
            
            // already check if the order has the serial inside.
            $objPaymentOrder->setSerialNo();
            
            if ($objPaymentOrder->save()) {
                foreach ($arrExtraSaveObjects as $obj) {
                    $obj->save();
                }
            }
            
            $arrResult[0] = true;
            $arrResult[1] = 'OK';
            
            \Yii::info("purchase service do purchase process trade_no:{$channelTradeNo} succeed, purchase_no:{$objPaymentOrder->serial}.", 'order');
        }while (0);
        
        return $arrResult;
    }
    
}
