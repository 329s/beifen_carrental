/**
 * Created by Administrator on 2017/8/7.
 */
//var app = angular.module('app',[]);
app.controller('calcAppCtrl',function($scope){
    $scope.orderList = {
        orderCost: 117,
        addCost: 40,
        otherCost: 0,
        day: 2,
    }
    $scope.add = true;
    $scope.orderPrice  = $scope.orderList.orderCost * $scope.orderList.day;
    $scope.addPrice = $scope.orderList.addCost * $scope.orderList.day;
    $scope.addChange = function(){
        $scope.add ? $scope.add = false : $scope.add = true;
        $scope.add ? $scope.addPrice = $scope.orderList.addCost * $scope.orderList.day : $scope.addPrice = 0;
    }
    $scope.$watch('addPrice',function(){
        $scope.totalPrice = $scope.orderPrice + $scope.addPrice + $scope.orderList.otherCost;
    })
    $scope.orderInfo = {}
})