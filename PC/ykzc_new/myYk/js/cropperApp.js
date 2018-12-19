var cropperApp = angular.module('cropper',['cropper']).controller('cropperCtrl', function ($scope) {
    $scope.cropContext = {};
    $scope.phoneChange = false;
    $scope.emailChange = false;
});