'use strict';

/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['Track', '$scope', '$http', '$q', '$sce', function(Track, $scope, $http, $q, $sce) {
    $scope.tracks = [];
    $scope.filters = [];
    $scope.searchTerm = '';
    var acTimeout,
        acDelay = 300;


    var Filter = function(text, type, label) {
        this.text = text;
        this.type = type || '_all';
        this.label = label || text;

        $scope.filters.push(this);
    };

    Filter.prototype.remove = function() {
        for(var i = 0; i < $scope.filters.length; i++) {
            if(angular.equals(this, $scope.filters[i])) {
                $scope.filters.splice(i, 1);
                break;
            }
        }
        doSearch();
    };




    $scope.addTextFilter = function() {
        if($scope.searchTerm.length > 0) {
            new Filter($scope.searchTerm);
            $scope.searchTerm = '';
            doSearch();
        }
    };

    function doSearch() {
        var searchParams = buildSearchParams();
        Track.search(
            { search: searchParams },
            function(response) {
                $scope.tracks = response;
            }
        );
    }

    function buildSearchParams() {
        var params = [];

        $scope.filters.forEach(function(filter){
            var obj = {};
            obj[filter.type] = filter.text;
            params.push(obj);
        });

        return JSON.stringify(params);
    }

    $scope.getSuggestions = function(searchInput) {
        if(searchInput.length === 0) {
            return;
        }

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

    $scope.$on('$typeahead.select', function(event, selectedItem) {
        new Filter(
            selectedItem.id,
            selectedItem.type.substring(0, selectedItem.type.length - 1) + '.id',
            selectedItem.name + ' (' + selectedItem.id + ')'
        );
        $scope.searchTerm = '';
        doSearch();
    });

}]);
