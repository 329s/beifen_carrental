var myapp = angular.module('myApp',['cropper']).controller('myAppCtrl',function($scope,$http){

    $scope.recharge = true;
    $(".btn-recharge").click(function(){
        $(".box-medium").slideDown();
        $scope.recharge = false;
        $scope.$apply();
    })
    $(".btn-uncharge").click(function(){
        $(".box-medium").slideUp();
        $scope.recharge = true;
        $scope.$apply();
    })




})

myapp.controller('cropperCtrl', function ($scope) {
    $scope.cropContext = {};
    $scope.phoneChange = false;
    $scope.emailChange = false;
});