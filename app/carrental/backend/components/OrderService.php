<?php

namespace backend\components;

/**
 * Description of OrderService
 *
 * @author kevin
 */
class OrderService extends BaseService
{
    
    public static function processEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            
            $validActions = ['insert'=>1, 'update'=>1, 'settlement'=>1];
            $authoration = \backend\components\AdminModule::getCurRoleAuthoration();
            $authOfficeId = \backend\components\AdminModule::getAuthorizedOfficeId();
            $nextAction = \Yii::$app->request->getParam('next_action');
            $objOrder = null;
            $curTime = time();
            if (!isset($validActions[$action])) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
            }
            if ($action != 'insert') {
                $orderId = intval(\Yii::$app->request->getParam('id'));
                if (!$orderId) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                $cdb = \common\models\Pro_vehicle_order::find();
                $cdb->where(['id' => $orderId]);
                $objOrder = $cdb->one();
                if (!$objOrder) {
                    return self::errorResult(\Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]));
                }
                
                if ($authoration < \backend\models\Rbac_role::AUTHORITY_OFFICE_MANAGER) {
                    if (!$objOrder->isValidForOfficeId($authOfficeId)) {
                        return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'));
                    }
                }
                
                if ($nextAction == 'dispatch_vehicle') {
                    //if ($objOrder->confirmed_at == 0) {
                    //  return self::errorResult(\Yii::t('carrental', 'The order were not confirmed, you cannot dispatch the vehicle yet.'));
                    //}
                }
            }
            
            \Yii::trace("operation a vehicle order, action:{$action}", 'order');
            $objFormData = new \common\models\Form_pro_vehicle_order();
            $objFormOptionalServices = null;
            if ($action == 'settlement' || ($objOrder && $objOrder->status >= \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING)) {
                $objFormData->isUpdateSettlement = true;
            }
            elseif (!intval(\Yii::$app->request->getParam('optional_service_readonly'))) {
                $objFormOptionalServices = new \backend\models\Form_pro_optional_services();
            }
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                if ($errText) {
                    \Yii::error($errText, 'order');
                }
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            // sjj 驾照判断
            if(!empty($objFormData->customer_driver_license_time) && $objFormData->customer_driver_license_time < 100000){
                $objFormData->customer_driver_license_time = strtotime($objFormData->customer_driver_license_time);
            }
            if(!empty($objFormData->customer_driver_license_expire_time) && $objFormData->customer_driver_license_expire_time < 100000){
                $objFormData->customer_driver_license_expire_time = strtotime($objFormData->customer_driver_license_expire_time);
            }
            // sjj
            
            //车辆租赁登记 预约 sjj
            if($nextAction == 'print_booking' && $action == 'insert'){
                $objFormData->yuyue_time = $objFormData->start_time;
                $objFormData->yuyue_end_time = $objFormData->end_time;
            }
            if(empty($objFormData->yuyue_time)){
                $objFormData->yuyue_time = $objFormData->start_time;
                $objFormData->yuyue_end_time = $objFormData->end_time;
            }
            /*echo "<pre>";
            print_r($objFormData);
            echo "</pre>";die;*/

            // 判断是否三天打包价
            /*得到开始时间和结束时间之差的日时分秒*/
            $timediff = \common\components\CheckModule::timediff($objFormData->start_time,$objFormData->end_time);
            if($timediff['day'] == 3 && $objFormData->rent_days == 3 && $objFormData->pay_type == 2){
                $res = \common\components\CheckModule::is_discount_period($objFormData->start_time,$objFormData->end_time);
                if($res==1){
                    return self::errorResult('周五和周末'.\Yii::t('locale', 'Not in'), 300);
                }
            }


            $endTime = $objFormData->end_time;
            $originEndTime = 0;
            $reletMaxEndTime = 0;
            if ($objOrder) {
                //以下注释代码是判断打包价时间是否比预约时间提早:价格类型：pay_type 订单状态:status
                 /*if (\common\models\Pro_vehicle_order::isMultidaysPackagePriceType($objFormData->pay_type) && $endTime < $objOrder->new_end_time && $objFormData->status <= \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    return self::errorResult(\Yii::t('carrental', 'You can not reduce the rent time for multidays package price type order.'));
                }*/
                $reletMaxEndTime = \common\components\OrderModule::getOrderReletMaxEndTime($objOrder->id);

                $originEndTime = $objOrder->new_end_time;
                if ($action == 'settlement') {
                    $endTime = $objFormData->car_returned_at;
                    //$objFormData->end_time = $endTime;
                }
                elseif ($reletMaxEndTime > 0 && ($endTime != $originEndTime && $endTime != $reletMaxEndTime)) {
                    return self::errorResult(\Yii::t('carrental', 'The order had already releted, please do not change the order time.'));
                }
            }
            $validateRentDaysResult = \common\components\OrderModule::validateRentDays($objFormData->rent_days, $objFormData->start_time, $endTime,$objFormData->pay_type);
            if ((!$objOrder || ($action != 'settlement' && $objOrder->status <= \common\models\Pro_vehicle_order::STATUS_RENTING)) && $validateRentDaysResult['result'] != 0) {
                return self::errorResult($validateRentDaysResult['desc']);
            }
            
            $objVehicle = \common\models\Pro_vehicle::findById($objFormData->vehicle_id);
            if (!$objVehicle) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle does not exists.'));
            }
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
            if (!$objVehicleModel) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle does not exists.').'1');
            }
            if ($objVehicle->model_id != $objFormData->vehicle_model_id) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle model does not match.'));
            }
            if ($objVehicle->status != \common\models\Pro_vehicle::STATUS_NORMAL) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle is not valid for rent.'));
            }
            
            if (empty($objFormData->belong_office_id)) {
                //if ($action == 'insert' || empty($objOrder->vehicle_id)) {
                //    $objFormData->belong_office_id = \backend\components\AdminModule::getAdminActualOfficeId();
                //}
                if (!$objOrder || empty($objOrder->belong_office_id)) {
                    if ($authOfficeId > 0) {
                        $objFormData->belong_office_id = $authOfficeId;
                    }
                    else {
                        $objFormData->belong_office_id = $objVehicle->belong_office_id;
                    }
                }
                else {
                    $objFormData->belong_office_id = $objOrder->belong_office_id;
                }
            }
            
            if ($objFormOptionalServices) {
                $objFormOptionalServices->belongOfficeId = $objFormData->belong_office_id;
                if (!$objFormOptionalServices->load(\Yii::$app->request->post())) {
                    $errText = $objFormOptionalServices->getErrorAsHtml();
                    if ($errText) {
                        \Yii::error($errText, 'order');
                    }
                    return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
                }
            }
            
            $verifyResult = \common\components\UserModule::validateIdentity($objFormData->customer_id_type, $objFormData->customer_id);
            if ($verifyResult[0] < 0) {
                return self::errorResult($verifyResult[1]);
            }
            
            if (!\common\helpers\Utils::validatePhoneno($objFormData->customer_telephone)) {
                return self::errorResult(\Yii::t('locale', 'Telephone No. not valid'), 300);
            }
            if (!empty($objFormData->customer_fixedphone)) {
                if (!\common\helpers\Utils::validatePhoneno($objFormData->customer_fixedphone)) {
                    return self::errorResult(\Yii::t('locale', 'Telephone No. not valid'), 300);
                }
            }
            
            $checkVehicleId = $objFormData->vehicle_id;
            $checkSkipOrderId = 0;
            if ($objOrder) {
                $checkSkipOrderId = $objOrder->id;
            }
            if (!$objOrder 
                || $objOrder->status < \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING) {
                if (\common\components\OrderModule::hasVehicleRented($checkVehicleId, $objFormData->start_time, $objFormData->end_time, 0, $checkSkipOrderId)) {
                    return self::errorResult(\Yii::t('carrental', 'Sorry, the vehicle already rented at your rent time!'), 300);
                }
            }
            
			
			
            if ($authOfficeId >= 0) {
                if ($objOrder) {
                    if (!$objOrder->isValidForOfficeId($authOfficeId)) {
                        return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
                    }
                }else{
					if($authOfficeId != $objFormData->office_id_rent){
						$isOffice = \common\models\Pro_office::find()->where(['parent_id'=>$authOfficeId,'id'=>$objFormData->office_id_rent])->count();
						if(empty($isOffice) || $isOffice == 0){
							return self::errorResult(\Yii::t('locale', 'Sorry, no operating privileges for current user!'), 300);
						}
					}
                }
            }
            
            $customerBirthday = null;
            $objUserInfo = null;
            if (empty($objFormData->user_id)) {
                $objUserInfo = \common\components\OrderModule::getUserInfoByOrderFormData($objFormData);
                if ($objUserInfo) {
                    $objFormData->user_id = $objUserInfo->id;
                    $customerBirthday = $objUserInfo->getBirthday();
                }
            }
            else {
                $objUserInfo = \common\models\Pub_user_info::findById($objFormData->user_id);
                if (!$objUserInfo) {
                    return self::errorResult(\Yii::t('carrental', 'Customer ID not exists'));
                }
                else {
                    $customerBirthday = $objUserInfo->getBirthday();
                }
            }
            
            // sjj 判断新老用户
            $userId = $objFormData->user_id;
                $re = \common\models\Pro_vehicle_order::CheckCustomerIsNew($userId);
                if($re > 0){
                    $userisnew = 0;//老用户
                }else{
                    $userisnew = 1;//新用户
                }
                // sjj
            $arrPriceData = null;
            $verifyPriceData = false;
            if ($action == 'settlement') {
                $arrPriceData = \common\components\OrderModule::calculateVehicleModelRentPriceData($objFormData->vehicle_model_id, $objFormData->start_time, $objFormData->car_returned_at, $objFormData->belong_office_id, $objFormData->source, $objFormData->pay_type, $customerBirthday,$userisnew);
            }
            else {
                $arrPriceData = \common\components\OrderModule::calculateVehicleModelRentPriceData($objFormData->vehicle_model_id, $objFormData->start_time, $objFormData->end_time, $objFormData->belong_office_id, $objFormData->source, $objFormData->pay_type, $customerBirthday,$userisnew);
                if ($action == 'insert') {
                    $verifyPriceData = true;
                }
            }
            if ($verifyPriceData && (!$arrPriceData || $arrPriceData['price'] != $objFormData->price_rent)) {
                //$verifyPriceData true
                return self::errorResult(\Yii::t('carrental', $arrPriceData['price']), 300);
                // return self::errorResult(\Yii::t('carrental', 'Vehicle rent price not valid!'), 300);
            }
            /*echo "<pre>";
            print_r($arrPriceData);
            echo "</pre>";die;*/
            if ($arrPriceData['hasFestivalPrice']) {
                $festival = $arrPriceData['festival'];
                if ($festival && $festival->alldays_required) {
                    if (!$festival->isValidRentTime($objFormData->start_time, $objFormData->end_time)) {
                        \Yii::error("user:{$objFormData->user_id} rent car by model:{$objFormData->vehicle_model_id} while rent time appears in festival time region and festival:{$festival->id} name:{$festival->name} requires all festival days to be rented.", 'order');
                        $errText = \Yii::t('carrental', 'When rent car in {name}, you should rent car for all days between {start} and {end}.', ['name'=>$festival->name, 'start'=>date('Y-m-d', $festival->start_time), 'end'=>date('Y-m-d', $festival->end_time)]);
                        return self::errorResult($errText);
                    }
                }
                if (\common\models\Pro_vehicle_order::isMultidaysPackagePriceType($objFormData->pay_type) && $objFormData->rent_days < \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
                    return self::errorResult('节日期间无法使用打包价格。');
                }
            }
            
            if ($nextAction == 'dispatch_vehicle') {
                if (empty($objFormData->car_dispatched_at)) {
                    $objFormData->car_dispatched_at = $curTime;
                }
                //sjj
                if(!$objFormData->id){
                    if(empty($objFormData->yuyue_time)){
                        $objFormData->yuyue_time = $objFormData->start_time;
                        // $objFormData->yuyue_time = $objFormData->car_dispatched_at;
                        $objFormData->yuyue_end_time = $objFormData->car_dispatched_at;
                    }
                }
                //sjj
            }
            if ($objFormData->car_dispatched_at) {
                // force change order start time if car dispatched time not equals start time to make sure the price is correct.
                $rentTimeData1 = \common\models\Pri_renttime_data::create($objFormData->start_time, $objFormData->end_time,$objFormData->pay_type);
                $rentTimeData2 = \common\models\Pri_renttime_data::create($objFormData->car_dispatched_at, $objFormData->end_time,$objFormData->pay_type);
                if ($rentTimeData1->calcStartTime != $rentTimeData2->calcStartTime 
                    || $rentTimeData1->days != $rentTimeData2->days || $rentTimeData1->hours != $rentTimeData2->hours) {
                    \Yii::warning("The order {$objFormData->id} start_time:{$objFormData->start_time} not match car_dispatched_at:{$objFormData->car_dispatched_at}, force change order start_time to car_dispatched_at time.", 'orders');
                    if(!$objFormData->id){
                        $objFormData->start_time = $objFormData->car_dispatched_at;
                    //sjj
                        // $objFormData->end_time = $objFormData->car_dispatched_at;
                    }
                    //sjj
                }
            }
            
            if (empty($objFormData->user_id)) {
                $userResult = \common\components\OrderModule::instanceUserInfoByOrderFormData($objFormData);
                if ($userResult['result'] != 0) {
                    return self::errorResult($userResult['desc']);
                }
                $objUserInfo = $userResult['userInfo'];
                $objFormData->user_id = $objUserInfo->id;
            }
            
            $userRentingCount = \common\components\OrderModule::getUserRentingCarCount($objFormData->user_id, $objFormData->start_time, $objFormData->end_time, $objFormData->id);
            if ($userRentingCount >= $objUserInfo->max_renting_cars) {
                if ($action != 'settlement' && (!$objOrder || $objOrder->status < \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING)) {
                    \Yii::error("edit order by user_id:{$objFormData->user_id} start_time:{$objFormData->start_time} end_time:{$objFormData->end_time} vehicle_model_id:{$objFormData->vehicle_model_id} vehicle_id:{$objFormData->vehicle_id} that there are {$userRentingCount} orders renting this time.", 'order');
                    return self::errorResult(\Yii::t('carrental', 'Sorry, the customer already have {number} order this time.', ['number'=>$userRentingCount]));
                }
            }
            
            // calculate other service prices
            // 
            // calculate the service price between difficrent office.
            // calculate the diffirent take car and return car price.
            $arrCalcResult = $objFormData->calculateCarDeliveryServicePrice();
            if ($arrCalcResult[0] != 0) {
                return self::errorResult($arrCalcResult[1]);
            }
            
            $originStartTime = 0;
            $originPaid = 0;
            $originDeposit = 0;
            $originOrderStatus = 0;
            
            if ($objOrder) {
                $originStartTime = $objOrder->start_time;
                $originPaid = $objOrder->paid_amount;
                if ($objOrder->deposit_pay_source != \common\models\Pro_vehicle_order::PAY_TYPE_NONE) {
                    $originDeposit = $objOrder->price_deposit_violation + $objOrder->price_deposit;
                }
                $originOrderStatus = $objOrder->status;
            }
            
            $isPrematureReturnCar = false;
			// print_r($action);exit;
            if ($action == 'insert') {
                $objOrder = new \common\models\Pro_vehicle_order();
                $objFormData->save($objOrder);
                $objOrder->setSerialNo();
                $objOrder->onUpdateEndTime($objOrder->new_end_time);
            }
            else if ($action == 'update') {
                if ($reletMaxEndTime > 0 && ($objFormData->end_time != $originEndTime && $objFormData->end_time != $reletMaxEndTime)) {
                    return self::errorResult(\Yii::t('carrental', 'Sorry, the order already releted, please do not change the end time now!'), 300);
                }
                $originVehicleId = $objOrder->origin_vehicle_id;
                if (!$originVehicleId && $objOrder->status == \common\models\Pro_vehicle_order::STATUS_RENTING
                    && $objFormData->vehicle_id != $objOrder->vehicle_id) {
                    $originVehicleId = $objOrder->vehicle_id;
                    $objOrder->origin_vehicle_id = $originVehicleId;
                }
				
                $objFormData->save($objOrder);
                if ($reletMaxEndTime == 0) {
                    $objOrder->new_end_time = $objOrder->end_time;
                }
               // $objOrder->onUpdateEndTime($objOrder->new_end_time);
            }
            else if ($action == 'settlement') {
                // there will recalculate the price data by onUpdateEndTime, so do not copy the new price_rent field.
                $objFormData->price_rent = $objOrder->price_rent;
				// print_r($objFormData);exit;
				
                $objFormData->save($objOrder);
                if ($objOrder->settlemented_at == 0) {
                    $objOrder->settlemented_at = $curTime;
                }
				
                $objOrder->settlement_user_id = \Yii::$app->user->id;
				if($objOrder->pay_type == 6){
					$objOrder->onOneWayUpdateEndTime($objFormData->car_returned_at);
				}else{
					$objOrder->onUpdateEndTime($objOrder->car_returned_at);
				}

                // 结算金额判断
                $settlement_left_amount = $objOrder->total_amount - $objOrder->paid_amount;
                if($objOrder->settlement_status == 1 && $settlement_left_amount > 0){
                    return self::errorResult(\Yii::t('locale', 'Sorry this order is not pay'), 300);
                }
                /*echo "<pre>";
                print_r($objOrder);
                echo "</pre>";
                exit();*/
                // echo 1;exit;
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'), 300);
            }
            
            if ($objFormOptionalServices) {
                if (true) {
                    $objOrder->setOptionalServices($objFormOptionalServices->serviceObjectsArray);
                }
                elseif ($objOrder->status <= \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    // 如果订单结算，比如存在提前还车情况，增值服务项不退还。
                    if (!$isPrematureReturnCar) {
                        $objOrder->setOptionalServices($objFormOptionalServices->serviceObjectsArray);
                    }
                }
            }
            
            $objOrder->getDailyRentDetailedPriceArray();
			if($objOrder->pay_type == 6){
				$objOrder->calculateOneWayTotalPrice();
			}else{
				$objOrder->calculateTotalPrice();
			}
           
            
            if ($nextAction == 'dispatch_vehicle') {
                // directly convert to rented.
                //$objOrder->confirmed_at = $curTime;
                if (!$objOrder->confirmed_at) {
                    $objOrder->confirmed_at = $curTime;
                }
                if (intval($objOrder->status) < \common\models\Pro_vehicle_order::STATUS_RENTING) {
                    $objOrder->status = \common\models\Pro_vehicle_order::STATUS_RENTING;
                }
            }
            elseif ($nextAction == 'settlement' || $action == 'settlement') {
                //$objOrder->car_returned_at = $curTime;
                $objOrder->status = \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING;
            }
           
            if ($objOrder->save()) {
                $isSaveVehicle = false;
                if ($objOrder->vehicle_inbound_mileage > $objVehicle->cur_kilometers) {
                    $objVehicle->cur_kilometers = $objOrder->vehicle_inbound_mileage;
                    $isSaveVehicle = true;
                }
                if ($objOrder->vehicle_outbound_mileage > $objVehicle->cur_kilometers) {
                    $objVehicle->cur_kilometers = $objOrder->vehicle_outbound_mileage;
                    $isSaveVehicle = true;
                }
                if ($action == 'settlement' && $objOrder->office_id_return != $objVehicle->stop_office_id) {
                    if (\common\models\Pro_office::findById($objOrder->office_id_return)) {
                        $objVehicle->stop_office_id = $objOrder->office_id_return;
                        $isSaveVehicle = true;
                    }
                }
                if ($isSaveVehicle) {
                    $objVehicle->save();
                }
                
				$shopaddress = \common\models\Pro_office::find()->select(['shortname','shopowner_tel'])->where(['id'=>$objFormData->office_id_rent])->asArray()->one();
				$vehicle_model = \common\models\Pro_vehicle_model::find()->select(['vehicle_model'])->where(['id'=>$objFormData->vehicle_model_id])->asArray()->one();
				
                /*if ($action == 'insert') {
					//发送客户短信
					\common\components\SmsComponent::send(
						$objOrder->customer_telephone, 
						\common\components\Consts::KEY_SMS_ORDER_BOOKED_PAID, 
						[
							'name'=>$objOrder->customer_name,
							'usetime'=>date('Y-m-d H:i',$objOrder->start_time),
							'automodel'=>$vehicle_model['vehicle_model'],
							'shopaddress'=>$shopaddress['shortname'],
						]
					);
					if(!empty($shopaddress['shopowner_tel'])){
						//发送门店短信
						\common\components\SmsComponent::send(
							$shopaddress['shopowner_tel'], 
							\common\components\Consts::KEY_SMS_ORDER_STORE, 
							[
								'usetime'=>date('Y-m-d H:i',$objOrder->start_time),
								'automodel'=>$vehicle_model['vehicle_model'],
								'shopaddress'=>$shopaddress['shortname'],
							]
						);
					}
                }
                elseif ($action == 'update') {
                    if ($originStartTime && $originStartTime != $objOrder->start_time) {
                        //出车短信
						\common\components\SmsComponent::send(
							$objOrder->customer_telephone, 
							\common\components\Consts::KEY_SMS_ORDER_CHANGED, 
							[
								'name'=>$objOrder->customer_name,
								'orderid'=>$objOrder->serial,
								'automodel'=>$vehicle_model['vehicle_model'],
								'usetime'=>date('Y-m-d H:i',$objOrder->end_time),
							]
						);
                    }
                }
                
                if (($action == 'dispatch_vehicle' && $nextAction != 'paymentinput') || $nextAction == 'dispatch_vehicle') {
                   //出车短信
					\common\components\SmsComponent::send(
						$objOrder->customer_telephone, 
						\common\components\Consts::KEY_SMS_ORDER_CAR, 
						[
							'name'=>$objOrder->customer_name,
							'usetime'=>date('Y-m-d H:i',$objOrder->end_time),
						]
					);
                }
                else if ($action == 'settlement' && $nextAction != 'paymentinput') {
                    //结算短信
					\common\components\SmsComponent::send(
						$objOrder->customer_telephone, 
						\common\components\Consts::KEY_SMS_ORDER_SETTLEMENTED, 
						[
							'name'=>$objOrder->customer_name,
							'pricestandard'=>$objOrder->total_amount,
							'day'=>10,
						]
					);
                }*/
				 
                if ($action == 'insert') {
                    $objOffice = \common\models\Pro_office::findById($objOrder->office_id_rent);
                    \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_OFFICE, [
                        'CNAME'=>$objOrder->customer_name, 
                        'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                        'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                        'SHOPADDRESS'=>$objOrder->getTakeCarAddressText(),
                        'SHOPTELEPHONE'=>$objOffice ? $objOffice->telephone : '',
                        'ORDERID'=>$objOrder->serial,
                    ]);
                }
                elseif ($action == 'update') {
                    if ($originStartTime && $originStartTime != $objOrder->start_time) {
                        \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_CHANGED, [
                            'CNAME'=>$objOrder->customer_name, 
                            'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                            'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                            'ORDERID'=>$objOrder->serial,
                        ]);
                    }
                }
                
                if (($action == 'dispatch_vehicle' && $nextAction != 'paymentinput') || $nextAction == 'dispatch_vehicle') {
                    //$forwardUrl = \yii\helpers\Url::to(['vehicle/validation', 'purpose'=>'vehicle_dispatch', 'vehicle_id'=>$objOrder->vehicle_id, 'order_id'=>$objOrder->id]);
                    \Yii::info("save order:{$objOrder->serial} with order_id:{$objOrder->id} while dispatch vehicle.", 'order');
                    \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_USER_TAKEN_CAR0, [
                        'CNAME'=>$objOrder->customer_name, 
                        'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                        'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                        'BACKTIME'=>  date('Y-m-d H:i', $objOrder->new_end_time),
                        'DAYS'=>$objOrder->rent_days,
                        'CITY'=>$objOrder->getTakeCarCityText(),
                        'SHOPNAME'=>$objOrder->getTakeCarOfficeText(),
                        'PRICESTANDARD'=>$objOrder->total_amount,
                        'ORDERID'=>$objOrder->serial,
                    ]);
                    $objOffice = \common\models\Pro_office::findById($objOrder->office_id_return);
                    \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_USER_TAKEN_CAR1, [
                        'CNAME'=>$objOrder->customer_name, 
                        //'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                        //'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                        'BACKTIME'=>  date('Y-m-d H:i', $objOrder->new_end_time),
                        //'DAYS'=>$objOrder->rent_days,
                        //'CITY'=>$objOrder->getTakeCarCityText(),
                        'SHOPNAME'=>$objOrder->getTakeCarOfficeText(),
                        'SHOPADDRESS'=>$objOffice ? $objOffice->address : $objOrder->getReturnCarAddressText(),
                        'SHOPTELEPHONE'=>$objOffice ? $objOffice->telephone : '',
                        //'PRICESTANDARD'=>$objOrder->total_amount,
                        'ORDERID'=>$objOrder->serial,
                    ]);
                }
                else if ($action == 'settlement' && $nextAction != 'paymentinput') {
                    //$forwardUrl = \yii\helpers\Url::to(['vehicle/validation', 'purpose'=>'vehicle_validation', 'vehicle_id'=>$objOrder->vehicle_id, 'order_id'=>$objOrder->id]);
                    \Yii::info("save order:{$objOrder->serial} with order_id:{$objOrder->id} while settlement order.", 'order');
                    \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_SETTLEMENTED, [
                        'CNAME'=>$objOrder->customer_name, 
                        'PRICESTANDARD'=>$objOrder->total_amount,
                        'ORDERID'=>$objOrder->serial,
                    ]);
                } 
                
                if ($objUserInfo) {
                    if ($originOrderStatus < \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING
                        && ($objOrder->status >= \common\models\Pro_vehicle_order::STATUS_VIOLATION_CHECKING
                            && $objOrder->status <= \common\models\Pro_vehicle_order::STATUS_COMPLETED)) {
                        $objUserInfo->onConsumeAmount($objOrder->paid_amount);
                        \common\components\UserModule::onUserConsumeByRent($objUserInfo, $objOrder->paid_amount, true);
                    }
                    if (!empty($objUserInfo->invited_code)) {
                        if ($originOrderStatus < \common\models\Pro_vehicle_order::STATUS_RENTING
                            && $objOrder->status >= \common\models\Pro_vehicle_order::STATUS_RENTING
                            && $objOrder->status <= \common\models\Pro_vehicle_order::STATUS_COMPLETED) {
                            \common\components\UserModule::onInvitedUserRentcar($objUserInfo);
                        }
                    }
                }
                
                $arrResult[0] = Consts::CODE_OK;
                $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
                $arrResult['attributes'] = ['orderId'=>$objOrder->id];
                if ($nextAction == 'paymentinput') {
                    $url = \yii\helpers\Url::to(['order/edit', 'id'=>$objOrder->id]);
                    $arrResult['callbackType'] = 'forward';
                    $arrResult['forwardUrl'] = $url;
                    break;
                }
                $arrResult['callbackType'] = 'closeNavTab';
            } else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
        }while(0);
        return $arrResult;
    }
    
    public static function processReletEdit()
    {
        $arrResult = [Consts::CODE_NOACTION, ''];
        do
        {
            $action = \Yii::$app->request->getParam('action');
            if (empty($action)) {
                break;
            }
            \Yii::trace("operation a vehicle order, action:{$action}", 'order');
            
            $objFormData = new \backend\models\Form_pro_vehicle_order_relet();
            if (!$objFormData->load(\Yii::$app->request->post())) {
                $errText = $objFormData->getErrorAsHtml();
                if ($errText) {
                    \Yii::error($errText, 'order');
                }
                return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
            }
            
            $orderId = intval(\Yii::$app->request->getParam('order_id'));
            if (!$orderId) {
                return self::errorResult(\Yii::t('locale', '{name} should not be empty!', ['name'=>'Main order']));
            }
            $objOrder = \common\models\Pro_vehicle_order::findById($orderId);
            if (!$objOrder) {
                return self::errorResult(\Yii::t('carrental', 'The main order does not exists.'));
            }
            
            if (\common\components\OrderModule::hasVehicleRented($objOrder->vehicle_id, $objFormData->origion_end_time, $objFormData->new_end_time, 0/*$objOrder->user_id*/)) {
                return self::errorResult(\Yii::t('locale', 'Sorry, vehicle already rented!'));
            }
            
            $objVehicle = \common\models\Pro_vehicle::findById($objOrder->vehicle_id);
            if (!$objVehicle) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle does not exists.'));
            }
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objVehicle->model_id);
            if (!$objVehicleModel) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle does not exists.'));
            }
            if ($objVehicle->status != \common\models\Pro_vehicle::STATUS_NORMAL) {
                return self::errorResult(\Yii::t('carrental', 'The vehicle is not valid for rent.'));
            }
            
            $curTime = time();
            $objReletOrder = null;
            $origionPaid = 0;
            if ($action == 'insert') {               
                $objReletOrder = new \common\models\Pro_vehicle_order_relet();
                if (!$objFormData->save($objReletOrder)) {
                    $errText = $objFormData->getErrorAsHtml();
                    return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
                }
                $objReletOrder->order_id = $objOrder->id;
                $objReletOrder->setSerialNo();
            }
            else if ($action == 'update') {
                $objReletOrder = \common\models\Pro_vehicle_order_relet::findById($objFormData->id);
                if (!$objReletOrder) {
                    return self::errorResult(\Yii::t('locale', 'ID should not be empty!'));
                }
                $origionPaid = $objReletOrder->paid_amount;
                if (!$objFormData->save($objReletOrder)) {
                    $errText = $objFormData->getErrorAsHtml();
                    return self::errorResult((empty($errText) ? \Yii::t('locale', 'Sorry, the operation failed!') : $errText));
                }
            }
            else {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation failed!'));
            }
            
            if ($objReletOrder->origion_end_time != $objOrder->new_end_time) {
                return self::errorResult(\Yii::t('carrental', 'Relet origion return car time not valid!'));
            }
            
            if (!$objReletOrder->save()) {
                return self::errorResult(\Yii::t('locale', 'Sorry, the operation fails, please re-submit!'));
            }
            /*$deltaAmount = $objReletOrder->paid_amount - $origionPaid;
            if ($deltaAmount > 0) {
                $officeId = \backend\components\AdminModule::getAuthorizedOfficeId();
                if ($officeId <= 0) {
                    $officeId = $objOrder->belong_office_id;
                }
                $objPurchaseOrder = \common\models\Pro_purchase_order::createWithVehicleRelet($objOrder, $objReletOrder, $deltaAmount, $officeId);
                $objPurchaseOrder->save();
            }*/

            if ($objReletOrder->new_end_time > $objOrder->new_end_time) {
                $objOrder->onUpdateEndTime($objReletOrder->new_end_time);

                /*if ($deltaAmount > 0) {
                    $objOrder->paid_amount += $deltaAmount;
                }*/
                $objOrder->save();
				//发送客户短信
				\common\components\SmsComponent::send(
					$objOrder->customer_telephone, 
					\common\components\Consts::KEY_SMS_ORDER_RENEWAL, 
					[
						'name'=>$objOrder->customer_name,
						'usetime'=>date('Y-m-d H:i',$objOrder->new_end_time),
					]
				);
            }
            
            $arrResult[0] = Consts::CODE_OK;
            $arrResult[1] = \Yii::t('locale', 'Congratulations, successful operation!');
            $arrResult['callbackType'] = 'refreshCurrent';
        
        }while(0);
        return $arrResult;
    }
    
}
