/**
 * Created by Administrator on 2017/11/18.
 */

app.controller('carRantCtr', function ($scope) {
    $(function () {
        $scope.pinpaiList=[];
        $scope.chexingList=[];
        $scope.chexingListTop=[];
        //打包价判断
        $scope.packFunction = function(){
            return $('#packFunt').text();

        }();
        //console.log(55,'sd',$scope.packFunction)
        //监听上门取还车地址信息
        $scope.takeDataAddress
        $scope.returnDataAddress
        $scope.$watch('takeDataAddress', function (newValue,oldValue) {
            //console.log(newValue,oldValue)
        })
        $scope.$watch('returnDataAddress', function (newValue,oldValue) {
            //console.log(newValue,oldValue)
        })
        //--------------------------------------
        //----------------查询按钮--------------
        //--------------------------------------


        //地址栏参数获取
        function GetQueryString(name) {
            var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
            var r = window.location.search.substr(1).match(reg);
            if(r!=null)return  unescape(r[2]); return null;
        }
        function mrShowCar_pinpai(){
            setTimeout(function () {
                $('li[pinpai='+ GetQueryString('pinpai')+']').click();
                //console.log( $('li[pinpai='+ GetQueryString('pinpai')+']').length);
            },200)
        }
        //--------------------------------------
        //----------------取车信息--------------
        //--------------------------------------
        $scope.takeInfo={};
        $scope.getTakeInfo= function(zuPack){
            var qucheTime = $('input[name=takeDate]').val()+' '+$('input[name=takeTime]').val();
            var timeForQuche = Date.parse(new Date(qucheTime));
            timeForQuche /=1000;
            if(zuPack === 'duanzu'){
                var haicheTime = $('input[name=returnDate]').val()+' '+$('input[name=returnTime]').val();
                var timeForHaiche = Date.parse(new Date(haicheTime));
                timeForHaiche /=1000;
            }else if(zuPack === 'yuezu'){
                timeForHaiche=timeForQuche+2592000;
                //console.log(zuPack)
            }else if(zuPack === 'zhouzu'){
                timeForHaiche=timeForQuche+604800;
                //console.log(zuPack)
            }else  if(zuPack === 'santianzu'){
                timeForHaiche=timeForQuche+259200;
                //console.log( zuPack )
            }else{
                //console.log(zuPack)
            }


            $scope.takeInfo.takeTime =timeForQuche;
            $scope.takeInfo.returnTime =timeForHaiche;
            $scope.takeInfo.takeSid = $scope.takeSid;
                if($scope.takeCar){
                    $scope.takeInfo.isTakeCallAddress = 1;
                }else{
                    $scope.takeInfo.isTakeCallAddress = 0;
                }
            $scope.takeInfo.takeAddress = $('b[name=takeAddressXX]').text()

            $scope.rentDay =($scope.takeInfo.returnTime - $scope.takeInfo.takeTime)/86400;
            $scope.rentDay = $scope.rentDay%1>0.208?($scope.rentDay-$scope.rentDay%1+1):parseInt($scope.rentDay);
            //console.log($scope.rentDay);
            //$scope.$apply();
            //--------------------------------------
            //----------------还车信息--------------
            //--------------------------------------

            $scope.returnInfo={};
                //console.log($scope.returnCar);
                if($scope.returnCar){
                    $scope.returnInfo.isReturnCallAddress = 1;
                }else{
                    $scope.returnInfo.isReturnCallAddress = 0;
                }
                //console.log("****7777****")
                $scope.returnInfo.returnSid = $scope.returnSid;
                $scope.returnInfo.returnAddress = $('b[name=returnAddressXX]').text()
                //console.log($scope.returnInfo.returnAddress,$scope.returnInfo.isReturnCallAddress);

            //console.log(    $scope.takeInfo.takeAddress, $scope.takeInfo.returnAddress,"**********")
        }
    $('div.pop.pop-time').find('div.pop-body ul').on('click','li',function (){$scope.getTakeInfo($scope.packFunction);$scope.$apply()})
        $scope.getTakeInfo($scope.packFunction)



       //
       //$scope.$watch('takeInfo',function(){
       //  console.log($scope.takeInfo)
       // })


        //测试
        //start_time:                                                          //取车时间
        //end_time:                                                             //还车时间
        //car_id:                                                                //车系ID
        //days:                                                                  //租车天数
        //price_type:                                                            //支付方式  在线支付固定 1
        //return_sid:                                                            //还车门店
        //sid:                                                                   //取车门店
        //time:                                                                  //当前时间
        //address_take_car:                                                      //取车地址
        //address_return_car:                                                    //还车地址

        //判断是否为空对象
        function isObjectNull(obj){
            for( var key in obj){
                return false;
            }
            return true;
        }
        $scope.getInfoFortest=function(car_id,price_type){
            switch(price_type)
            {
                case 4:
                    $scope.rentDay = 7;
                    break;
                case 2:
                    $scope.rentDay = 3;
                    break;
                case 5:
                    $scope.rentDay = 30;
            }
            if(price_type == 2){
                $scope.takeInfo.returnTime = $scope.takeInfo.takeTime + 259200
            }
            if(price_type == 4){
                $scope.takeInfo.returnTime = $scope.takeInfo.takeTime + 604800
            }
            var rentData = {
                "start_time": $scope.takeInfo.takeTime,
                "end_time":$scope.takeInfo.returnTime,
                "car_id": car_id,
                "days":$scope.rentDay,
                "price_type": price_type,
                "return_sid": $scope.returnInfo.returnSid,
                "sid": $scope.takeInfo.takeSid,
                "time": Date.parse(new Date()) / 1000,
                "isTakeCarAddress":$scope.takeInfo.isTakeCallAddress,
                "address_take_car": $scope.takeInfo.takeAddress,
                "isReturnCarAddress": $scope.returnInfo.isReturnCallAddress,
                "address_return_car": $scope.returnInfo.returnAddress
            }
            //console.log(rentData,999999999);
            sessionStorage.setItem("rentData",JSON.stringify(rentData));
            window.location.href="../orderProcess/confirmOrder.html"
        }



        //车辆列表获取
        $scope.getCarListsRR = function (zuPack) {

            var zuPack = zuPack;
            $scope.getTakeInfo(zuPack)
            function getCarlistNow(){
                //console.log($scope.fujinMenDianData,'======');
                $scope.takeInfo.takeAddress;
                $scope.takeInfo.takeSid;
                $scope.kefouZuChe = true;
                if( $scope.takeCar && $scope.fujinMenDianData.sress == 0){
                    alert($scope.fujinMenDianData.desc);
                    $scope.kefouZuChe = false;
                    $scope.$apply()
                }else {
                    //console.log(  $scope.takeInfo.takeTime,  $scope.takeInfo.returnTime,$scope.takeInfo.takeSid, $scope.takeInfo.returnSid)
                    //console.log($scope.takeInfo, '3333333333333333333');
                    $.ajax({
                        type: 'POST',
                        url: $scope.https+"/app/carrental/frontend/web/index.php/porder/rental_model_car_list",
                        data: {
                            sid: $scope.takeInfo.takeSid,
                            //_csrf:csrfToken
                            isTakeCarAddress: $scope.takeInfo.isTakeCallAddress,
                            address_take_car: $scope.takeInfo.takeAddress,
                            take_car_time: $scope.takeInfo.takeTime,
                            return_car_time: $scope.takeInfo.returnTime
                        },
                        dataType: "json",
                        success: function (data) {
                            $scope.carListsShort = data.car_list;
                            //console.log($scope.carListsShort);
                            //console.log($scope.carListsShort[0].car_type);
                            for(i=0;i<$scope.carListsShort.length;i++){
                                $scope.carListsShort[i].carShowTypeNew = false;
                                $scope.carListsShort[i].carShowTypeHot = false;
                                for(key in $scope.carListsShort[i].car_type){
                                    if($scope.carListsShort[i].car_type[key] == 2){
                                        $scope.carListsShort[i].carShowTypeNew = true;
                                    }else if($scope.carListsShort[i].car_type[key] == 4){
                                        $scope.carListsShort[i].carShowTypeHot = true;
                                    }
                                }
                            }
                            //console.log($scope.carListsShort);
                            $scope.$apply();
                            //console.log($scope.carListsShort, 666, $scope.carListsShort[1].car_name);
                            //console.log($scope.takeInfo)
                            $('.list-car').show();
                            mrShowCar_pinpai()
                            mrShowCar_pinpai = function () {
                                
                            };

                        }
                    });

                }

            }
            var nowTime =  Date.parse(new Date());
            if($scope.rentDay<1){
                alert("租车天数不能少于1天！");
                $scope.kefouZuChe = false;
            }else if($scope.takeInfo.takeTime <= nowTime/1000){
                alert('取车时间不能早于当前时间');
                $scope.kefouZuChe = false;
            }else if($scope.takeInfo.returnTime<$scope.takeInfo.takeTime){
                alert('还车时间不能早于取车时间！');
                $scope.kefouZuChe = false;
            }else if((!$('input[name="takeStore"]').hasClass('ng-hide')&&$('input[name="takeStore"]').val() == '本城市暂无租车点，可以选择加盟易卡') || (!$('input[name="returnStore"]').hasClass('ng-hide')&&$('input[name="returnStore"]').val() =='本城市暂无租车点，可以选择加盟易卡')){
                alert('请选择正确的租还车门店地址!');
                $scope.kefouZuChe = false;
            }else if((!$('input[name="takeStore"]').hasClass('ng-hide')&&$('input[name="takeStore"]').val() == '')||(!$('input[name="returnStore"]').hasClass('ng-hide')&&$('input[name="returnStore"]').val() =='')) {
                alert('请输入租还车门店！')
            }else if($('input[name="takeStore"]').hasClass('ng-hide')){
                if(!$("input[name='takeAddress']").val()){
                    alert('请选择送车上门地址!')
                    $scope.kefouZuChe = false;
                }else{
                    $scope.takeInfo.takeSid =$scope.fujinMenDianData.sid;
                    getCarlistNow();
                }
            }else if($('input[name="returnStore"]').hasClass('ng-hide')){
                if(!$("input[name='returnAddress']").val()){
                    alert('请选择上门取车地址!')
                    $scope.kefouZuChe = false;
                }else{
                    getCarlistNow();
                }
            }else {

                getCarlistNow();
            }
            if(!$scope.kefouZuChe){
                btnOck?btnOck():0
            }else{
                getCarlistNow();
            }
            //console.log($scope.kefouZuChe);
            //console.log($scope.fujinMenDianData);
            //console.log('97779999999999999999999999999999999999999')

        }
      //$scope.getCarListsRR($scope.packFunction);
        //$scope.carListsShort = [{car_id: 2,car_image: "http://gm.yikazc.com:8010/public/upload/d5489910/2-1510120182-1.png",car_mode: 8,car_name: "别克GL8（2017款）",car_type: Array(1),carriage: 2,consume: "2.4L",gearboxmode: "2",left: 1,price_3days: 528,price_month: 326,price_online: 545,price_shop: 550,price_week: 510,property_text: "2.4L|自动|两厢|7座",seat: 7}];





        //车型
        $.ajax({
            type: 'get',
            url: $scope.https+"/app/carrental/frontend/web/index.php/papi/getallflag",
            dataType: "json",
            success: function (data) {
                $scope.chexingList =[];
                $scope.chexingListTop=[];
                //console.log(data,8890)
                for(var i = 0;i<data.flags.length;i++){
                    $scope.chexingList.push(data.flags[i])
                }
                for(var i = 3;i<data.flags.length;i++){
                    $scope.chexingListTop.push( $scope.chexingList[i])
                }

                //console.log($scope.chexingList,7777, $scope.chexingListTop)
                $scope.$apply();
            }
        });
        //车品牌
        $.ajax({
            type: 'post',
            url: $scope.https+"/app/carrental/frontend/web/index.php/papi/getallbrand",
            dataType: "json",
            success: function (data) {
                $scope.pinpaiList = [];
                //console.log(data,8890)
                for(i=0;i<data.brand.length;i++){
                    $scope.pinpaiList.push(data.brand[i])
                }
                //console.log($scope.pinpaiList);
                $scope.$apply();
                //选中相应车品牌

            }
        });


        //---------------------------------------
        //--------------热门车型默认-------------
        //---------------------------------------

        //setTimeout(function () {
        //    $('li[pinpai='+ GetQueryString('pinpai')+']').click();
        //    console.log( $('li[pinpai='+ GetQueryString('pinpai')+']').length);
        //},1500)













    })



});



