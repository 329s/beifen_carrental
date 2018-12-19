/**
 * Created by Administrator on 2017/12/14.
 */

app.controller('myOrderCtr', function ($scope, $http) {
    $scope.getLocalTime =function(now) {
        now = now * 1000;
        //console.log(now, 78987)
        now = new Date(now);
        //console.log(now, 78987)
        var year = now.getFullYear();
        //console.log(year);
        var month = now.getMonth() + 1;
        var date = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var secrt = now.getSeconds();
        return year + "-" + month + "-" + date + " " + hour + ":" + minute;
    }
    //console.log($scope.login)
        $scope.loginPd();
        $scope.changeInfo ;
        $scope.myOrders = [];
        $scope.myOrder_fenye = [];
        $scope.getOrderlist_myyk = function (page, status) {

            $scope.fy_ym = page;
            $http.get('http://www.yikazc.com/app/carrental/frontend/web/index.php/porder/get_order_list?page=' + page + '&rows=5&status=' + status).success(
                function (data, status, config, headers) {

                    $scope.myOrders = data;
                    //console.log(data, $scope.myOrders, Math.ceil($scope.myOrders.count / 5));
                    var k = Math.ceil($scope.myOrders.count / 5);
                    $scope.myOrder_fenye = [];
                    for (i = 0; i < k; i++) {
                        $scope.myOrder_fenye.push({"index": i});
                    }
                }
            )

        }

        //分页
        $scope.getOrderlist_myyk(1, " ");
        $scope.getOrder_fy = function (ym) {
            $scope.fy_ym = ym;
            var status = $('.order_type').attr('data-typeOd');
            $http.get('http://www.yikazc.com/app/carrental/frontend/web/index.php/porder/get_order_list?page=' + ym + '&rows=5&status=' + status).success(
                function (data, status, config, headers) {
                    $scope.myOrders = data;
                    //console.log(data, $scope.myOrders, Math.ceil($scope.myOrders.count / 5));
                    var k = Math.ceil($scope.myOrders.count / 5);
                    $scope.myOrder_fenye = [];
                    for (i = 0; i < k; i++) {
                        $scope.myOrder_fenye.push({"index": i});
                    }
                }
            )
        }
        $scope.isThisPage = function (ym) {
            return $scope.fy_ym === ym;
        }


        //获取订单追踪
        $scope.getOrderGz = function (order_id) {
            $http.get('http://www.yikazc.com/app/carrental/frontend/web/index.php/porder/order_change_way?order_id='+order_id).success(
                function (data, status, config, headers) {
                    //console.log(data)
                    if(data.result){
                        alert(data.desc);
                    }else {
                        $scope.orderGzList = data.Pro_vehicle_order_change_log;
                    }
                }
            )

        }
        //获取信息
        //http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/puser/getinfo
        function getInfo(){
            $http.get(
                "http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/getinfo"
            ).success(function (data, status, config, headers) {
                //console.log(data);
                if(data.result==0){
                    $scope.userInfo = data;
                }else if(data.result ==1005){
                    //console.log(777,window.location.href);
                    alert(data.desc );
                    if(!$('.ak47m4a1')[0]){
                        window.location.href = "myInformation.html"
                    }
                }else {
                    alert(data.desc);
                    window.location.href = '../login/login.html';
                }

            })
        }
        getInfo();

        $scope.changeInfoFn= function () {
            function changeInfo_user(obj){
                $.ajax({
                    url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/editinfo",
                    //url:"http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/ptest/te",
                    //url:"http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/puser/login",
                    //url:"http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/ptest/login",
                    type:"post",
                    data :obj,
                    dataType:"json",
                    contentType:"application/x-www-form-urlencoded",
                    success:function( data  ){
                        console.log(data)
                        if(data.result!=0){
                            alert(data.desc);
                        }else {
                            alert('修改成功！');
                            location.reload();
                        }
                    },
                });
            }
            console.log($scope.changeInfo);
            if($scope.changeInfo){
                if(!$scope.changeInfo.name){
                    alert("请输入正确的姓名！");

                }else
                if(!$scope.changeInfo.card_type){
                    alert("请选择您的证件类型！");

                }else
                if(!$scope.changeInfo.card_id){
                    alert("请确认证件号码是否正确！");

                }else
                if(!$scope.changeInfo.phone){
                    alert("请确认手机号码是否正确！");

                }else
                if(!$scope.changeInfo.email){
                    alert('请输入正确的电子邮箱！');

                }
                //else
                //if(!$scope.changeInfo.emergency_contact){
                //    alert('请输入正确的紧急联系人姓名！');
                //
                //}else
                //if(!$scope.changeInfo.emergency_telephone){
                //    alert('请输入正确的紧急联系人电话！');
                //
                //}else
                //if(!$scope.changeInfo.home_address){
                //    alert('请输入紧急联系人地址！');
                //    return false;
                //}
                else{
                    changeInfo_user($scope.changeInfo)
                }
            }else {
                alert('请填写相关信息！')
            }



        }



    $scope.gotoPayAli= function (orderid) {
        var urlAdd = "../orderProcess/payOrder.html?orderid="+orderid;
        window.location.href= urlAdd;
    }






})