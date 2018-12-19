<?php

namespace frontend\components;

class OrderService
{
    
    /*
    *@params    "car_id": "50",车型id
    *@params    "days": "1.0",时间天数
    *@params    "end_time": "1498723200",
    *@params    "price_type": "1",1：门店价格，3：在线价格
    *@params     "return_sid": "20",还车门店id
    *@params    "ser_list": "1|2",
    *@params    "sid": "20",租车门店id
    *@params    "start_time": "1498636800",
    *@params    "time": "1498635476",
    *@params    "sign": "b9498845af575b96f9a9c7effdf48207"*/
    public static function processOrder($params, $isGenerateOrder = true)
    {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do
        {
            $requiredFields = ['car_id' => 'vehicle_model_id', 'start_time', 'end_time', 
                'days' => 'rent_days', 'sid' => 'office_id_rent', 'return_sid' => 'office_id_return', 
                'price_type'=>'pay_type', 'ser_list'];
            $optionalFields = [
                'inv_title', 
                'inv_name', 
                'inv_phone', 
                'inv_addr' => 'inv_address', 
                'inv_postcode',
                'remark'];
            $integerfields = ['vehicle_model_id' => 1, 'status' => 1, 'start_time' => 1, 'end_time' => 1, 
                'rent_days' => 1, 'office_id_rent' => 1, 'office_id_return' => 1,
                'pay_type' => 1];

            $objFormData = new \common\models\Form_pro_vehicle_order();
            
            $arrSerIdList = [];
            
            // load required fields
            foreach ($requiredFields as $k1 => $k2) {
                $k = $k1;
                if (is_integer($k1)) {
                    $k = $k2;
                }
                //$k=car_id,start_time,end_time,days,sid,return_sid,price_type,ser_list
                //$k2=vehicle_model_id,start_time,end_time,rent_days,office_id_rent,office_id_return,pay_type,ser_list
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');
                    break;
                }

                if (isset($integerfields[$k2])) {
                    if ($k == 'start_time' || $k == 'end_time') {
                        if (is_string($params[$k])) {
                            $objFormData[$k2] = \common\helpers\Utils::toTimestamp($params[$k]);
                        }
                        else {
                            $objFormData[$k2] = intval($params[$k]);
                        }
                    }
                    else {
                        $objFormData[$k2] = intval($params[$k]);
                    }
                }
                elseif ($k == 'ser_list') {
                    if (is_array($params[$k])) {
                        foreach ($params[$k] as $_serId) {
                            $arrSerIdList[intval($_serId)] = 1;
                        }
                    }
                    else {
                        $_arr = explode('|', $params[$k]);
                        foreach ($_arr as $_serId) {
                            $arrSerIdList[intval($_serId)] = 1;
                        }
                    }
                }
                else {
                    $objFormData[$k2] = $params[$k];
                }
            }
            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {
                break;
            }
            // load optional fields
            foreach ($optionalFields as $k1 => $k2) {
                $k = $k1;
                if (is_integer($k1)) {
                    $k = $k2;
                }
                if (isset($params[$k])) {
                    if (isset($integerfields[$k2])) {
                        $objFormData[$k2] = intval($params[$k]);
                    }
                    else {
                        $objFormData[$k2] = $params[$k];
                    }
                }
            }

            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
        
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => \Yii::$app->user->id]);
            $objUser = $cdb->one();
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            /**
             * @type common\models\Pub_user_info
             */
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            if($objUserInfo->credit_level < 0){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'This people is black');//黑名单。
                break;
            }
            // sjj 判断新老用户
            $userId = \Yii::$app->user->id;
            $re = \common\models\Pro_vehicle_order::CheckCustomerIsNew($userId);
            if($re > 0){
                $userisnew = 0;//老用户
            }else{
                $userisnew = 1;//新用户
            }
            //杭州萧山便利点延后两小时下单
            $ishz = \common\components\CheckModule::check_office_order_time($params['sid'],$params['start_time']);
            if(!$ishz){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = '请提前两个小时下单';
                break;
            }
            // sjj


            // sjj 国庆期间3天起租判断
            /*if(($objFormData->start_time > 1506700800 && $objFormData->start_time < 1507478399) || ($objFormData->end_time > 1506700800 && $objFormData->end_time < 1507478399)){
                if($objFormData->rent_days<3){
                    $arrData['result'] = -1;
                    $arrData['desc'] = '国庆期间(9月30日-10月8日)3天起租';
                    break;
                    // return self::errorResult('国庆期间3天起租', 300);
                }
            }*/
            // sjj 国庆期间3天起租判断
            $objFormData->source = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;//1手机app下单
            $objFormData->belong_office_id = $objFormData->office_id_rent;
            $belongOfficeId = $objFormData->belong_office_id ? $objFormData->belong_office_id : 0;
            
            $arrServicePriceObjects = null;
            if (empty($arrSerIdList)) {
                $arrServicePriceObjects = [];
            }
            else {
                $arrServicePriceObjects = \common\models\Pro_service_price::findAllServicePrices($belongOfficeId, array_keys($arrSerIdList));
            }
            
            $validateRentDaysResult = \common\components\OrderModule::validateRentDays($objFormData->rent_days, $objFormData->start_time, $objFormData->end_time);
            if ($validateRentDaysResult['result'] != 0) {
                $arrData['desc'] = $validateRentDaysResult['desc'];
                if ($validateRentDaysResult['result'] == -1) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                }
                else if ($validateRentDaysResult['result'] == -2) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_RENT_DAYS_TOO_SHORT;
                }
                else if ($validateRentDaysResult['result'] == -3) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                }
                else {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                }
                break;
            }

            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objFormData->vehicle_model_id);
            if (!$objVehicleModel) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_CAR_NOT_EXISTS;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                break;
            }
            
            $arrFeePlans = \common\components\VehicleModule::getFeePlanObjects($objVehicleModel->id, $belongOfficeId);
            // sjj临时测试添加
            $feeOnline = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $belongOfficeId, $objVehicleModel->id);
            $feeOffice = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_OFFICE, $belongOfficeId, $objVehicleModel->id);
            if ($feeOnline || $feeOffice) {
                $priceOnlineInfo = null;
                $priceOfficeInfo = null;
                if ($feeOnline) {
                    $priceOnlineInfo = $feeOnline->getPriceForDuration($objFormData->start_time, $objFormData->end_time, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE ,$objUserInfo->getBirthday(),$userisnew);
                }
                if ($feeOffice) {
                    $priceOfficeInfo = $feeOffice->getPriceForDuration($objFormData->start_time, $objFormData->end_time, \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE ,$objUserInfo->getBirthday(),$userisnew);
                }
            // sjj
            // $date=date('Y-m-d H:i:s',time());
            // $a=json_encode($objFormData->source);
            // $b=$params['price_type'];
            // file_put_contents('preview.txt',"$date'-----source'$a',price_type--.$b\n",FILE_APPEND);
            // sjj
                if($params['price_type'] == 3){//在线价格
                    $feeDefault = $priceOnlineInfo;
                }else{
                    $feeDefault = $priceOfficeInfo;
                }
            }
            // sjj临时测试

            /*if($params['price_type'] == 1){
                $objVehicleFeePlan = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, 3, $belongOfficeId, $objVehicleModel->id);

            }else{
                $objVehicleFeePlan = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, $objFormData->source, $belongOfficeId, $objVehicleModel->id);
            }*/
            $objVehicleFeePlan = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, $objFormData->source, $belongOfficeId, $objVehicleModel->id);
            
            
            if (!$objVehicleFeePlan) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_PRICE_NOT_CONFIGURED;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle price info')]);
                break;
            }
            
            $arrOfficeNames = [];
            if ($objFormData->office_id_rent) {
                if (!isset($arrOfficeNames[$objFormData->office_id_rent])) {
                    $objOffice = \common\models\Pro_office::findById($objFormData->office_id_rent);
                    if (!$objOffice) {
                        $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                        $arrData['desc'] = \Yii::t('carrental', 'Could not find take car office');
                        break;
                    }
                    $arrOfficeNames[$objFormData->office_id_rent] = $objOffice->fullname;
                }
            }
            if ($objFormData->office_id_return) {
                if (!isset($arrOfficeNames[$objFormData->office_id_return])) {
                    $objOffice = \common\models\Pro_office::findById($objFormData->office_id_return);
                    if (!$objOffice) {
                        $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                        $arrData['desc'] = \Yii::t('carrental', 'Could not find return car office');
                        break;
                    }
                    $arrOfficeNames[$objFormData->office_id_return] = $objOffice->fullname;
                }
            }
            
            // check price

            $curTime = time();
            
            $objFormData->user_id = $objUserInfo->id;
            //$objFormData->serial = date('YmdHis', $curTime).$objFormData->user_id.$objFormData->vehicle_model_id;
            $objFormData->vehicle_id = 0;
            $objFormData->status = \common\models\Pro_vehicle_order::STATUS_WAITING;
            $objFormData->type = \common\models\Pro_vehicle_order::TYPE_PERSONAL;
            //$objFormData->pay_type = \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE;
            $objFormData->vehicle_color = 0;        // todo
            $objFormData->vehicle_oil_label = $objVehicleModel->oil_label;
            $objFormData->vehicle_outbound_mileage = 0;
            $objFormData->vehicle_inbound_mileage = 0;
            $objFormData->price_poundage = $objVehicleModel->poundage;
            $objFormData->unit_price_basic_insurance = $objVehicleModel->basic_insurance;
            $objFormData->deposit_pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;
            $objFormData->price_deposit_violation = $objVehicleModel->rent_deposit;
            $objFormData->price_deposit = 0;
            $objFormData->pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;
            //$objFormData->paid_amount = 0;
            $objFormData->unit_price_overtime = $objVehicleModel->overtime_price_personal;
            $objFormData->unit_price_overmileage = $objVehicleModel->overmileage_price_personal;
            $objFormData->unit_price_designated_driving = $objVehicleModel->designated_driving_price;
            $objFormData->unit_price_designated_driving_overtime = $objVehicleModel->overtime_price_designated;
            $objFormData->unit_price_designated_driving_overmileage = $objVehicleModel->overmileage_price_designated;
            //$objFormData->optional_service = $optionalServiceFlag;
            //$objFormData->optional_service_info = $optionalServiceInfo;
            //$objFormData->price_optional_service = $optionalServicePrice;
            
            $arrPriceData = $objVehicleFeePlan->getPriceForDuration($objFormData->start_time, $objFormData->end_time, $objFormData->pay_type, $objUserInfo->getBirthday(),$userisnew);
            // sjj价格替换start_time, end_time, pay_type, getBirthday(),vehicle_model_id,source
            // $pprice = $objVehicleFeePlan->getPriceForDuration2($objFormData->start_time, $objFormData->end_time, $objFormData->pay_type, $objUserInfo->getBirthday());
            // sjj
            // $objFormData->price_rent = $arrPriceData['price'];
            // sjj替换上一句
            $daysData = \common\models\Pri_renttime_data::create($objFormData->start_time, $objFormData->end_time);
            if($params['price_type'] == 1){//门店
                // $objFormData->price_rent = ($feeOffice ? (($priceOfficeInfo['price'] && $daysData->days) ? ceil($priceOfficeInfo['price'] / $daysData->days) : $feeOffice->getDayPriceByTime($objFormData->start_time)) : 0);
                $objFormData->price_rent = $feeDefault['price'];
           
            }else{
                $objFormData->price_rent = $feeDefault['price'];
                // $objFormData->price_rent = ($feeOnline ? (($priceOnlineInfo['price'] && $daysData->days) ? ceil($priceOnlineInfo['price'] / $daysData->days) : $feeOnline->getDayPriceByTime($objFormData->start_time)) : 0);
            }
            // sjj
            $objFormData->rent_per_day = round($objFormData->price_rent / $objFormData->rent_days, 2);
            
            $objFormData->price_overtime = 0;
            $objFormData->price_overmileage = 0;
            $objFormData->price_designated_driving = 0;
            $objFormData->price_designated_driving_overtime = 0;
            $objFormData->price_designated_driving_overmileage = 0;
            
            if (!isset($arrSerIdList[\common\models\Pro_service_price::ID_DESIGNATED_DRIVING])) {
                $objFormData->unit_price_designated_driving = 0;
                $objFormData->unit_price_designated_driving_overtime = 0;
                $objFormData->unit_price_designated_driving_overmileage = 0;
            }
            //if (isset($arrSerIdList[\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME])) {
            //    $objFormData->price_designated_driving = $objVehicleModel->designated_driving_price * $objFormData->rent_days;
            //}
            
            $objFormData->price_oil = 0;
            $objFormData->price_oil_agency = 0;
            $objFormData->price_car_damage = 0;
            $objFormData->price_working_loss = 0;
            $objFormData->price_accessories = 0;
            $objFormData->price_agency = 0;
            $objFormData->price_other = 0;
            $objFormData->price_insurance_overtime = 0;
            $objFormData->price_bonus_point_deduction = 0;
            
            // 优惠信息
            $objFormData->preferential_info = '';
            $objFormData->price_preferential = 0;
            
            // 客户信息 
            $objFormData->customer_name = $objUserInfo->name;
            $objFormData->customer_id_type = $objUserInfo->identity_type;
            $objFormData->customer_id = $objUserInfo->identity_id;
            $objFormData->customer_telephone = $objUserInfo->telephone;
            $objFormData->customer_fixedphone = $objUserInfo->fixedphone;
            //$objFormData->email = $objUserInfo->email;
            $objFormData->customer_operator_name = $objUserInfo->name;
            $objFormData->customer_driver_license_time = $objUserInfo->driver_license_time;
            $objFormData->customer_driver_license_expire_time = $objUserInfo->driver_license_expire_time;
            $objFormData->customer_address = $objUserInfo->home_address ? $objUserInfo->home_address : '';
            
            if ($arrPriceData['hasFestivalPrice']) {
                $festival = $arrPriceData['festival'];
                if ($festival && $festival->alldays_required) {
                    if (!$festival->isValidRentTime($objFormData->start_time, $objFormData->end_time)) {
                        \Yii::trace("user:{$objFormData->user_id} rent car by model:{$objFormData->vehicle_model_id} while rent time appears in festival time region and festival:{$festival->id} name:{$festival->name} requires all festival days to be rented.", 'order');
                        $arrData['result'] = \frontend\components\ApiModule::CODE_RENT_DAYS_TOO_SHORT;
                        $arrData['desc'] = \Yii::t('carrental', 'When rent car in {name}, you should rent car for all days between {start} and {end}.', ['name'=>$festival->name, 'start'=>date('Y-m-d', $festival->start_time), 'end'=>date('Y-m-d', $festival->end_time)]);
                        break;
                    }
                }
                if (\common\models\Pro_vehicle_order::isMultidaysPackagePriceType($objFormData->pay_type) && $objFormData->rent_days < \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                    $arrData['desc'] = '节日期间无法使用打包价格。';
                    break;
                }

                /*//节假日不能有租一送一
                $cdb = \common\models\Pro_vehicle_order::find(true);
                $cdb->where(['user_id'=>$objUserInfo->id]);
                $cdb->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
                $hasRentedBefore = $cdb->one() ? true : false;

                if(!$hasRentedBefore){
                    if($objFormData->rent_days >= 2){
                        $objPreferential = \common\models\Pro_preferential_info::findById(\common\components\Consts::PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY, 'process_type');
                        if ($objPreferential) {
                            $objFormData->preferential_info = $objPreferential->name;
                            $objFormData->preferential_type = $objPreferential->process_type;
                            $objFormData->price_preferential = $arrPriceData['details'][1];
                        }
                    }
                }*/
            }
            elseif (!\common\models\Pro_vehicle_order::isMultidaysPackagePriceType($objFormData->pay_type) || $objFormData->rent_days > \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {
                // should not use default findOne because the order default filtered belong office
                $cdb = \common\models\Pro_vehicle_order::find(true);
                $cdb->where(['user_id'=>$objUserInfo->id]);
                $cdb->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
                $hasRentedBefore = $cdb->one() ? true : false;
                if ($hasRentedBefore) {
                    \Yii::trace("user:{$objFormData->user_id} rent car by model:{$objFormData->vehicle_model_id} while already rented car before, so skip give zu-yi-song-yi preferential.", 'order');
                }
                elseif ($objFormData->rent_days >= 2) {
                    \Yii::warning("user:{$objFormData->user_id} rent car by model:{$objFormData->vehicle_model_id} by first time, give the zu-yi-song-yi perferential.", 'order');
                    $objPreferential = \common\models\Pro_preferential_info::findById(\common\components\Consts::PROCESS_TYPE_FIRST_RENTAL_GIFT_ONE_DAY, 'process_type');
                    if ($objPreferential) {
                        $objFormData->preferential_info = $objPreferential->name;
                        $objFormData->preferential_type = $objPreferential->process_type;
                        $objFormData->price_preferential = $arrPriceData['details'][1];
                    }
                }
            }
            
            // check if car can be rented
            $userRentingCount = \common\components\OrderModule::getUserRentingCarCount($objFormData->user_id, $objFormData->start_time, $objFormData->end_time);
            if ($userRentingCount >= $objUserInfo->max_renting_cars) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_ALREADY_RENTED_CAR;
                $arrData['desc'] = \Yii::t('carrental', 'Sorry, you have already {number} order this time.', ['number'=>$userRentingCount]);
                break;
            }
            
            $leftCount = \common\components\OrderModule::getVehicleLeftCountByModelId($objFormData->vehicle_model_id, $objFormData->start_time, $objFormData->end_time);
            if ($leftCount <= 0) {
                if (\common\models\Pro_vehicle::find(true)->where(['status'=>\common\models\Pro_vehicle::STATUS_NORMAL])->count() == 0) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NO_CAR_VALID_FOR_RENT;
                    $arrData['desc'] = \Yii::t('carrental', 'Sorry, there is no valid vehicle to rent!');
                    break;
                }
                elseif ($leftCount < 0) {   // if left count is zero and there is valid vehicles, enable rent anyway.
                    $arrData['result'] = \frontend\components\ApiModule::CODE_CAR_ALREADY_BEEN_RENT;
                    $arrData['desc'] = \Yii::t('locale', 'Sorry, vehicle already rented!');
                    break;
                }
            }
            
            // calculate other service prices
            // 
            // calculate the service price between difficrent office.
            // calculate the diffirent take car and return car price.
            $arrCalcResult = $objFormData->calculateCarDeliveryServicePrice();
            if ($arrCalcResult[0] != 0) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = $arrCalcResult[1];
                break;
            }
            
            if (!$objFormData->validate()) {
                $errText = $objFormData->getErrorDebugString();
                \Yii::error("Order api validate form data failed with error:{$errText}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', 'Validate the input failed!')." {$errText}";
                break;
            }
            
            $curDayStart = strtotime(date('Y-m-d'));
            if ($objFormData->start_time < $curDayStart) {
                \Yii::error("Order api validate order start_time:{$objFormData->start_time} that earlier than current day start time:{$curDayStart}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('carrental', 'Order start time should not earlier than today.');
                break;
            }
            /*sjj*/
            $objFormData->yuyue_time = $objFormData->start_time;
            $objFormData->yuyue_end_time = $objFormData->end_time;
            /*sjj*/
            $objOrder = new \common\models\Pro_vehicle_order();
            // sjj
            // $date=date('Y-m-d H:i:s',time());
            // $b=json_encode($objOrder);
            // file_put_contents('preview.txt',"$date':'$b'\n",FILE_APPEND);
            // sjj
            if (!$objFormData->save($objOrder)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = \Yii::t('locale', 'Create order failed!');
                break;
            }
            

            $objOrder->setSerialNo();
            $objOrder->onUpdateEndTime($objOrder->new_end_time);
            $objOrder->setOptionalServices($arrServicePriceObjects);
            $objOrder->getDailyRentDetailedPriceArray();
            $objOrder->calculateTotalPrice();
            
            if ($isGenerateOrder) {
                if (!$objOrder->save()) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                    $arrData['desc'] = \Yii::t('locale', 'Create order failed!');
                    break;
                }
                
                $arrData['order_id'] = $objOrder->serial;
                
                if ($objUserInfo->belong_office_id == 0) {
                    $objUserInfo->belong_office_id = $objOrder->belong_office_id;
                    $objUserInfo->save();
                }
                
                //$objOffice = \common\models\Pro_office::findById($objOrder->office_id_rent);
                \common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_APP, ['CNAME'=>$objOrder->customer_name, 
                    //'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                    //'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                    //'SHOPADDRESS'=>$objOrder->getTakeCarAddressText(),
                    //'SHOPTELEPHONE'=>$objOffice ? $objOffice->telephone : '',
                    'ORDERID'=>$objOrder->serial,
                    'PRICESTANDARD'=>$objOrder->total_amount,
                ]);
            }
            
            $orderData = self::getOrderAttributes($objOrder, true, [$objOrder->vehicle_model_id=>$objVehicleModel], $arrOfficeNames);
            
            foreach ($orderData as $k => $v) {
                $arrData[$k] = $v;
            }

        }while (0);
        return $arrData;
    }
    
    public static function getOrderAttributes($objOrder, $isFillSerList = false, $arrVehicleModels = null, $arrOfficeNames = null) {
        $objVehicleModel = null;
        if ($arrVehicleModels === null || !is_array($arrVehicleModels)) {
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objOrder->vehicle_model_id);
        }
        else {
            $objVehicleModel = (isset($arrVehicleModels[$objOrder->vehicle_model_id]) ? $arrVehicleModels[$objOrder->vehicle_model_id] : null);
        }
        if ($arrOfficeNames === null || !is_array($arrOfficeNames)) {
            $arrOfficeNames = [];
            $arrOfficeIds = [];
            if ($objOrder->office_id_rent) {
                $arrOfficeIds[$objOrder->office_id_rent] = 1;
            }
            if ($objOrder->office_id_return) {
                $arrOfficeIds[$objOrder->office_id_return] = 1;
            }
            if (!empty($arrOfficeIds)) {
                $arrOfficeNames = \common\components\OfficeModule::getOfficeNamesArrayByOfficeIds(array_keys($arrOfficeIds));
            }
        }
        
        if($objOrder->pay_type == 6){
            $rentTimeData = \common\models\Pri_renttime_data::create($objOrder->start_time, $objOrder->new_end_time,$objOrder->pay_type);
        }else{
            $rentTimeData = \common\models\Pri_renttime_data::create($objOrder->start_time, $objOrder->new_end_time);
        }
        $orderData = [
            'order_id' => $objOrder->serial,
            'total_price' => $objOrder->total_amount,
            'rent_price' => $objOrder->price_rent,
            'price_type' => $objOrder->pay_type,
            'pledge_cash' => $objOrder->price_deposit_violation,
            'car_pledge_cash' => $objOrder->price_deposit,
            'car' => [
                'car_id' => ($objVehicleModel ? $objVehicleModel->id : 0),
                'car_name' => ($objVehicleModel ? $objVehicleModel->vehicle_model : ''),
                'carriage' => ($objVehicleModel ? $objVehicleModel->carriage : ''),
                'car_image' => ($objVehicleModel ? \common\helpers\Utils::toFileAbsoluteUrl($objVehicleModel->image_0) : ''),
                'car_mode' => $objVehicleModel->vehicle_type,
                'carriage' => $objVehicleModel->carriage,
                'seat' => ($objVehicleModel ? $objVehicleModel->seat : ''),
                'consume' => ($objVehicleModel ? $objVehicleModel->vehicleEmissionHumanText() : ''),
                'gearboxmode' => ($objVehicleModel ? (($objVehicleModel->gearbox & \common\models\Pro_vehicle_model::GEARBOX_AUTO) ? '2' : '1') : '0'),
                'property_text' => ($objVehicleModel ? $objVehicleModel->getPropertyHumanDisplayText() : ''),
            ],
            'start_time' => $objOrder->start_time,
            'end_time' => $objOrder->new_end_time,
            'created_at'=>$objOrder->created_at,
            'status' => $objOrder->status,
            'pay_source' => $objOrder->pay_source,
            'preferential_info' => $objOrder->preferential_info,
            'preferential_price' => $objOrder->price_preferential,
        ];
        $orderData['store'] = ['sid'=>$objOrder->office_id_rent, 'store_name'=>(isset($arrOfficeNames[$objOrder->office_id_rent]) ? $arrOfficeNames[$objOrder->office_id_rent] : '')];
        $orderData['re_store'] = ['sid'=>$objOrder->office_id_return, 'store_name'=>(isset($arrOfficeNames[$objOrder->office_id_return]) ? $arrOfficeNames[$objOrder->office_id_return] : '')];
        if (!empty($objOrder->address_take_car)) {
            $orderData['take_car_addr'] = $objOrder->address_take_car;
        }
        if (!empty($objOrder->address_return_car)) {
            $orderData['return_car_addr'] = $objOrder->address_return_car;
        }
        if ($objOrder->status <= \common\models\Pro_vehicle_order::STATUS_BOOKED && $objOrder->paid_amount > 0) {
            $orderData['status'] = \common\models\Pro_vehicle_order::STATUS_PAID;
        }
        if (floatval($objOrder->price_address_km)) {
            $orderData['price_address_km'] = $objOrder->price_address_km;
        }
        if ($isFillSerList) {
            $arrSerlist = [];
            // 租车费用
            // if (floatval($objOrder->price_rent)) {
                $arrSerlist[] = ['ser_id'=>8, 'ser_price'=>floatval($objOrder->price_rent),'isoption'=>0, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Rent')];
            // }
            $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_POUNDAGE, 'ser_price'=>floatval($objOrder->price_poundage), 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Poundage')];
            //$arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_BASIC_INSURANCE, 'ser_price'=>floatval($objOrder->price_basic_insurance)/floatval($objOrder->rent_days), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Basic insurance')];
            $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_BASIC_INSURANCE, 'ser_price'=>($objOrder->price_basic_insurance == 0 || $objOrder->rent_days == 0) ? 0 : floatval($objOrder->price_basic_insurance)/floatval($objOrder->rent_days), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Basic insurance')];
            $arrSerNames = \common\components\OrderModule::getOptionalServiceNameArray();
            $arrOptionalPrices = $objOrder->getOptionalServicePriceArray();
            foreach ($arrOptionalPrices as $k => $v) {
                $arrSerlist[] = ['ser_id'=>$k, 'ser_price'=>floatval($v['price']), 'ser_count'=>$v['count'], 'ser_name'=>(isset($arrSerNames[$k]) ? $arrSerNames[$k] : '')];
            }
            if(floatval($objOrder->unit_price_overtime)){
                if ($rentTimeData->hours > 0) {
                    $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_OVERTIME, 'ser_price'=>floatval($objOrder->unit_price_overtime), 'ser_count'=>$rentTimeData->hours, 'ser_name'=>\Yii::t('carrental', 'Overtime service fee')];
                }
            }
            if (floatval($objOrder->price_designated_driving)) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING, 'ser_price'=>floatval($objOrder->unit_price_designated_driving), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Designated driving')];
            }
            //异店还车费
            if (floatval($objOrder->price_different_office)) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME, 'ser_price'=>floatval($objOrder->price_different_office), 'ser_count'=>1, 'ser_name'=>\Yii::t('carrental', 'Fee of different shop return car')];
            }
            // 送车上门费
            if (floatval($objOrder->price_take_car)) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_OVERTIME, 'ser_price'=>floatval($objOrder->price_take_car), 'ser_count'=>1, 'ser_name'=>\Yii::t('carrental', 'Fee of delivery car to house')];
            }
            // 上门取车费
            if (floatval($objOrder->price_return_car)) {
                $arrSerlist[] = ['ser_id'=>6, 'ser_price'=>floatval($objOrder->price_return_car), 'ser_count'=>1, 'ser_name'=>\Yii::t('carrental', 'Fee of take car from house')];
            }

            // 单程租车两地公里油耗
            if (floatval($objOrder->price_address_km)) {
                $arrSerlist[] = ['ser_id'=>7, 'ser_price'=>floatval($objOrder->price_address_km),'isoption'=>0, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Price address km')];
            }
            //if ($objOrder->unit_price_designated_driving_overtime) {
            //    $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME, 'ser_price'=>$objOrder->unit_price_designated_driving_overtime, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')])];
            //}
            $orderData['ser_list'] = $arrSerlist;
        }
        
        return $orderData;
    }
    
    public static function getOrderBySerial($serial) {
        $arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        $objOrder = null;
        do
        {
            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');
                break;
            }
            
            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => \Yii::$app->user->id]);
            $objUser = $cdb->one();
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc'] = \Yii::t('locale', 'Login required.');
                break;
            }
            
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');
                break;
            }
            
            if (empty($serial)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', '{name} should not be empty!', ['name'=>\Yii::t('locale', '{name} No.', ['name'=>\Yii::t('locale', 'Order')])]);
                break;
            }
            
            $cdb = \common\models\Pro_vehicle_order::find(true);
            $cdb->where(['serial' => $serial]);
            $objOrder = $cdb->one();
            
            if (!$objOrder) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Order')]);
                break;
            }
            
            if ($objOrder->user_id != $objUserInfo->id) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ORDER_CANNOT_CANCEL;
                $arrData['desc'] = \Yii::t('locale', 'Order does not belongs to you.');
                break;
            }
            
        }while (0);

        return [$arrData, $objOrder];
    }
    
}
