"use strict"

SearchFilter = ($rootScope, _) ->
    class Filter
        constructor: (@text, type, label, @fromAutocomplete = false) ->
            @types = Filter.generateFilterTypes type
            @label = label or @text
            service.all.push @
            $rootScope.$broadcast "filters.update"

        remove: ->
            service.all = service.all.filter (filter) =>
                not angular.equals @, filter
            $rootScope.$broadcast "filters.update"

        @generateFilterTypes = (type) ->
            allFilterTypes = [
                { name: "Artist name", type: "artist.name", active: no, visible: yes }
                { name: "Artist ID",   type: "artist.id",   active: no, visible: no }
                { name: "Album name",  type: "album.name",  active: no, visible: yes }
                { name: "Album ID",    type: "album.id",    active: no, visible: no }
                { name: "Track name",  type: "track.name",  active: no, visible: yes }
                { name: "Track ID",    type: "track.id",    active: no, visible: no }
            ]

            _.find(allFilterTypes, {type: type}).active = yes if type

            allFilterTypes

        @buildParams = () ->
            params = service.all.map (filter) ->
                obj = {}
                obj[filterType.type] = filter.text for filterType in filter.types when filterType.active
                obj._all = filter.text if angular.equals obj, {}
                obj

            JSON.stringify params

    service =
        all: []
        reset: () -> service.all = []
        add: (vars...) -> new Filter vars...
        buildParams: Filter.buildParams

module.exports = SearchFilter
