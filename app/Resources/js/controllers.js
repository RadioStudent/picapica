'use strict';

/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['Track', '$scope', '$http', '$q', function(Track, $scope, $http, $q) {
    $scope.tracks = [];
    $scope.searchTerm = '';
    var acTimeout,
        acDelay = 300;

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
        var deferred = $q.defer();
        clearTimeout(acTimeout);

        acTimeout = setTimeout(function() {
            $http({
                method: 'GET',
                url: 'api/v1/ac',
                params: { search: searchInput }
            }).then(function(res) {
                var results = res.data;

                for (var key in results) {
                    if(results[key].length > 0) {
                        results[key].forEach(function(result){
                            result.type = key;
                        });
                    }
                }

                deferred.resolve(results.artists.concat(results.albums, results.tracks));
            });
        }, acDelay);

        return deferred.promise;
    }

    $scope.$on('$typeahead.select', function(value, index){
        $scope.searchTerm = '';
    });
\
}]);
