'use strict';

/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['$scope', '$http', function($scope, $http) {
    $scope.tracks = [];
    $scope.searchTerm = '';

    $scope.performSearch = function() {
        $http.get('api/v1/tracks?search=' + $scope.searchTerm + '&results=100').success(function(data) {
            $scope.tracks = data;
        });
        $scope.searchTerm = '';
    };

}]);
