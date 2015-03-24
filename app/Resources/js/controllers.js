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
            { name: 'Artist name', type: 'artist.name', active: false, visible: true },
            { name: 'Artist ID',   type: 'artist.id',   active: false, visible: false },
            { name: 'Album name',  type: 'album.name',  active: false, visible: true },
            { name: 'Album ID',    type: 'album.id',    active: false, visible: false },
            { name: 'Track name',  type: 'track.name',  active: false, visible: true },
            { name: 'Track ID',    type: 'track.id',    active: false, visible: false },
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

    $scope.typeInInput = function(event) {
        if(event.type === 'keyup' && event.keyCode === 13) {
            $scope.addTextFilter(event.shiftKey ? 'add' : 'replace');
        }
    }

    $scope.addTextFilter = function(type) {
        if($scope.searchTerm.length > 0) {
            if(type === 'replace') {
                $scope.filters = [];
            }

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
            var obj = {};

            filter.types.forEach(function(filterType){
                if(filterType.active) {
                    obj[filterType.type] = filter.text;
                }
            });

            if(Object.keys(obj).length === 0) {
                obj._all = filter.text;
            }

            params.push(obj);
        });

        return JSON.stringify(params);
    }

    $scope.getSuggestions = function(searchInput) {
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
                            var prepend = '',
                                highlights;

                            if(result.type !== 'artist') {
                                prepend = result.albumArtistName + ' - ';
                            }

                            if(typeof result.elastica_highlights['name.autocomplete'] !== 'undefined' && result.elastica_highlights['name.autocomplete'].length > 0) {
                                highlights = result.elastica_highlights['name.autocomplete'];
                            }

                            result.label = prepend + (highlights || result.name);
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
        $scope.filters = [];

        if(selectedItem.searchInField === true) {
            new Filter(
                selectedItem.name,
                selectedItem.type + '.name'
            );
        } else {
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
