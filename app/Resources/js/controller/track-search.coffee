"use strict"

class TrackSearchCtrl
    constructor: (Track, Filters, $http, $q, $sce, $rootScope) ->
        trackSearch = this

        @tracks = []
        @searchTerm = ""

        $rootScope.$on "filters.update", (event) ->
           trackSearch.filters = Filters.all

        @filters = Filters.all

        class @Filter
            constructor: (@text, type, label, fromAutocomplete) ->
                @types = trackSearch.Filter.generateFilterTypes(type)
                @label = label || @text
                @fromAutocomplete = fromAutocomplete?
                Filters.add(@)

            @filters: []

            remove: () ->
                for filter, index in Filters.all
                    if angular.equals this, filter
                        Filters.remove(index)
                        break
                trackSearch.doSearch()

            setType: (type) ->
                @type = type
                trackSearch.doSearch()

            @generateFilterTypes = (type) ->
                allFilterTypes = [
                    { name: "Artist name", type: "artist.name", active: false, visible: true }
                    { name: "Artist ID",   type: "artist.id",   active: false, visible: false }
                    { name: "Album name",  type: "album.name",  active: false, visible: true }
                    { name: "Album ID",    type: "album.id",    active: false, visible: false }
                    { name: "Track name",  type: "track.name",  active: false, visible: true }
                    { name: "Track ID",    type: "track.id",    active: false, visible: false }
                ]

                if type
                    for filterType in allFilterTypes
                        if filterType.type is type
                            filterType.active = true

                return allFilterTypes

        @typeInInput = (event) ->
            if event.type is "keyup" and event.keyCode is 13
                @addTextFilter(event.shiftKey ? "add" : "replace")

        @addTextFilter = (type) ->
            if @searchTerm.length > 0
                if type is "replace"
                    Filters.reset()

                new @Filter(@searchTerm)
                @searchTerm = ""
                @doSearch()

        @doSearch = (sortParams) ->
            if Filters.all.length is 0
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

            for filter in Filters.all
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
            Filters.reset()

            if selectedItem.searchInField is true
                new @Filter selectedItem.name,
                           selectedItem.type + ".name"
            else
                new @Filter selectedItem.id,
                           selectedItem.type + ".id",
                           selectedItem.type + ": " + selectedItem.name,
                           true

            @searchTerm = ""
            @doSearch()

        return false

module.exports = TrackSearchCtrl
