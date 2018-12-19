/**
 * Created by Administrator on 2017/11/1.
 */

app.controller('loginCtrl', function ($scope) {
//验证码发送按钮
    $scope.isClick = true;
    $scope.paracont = '获取验证码';
    $scope.loginCode = function (phone) {
        $scope.isClick = false;
        var second = 59;
        $scope.paracont = second + '秒后重发';
        var timer = setInterval(function () {

            if (second <= 0) {
                $scope.paracont = '重发验证码';
                second = 59;
                clearInterval(timer);
                $scope.isClick = true;
            } else {

                second--;
                $scope.paracont = second + '秒后重发';
            }

            $scope.$apply(function () {
                $scope.date = new Date();
            });
        }, 1000);
        $.ajax({
            url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/get_verify_code",
            type:"post",
            data :{phone:phone},
            dataType:"json",
            contentType:"application/x-www-form-urlencoded",
            success:function( data  ){
                //console.log(data)
                if(data.result){
                    alert(data.desc);
                }

            },
        });

    }

    $scope.loginKeyDown =function()
    {
        if (event.keyCode == 13)
        {
            event.returnValue=false;
            event.cancel = true;
            $scope.order=='dz'?$scope.userLodin($scope.tel_mm,$scope.psw_mm,'dz'):$scope.userLodin($scope.tel_mm,$scope.psw_mm,'cz')
        }
    }
});