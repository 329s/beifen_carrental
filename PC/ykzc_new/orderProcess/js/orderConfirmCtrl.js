/**
 * Created by Administrator on 2017/11/25.
 */
//function getLocalTime(nS) {
//    return new Date(parseInt(nS) * 1000).toLocaleString().replace(/\//g, "-").replace(/上午/g, " ").replace(/下午/g," ");
//
//}
function getLocalTime(now) {
    now = now *1000;
    //console.log(now,78987)
     now = new Date(now);
    //console.log(now,78987)
    var year=now.getFullYear();
    //console.log(year);
    var month=now.getMonth()+1;
    var date=now.getDate();
    var hour=now.getHours();
    var minute=now.getMinutes();
    return year+"-"+month+"-"+date+" "+hour+":"+minute;
}

app.controller('carRantCtr', function ($scope,$http) {
    $scope.rentCarsInfo;                   //租车信息
    $scope.gudingPrice=[];                     //固定服务费
    $scope.kexuanPrice=[];                     //可选服务费
    $scope.kexuanPriceHeji = 0;
    $scope.dingdanZongjia = 0;
    $scope.isPackPriceTime;
    $scope.kexuanPriceIsChecked= function () {
        $scope.kexuanPriceHeji = 0;
        for(i=0;i<$('#kexuanPriceBox').find('input[type=checkbox]').length;i++){
            if($($('#kexuanPriceBox').find('input[type=checkbox]')[i]).is(':checked')){

            }else{
                $scope.kexuanPriceHeji+=parseInt( $($('#kexuanPriceBox').find('input[type=checkbox]')[i]).siblings('span').find('b').text())
            }
        }
        $scope.dingdanZongjia = $scope.rentCarsInfo.total_price-  $scope.kexuanPriceHeji*$scope.rentCarsInfo.wywyDays;
    }
    //获取车辆配置信息
    //http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/neworder/vehicle_model_price_detail
   $scope.getCarConfig = function(){
       //console.log($scope.rentCarsInfo.car.car_id,$scope.rentCarsInfo.store.sid)
       var postCfg = {
           headers: {'Content-Type': 'application/x-www-form-urlencoded'},
           transformRequest: function (data) {
               $scope.carConfigList = response.carModelAllInfo;
               return $.param(data);
           }
       };
       //$http.post(
       //    url = "http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/porder/vehicle_model_detail",
       //    data = {
       //        "vehicle_model_id ":50
       //        //"vehicle_model_id ":$scope.rentCarsInfo.car.car_id
       //    },
       //    postCfg
       //).success(function (response) {
       //    //$scope.carConfigList = response.carModelAllInfo;
       //    console.log(response)
       //});

       $http.get(
           $scope.https+"/app/carrental/frontend/web/index.php/porder/vehicle_model_detail?vehicle_model_id="+$scope.rentCarsInfo.car.car_id
       ).success(function (data) {
           $scope.carConfigList = data.carModelAllInfo;
           //console.log(data)
       });
   }





    //console.log($scope.login)

    //获取价格ID列表
    function getChecked(){
        var  priceCheckeds = [];
        for(i=0;i<$('#kexuanPriceBox').find('input[type=checkbox]').length;i++){
            if($($('#kexuanPriceBox').find('input[type=checkbox]')[i]).is(':checked')){
                //console.log($($('#kexuanPriceBox').find('input[type=checkbox]')[i]).attr('data-serid'));
                priceCheckeds.push($($('#kexuanPriceBox').find('input[type=checkbox]')[i]).attr('data-serid'))
            }
        }
        if( priceCheckeds.length ){
            priceCheckeds ="1|2|" + priceCheckeds.join("|");
        }else{
            priceCheckeds = "1|2"
        }
        return priceCheckeds;
    }
    ////1、先定义一个空数组
    //$scope.kexuanPriceSelected = [];
    ////2、把选中的那条数据push到数组中
    //$scope.kexuanPriceSelected.push(Item.Id);
    ////3、ng-checked根据数据索引返回的boolean进行判断是否显示并判断显示哪一条
    //$scope.isSelected = function(id){
    //    return $scope.kexuanPriceSelected.indexOf(id) >= 0;
    //}

   function getRentCarsInfo(){
       var rentData =  JSON.parse(sessionStorage.getItem("rentData"));
       //rentData = JSON.stringify(rentData);
       //console.log(rentData,'asdsadasdsadsadsd999')
        //if(!rentData.sid){
        //    rentData.sid = "20";
        //}
        if(!rentData||rentData == null){
            alert('信息有误，请重新操作！');
            window.location.href = "../index/index.html";
        }




       //要通过post传递的参数
       var data =rentData,
       //post请求的地址
           url = $scope.https+"/app/carrental/frontend/web/index.php/porder/porder_preview",
       //将参数传递的方式改成form
           postCfg = {
               headers: {'Content-Type': 'application/x-www-form-urlencoded'},
               transformRequest: function (data) {
                   return $.param(data);
               }
           };
       //发送post请求，获取数据
       $http.post(url, data, postCfg)
           .success(function (response) {
               if(response.result == 0 ){
                   response.start_time = getLocalTime(response.start_time);
                   response.end_time =  getLocalTime(response.end_time);
                   $scope.rentCarsInfo = response;
                   for(i=0;i<$scope.rentCarsInfo.ser_list.length;i++){
                       if( $scope.rentCarsInfo.ser_list[i].isoption == 1){
                           $scope.kexuanPrice.push( $scope.rentCarsInfo.ser_list[i])
                           $scope.kexuanPrice[i].isChecked = true;
                           //$scope.dingdanZongjia +=  $scope.rentCarsInfo.ser_list[i].ser_price;
                           //$scope.dingdanZongjia  += $scope.rentCarsInfo.ser_list[i].ser_price;
                       }else if ($scope.rentCarsInfo.ser_list[i].isoption == 0){
                           $scope.gudingPrice.push( $scope.rentCarsInfo.ser_list[i])

                       }
                   }
                   var end = response.end_time,start = response.start_time;
                   //console.log(response.end_time,777)
                   end = Date.parse(new Date(end))/1000;
                   start = Date.parse(new Date(start))/1000;

                   $scope.rentCarsInfo.rentDays = (end - start)/86400%1>0.208?((end - start)/86400-(end - start)/86400%1+1):parseInt((end - start)/86400);
                   //console.log(end,'---777---777---',start,$scope.rentCarsInfo.rentDays);
                   $scope.rentCarsInfo.wywyDays =   $scope.rentCarsInfo.rentDays%30>10?10+Math.floor($scope.rentCarsInfo.rentDays/30)*10:$scope.rentCarsInfo.rentDays%30+Math.floor($scope.rentCarsInfo.rentDays/30)*10
                   $scope.dingdanZongjia=$scope.rentCarsInfo.total_price -  $scope.dingdanZongjia*$scope.rentCarsInfo.wywyDays;
                   //console.log(response,'←返回值')
               }else{
                   alert(response.desc);
                   if(response.result == 1005){
                       window.location.href = "../myYK/myInformation.html";
                   }else  if(response.result==1004){
                       window.location.href = "../login/login.html";
                   }else {
                       window.location.href = "../book/shortOrder.html";
                   }
               }

               //console.log(response);

               getIsActive();

           });
       //http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/papi/check_order_time
     function getIsActive(){
         //console.log(rentData);
        if(rentData.price_type!=3){
            $.ajax({
                type: 'get',
                url: $scope.https+"/app/carrental/frontend/web/index.php/papi/check_order_time?start_time="+rentData.start_time+"&end_time="+ rentData.end_time,
                dataType: "json",
                success: function (data) {
                    //console.log(data);
                    $scope.isPackPriceTime = data;
                    //console.log(isPackPriceTime,'-6-6-6-6-6-6-6-6-6-6-');
                    $scope.priceType =  $scope.rentCarsInfo.price_type;
                    //console.log($scope.priceType,'-7-7-7-7-7-7-7-7-7-')
                }
            });
        }else {
        }
     }

       //$http({
       //    method: 'post',
       //    //data:rentData,
       //    data:"{sid:20}",
       //    url: "http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/porder/porder_preview"
       //}).success(function(data,status){
       //    //data.start_time = getLocalTime(data.start_time);
       //    //data.end_time =  getLocalTime(data.end_time);
       //    //$scope.rentCarsInfo = data;
       //    //for(i=0;i<$scope.rentCarsInfo.ser_list.length;i++){
       //    //    if( $scope.rentCarsInfo.ser_list[i].isoption == 1){
       //    //         $scope.kexuanPrice.push( $scope.rentCarsInfo.ser_list[i])
       //    //         $scope.kexuanPrice[i].isChecked = true;
       //    //        $scope.dingdanZongjia +=  $scope.rentCarsInfo.ser_list[i].ser_price;
       //    //    }else if ($scope.rentCarsInfo.ser_list[i].isoption == 0){
       //    //        $scope.gudingPrice.push( $scope.rentCarsInfo.ser_list[i])
       //    //
       //    //    }
       //    //}
       //    //$scope.dingdanZongjia+=$scope.rentCarsInfo.total_price;
       //    console.log(data,"sdsdsdsdsd")
       //})
   }
    function tests(){
        $http({
            method:"post",
            url:$scope.https+"/app/carrental/frontend/web/index.php/papi/service_price"
        }).success(function (data,status) {
        })
    }

    getRentCarsInfo();
    tests();

    //提交订单
    $scope.putOrder = function (isPack) {
        if(isPack == 1){
            alert('您所选择的时间不在打包期间以内按短租计算，三天打包价仅限租赁时间在周日下午4点至周四下午4点之间的订单！');
            return false;
        }
        //console.log(
        //    "start_time",$scope.rentCarsInfo.start_time,
        //    "end_time",$scope.rentCarsInfo.end_time,
        //    "days",$scope.rentCarsInfo.rentDays,
        //    "price_type",1,
        //    "return_sid",$scope.rentCarsInfo.re_store.sid,
        //    "ser_list", getChecked(),
        //    "sid",$scope.rentCarsInfo.store.sid,
        //    "address_take_car",$scope.rentCarsInfo.take_car_addr||"",
        //    "address_return_car",$scope.rentCarsInfo.return_car_addr||"",
        //    "isTakeCarAddress",$scope.rentCarsInfo.isTakeCarAddress,
        //    "isReturnCarAddress",$scope.rentCarsInfo.isReturnCarAddress,
        //    "time",Date.parse(new Date())/1000
        //)
        var postCfg = {
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            transformRequest: function (data) {
                return $.param(data);
            }
        };
        $http.post(
            url = $scope.https+"/app/carrental/frontend/web/index.php/porder/porder",
            data = {
                "car_id":$scope.rentCarsInfo.car.car_id,
                "start_time":Date.parse(new Date($scope.rentCarsInfo.start_time))/1000,
                "end_time":Date.parse(new Date($scope.rentCarsInfo.end_time))/1000,
                "days":$scope.rentCarsInfo.rentDays,
                "price_type":3,
                "return_sid":$scope.rentCarsInfo.re_store.sid,
                "ser_list": getChecked(),
                "sid":$scope.rentCarsInfo.store.sid,
                "address_take_car":$scope.rentCarsInfo.take_car_addr||"",
                "address_return_car":$scope.rentCarsInfo.return_car_addr||"",
                "isTakeCarAddress":$scope.rentCarsInfo.isTakeCarAddress,
                "isReturnCarAddress":$scope.rentCarsInfo.isReturnCarAddress,
                "time":Date.parse(new Date())/1000
            },
            postCfg
        ).success(function (response) {
            if(response.result){
                alert(response.desc)
            }else {
                sessionStorage.setItem("orderid",response.order_id);
                var urlAdd = "payOrder.html?orderid="+response.order_id;
               //alert(urlAdd);
                window.location.href =urlAdd;
            }
                //console.log(response)
            });
    }


    
    
    
    
    
    
    
    
    
    
    
    

})