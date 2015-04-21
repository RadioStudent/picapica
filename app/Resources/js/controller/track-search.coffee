'use strict'

class TrackSearchCtrl
    constructor: (Track, $http, $q, $sce) ->
        trackSearch = this

        trackSearch.tracks = []
        trackSearch.filters = []
        trackSearch.searchTerm = ""

        generateFilterTypes = (type) ->
            allFilterTypes = [
                { name: 'Artist name', type: 'artist.name', active: false, visible: true }
                { name: 'Artist ID',   type: 'artist.id',   active: false, visible: false }
                { name: 'Album name',  type: 'album.name',  active: false, visible: true }
                { name: 'Album ID',    type: 'album.id',    active: false, visible: false }
                { name: 'Track name',  type: 'track.name',  active: false, visible: true }
                { name: 'Track ID',    type: 'track.id',    active: false, visible: false }
            ]

            if type
                for filterType in allFilterTypes
                    if filterType.type is type
                        filterType.active = true

            return allFilterTypes

        class Filter
            constructor: (@text, type, label, fromAutocomplete) ->
                @types = generateFilterTypes(type)
                @label = label || @text
                @fromAutocomplete = fromAutocomplete?
                trackSearch.filters.push @

            remove: () ->
                for filter, index in trackSearch.filters
                    if angular.equals this, filter
                        trackSearch.filters.splice index, 1
                        break
                trackSearch.doSearch()

            setType: (type) ->
                @type = type
                trackSearch.doSearch()

        trackSearch.typeInInput = (event) ->
            if event.type is 'keyup' and event.keyCode is 13
                trackSearch.addTextFilter(event.shiftKey ? 'add' : 'replace')

        trackSearch.addTextFilter = (type) ->
            if trackSearch.searchTerm.length > 0
                if type is 'replace'
                    trackSearch.filters = []

                new Filter(trackSearch.searchTerm)
                trackSearch.searchTerm = ''
                trackSearch.doSearch()

        trackSearch.doSearch = () ->
            if trackSearch.filters.length is 0
                trackSearch.tracks = []
            else
                searchParams = buildSearchParams()
                sortParams = buildSortParams()
                Track.search
                    search: searchParams
                    sort: sortParams,
                    (response) ->
                        trackSearch.tracks = response

        buildSearchParams = () ->
            params = []

            for filter in trackSearch.filters
                obj = {}

                for filterType in filter.types
                    if filterType.active
                        obj[filterType.type] = filter.text

                if Object.keys(obj).length is 0
                    obj._all = filter.text

                params.push(obj)

            return JSON.stringify(params)

        buildSortParams = () ->
            obj = {}

            for column in trackSearch.columns
                if column.sortOrder
                    obj[column.name] = column.sortOrder

            return JSON.stringify(obj)

        trackSearch.getSuggestions = (searchInput) ->
            if searchInput.length is 0 or typeof searchInput is 'object'
                return

            return $http(
                method: 'GET'
                url: 'api/v1/ac'
                params: { search: searchInput }
            ).then (res) ->
                results = res.data;

                for key, value of results
                    if results[key].length > 0 and key != 'query'
                        for result in results[key]
                            result.type = key.substring(0, key.length - 1);
                            prepend = ''
                            highlights

                            if result.type is not 'artist'
                                prepend = result.albumArtistName + ' - '

                            if typeof result.elastica_highlights['name.autocomplete'] is not 'undefined' and result.elastica_highlights['name.autocomplete'].length > 0
                                highlights = result.elastica_highlights['name.autocomplete']

                            result.label = prepend + (highlights || result.name);

                        singularType = key.substring(0, key.length - 1)
                        results[key].push
                            searchInField: true
                            type: singularType
                            name: results.query
                            label: "All tracks with #{singularType} <strong>#{results.query}</strong>"

                results.tracks[results.tracks.length - 1].last = true
                return results.artists.concat results.albums, results.tracks

        trackSearch.selectSuggestion = (selectedItem, model, label) ->
            trackSearch.filters = []

            if selectedItem.searchInField is true
                new Filter selectedItem.name,
                           selectedItem.type + '.name'
            else
                new Filter selectedItem.id,
                           selectedItem.type + '.id',
                           selectedItem.type + ': ' + selectedItem.name,
                           true

            trackSearch.searchTerm = ''
            trackSearch.doSearch()

        trackSearch.columns = [
            { name: 'fid',        label: '#' },
            { name: 'artistName', label: 'Artist' },
            { name: 'name',       label: 'Title' },
            { name: 'album',      label: 'Album' },
            { name: 'date',       label: 'Year' },
            { name: 'duration',   label: 'Duration' }
        ];

        trackSearch.sortByColumn = (column) ->
            if column.sortOrder is 'asc'
                column.sortOrder = 'desc'
            else
                column.sortOrder = 'asc'
            trackSearch.doSearch()

        return false

module.exports = TrackSearchCtrl
