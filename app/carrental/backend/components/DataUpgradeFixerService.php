<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\components;

/**
 * Description of DataUpgradeFixerService
 *
 * @author kevin
 */
class DataUpgradeFixerService {
    
    public static function fixPurchaseOrder($arrSerials) {
        foreach ($arrSerials as $serial => $dat) {
            $cdb = \common\models\Pro_vehicle_order::find();
            $cdb->where(['serial'=>$serial]);
            $objOrder = $cdb->one();
            if ($objOrder) {
                $subType = (isset($dat['sub_type']) ? $dat['sub_type'] : \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT_RENEWAL);
                $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrder($objOrder, $dat['amount'], $objOrder->belong_office_id, $subType, $dat['tim']);
                if ($objPurchaseOrder) {
                    $objPurchaseOrder->purchased_at = $dat['tim'];
                    $objPurchaseOrder->edit_user_id = $objOrder->edit_user_id;
                    $objPurchaseOrder->save();
                }
            }
            else {
                $text = "order({$serial}) not exists!";
                echo \yii\helpers\Html::tag('div', $text, ['class'=>'alert alert-danger']);
                \Yii::error(" ---------------- {$text}");
            }
        }
    }
    
    public static function fixPaymentDetail($isFixLostPurchase = true, $isSkipDeposit = true, $isSave = false) {
        $logs = [];
        
        $cdb0 = \common\models\Pro_vehicle_order::find();
        $cdb0->where(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
        $arrAllOrderObjects = $cdb0->all();
        
        $officeIds = [];
        foreach ($arrAllOrderObjects as $row) {
            if (!isset($officeIds[$row->belong_office_id])) {
                $officeIds[$row->belong_office_id] = 1;
            }
        }
        
        $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($officeIds));
        
        $priceKeys = \common\models\Pro_vehicle_order_price_detail::getPriceKeys();
        $depositKeys = ['price_deposit_violation', 'price_deposit'];

        foreach ($arrAllOrderObjects as $objOrder) {
            $cdb1 = \common\models\Pro_vehicle_order_change_log::find();
            $cdb1->where(['serial' => $objOrder->serial]);
            $cdb1->orderBy("created_at ASC");
            $arrChangeLogs = $cdb1->all();
            
            $cdb2 = \common\models\Pro_purchase_order::find();
            $cdb2->where(['bind_id'=>$objOrder->id]);
            $cdb2->andWhere(['>=', 'status', \common\models\Pro_purchase_order::STATUS_SUCCEES]);
            $cdb2->orderBy("purchased_at ASC");
            $arrPurchases = $cdb2->all();
            
            $cdb3 = \common\models\Pro_vehicle_order_price_detail::find();
            $cdb3->where(['order_id'=>$objOrder->id]);
            $cdb3->andWhere(['>=', 'status', \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL]);
            $arrDetailsRows = $cdb3->all();
            
            $totalAmount = $objOrder->total_amount;
            //$totalPaid = $objOrder->paid_amount;
            $depositAmount = $objOrder->getTotalDepositPrice();
            //$depositPaid = $objOrder->paid_deposit;
            
            $objDetailNeedPay = new \common\models\Pro_vehicle_order_price_detail();
            $objDetailTotalPaid = new \common\models\Pro_vehicle_order_price_detail();
            $arrDetailPaids = [];
            $arrDetailNeedPays = [];
            
            $arrSavingDetails = [];
            
            if (empty($arrChangeLogs) || $arrChangeLogs[0]->created_at > $objOrder->created_at + 1) {
                $tmpOdr = empty($arrChangeLogs) ? $objOrder : $arrChangeLogs[0];
                $log = new \common\models\Pro_vehicle_order_change_log();
                $log->load($tmpOdr);
                $log->beforeSave(true);
                $log->created_at = $objOrder->created_at;
                $log->edit_user_id = $tmpOdr->edit_user_id;
                //$log->save();
                array_splice($arrChangeLogs, 0, 0, [$log]);
            }
            
            foreach ($arrDetailsRows as $row) {
                if ($row->type == \common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY) {
                    $arrDetailNeedPays[] = $row;
                    $obj = $objDetailNeedPay;
                }
                elseif($row->type == \common\models\Pro_vehicle_order_price_detail::TYPE_PAID) {
                    $arrDetailPaids[] = $row;
                    $obj = $objDetailTotalPaid;
                }
                
                foreach ($priceKeys as $k) {
                    $v = floatval($obj->$k) + floatval($row->$k);
                    $obj->$k = $v;
                }
            }
            $objDetailNeedPay->summary();
            $objDetailTotalPaid->summary();
            
            if ($totalAmount > $objDetailNeedPay->summary_amount || $depositAmount > $objDetailNeedPay->summary_deposit) {
                $lastTotalAmount = 0;
                $lastTotalDeposit = 0;
                $oLastLog = NULL;
                foreach ($arrChangeLogs as $row) {
                    if ($lastTotalAmount != $row->total_amount || $lastTotalDeposit != $row->getTotalDepositPrice()) {
                        // find the exists log
                        $oDetail = NULL;
                        foreach ($arrDetailNeedPays as $o) {
                            if ($o->time >= $row->created_at && $o->time < $row->created_at+2) {
                                $oDetail= $o;
                                break;
                            }
                        }
                        
                        $isUpdate = false;
                        if ($oDetail) {
                            if (!$oDetail->summary_amount && $lastTotalAmount != $row->total_amount) {
                                $isUpdate = true;
                            }
                            elseif ($oDetail->summary_deposit && $lastTotalDeposit != $row->getTotalDepositPrice()) {
                                $isUpdate = true;
                            }
                            // TODO
                        }
                        else {
                            $arrPriceDeltas = [];
                            $hasPriceUpdates = false;
                            $arrAttributesOrigin = $oLastLog ? $oLastLog->getAttributes() : [];
                            $arrAttributesNow = $row->getAttributes();
                            foreach ($arrAttributesNow as $k => $v) {
                                if (substr($k, 0, 6) == 'price_') {
                                    $v0 = (isset($arrAttributesOrigin[$k]) ? floatval($arrAttributesOrigin[$k]) : 0);
                                    $v = floatval($v);
                                    if ($v == $v0) {
                                        $arrPriceDeltas[$k] = 0;
                                    }
                                    else {
                                        $arrPriceDeltas[$k] = $v - $v0;
                                        $hasPriceUpdates = true;
                                    }
                                }
                            }
                            
                            if ($hasPriceUpdates) {
                                $oDetail = new \common\models\Pro_vehicle_order_price_detail();
                                $oDetail->load($row, $arrPriceDeltas);
                                $oDetail->time = $row->created_at;
                                $oDetail->type = \common\models\Pro_vehicle_order_price_detail::TYPE_SHOULD_PAY;
                                $oDetail->order_id = $objOrder->id;
                                $oDetail->pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;
                                $oDetail->deposit_pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;
                                $oDetail->autoSerial();
                                $oDetail->edit_user_id = intval($row->edit_user_id);

                                // insert by ordered
                                $_i = 0;
                                $_found = false;
                                foreach ($arrDetailNeedPays as $o) {
                                    if ($o->time > $oDetail->time) {
                                        array_splice($arrDetailNeedPays, $_i, 0, [$oDetail]);
                                        $_found = true;
                                        break;
                                    }
                                    $_i++;
                                }
                                if (!$_found) {
                                    $arrDetailNeedPays[] = $oDetail;
                                }

                                foreach ($priceKeys as $k) {
                                    if ($oDetail->hasAttribute($k)) {
                                        $objDetailNeedPay->$k += floatval($oDetail->$k);
                                    }
                                }
                                $objDetailNeedPay->summary();

                                $arrSavingDetails[$oDetail->serial] = $oDetail;

                                $log = $oDetail->getAttributes();
                                $log['order'] = $objOrder->serial;
                                $log['is_new'] = true;
                                $log['start_time'] = $objOrder->start_time;
                                $log['end_time'] = $objOrder->new_end_time;
                                $log['customer'] = $objOrder->customer_name;
                                $log['office'] = isset($arrOfficeNames[$objOrder->belong_office_id]) ? $arrOfficeNames[$objOrder->belong_office_id] : '';
                                $logs[] = $log;
                            }
                        }
                        
                        $lastTotalAmount = $row->total_amount;
                        $lastTotalDeposit = $row->getTotalDepositPrice();
                    }
                    $oLastLog = $row;
                }
            }
            foreach ($arrPurchases as $oPurchase) {
                if ($isSkipDeposit && $oPurchase->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    continue;
                }
                
                $oDetail = NULL;
                foreach ($arrDetailPaids as $o) {
                    if ($o->time == $oPurchase->purchased_at) {
                        $oDetail= $o;
                        break;
                    }
                }
                
                $isCreated = false;
                if ($oDetail) {
                    if ($oPurchase->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                        if ($oDetail->summary_deposit) {
                            continue;
                        }
                    }
                    else {
                        if ($oDetail->summary_amount) {
                            continue;
                        }
                    }
                }
                else {
                    $oDetail = new \common\models\Pro_vehicle_order_price_detail();
                    $oDetail->order_id = $objOrder->id;
                    $oDetail->type = \common\models\Pro_vehicle_order_price_detail::TYPE_PAID;
                    $oDetail->belong_office_id = $objOrder->belong_office_id;
                    $oDetail->status = \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL;
                    $oDetail->pay_source = $oPurchase->pay_source;
                    $oDetail->time = $oPurchase->purchased_at;
                    $oDetail->edit_user_id = intval($oPurchase->edit_user_id);
                    $oDetail->autoSerial();
                    $oDetail->summary_amount = 0;
                    $oDetail->summary_deposit = 0;
                    
                    $isCreated = true;
                    
                    foreach ($priceKeys as $k) {
                        $oDetail->$k = 0;
                    }

                    // insert by ordered
                    $_i = 0;
                    $_found = false;
                    foreach ($arrDetailPaids as $o) {
                        if ($o->time > $oDetail->time) {
                            array_splice($arrDetailPaids, $_i, 0, [$oDetail]);
                            $_found = true;
                            break;
                        }
                        $_i++;
                    }
                    if (!$_found) {
                        $arrDetailPaids[] = $oDetail;
                    }
                }
                
                // find nearest order log
                $oLastLog = NULL;
                foreach ($arrChangeLogs as $o) {
                    if ($o->created_at > $oDetail->time + 2) {
                        break;
                    }
                    $oLastLog = $o;
                }
                if (empty($oLastLog)) {
                    $oLastLog = $objOrder;
                }
                
                // calculate prices
                $leftamount = floatval($oPurchase->amount);
                if ($oPurchase->sub_type == \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_DEPOSIT) {
                    foreach ($depositKeys as $k) {
                        if ($oDetail->hasAttribute($k)) {
                            $n = floatval($oLastLog->$k) - floatval($objDetailTotalPaid->$k);
                            if ($n > 0) {
                                if ($leftamount < $n) {
                                    $n = $leftamount;
                                }
                                $leftamount -= $n;
                                $oDetail->$k += $n;
                                $objDetailTotalPaid->$k += $n;
                            }
                        }
                    }
                    
                    if ($leftamount > 0) {
                        $oDetail->price_deposit += $leftamount;
                        $objDetailTotalPaid->price_deposit += $leftamount;
                    }
                    $oDetail->deposit_pay_source = $oPurchase->pay_source;
                    if ($oDetail->deposit_pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_NONE && $objOrder->deposit_pay_source != \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                        $oDetail->deposit_pay_source = $objOrder->deposit_pay_source;
                    }
                }
                else {
                    foreach ($priceKeys as $k) {
                        if ($leftamount <= 0) {
                            break;
                        }
                        if ($k == 'price_rent') {
                            $oDetail->$k += $leftamount;
                            $objDetailTotalPaid->$k += $leftamount;
                            $leftamount = 0;
                            break;
                        }
                        if (substr($k, 0, 13) == 'price_deposit') {
                            continue;
                        }
                        $n = floatval($oLastLog->$k) - floatval($objDetailTotalPaid->$k);
                        if ($n > 0) {
                            if ($leftamount < $n) {
                                $n = $leftamount;
                            }
                            $leftamount -= $n;
                            $oDetail->$k += $n;
                            $objDetailTotalPaid->$k += $n;
                        }
                    }
                    $oDetail->pay_source = $oPurchase->pay_source;
                    if ($oDetail->pay_source == \common\models\Pro_vehicle_order::PAY_TYPE_NONE && $objOrder->pay_source != \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                        $oDetail->pay_source = $objOrder->pay_source;
                    }
                }
                
                $oDetail->summary();
                $objDetailTotalPaid->summary();
                
                $arrSavingDetails[$oDetail->serial] = $oDetail;
                
                $log = $oDetail->getAttributes();
                $log['order'] = $objOrder->serial;
                $log['start_time'] = $objOrder->start_time;
                $log['end_time'] = $objOrder->new_end_time;
                $log['customer'] = $objOrder->customer_name;
                $log['office'] = isset($arrOfficeNames[$objOrder->belong_office_id]) ? $arrOfficeNames[$objOrder->belong_office_id] : '';
                $log['is_new'] = $isCreated;
                $logs[] = $log;
            }
            
            if ($objOrder->paid_amount > $objDetailTotalPaid->summary_amount) {
                if ($isFixLostPurchase) {
                    $lastPaid = 0;
                    foreach ($arrChangeLogs as $row) {
                        $leftamount = floatval($row->paid_amount) - $lastPaid;
                        if ($leftamount > 0) {
                            $lastPaid = floatval($row->paid_amount);
                            $_found = false;
                            foreach ($arrPurchases as $oPurchase) {
                                if ($oPurchase->amount == $leftamount && ($oPurchase->purchased_at >= $row->created_at-5 && $oPurchase->purchased_at < $row->created_at+5)) {
                                    $_found = true;
                                    break;
                                }
                            }
                            if ($_found) {
                                continue;
                            }


                            $oDetail = new \common\models\Pro_vehicle_order_price_detail();
                            $oDetail->order_id = $objOrder->id;
                            $oDetail->type = \common\models\Pro_vehicle_order_price_detail::TYPE_PAID;
                            $oDetail->belong_office_id = $objOrder->belong_office_id;
                            $oDetail->status = \common\models\Pro_vehicle_order_price_detail::STATUS_NORMAL;
                            $oDetail->pay_source = $row->pay_source;
                            $oDetail->time = $row->created_at;
                            $oDetail->edit_user_id = intval($row->edit_user_id);
                            $oDetail->autoSerial();
                            $oDetail->summary_amount = 0;
                            $oDetail->summary_deposit = 0;

                            $isCreated = true;

                            foreach ($priceKeys as $k) {
                                $oDetail->$k = 0;
                            }
                            foreach ($priceKeys as $k) {
                                if ($leftamount <= 0) {
                                    break;
                                }
                                if ($k == 'price_rent') {
                                    $oDetail->$k += $leftamount;
                                    $objDetailTotalPaid->$k += $leftamount;
                                    $leftamount = 0;
                                    break;
                                }
                                if (substr($k, 0, 13) == 'price_deposit') {
                                    continue;
                                }
                                $n = floatval($row->$k) - floatval($objDetailTotalPaid->$k);
                                if ($n > 0) {
                                    if ($leftamount < $n) {
                                        $n = $leftamount;
                                    }
                                    $leftamount -= $n;
                                    $oDetail->$k += $n;
                                    $objDetailTotalPaid->$k += $n;
                                }
                            }

                            $oDetail->summary();
                            $objDetailTotalPaid->summary();

                            $arrDetailPaids[] = $oDetail;
                            $arrSavingDetails[$oDetail->serial] = $oDetail;

                            $log = $oDetail->getAttributes();
                            $log['order'] = $objOrder->serial;
                            $log['start_time'] = $objOrder->start_time;
                            $log['end_time'] = $objOrder->new_end_time;
                            $log['customer'] = $objOrder->customer_name;
                            $log['office'] = isset($arrOfficeNames[$objOrder->belong_office_id]) ? $arrOfficeNames[$objOrder->belong_office_id] : '';
                            $log['is_new'] = $isCreated;
                            $logs[] = $log;

                            // add purchase order
                            $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleOrder($objOrder, $oDetail->summary_amount, $oDetail->belong_office_id, \common\models\Pro_purchase_order::SUB_TYPE_VEHICLE_ORDER_RENT, $oDetail->time);
                            $objPurchaseOrder->edit_user_id = $row->edit_user_id;
                            $arrSavingDetails[$objPurchaseOrder->serial] = $objPurchaseOrder;
                            $arrPurchases[] = $objPurchaseOrder;
                        }
                    }
                }
            }
            
            if ($isSave) {
                foreach ($arrSavingDetails as $k => $oDetail) {
                    $oDetail->save();
                }
            }
            
            if (!floatval($objOrder->paid_deposit) && $objDetailTotalPaid->summary_deposit) {
                //if ($objOrder->getTotalDepositPrice() < $objDetailTotalPaid->summary_deposit) {
                //    $objOrder->paid_deposit = $objOrder->getTotalDepositPrice();
                //}
                //else {
                    $objOrder->paid_deposit = $objDetailTotalPaid->summary_deposit;
                //}
                if ($isSave) {
                    $objOrder->save();
                }
            }
            
        }
        
        return $logs;
    }
    
