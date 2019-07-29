class TrackSearchController
    constructor: (Track, Suggestion, SearchFilter, SortableColumn, CurrentTrackList, $http, $sce, $rootScope) ->
        @tracks     = []
        @searchTerm = ''
        @filters    = SearchFilter.all
        @columns    = SortableColumn.all
        @sort       = SortableColumn.sort
        @trackList  = CurrentTrackList

        @offset     = 0

        $rootScope.$on 'filters.update', =>
            @filters = SearchFilter.all

            if @filters.length is 0
                # Clear sort on filter reset
                for col in SortableColumn.all
                    delete col.sortOrder

                # Loadmore reset, too
                @offset = 0

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
                searchParams = SearchFilter.buildParams()

                Track.search
                    search: searchParams
                    sort:   SortableColumn.buildParams(),
                    (response) =>
                        @tracks = response

                        if @tracks.length is 100
                            @offset = 100
                        else
                            @offset = 0

        @loadMore = () ->
            searchParams = SearchFilter.buildParams()

            Track.search
                search: searchParams
                from:   @offset
                sort:   SortableColumn.buildParams(),
                (response) =>
                    @tracks = @tracks.concat response

                    if response.length is 100
                        @offset += 100
                    else
                        @offset = 0

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
