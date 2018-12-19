
/**
 * Created by Administrator on 2017/8/21.
 */
app.controller('cancelledOrder',function($scope,$http,urlService,$timeout,$interval,commonService){
    //http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/porder/cancel_order  取消订单
    //http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/porder/order_detail  获取订单
    if( !$scope.login){

        //document.write("");
        //alert("您尚未登录，请登录或注册账号登录后再试！")

        //window.location.href="../login/login.html";


        //console.log( $scope.userExit)
        function getLocalTime(now) {
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
            return year + "-" + month + "-" + date + " " + hour + ":" + minute;
        }

        $scope.orderXq;
        function UrlSearch() {
            var name, value;
            var str = location.href; //取得整个地址栏
            var num = str.indexOf("?")
            str = str.substr(num + 1); //取得所有参数   stringvar.substr(start [, length ]

            var arr = str.split("&"); //各个参数放到数组里
            for (var i = 0; i < arr.length; i++) {
                num = arr[i].indexOf("=");
                if (num > 0) {
                    name = arr[i].substring(0, num);
                    value = arr[i].substr(num + 1);
                    this[name] = value;
                }
            }
        }

        var Request = new UrlSearch(); //实例化
        //删除订单
        $scope.cancel_order = function (orderid) {
            $http.get($scope.https+'/app/carrental/frontend/web/index.php/porder/cancel_order?order_id=' + orderid).success(
                function (data, status, config, headers) {
                    //console.log(data)
                    if (data.result) {
                        alert(data.desc);
                        window.location.href = '../myYk/myYK.html'
                    } else {
                        alert(data.desc)
                        window.location.href = '../myYk/myYK.html'
                    }
                }
            )
        }
        //获取订单
        function getOrderXq(orderId) {
            $http.get($scope.https+'/app/carrental/frontend/web/index.php/porder/order_detail?order_id=' + orderId).success(
                function (data, status, config, headers) {
                    //console.log(data)
                    $scope.orderXq = data;
                    $scope.orderXq.start_time = getLocalTime($scope.orderXq.start_time);
                    $scope.orderXq.end_time = getLocalTime($scope.orderXq.end_time);
                }
            )
        }

        getOrderXq(Request.orderid);

        //获取车辆配置信息
        $scope.getCarConfig = function (carid) {
            $http.get(
                $scope.https+"/app/carrental/frontend/web/index.php/porder/vehicle_model_detail?vehicle_model_id=" + carid
            ).success(function (data) {
                $scope.carConfigList = data.carModelAllInfo;
                //console.log(data)
            });
        }


    }



})

