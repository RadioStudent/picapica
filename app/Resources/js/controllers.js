'use strict';

/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['Track', '$scope', '$http', '$q', function(Track, $scope, $http, $q) {
    var canceler = $q.defer();
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

    $scope.getSuggestions = function(searchInput) {
        canceler.resolve(true);
        canceler = $q.defer();
        return $http({
            method: 'GET',
            url: 'api/v1/ac',
            params: { search: searchInput },
            timeout: canceler.promise
        }).then(function(res) {
            var results = res.data;

            for (var key in results) {
                if(results[key].length > 0) {
                    results[key].forEach(function(result){
                        result.type = key;
                    });
                }
            }

            return results.artists.concat(results.albums, results.tracks);
        });
    };

    $scope.$on('$typeahead.select', function(value, index){
        $scope.searchTerm = '';
    });

}]);
