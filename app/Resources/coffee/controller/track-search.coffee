"use strict"

class TrackSearchCtrl
    constructor: (Track, SearchFilter, $http, $q, $sce, $rootScope) ->
        trackSearch = this

        @tracks = []
        @searchTerm = ""

        $rootScope.$on "filters.update", (event) ->
           trackSearch.filters = SearchFilter.all
           trackSearch.doSearch()

        @filters = SearchFilter.all

        @typeInInput = (event) ->
            if event.type is "keyup" and event.keyCode is 13
                @addTextFilter(event.shiftKey ? "add" : "replace")

        @addTextFilter = (type) ->
            if @searchTerm.length > 0
                if type is "replace"
                    SearchFilter.reset()

                SearchFilter.add(@searchTerm)
                @searchTerm = ""

        @doSearch = (sortParams) ->
            if SearchFilter.all.length is 0
                @tracks = []
            else
                searchParams = @buildSearchParams()
                Track.search
                    search: searchParams
                    sort: sortParams,
                    (response) ->
                        trackSearch.tracks = response

        @buildSearchParams = () ->
            params = []

            for filter in SearchFilter.all
                obj = {}

                for filterType in filter.types
                    if filterType.active
                        obj[filterType.type] = filter.text

                if Object.keys(obj).length is 0
                    obj._all = filter.text

                params.push(obj)

            return JSON.stringify(params)

        @getSuggestions = (searchInput) ->
            if searchInput.length is 0 or typeof searchInput is "object"
                return

            return $http(
                method: "GET"
                url: "api/v1/ac"
                params: { search: searchInput }
            ).then (res) ->
                results = res.data;

                for key, value of results
                    if results[key].length > 0 and key != "query"
                        for result in results[key]
                            result.type = key.substring(0, key.length - 1);
                            prepend = ""
                            highlights

                            if result.type != "artist"
                                prepend = result.albumArtistName + " - "

                            if typeof result.elastica_highlights["name.autocomplete"] != "undefined" and result.elastica_highlights["name.autocomplete"].length > 0
                                highlights = result.elastica_highlights["name.autocomplete"]

                            result.label = prepend + (highlights || result.name);

                        singularType = key.substring(0, key.length - 1)
                        results[key].push
                            searchInField: true
                            type: singularType
                            name: results.query
                            label: "All tracks with #{singularType} <strong>#{results.query}</strong>"

                results = results.artists.concat results.albums, results.tracks
                if results.length > 0
                    results[results.length - 1].last = true
                return results

        @selectSuggestion = (selectedItem, model, label) ->
            SearchFilter.reset()

            if selectedItem.searchInField is true
                SearchFilter.add selectedItem.name,
                            selectedItem.type + ".name"
            else
                SearchFilter.add selectedItem.id,
                            selectedItem.type + ".id",
                            selectedItem.type + ": " + selectedItem.name,
                            true

            @searchTerm = ""

        return false

module.exports = TrackSearchCtrl
