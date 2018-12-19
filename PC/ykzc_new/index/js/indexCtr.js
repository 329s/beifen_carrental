/**
 * Created by Administrator on 2017/12/14.
 */
app.controller('indexCtr', function ($scope,$http) {

    $scope.activeForIndex=[];
    //$http.get('http://gm.yikazc.com:8010/app/carrental/frontend/web/index.php/activity/activitybanner').success(function (data, status, config, headers) {
    //   console.log(data.image_list)
    //    $scope.activeForIndex = data.image_list;
    //}).error(function (data) {
    //    console.log(data);
    //});
    $http.get('http://www.yikazc.com/app/carrental/frontend/web/index.php/activity/activity_pc_banner').success(function (data, status, config, headers) {
       //console.log(data.image_list)
        for(i=0;i<data.image_list.length;i++){
            if(i<4){
                $scope.activeForIndex[i] = data.image_list[i];
            }
        }

    }).error(function (data) {
        //console.log(data,646567686961626360);
    });


    //console.log($scope.isShowAd);

   if(sessionStorage.getItem('indexForDown')){
       document.querySelector('#index_ad_down').style.display = 'none';
   }


})