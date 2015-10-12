'use strict'

class TrackSearchCtrl
    constructor: (Track, Suggestion, SearchFilter, SortableColumn, CurrentTrackList, IconGenerator, $http, $sce, $rootScope) ->
        @tracks = []
        @searchTerm = ''
        @filters = SearchFilter.all
        @columns = SortableColumn.all
        @sort = SortableColumn.sort
        @generateIcon = IconGenerator.forType

        @isTrackInTrackList = CurrentTrackList.hasTrack
        @toggleTrackInTrackList = CurrentTrackList.toggleTrack

        if CurrentTrackList.list is null
            CurrentTrackList.reset()

        $rootScope.$on 'filters.update', =>
            @filters = SearchFilter.all
            @doSearch()

        $rootScope.$on 'columns.update', =>
            @columns = SortableColumn.all
            @doSearch()

        @typeInInput = (event) ->
            if event.type is 'keyup' and event.keyCode is 13
                @addTextFilter if event.shiftKey then 'add' else 'replace'

        @addTextFilter = (type) ->
            if @searchTerm.length > 0
                SearchFilter.reset() if type is 'replace'
                SearchFilter.add @searchTerm
                @searchTerm = ''

        @doSearch = (sortParams) =>
            if SearchFilter.all.length is 0
                @tracks = []
            else
                searchParams =
                Track.search
                    search: SearchFilter.buildParams()
                    sort:   SortableColumn.buildParams(),
                    (response) =>
                        @tracks = response

        @getSuggestions = (searchInput) ->
            return if searchInput.length is 0 or typeof searchInput is 'object'
            Suggestion.query(search: searchInput).$promise

        @selectSuggestion = (selectedItem, model, label) ->
            SearchFilter.reset()

            if selectedItem.searchInField
                SearchFilter.add selectedItem.name,
                                 "#{selectedItem.type}.name"
            else
                SearchFilter.add selectedItem.id,
                                 "#{selectedItem.type}.id",
                                 "#{selectedItem.name}",
                                 yes

            @searchTerm = ''

        @doSearch() if @filters.length > 0

        return

module.exports = TrackSearchCtrl
