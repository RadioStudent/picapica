'use strict';

module.exports = function(Track, $http, $q, $sce) {
    var trackSearch = this;

    trackSearch.tracks = [];
    trackSearch.filters = [];
    trackSearch.searchTerm = '';

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

        trackSearch.filters.push(this);
    };

    Filter.prototype.remove = function() {
        for(var i = 0; i < trackSearch.filters.length; i++) {
            if(angular.equals(this, trackSearch.filters[i])) {
                trackSearch.filters.splice(i, 1);
                break;
            }
        }
        trackSearch.doSearch();
    };

    Filter.prototype.setType = function(type) {
        this.type = type;
        trackSearch.doSearch();
    };

    trackSearch.typeInInput = function(event) {
        if(event.type === 'keyup' && event.keyCode === 13) {
            trackSearch.addTextFilter(event.shiftKey ? 'add' : 'replace');
        }
    };

    trackSearch.addTextFilter = function(type) {
        if(trackSearch.searchTerm.length > 0) {
            if(type === 'replace') {
                trackSearch.filters = [];
            }

            new Filter(trackSearch.searchTerm);
            trackSearch.searchTerm = '';
            trackSearch.doSearch();
        }
    };

    trackSearch.doSearch = function() {
        if(trackSearch.filters.length === 0) {
            trackSearch.tracks = [];
        }
        else {
            var searchParams = buildSearchParams();
            var sortParams = buildSortParams();
            Track.search(
                {
                    search: searchParams,
                    sort: sortParams
                },
                function(response) {
                    trackSearch.tracks = response;
                }
            );
        }
    };

    function buildSearchParams() {
        var params = [];

        trackSearch.filters.forEach(function(filter){
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

    function buildSortParams() {
        var obj = {};

        trackSearch.columns.forEach(function(column){
            if(column.sortOrder) {
                obj[column.name] = column.sortOrder;
            }
        });

        return JSON.stringify(obj);
    };

    trackSearch.getSuggestions = function(searchInput) {
        if(searchInput.length === 0 || typeof searchInput === 'object') {
            return;
        }

        return $http({
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
                        label: 'All tracks with ' + key.substring(0, key.length - 1) + ' "<strong>' + results.query + '</strong>"'
                    });
                }
            }

            results.tracks[results.tracks.length - 1].last = true;

            return results.artists.concat(results.albums, results.tracks);
        });
    };

    trackSearch.selectSuggestion = function(selectedItem, model, label) {
        trackSearch.filters = [];

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

        trackSearch.searchTerm = '';
        trackSearch.doSearch();
    };

    trackSearch.columns = [
        { name: 'fid',        label: '#' },
        { name: 'artistName', label: 'Artist' },
        { name: 'name',       label: 'Title' },
        { name: 'album',      label: 'Album' },
        { name: 'date',       label: 'Year' },
        { name: 'duration',   label: 'Duration' }
    ];

    trackSearch.sortByColumn = function(column) {
        if(column.sortOrder === 'asc') {
            column.sortOrder = 'desc';
        } else {
            column.sortOrder = 'asc';
        }
        trackSearch.doSearch();
    };
};
