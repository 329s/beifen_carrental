<?php
namespace frontend\components;

class NewOrderService
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
    *@params    "sign": "b9498845af575b96f9a9c7effdf48207"
    */
	public static function processOrder($params, $isGenerateOrder = false){
		$arrData = ['result' => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
		do{
			$requiredFields = [
				'car_id' => 'vehicle_model_id',//车型id
				'start_time',
				'end_time',
                'days' => 'rent_days',
                'sid' => 'office_id_rent',//租车门店id
                'return_sid' => 'office_id_return',//还车门店id
                'price_type'=>'pay_type',//1门店价格，3在线价格
                'ser_list',
                'address_take_car',
                'address_return_car',
                'price_take_car',
                'price_return_car'
            ];
            //发票字段
            $optionalFields = [
                'inv_title',
                'inv_name',
                'inv_phone',
                'inv_addr' => 'inv_address',
                'inv_postcode',
                'remark'];
            $integerfields = [
            	'vehicle_model_id' => 1,
            	'status' => 1,
            	'start_time' => 1,
            	'end_time' => 1,
                'rent_days' => 1,
                'office_id_rent' => 1,
                'office_id_return' => 1,
                'pay_type' => 1,
                // 'address_take_car'=> 1,
                // 'address_return_car'=> 1,
                'price_take_car'=> 1,
                'price_return_car'=> 1,
            ];

            $objFormData = new \common\models\Form_pro_vehicle_order();
            $arrSerIdList = [];
            //所传参数赋值到对应数据库字段
            foreach ($requiredFields as $k1 => $k2) {
                $k = $k1;
                if (is_integer($k1)) {
                    $k = $k2;
                }
                //$k=car_id,start_time,end_time,days,sid,return_sid,price_type,ser_list
                //$k2=vehicle_model_id,start_time,end_time,rent_days,office_id_rent,office_id_return,pay_type,ser_list

            	//判断所传参数是否缺少
            	if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc'] = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }

                //
                if (isset($integerfields[$k2])) {
                    if ($k == 'start_time' || $k == 'end_time') {
                        if (is_string($params[$k])) {
                            $objFormData[$k2] = \common\helpers\Utils::toTimestamp($params[$k]);
                            if($k == 'start_time'){
                            	$objFormData['yuyue_time'] = $objFormData[$k2];
                            }else{
                                $objFormData['yuyue_end_time'] = $objFormData[$k2];
                                $objFormData['new_end_time'] = $objFormData[$k2];
                            }
                        } else {
                            $objFormData[$k2] = intval($params[$k]);
                            if($k == 'start_time'){
                                $objFormData['yuyue_time'] = $objFormData[$k2];
                            }else{
                                $objFormData['yuyue_end_time'] = $objFormData[$k2];
                            	$objFormData['new_end_time'] = $objFormData[$k2];
                            }
                        }
                    } else {
                        $objFormData[$k2] = intval($params[$k]);
                    }
                } elseif ($k == 'ser_list') {
                    if (is_array($params[$k])) {
                        foreach ($params[$k] as $_serId) {
                            $arrSerIdList[intval($_serId)] = 1;
                        }
                    } else {
                        $_arr = explode('|', $params[$k]);
                        foreach ($_arr as $_serId) {
                            $arrSerIdList[intval($_serId)] = 1;
                        }
                    }
                } else {
                    $objFormData[$k2] = $params[$k];
                }



            }

			//


            if ($arrData['result'] != \frontend\components\ApiModule::CODE_SUCCESS) {//0
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


            //登陆的用户信息
            /*if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'Login required, current is guest user.');//当前是游客模式，请先登录。
                break;//sjj，先注释调，上线后取消注释
            }*/

            $cdb = \common\models\Pub_user::find();
            $cdb->where(['id' => '10959']);
            // $cdb->where(['id' => \Yii::$app->user->id]);
            $objUser = $cdb->one();
           
            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'Login required.');//请先登陆
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
            // sjj

            /**
             * @type common\models\Pub_user_info
             * @desc 用户信息
             */
            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;//1005
                $arrData['desc'] = \Yii::t('locale', 'User identity information needed.');//请先完善实名认证信息。
                break;
            }

            if($objUserInfo->credit_level < 0){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'This people is black');//黑名单。
                break;
            }
            // 判断时间超过当前时间一个小时，保证距离远的送车上门
            $now  = time();
            $diff = $objFormData->start_time - $now;
            if($objFormData->start_time < $now){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc']   = \Yii::t('locale', 'Please be more than one hour earlier  place an order');
                break;
            }
            if(!empty($objFormData->address_take_car)){
                if($diff < 1800){
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                    $arrData['desc']   = \Yii::t('locale', 'Please be more than one hour earlier  place an order');
                    break;
                }
            }

            // sjj 国庆期间3天起租判断
            // if(($objFormData->start_time > 1506700800 && $objFormData->start_time < 1507478399) || ($objFormData->end_time > 1506700800 && $objFormData->end_time < 1507478399)){
            //     if($objFormData->rent_days<3){
            //         $arrData['result'] = -1;
            //         $arrData['desc'] = '国庆期间(9月30日-10月8日)3天起租';
            //         break;
            //         // return self::errorResult('国庆期间3天起租', 300);
            //     }
            // }
            // sjj 国庆期间3天起租判断


            $objFormData->source = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;//1手机app下单
            $objFormData->belong_office_id = $objFormData->office_id_rent;//订单所属门店为订单租车门店
            $belongOfficeId = $objFormData->belong_office_id ? $objFormData->belong_office_id : 0;

            //增值服务费用
            $arrServicePriceObjects = null;
            if (empty($arrSerIdList)) {
                $arrServicePriceObjects = [];
            } else {
                $arrServicePriceObjects = \common\models\Pro_service_price::findAllServicePrices($belongOfficeId, array_keys($arrSerIdList));
            }

            // $arrData['objFormData']=$objFormData;
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
            /*$arrData['objVehicleModel']=$objVehicleModel;
            echo "<pre>";
            print_r($objVehicleModel);
            echo "</pre>";*/
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
                if($params['price_type'] == 3){//在线价格
                    $feeDefault = $priceOnlineInfo;
                }else{
                    $feeDefault = $priceOfficeInfo;
                }
            }
            // sjj临时测试
            // $arrData['arrServicePriceObjects']=$arrServicePriceObjects;

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
            // $arrData['sss'] = $objFormData;
            $curTime = time();
            
            $objFormData->user_id = $objUserInfo->id;
            //$objFormData->serial = date('YmdHis', $curTime).$objFormData->user_id.$objFormData->vehicle_model_id;
            $objFormData->vehicle_id = 0;
            $objFormData->status = \common\models\Pro_vehicle_order::STATUS_WAITING;//1待确认
            $objFormData->type = \common\models\Pro_vehicle_order::TYPE_PERSONAL;//1个人订单
            //$objFormData->pay_type = \common\models\Pro_vehicle_order::PRICE_TYPE_ONLINE;//3在线支付价格
            $objFormData->vehicle_color = 0;        // todo//车身颜色
            $objFormData->vehicle_oil_label = $objVehicleModel->oil_label;//车辆燃油型号
            $objFormData->vehicle_outbound_mileage = 0;//车辆出库里程
            $objFormData->vehicle_inbound_mileage = 0;//车里入库里程
            $objFormData->price_poundage = $objVehicleModel->poundage;//基本手续费
            $objFormData->unit_price_basic_insurance = $objVehicleModel->basic_insurance;//每日基本保险费
            $objFormData->deposit_pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;//0 未交  押金支付方式
            $objFormData->price_deposit_violation = $objVehicleModel->rent_deposit;//违章押金
            $objFormData->price_deposit = 0;//押金
            $objFormData->pay_source = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;//0 未交 租金支付方式
            //$objFormData->paid_amount = 0;
            $objFormData->unit_price_overtime = $objVehicleModel->overtime_price_personal;//超时费用标准(元/小时)
            $objFormData->unit_price_overmileage = $objVehicleModel->overmileage_price_personal;//超里程费用标准(元/公里)
            $objFormData->unit_price_designated_driving = $objVehicleModel->designated_driving_price;//代驾费用标准（元/天 0表示无代驾）
            $objFormData->unit_price_designated_driving_overtime = $objVehicleModel->overtime_price_designated;//代驾超时费用标准(元/小时)
            $objFormData->unit_price_designated_driving_overmileage = $objVehicleModel->overmileage_price_designated;//代驾超里程费用标准(元/公里)
            // $objFormData->optional_service = $optionalServiceFlag;//已选增值服务
            // $objFormData->optional_service_info = $optionalServiceInfo;//增值服务明细(id:price;id:price...)
            // $objFormData->price_optional_service = $optionalServicePrice;//增值服务合计价格
            
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
                $objFormData->price_rent = $feeDefault['price'];//租金价格
                // $objFormData->price_rent = ($feeOnline ? (($priceOnlineInfo['price'] && $daysData->days) ? ceil($priceOnlineInfo['price'] / $daysData->days) : $feeOnline->getDayPriceByTime($objFormData->start_time)) : 0);
            }
            // sjj
            $objFormData->rent_per_day = round($objFormData->price_rent / $objFormData->rent_days, 2);//每日租金
            
            $objFormData->price_overtime = 0;
            $objFormData->price_overmileage = 0;
            $objFormData->price_designated_driving = 0;
            $objFormData->price_designated_driving_overtime = 0;
            $objFormData->price_designated_driving_overmileage = 0;
            
            if (!isset($arrSerIdList[\common\models\Pro_service_price::ID_DESIGNATED_DRIVING])) {//3
                $objFormData->unit_price_designated_driving = 0;
                $objFormData->unit_price_designated_driving_overtime = 0;
                $objFormData->unit_price_designated_driving_overmileage = 0;
            }
            //if (isset($arrSerIdList[\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME])) {//4
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
            
            $arrData['arrPriceData']=$arrPriceData;
            // 是否有特殊节日
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
                }
            } elseif (!\common\models\Pro_vehicle_order::isMultidaysPackagePriceType($objFormData->pay_type) || $objFormData->rent_days > \common\components\Consts::AUTO_MONTH_PRICE_DAYS) {//30
                // should not use default findOne because the order default filtered belong office
                $cdb = \common\models\Pro_vehicle_order::find(true);
                $cdb->where(['user_id'=>$objUserInfo->id]);
                $cdb->andWhere(['<', 'status', \common\models\Pro_vehicle_order::STATUS_CANCELLED]);
                $hasRentedBefore = $cdb->one() ? true : false;
                if ($hasRentedBefore) {
                    \Yii::trace("user:{$objFormData->user_id} rent car by model:{$objFormData->vehicle_model_id} while already rented car before, so skip give zu-yi-song-yi preferential.", 'order');
                } elseif ($objFormData->rent_days >= 2) {
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
            
            //验证驾照时间格式
            if (!$objFormData->validate()) {
                $errText = $objFormData->getErrorDebugString();
                \Yii::error("Order api validate form data failed with error:{$errText}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
                $arrData['desc'] = \Yii::t('locale', 'Validate the input failed!')." {$errText}";//验证提交信息失败！ 属性 领驾照时间 的格式无效。 属性 驾照到期时间 的格式无效。
                break;
            }
            
            $curDayStart = strtotime(date('Y-m-d'));
            if ($objFormData->start_time < $curDayStart) {
                \Yii::error("Order api validate order start_time:{$objFormData->start_time} that earlier than current day start time:{$curDayStart}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('carrental', 'Order start time should not earlier than today.');
                break;
            }
            $objOrder = new \common\models\Pro_vehicle_order();
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
            // $arrData['params']=$params;
            // $arrData['objFormData']=$objFormData;
		}while (0);
		return $arrData;
	}

	/*
	*@desc 通过地址查经纬度
	*/
	public function getLongitudeAndLatitude($address)
    {
        $map = \common\components\MapApiGaode::create();
        $arrCoordinateResult = $map->getCoordinateByAddress($address);
        return $arrCoordinateResult;
    }
	/*
	*@desc 通过两个经纬度查距离
	*/

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
        
        $rentTimeData = \common\models\Pri_renttime_data::create($objOrder->start_time, $objOrder->new_end_time);
        
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
            'status' => $objOrder->status,
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
        if ($isFillSerList) {
            $arrSerlist = [];
            // 租车费用
            // if (floatval($objOrder->price_rent)) {
                $arrSerlist[] = ['ser_id'=>8, 'ser_price'=>floatval($objOrder->price_rent),'isoption'=>0, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Rent')];
            // }
            $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_POUNDAGE, 'ser_price'=>floatval($objOrder->price_poundage), 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Poundage')];
            $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_BASIC_INSURANCE, 'ser_price'=>floatval($objOrder->price_basic_insurance)/floatval($objOrder->rent_days), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Basic insurance')];
            $arrSerNames = \common\components\OrderModule::getOptionalServiceNameArray();
            $arrOptionalPrices = $objOrder->getOptionalServicePriceArray();
            foreach ($arrOptionalPrices as $k => $v) {
                $arrSerlist[] = ['ser_id'=>$k, 'ser_price'=>floatval($v['price']), 'ser_count'=>$v['count'], 'ser_name'=>(isset($arrSerNames[$k]) ? $arrSerNames[$k] : '')];
            }
            if ($rentTimeData->hours > 0) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_OVERTIME, 'ser_price'=>floatval($objOrder->unit_price_overtime), 'ser_count'=>$rentTimeData->hours, 'ser_name'=>\Yii::t('carrental', 'Overtime service fee')];
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
            //if ($objOrder->unit_price_designated_driving_overtime) {
            //    $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME, 'ser_price'=>$objOrder->unit_price_designated_driving_overtime, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')])];
            //}
            $orderData['ser_list'] = $arrSerlist;
        }
        
        return $orderData;
    }


    // 单程租车接口
    public static function processOneWayOrder($params, $isGenerateOrder = false){
        $arrData = ['result'  => \frontend\components\ApiModule::CODE_SUCCESS, 'desc' => \Yii::t('locale', 'Success')];
        do{
            $requiredFields = [
                'car_id'     => 'vehicle_model_id',    //车型id
                                'start_time',
                                'end_time',
                'days'       => 'rent_days',
                'sid'        => 'office_id_rent',      //租车门店id
                'return_sid' => 'office_id_return',    //还车门店id
                'price_type' => 'pay_type',             //1门店价格，3在线价格
                                // 'ser_list',
                                'flag',
                                'address_take_car',
                                'address_return_car',
                                'price_take_car',
                                'price_return_car',
                                'address_km',
            ];

            $integerfields = [
                'vehicle_model_id' => 1,
                'status'           => 1,
                'start_time'       => 1,
                'end_time'         => 1,
                'rent_days'        => 1,
                'office_id_rent'   => 1,
                'office_id_return' => 1,
                'pay_type'         => 1,
                // 'address_take_car'=> 1,
                // 'address_return_car'=> 1,
                // 'price_take_car'=> 1,
                // 'price_return_car'=> 1,
            ];

            $objFormData  = new \common\models\Form_pro_vehicle_order();
            $arrSerIdList = [];
            foreach ($requiredFields as $key => $value) {
                $k        = $key;
                if (is_integer($key)) {
                    $k    = $value;
                }

                //判断所传参数是否缺少
                if (!isset($params[$k])) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;//4001
                    $arrData['desc']   = \Yii::t('locale', 'Missing required parameter!');//缺少必要参数
                    break;
                }

                if(isset($integerfields[$value])){
                    $objFormData[$value] = intval($params[$k]);
                }else{
                    $objFormData[$value] = $params[$k];
                }
            }
            // echo "<pre>";
            // // print_r($params);
            // // print_r($requiredFields);
            // print_r($objFormData);
            // echo "</pre>";die;


            // 登陆账号等信息begin
            if (\Yii::$app->user->getIsGuest()) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc']   = \Yii::t('locale', 'Login required, current is guest user.');
                break;//sjj，先注释调，上线后取消注释
            }

            $cdb     = \common\models\Pub_user::find();
            $cdb     ->where(['id' => \Yii::$app->user->id]);
            // $cdb     ->where(['id' => '13436']);
            $objUser = $cdb->one();

            if (!$objUser) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;
                $arrData['desc']   = \Yii::t('locale', 'Login required.');
                break;
            }

            $objUserInfo = \common\models\Pub_user_info::findById($objUser->info_id);
            if (!$objUserInfo) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_NO_USER_IDINEITY_INFO;
                $arrData['desc']   = \Yii::t('locale', 'User identity information needed.');
                break;
            }

            if($objUserInfo->credit_level < 0){
                $arrData['result'] = \frontend\components\ApiModule::CODE_NOT_LOGIN;//1004
                $arrData['desc'] = \Yii::t('locale', 'This people is black');//黑名单。
                break;
            }
            // end

            $daysData        = \common\models\Pri_renttime_data::createTime($objFormData->start_time, $objFormData->end_time);

            // 判断订单时间不能超过6小时
            // echo "<pre>";
            // print_r($daysData);
            // echo "</pre>";die;
            if($objFormData->rent_days > 6 || $daysData->hours > 6){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc']   = \Yii::t('locale', 'Time not over');
                break;
            }else{
                $objFormData->yuyue_time  = $objFormData->start_time;
                $objFormData->yuyue_end_time    = $objFormData->end_time;
            }
            // 判断时间超过当前时间一个小时，保证距离远的送车上门
            $now  = time();
            $diff = $objFormData->start_time - $now;
            if($objFormData->start_time < $now){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc']   = \Yii::t('locale', 'Please be more than one hour earlier  place an order');
                break;
            }
            if($diff < 1800){
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PACKAGE;
                $arrData['desc']   = \Yii::t('locale', 'Please be more than one hour earlier  place an order');
                break;
            }

            // 订单数据生成
            $objFormData->source           = \common\models\Pro_vehicle_order::ORDER_SOURCE_APP;//1手机APP下单
            $objFormData->belong_office_id = $objFormData->office_id_rent;//订单所属门店为订单租车门店
            $belongOfficeId =  $objFormData->belong_office_id ? $objFormData->belong_office_id : 0;

            //判断车型是否存在
            $objVehicleModel = \common\models\Pro_vehicle_model::findById($objFormData->vehicle_model_id);
            if (!$objVehicleModel) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_CAR_NOT_EXISTS;
                $arrData['desc']   = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle')]);
                break;
            }

            // 该车型价格体系
            $arrFeePlans     = \common\components\VehicleModule::getFeePlanObjects($objVehicleModel->id, $belongOfficeId);

            // 线上售价
            $feeOnline       = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, \common\models\Pro_vehicle_order::ORDER_SOURCE_APP, $belongOfficeId, $objVehicleModel->id);

            
            if($feeOnline){
                $priceOnlineInfo         = $feeOnline->getPriceForHours($objFormData->start_time, $objFormData->end_time, $objFormData->address_km,$objFormData->flag);
                $objFormData->price_rent = $priceOnlineInfo['price'];
                $objFormData->price_address_km = $priceOnlineInfo['price_address_km'];
            }

            $objVehicleFeePlan   = \common\components\VehicleModule::getFeePlanObjectFromArray($arrFeePlans, $objFormData->source, $belongOfficeId, $objVehicleModel->id);

            if (!$objVehicleFeePlan) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_PRICE_NOT_CONFIGURED;
                $arrData['desc']   = \Yii::t('locale', '{name} not exists!', ['name'=>\Yii::t('locale', 'Vehicle price info')]);
                break;
            }

            //租车门店和还车门店
            $arrOfficeNames    = [];
            if ($objFormData->office_id_rent) {
                if (!isset($arrOfficeNames[$objFormData->office_id_rent])) {
                    $objOffice = \common\models\Pro_office::findById($objFormData->office_id_rent);
                    if (!$objOffice) {
                        $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                        $arrData['desc']   = \Yii::t('carrental', 'Could not find take car office');
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
                        $arrData['desc']   = \Yii::t('carrental', 'Could not find return car office');
                        break;
                    }
                    $arrOfficeNames[$objFormData->office_id_return] = $objOffice->fullname;
                }
            }


            $curTime = time();
            $objFormData->user_id = $objUserInfo->id;
            //$objFormData->serial = date('YmdHis', $curTime).$objFormData->user_id.$objFormData->vehicle_model_id;
            $objFormData->vehicle_id        = 0;
            $objFormData->status            = \common\models\Pro_vehicle_order::STATUS_WAITING; //1待确认
            $objFormData->type              = \common\models\Pro_vehicle_order::TYPE_PERSONAL;  //1个人订单
            $objFormData->vehicle_color     = 0;         // todo//车身颜色
            $objFormData->vehicle_oil_label = $objVehicleModel->oil_label;//车辆燃油型号
            $objFormData->vehicle_outbound_mileage   = 0;//车辆出库里程
            $objFormData->vehicle_inbound_mileage    = 0;//车里入库里程
            $objFormData->price_poundage             = 0;        //基本手续费
            $objFormData->unit_price_basic_insurance = 0;//每日基本保险费
            $arr_oneWayDeposit = \common\models\Pro_vehicle_fee_plan::GetoneWayDeposit();
            // echo "<pre>";
            // print_r($arr_oneWayDeposit);
            // echo "</pre>";die;
            $objFormData->price_deposit_violation    = $arr_oneWayDeposit[$objFormData->flag];  //违章押金
            $objFormData->price_deposit              = 0;                              //押金
            $objFormData->deposit_pay_source         = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;//0 未交  押金支付方式
            // $objFormData->paid_amount                = 0;
            $objFormData->pay_source                 = \common\models\Pro_vehicle_order::PAY_TYPE_NONE;//0 未交 租金支付方式
            $objFormData->unit_price_overtime        = 0;//超时费用标准(元/小时)
            $objFormData->unit_price_overmileage     = $objVehicleModel->overmileage_price_personal;//超里程费用标准(元/公里)
            $objFormData->unit_price_designated_driving = $objVehicleModel->designated_driving_price;//代驾费用标准（元/天 0表示无代驾）
            $objFormData->unit_price_designated_driving_overtime    = $objVehicleModel->overtime_price_designated;//代驾超时费用标准(元/小时)
            $objFormData->unit_price_designated_driving_overmileage = $objVehicleModel->overmileage_price_designated;//代驾超里程费用标准(元/公里)
            // $objFormData->optional_service       = $optionalServiceFlag; //已选增值服务
            // $objFormData->optional_service_info  = $optionalServiceInfo; //增值服务明细(id:price;id:price...)
            // $objFormData->price_optional_service = $optionalServicePrice;//增值服务合计价格

            // $objFormData->rent_per_day = round($objFormData->price_rent / $objFormData->rent_days, 2);//每日租金
            $objFormData->rent_per_day = round($objFormData->price_rent / $objFormData->rent_days,2);//每日租金

            $objFormData->price_overtime                      = 0;
            $objFormData->price_overmileage                   = 0;
            $objFormData->price_designated_driving            = 0;
            $objFormData->price_designated_driving_overtime   = 0;
            $objFormData->price_designated_driving_overmileage= 0;

            // if (!isset($arrSerIdList[\common\models\Pro_service_price::ID_DESIGNATED_DRIVING])) {//3
                $objFormData->unit_price_designated_driving             = 0;
                $objFormData->unit_price_designated_driving_overtime    = 0;
                $objFormData->unit_price_designated_driving_overmileage = 0;
            // }

            $objFormData->price_oil                 = 0;
            $objFormData->price_oil_agency          = 0;
            $objFormData->price_car_damage          = 0;
            $objFormData->price_working_loss        = 0;
            $objFormData->price_accessories         = 0;
            $objFormData->price_agency              = 0;
            $objFormData->price_other               = 0;
            $objFormData->price_insurance_overtime  = 0;
            $objFormData->price_bonus_point_deduction = 0;

            // 优惠信息
            $objFormData->preferential_info  = '';
            $objFormData->price_preferential = 0;

            // 客户信息
            $objFormData->customer_name          = $objUserInfo->name;
            $objFormData->customer_id_type       = $objUserInfo->identity_type;
            $objFormData->customer_id            = $objUserInfo->identity_id;
            $objFormData->customer_telephone     = $objUserInfo->telephone;
            $objFormData->customer_fixedphone    = $objUserInfo->fixedphone;
            //$objFormData->email = $objUserInfo->email;
            $objFormData->customer_operator_name = $objUserInfo->name;
            $objFormData->customer_driver_license_time        = $objUserInfo->driver_license_time;
            $objFormData->customer_driver_license_expire_time = $objUserInfo->driver_license_expire_time;
            $objFormData->customer_address       = $objUserInfo->home_address ? $objUserInfo->home_address : '';


            $arrPriceData = $objVehicleFeePlan->getPriceForHours($objFormData->start_time, $objFormData->end_time, $objFormData->pay_type);

            // if($arrPriceData){

            // }

            // check if car can be rented
            $userRentingCount = \common\components\OrderModule::getUserRentingCarCount($objFormData->user_id, $objFormData->start_time, $objFormData->end_time);
            if ($userRentingCount >= $objUserInfo->max_renting_cars) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_USER_ALREADY_RENTED_CAR;
                $arrData['desc'] = \Yii::t('carrental', 'Sorry, you have already {number} order this time.', ['number'=>$userRentingCount]);
                break;
            }

            $leftCount = \common\components\OrderModule::oneWaygetVehicleLeftCountByModelId($objFormData->vehicle_model_id, $objFormData->start_time, $objFormData->end_time);
            
            if ($leftCount <= 0) {
                if (\common\models\Pro_vehicle::find(true)->where(['status'=>\common\models\Pro_vehicle::STATUS_NORMAL])->count() == 0) {
                    $arrData['result'] = \frontend\components\ApiModule::CODE_NO_CAR_VALID_FOR_RENT;
                    $arrData['desc'] = \Yii::t('carrental', 'Sorry, there is no valid vehicle to rent!');
                    break;
                } elseif ($leftCount < 0) {   // if left count is zero and there is valid vehicles, enable rent anyway.
                    $arrData['result'] = \frontend\components\ApiModule::CODE_CAR_ALREADY_BEEN_RENT;
                    $arrData['desc'] = \Yii::t('locale', 'Sorry, vehicle already rented!');
                    break;
                }
            }

            // 异店还车费和送车上门费用，上门取车费用
            // $arrCalcResult = $objFormData->calculateCarDeliveryServicePrice();
            // if ($arrCalcResult[0] != 0) {
            //     $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
            //     $arrData['desc'] = $arrCalcResult[1];
            //     break;
            // }

            /*echo "<pre>";
            print_r($priceOnlineInfo);
            echo "</pre>";die;*/

            //验证驾照时间格式
            if (!$objFormData->validate()) {
                $errText = $objFormData->getErrorDebugString();
                \Yii::error("Order api validate form data failed with error:{$errText}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;//3001
                $arrData['desc'] = \Yii::t('locale', 'Validate the input failed!')." {$errText}";//验证提交信息失败！ 属性 领驾照时间 的格式无效。 属性 驾照到期时间 的格式无效。
                break;
            }

            $curDayStart = strtotime(date('Y-m-d'));
            if ($objFormData->start_time < $curDayStart) {
                \Yii::error("Order api validate order start_time:{$objFormData->start_time} that earlier than current day start time:{$curDayStart}", 'order');
                $arrData['result'] = \frontend\components\ApiModule::CODE_INVALID_PARAMETER;
                $arrData['desc'] = \Yii::t('carrental', 'Order start time should not earlier than today.');
                break;
            }


            // $arrDeltaData['price'] = $this->total_amount - $originTotalPrice;
            // $arrDeltaData['now_overtime_price'] = $this->price_overtime;
            // // $arrDeltaData['optional_service'] = $this->price_optional_service - $originServicePrice;
            // $arrDeltaData['optional_service'] = $this->price_optional_service;

            // //sjj其他费用:加油费+车损费+违章费用+超时保费+加油代办价格+其他价格+个人自驾超时费用
            // $arrDeltaData['other_price'] = $this->price_oil + $this->price_car_damage + $this->price_violation + $this->price_insurance_overtime + $this->price_oil_agency + $this->price_other/* + $this->price_overtime*/;
            // $arrDeltaData['price_preferential'] = $this->price_preferential ;
            // $arrDeltaData['price_poundage'] = $this->price_poundage ;//手续费
            // $arrDeltaData['unit_price_basic_insurance'] = $this->unit_price_basic_insurance ;//基本保险费
            // $arrDeltaData['price_different_office'] = $this->price_different_office ;//异店还车费
            // $arrDeltaData['price_take_car'] = $this->price_take_car ;//送车上门服务费
            // $arrDeltaData['price_return_car'] = $this->price_return_car ;//上门取车服务费

            $objFormData->total_amount  = $objFormData->price_rent+$objFormData->price_take_car+$objFormData->price_return_car+$objFormData->price_address_km;
            // echo "<pre>";
            // print_r($objFormData);
            // echo "</pre>";die;
            $objOrder = new \common\models\Pro_vehicle_order();
            if (!$objFormData->save($objOrder)) {
                $arrData['result'] = \frontend\components\ApiModule::CODE_ERROR;
                $arrData['desc'] = \Yii::t('locale', 'Create order failed!');
                break;
            }

            $objOrder->setSerialNo();
            // $objOrder->onUpdateEndTime($objOrder->new_end_time);
            // $objOrder->onUpdateOneWayEndTime($objOrder->new_end_time);
            // $objOrder->setOptionalServices($arrServicePriceObjects);
            // $objOrder->getDailyRentDetailedPriceArray();
            // $objOrder->calculateTotalPrice();//万元无忧

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
                /*\common\components\SmsComponent::send($objOrder->customer_telephone, \common\components\Consts::KEY_SMS_ORDER_BOOKED_BY_APP, ['CNAME'=>$objOrder->customer_name, 
                    //'AUTOMODEL'=>$objVehicleModel->getHumanDisplayText(),
                    //'USETIME'=>  date('Y-m-d H:i', $objOrder->start_time),
                    //'SHOPADDRESS'=>$objOrder->getTakeCarAddressText(),
                    //'SHOPTELEPHONE'=>$objOffice ? $objOffice->telephone : '',
                    'ORDERID'=>$objOrder->serial,
                    'PRICESTANDARD'=>$objOrder->total_amount,
                ]);*/
            }

            $orderData = self::getOneWayOrderAttributes($objOrder, true, [$objOrder->vehicle_model_id=>$objVehicleModel], $arrOfficeNames);

            foreach ($orderData as $k => $v) {
                $arrData[$k] = $v;
            }
            // echo "<pre>";
            // print_r($objOrder);
            // echo "<hr>";
            // // print_r($objFormData);
            // echo "</pre>";

        }while (0);
        return $arrData;
    }

    public static function getOneWayOrderAttributes($objOrder, $isFillSerList = false, $arrVehicleModels = null, $arrOfficeNames = null) {
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
        
        $rentTimeData = \common\models\Pri_renttime_data::createTime($objOrder->start_time, $objOrder->new_end_time);
        
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
            'status' => $objOrder->status,
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
        $orderData['price_address_km'] = $objOrder->price_address_km;
        if ($objOrder->status <= \common\models\Pro_vehicle_order::STATUS_BOOKED && $objOrder->paid_amount > 0) {
            $orderData['status'] = \common\models\Pro_vehicle_order::STATUS_PAID;
        }
        if ($isFillSerList) {
            $arrSerlist = [];
            $arrSerNames = \common\components\OrderModule::getOptionalServiceNameArray();
            $arrOptionalPrices = $objOrder->getOptionalServicePriceArray();
            /*foreach ($arrOptionalPrices as $k => $v) {
                $arrSerlist[] = ['ser_id'=>$k, 'ser_price'=>floatval($v['price']), 'ser_count'=>$v['count'], 'ser_name'=>(isset($arrSerNames[$k]) ? $arrSerNames[$k] : '')];
            }*/
            // $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_POUNDAGE, 'ser_price'=>floatval($objOrder->price_poundage), 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Poundage')];
            // $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_BASIC_INSURANCE, 'ser_price'=>floatval($objOrder->price_basic_insurance)/floatval($objOrder->rent_days), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Basic insurance')];
            /*if ($rentTimeData->hours > 0) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_OVERTIME, 'ser_price'=>floatval($objOrder->unit_price_overtime), 'ser_count'=>$rentTimeData->hours, 'ser_name'=>\Yii::t('carrental', 'Overtime service fee')];
            }*/
            if (floatval($objOrder->price_designated_driving)) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING, 'ser_price'=>floatval($objOrder->unit_price_designated_driving), 'ser_count'=>$objOrder->rent_days, 'ser_name'=>\Yii::t('locale', 'Designated driving')];
            }
            //异店还车费
            /*if (floatval($objOrder->price_different_office)) {
                $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME, 'ser_price'=>floatval($objOrder->price_different_office), 'ser_count'=>1, 'ser_name'=>\Yii::t('carrental', 'Fee of different shop return car')];
            }*/
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
            // 租车费用
            // if (floatval($objOrder->price_rent)) {
                $arrSerlist[] = ['ser_id'=>8, 'ser_price'=>floatval($objOrder->price_rent),'isoption'=>0, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', 'Rent')];
            // }
            //if ($objOrder->unit_price_designated_driving_overtime) {
            //    $arrSerlist[] = ['ser_id'=>\common\models\Pro_service_price::ID_DESIGNATED_DRIVING_OVERTIME, 'ser_price'=>$objOrder->unit_price_designated_driving_overtime, 'ser_count'=>1, 'ser_name'=>\Yii::t('locale', '{type} overtime price', ['type'=>\Yii::t('locale', 'Designated driving')])];
            //}
            $orderData['ser_list'] = $arrSerlist;
        }

        return $orderData;
    }



}
