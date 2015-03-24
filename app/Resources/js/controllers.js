'use strict';
/* global angular: false */


/* Controllers */

var picapicaControllers = angular.module('picapicaControllers', []);

picapicaControllers.controller('TrackSearchCtrl', ['Track', '$scope', '$http', '$q', '$sce', function(Track, $scope, $http, $q, $sce) {
    $scope.tracks = [];
    $scope.filters = [];
    $scope.searchTerm = '';
    var acTimeout,
        acDelay = 300;

    function generateFilterTypes(type) {
        var allFilterTypes = [
            { name: 'Artist name', type: 'artist.name', active: false },
            { name: 'Artist ID', type: 'artist.id', active: false },
            { name: 'Album name', type: 'album.name', active: false },
            { name: 'Album ID', type: 'album.id', active: false },
            { name: 'Track name', type: 'track.name', active: false },
            { name: 'Track ID', type: 'track.id', active: false },
        ];

        if(type) {
            allFilterTypes.forEach(function(filterType) {
                if(filterType.type === type) {
                    filterType.active = true;
                }
            });
        }

        return allFilterTypes;
    }

    var Filter = function(text, type, label, fromAutocomplete) {
        this.text = text;
        this.types = generateFilterTypes(type);
        this.label = label || text;
        this.fromAutocomplete = !!fromAutocomplete;

        $scope.filters.push(this);
    };

    Filter.prototype.remove = function() {
        for(var i = 0; i < $scope.filters.length; i++) {
            if(angular.equals(this, $scope.filters[i])) {
                $scope.filters.splice(i, 1);
                break;
            }
        }
        $scope.doSearch();
    };

    Filter.prototype.setType = function(type) {
        this.type = type;
        $scope.doSearch();
    };

    $scope.addTextFilter = function() {
        if($scope.searchTerm.length > 0) {
            new Filter($scope.searchTerm);
            $scope.searchTerm = '';
            $scope.doSearch();
        }
    };

    $scope.doSearch = function() {
        if($scope.filters.length === 0) {
            $scope.tracks = [];
        }
        else {
            var searchParams = buildSearchParams();
            Track.search(
                { search: searchParams },
                function(response) {
                    $scope.tracks = response;
                }
            );
        }
    };

    function buildSearchParams() {
        var params = [];

        $scope.filters.forEach(function(filter){
            var paramsLength = params.length;

            filter.types.forEach(function(filterType){
                if(filterType.active) {
                    var obj = {};
                    obj[filterType.type] = filter.text;
                    params.push(obj);
                }
            });

            if(paramsLength === params.length) {
                params.push({ '_all' : filter.text });
            }
        });

        return JSON.stringify(params);
    }

    $scope.getSuggestions = function(searchInput) {
        console.log(searchInput);
        if(searchInput.length === 0 || typeof searchInput === 'object') {
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
                    if(results[key].length > 0 && key !== 'query') {
                        results[key].forEach(function(result){
                            result.type = key.substring(0, key.length - 1);
                            var prepend = '';
                            if(result.type !== 'artist') {
                                prepend = result.albumArtistName + ' - ';
                            }
                            result.label = prepend + result.elastica_highlights['name.autocomplete'][0];
                        });

                        results[key].push({
                            searchInField: true,
                            type: key.substring(0, key.length - 1),
                            name: results.query,
                            label: key.substring(0, key.length - 1) + ' name matches ' + results.query
                        });
                    }
                }

                deferred.resolve(results.artists.concat(results.albums, results.tracks));
            });
        }, acDelay);

        return deferred.promise;
    };

    $scope.$on('$typeahead.select', function(event, selectedItem) {
        if(selectedItem.searchInField === true) {
            new Filter(
                selectedItem.name,
                selectedItem.type + '.name'
            );
        } else {
            console.log(selectedItem);
            new Filter(
                selectedItem.id,
                selectedItem.type + '.id',
                selectedItem.type + ': ' + selectedItem.name,
                true
            );
        }

        $scope.searchTerm = '';
        $scope.doSearch();
    });
}]);
