/**
 * Created by Administrator on 2017/4/28.
 */
app.controller('logCtrl', function ($scope) {
    $scope.step = 1;
    $scope.phoneNub = "";
    $scope.phoneYZ = function(phone){
        if (!(/^1(3|4|5|7|8)\d{9}$/.test( phone))) {
            alert('手机号码有误，请重试！')
        }
        else {
            $scope.step = 2
        }
    };
    //手机星号保密
    $scope.phoneNubbm = function () {
        return  $scope.phoneNub.replace( $scope.phoneNub.substring(3,7),"****")
    }

    //验证码发送按钮
    $scope.isClick = true;
    $scope.paracont ='获取验证码';
    $scope.loginCode = function(){
            //console.log($scope.phoneNub)
            $scope.isClick = false;
            var second = 59;
            $scope.paracont = second + '秒后重发';
            var timer = setInterval(function(){

                if(second <=0){
                    $scope.paracont = '重发验证码';
                    second = 59;
                    clearInterval(timer);
                    $scope.isClick = true;
                }else{

                    second--;
                    $scope.paracont = second + '秒后重发';
                }

                $scope.$apply(function(){
                    $scope.date = new Date();
                });
            },1000);
        $.ajax({
            url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/get_verify_code",
            type:"post",
            data :{phone:$scope.phoneNub},
            dataType:"json",
            contentType:"application/x-www-form-urlencoded",
            success:function( data  ){
                //console.log(data)
                if(data.result){
                    alert(data.desc);
                    return false;
                }

            },
        });

    }
    $scope.changePassWord = function () {

        //console.log(  $('.form-group').find("input[name='password']").val(),  $('.form-group').find("input[name='repassword']").val())
        //console.log(hex_md5($('.form-group').find("input[name='password']").val()),77777)
        if( $('.form-group').find("input[name='password']").val() == $('.form-group').find("input[name='repassword']").val()&& $('.form-group').find("input[name='password']").val()){
            if($('input[name="vercode"]').val().length ==4){
                $.ajax({
                    url:"http://www.yikazc.com/app/carrental/frontend/web/index.php/puser/reset_password",
                    type:"post",
                    data :{
                        phone:$scope.phoneNub,
                        code:$('input[name="vercode"]').val(),
                        password:hex_md5($('.form-group').find("input[name='password']").val())
                    },
                    dataType:"json",
                    contentType:"application/x-www-form-urlencoded",
                    success:function( data  ){
                        //console.log(data);
                        if(data.result == 0){
                            alert('修改密码成功!');
                            $scope.step = 87;
                        }else {
                            alert(data.desc)
                        }
                    },
                })
            }else {
                alert("请输入验证码，或确认验证码长度！")
            }
        }else {
            alert('请确认密码一致!');
        }
        //http://www.ykzc_beifen.com/app/carrental/frontend/web/index.php/puser/reset_password

        //console.log()


    }
    $('#forgetPsdBtn').click(function () {
        //console.log($scope.phoneNub)
    })




})