    public static function upgradeVehicleFeeplan($isSave) {
        $logs = [];
        $arrSavingData = [];
        
        $arrWeekdayKeys = ['price_sunday', 'price_monday', 'price_tuesday', 'price_wednesday', 'price_thirsday', 'price_friday', 'price_saturday'];
        
        $cdb = \common\models\Pro_vehicle_fee_plan::find();
        $arrRows = $cdb->all();
        
        $arrModelNames = [];
        $arrOfficeNames = [];
        $cdb1 = \common\models\Pro_vehicle_model::find();
        $cdb2 = \common\models\Pro_office::find();
        $rows1 = $cdb1->all();
        $rows2 = $cdb2->all();
        foreach ($rows1 as $row) {
            $arrModelNames[$row->id] = $row->vehicle_model;
        }
        foreach ($rows2 as $row) {
            $arrOfficeNames[$row->id] = $row->shortname;
        }
        
        foreach ($arrRows as $row) {
            if (empty($row->price_default)) {
                if ($row->source == \common\models\Pro_vehicle_fee_plan::DEFAULT_SOURCE) {
                    $row->price_default = $row->price_office;
                    $arrSavingData[] = $row;
                    
                    $o = new \common\models\Pro_vehicle_fee_plan();
                    $attrs = $row->getAttributes();
                    foreach ($attrs as $k => $v) {
                        if ($k != 'id' && $k != 'created_at' && $k != 'updated_at') {
                            $o->$k = $row->$k;
                        }
                    }
                    $o->source = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;
                    $o->price_default = $o->price_online;
                    $d = intval($o->price_office) - intval($o->price_online);
                    foreach ($arrWeekdayKeys as $k) {
                        if (!empty($o->$k)) {
                            $o->$k = $o->$k - $d;
                        }
                    }
                    
                    $arrSavingData[] = $o;
                }
                else {
                    $row->price_default = $row->price_online;
                    $d = intval($row->price_office) - intval($row->price_online);
                    foreach ($arrWeekdayKeys as $k) {
                        if (!empty($row->$k)) {
                            $row->$k = $row->$k - $d;
                        }
                    }
                    
                    $arrSavingData[] = $row;
                }
            }
        }
        
        foreach ($arrSavingData as $o) {
            $_log = $o->getAttributes();
            $_log['model_name'] = (isset($arrModelNames[$o->vehicle_model_id])? $arrModelNames[$o->vehicle_model_id] : '');
            $_log['office_name'] = (isset($arrOfficeNames[$o->office_id]) ? $arrOfficeNames[$o->office_id] : '');
            $logs[] = $_log;
        }
        
        if ($isSave) {
            foreach ($arrSavingData as $o) {
                $o->save();
            }
        }
        
        return $logs;
    }
    
}
