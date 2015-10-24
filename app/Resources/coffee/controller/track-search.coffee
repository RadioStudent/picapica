class TrackSearchController
    constructor: (Track, Suggestion, SearchFilter, SortableColumn, CurrentTrackList, $http, $sce, $rootScope) ->
        @tracks     = []
        @searchTerm = ''
        @filters    = SearchFilter.all
        @columns    = SortableColumn.all
        @sort       = SortableColumn.sort
        @trackList  = CurrentTrackList

        $rootScope.$on 'filters.update', =>
            @filters = SearchFilter.all
            @doSearch()

        $rootScope.$on 'columns.update', =>
            @columns = SortableColumn.all
            @doSearch()

        @addFilterOnEnter = (event) ->
            if event.type is 'keyup' and event.keyCode is 13
                @addTextFilter not event.shiftKey

        @addTextFilter = (reset = yes) ->
            return if @searchTerm.length is 0
            SearchFilter.reset() if reset
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

module.exports = TrackSearchController
