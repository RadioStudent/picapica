"use strict"

class TrackSearchCtrl
    constructor: (Track, Suggestion, SearchFilter, SortableColumn, $http, $sce, $rootScope) ->
        trackSearch = this

        @tracks = []
        @searchTerm = ""
        @filters = SearchFilter.all
        @columns = SortableColumn.all
        @sort = SortableColumn.sort

        $rootScope.$on "filters.update", (event) ->
           trackSearch.filters = SearchFilter.all
           trackSearch.doSearch()

        $rootScope.$on "columns.update", (event) ->
           trackSearch.columns = SortableColumn.all
           trackSearch.doSearch()

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
                searchParams =
                Track.search
                    search: SearchFilter.buildParams()
                    sort:   SortableColumn.buildParams(),
                    (response) ->
                        trackSearch.tracks = response

        @getSuggestions = (searchInput) ->
            if searchInput.length is 0 or typeof searchInput is "object"
                return

            return Suggestion.query(search: searchInput).$promise

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

        return

module.exports = TrackSearchCtrl
