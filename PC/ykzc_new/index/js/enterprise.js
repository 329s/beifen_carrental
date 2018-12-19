/**
 * Created by Administrator on 2017/4/14.
 */
    //企业车型展示  管理数据
var app = angular.module('app',[]).controller('appCtrl',function($scope){
    $scope.carList = [
        {imgUrl:"img/car1.jpg",carname:"全新别克凯越",carriage:"3",seat:"5",gear:"自动档",type:1},
        {imgUrl:"img/car1.jpg",carname:"别克凯越2",carriage:"2",seat:"4",gear:"自动档",type:2},
        {imgUrl:"img/car1.jpg",carname:"别克999",carriage:"2",seat:"5",gear:"手动档",type:3},
    {imgUrl:"img/car1.jpg",carname:"别克999",carriage:"2",seat:"5",gear:"手动档",type:3}
    ]
    var swiper = new Swiper('.swiper-container',{
        scrollbar:'.swiper-scrollbar',
        scrollbarHide:false,
        scrollbarSnapOnRelease : true ,
        scrollbarDraggable : true ,
        observer:true,
        observeParents:true,
        prevButton:'.prev',
        nextButton:'.next',
        onSlideChangeEnd: function(swiper){
            $scope.showCarChange(swiper.activeIndex);
        }
    });


    $scope.showCarName = $scope.carList[0].carname;
    $scope.showCarUrl = $scope.carList[0].type;
    $scope.showCarChange = function(n){
        $scope.$apply(function(){
            $scope.showCarName = $scope.carList[n].carname;
            $scope.showCarUrl = $scope.carList[n].type;
        })
    }
})
//$(function(){
//    $.when(wait()).done(function(base){
//        var geo;
//        base = base;
//        initialize(geo,base,[{id:'map',zoom:7}]);
//    });
//})
