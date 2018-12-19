
/**
 * Created by Administrator on 2017/8/21.
 */
app.controller('payOrder',function($scope,$http,urlService,$timeout,$interval,commonService){

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

    $scope.goOrderXQ= function () {

        var urlAdd = "orderXq.html?orderid="+Request.orderid;
        sessionStorage.setItem("orderid",Request.order_id);
        window.location.href= urlAdd;
    }

    $scope.gotoPay= function () {
             //alert(Request.orderid)
            var urlAdd = $scope.https+"/app/carrental/frontend/web/index.php/ppayment/order_alipay?order_id="+Request.orderid;
            window.location.href= urlAdd;
    }
    //获取订单
    function getOrderPrice(orderid) {
        $http.get($scope.https+'/app/carrental/frontend/web/index.php/porder/order_detail?order_id=' + orderid).success(
            function (data, status, config, headers) {
                //console.log(data);
                if(data.result == 0){
                    $scope.payOrderPrice = data.total_price;
                }
            }
        )
    }
    getOrderPrice(Request.orderid);
//$scope.goToAliPay= function () {
//    var urlAdd = Request.orderid;
//    if(!urlAdd){
//        alert('订单编号异常，错误代码：AC001');
//        return false;
//    }
//    console.log(urlAdd);
//    console.log( JSON.stringify({
//        order_id:urlAdd
//    }));
//    //$.ajax({
//    //    type: 'post',
//    //    url: "http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/ppayment/order_alipay",
//    //    contentType:"application/x-www-form-urlencoded",
//    //    dataType: "json",
//    //    //data: JSON.stringify({
//    //    //    order_id:urlAdd
//    //    //}),
//    //    success: function (data) {
//    //        console.log(data)
//    //    }
//    //});
//    $.ajax({
//        url:"http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/ppayment/order_alipay",
//        type:"post",
//        data :{
//            "order_id":urlAdd
//        },
//        dataType:"json",
//        contentType:"application/x-www-form-urlencoded",
//        success:function(data){
//          console.log(data)
//        }
//    });
//
//}









})

