'use strict';

/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['Track', '$scope', function(Track, $scope) {
    $scope.tracks = [];
    $scope.searchTerm = '';

    $scope.performSearch = function() {
        Track.search(
            { search: $scope.searchTerm },
            function(response) {
                $scope.tracks = response;
            }
        );
        $scope.searchTerm = '';
    };
}]);
