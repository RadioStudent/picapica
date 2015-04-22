"use strict"

SearchFilter = ($rootScope) ->
    class Filter
        constructor: (@text, type, label, fromAutocomplete) ->
            @types = Filter.generateFilterTypes(type)
            @label = label || @text
            @fromAutocomplete = fromAutocomplete?
            service.all.push @
            $rootScope.$broadcast("filters.update")

        remove: () ->
            for filter, index in service.all
                if angular.equals this, filter
                    service.remove(index)
                    break
            $rootScope.$broadcast("filters.update")

        setType: (type) ->
            @type = type
            $rootScope.$broadcast("filters.update")

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

    service =
        all: []

        remove: (index) ->
            service.all.splice index, 1

        reset: () ->
            service.all = []

        add: (vars...) ->
            new Filter(vars...)

    return service

module.exports = SearchFilter
