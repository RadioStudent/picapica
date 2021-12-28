class TrackSearchController {
  constructor(Track, Suggestion, SearchFilter, SortableColumn, CurrentTrackList, $http, $sce, $rootScope, Authorization, $scope) {
    this.tracks = [];
    this.searchTerm = '';
    this.filters = SearchFilter.all;
    this.columns = SortableColumn.all;
      this.sort = SortableColumn.sort;
      this.trackList = CurrentTrackList;
      $scope.haveRole = Authorization.haveRole;
      $scope.getJoined = (prop) => {
        return prop.map((label) => {
          return label.name;
        }).join(', ');
      };
      $scope.getTrackInfo = (track) => {
        var genres, herkunft, out;
        genres = $scope.getJoined(track.genres);
        herkunft = $scope.getJoined(track.herkunft);
        out = [];
        if (genres) {
          out.push("Zvrsti: " + genres);
        }
        if (herkunft) {
          out.push("Izvor: " + herkunft);
        }
        if (out) {
          return $sce.trustAsHtml('<div>' + out.join('<br>') + '</div>');
        }
      };
      this.offset = 0;
      $rootScope.$on('filters.update', () => {
        var col, i, len, ref;
        this.filters = SearchFilter.all;
        if (this.filters.length === 0) {
          ref = SortableColumn.all;
          // Clear sort on filter reset
          for (i = 0, len = ref.length; i < len; i++) {
            col = ref[i];
            delete col.sortOrder;
          }
          // Loadmore reset, too
          this.offset = 0;
        }
        return this.doSearch();
      });
      $rootScope.$on('columns.update', () => {
        this.columns = SortableColumn.all;
        return this.doSearch();
      });
      this.addFilterOnEnter = function(event) {
        if (event.type === 'keyup' && event.keyCode === 13) {
          return this.addTextFilter(!event.shiftKey);
        }
      };
      this.addTextFilter = function(reset = true) {
        if (this.searchTerm.length === 0) {
          return;
        }
        if (reset) {
          SearchFilter.reset();
        }
        SearchFilter.add(this.searchTerm);
        return this.searchTerm = '';
      };
      this.doSearch = (sortParams) => {
        var searchParams;
        if (SearchFilter.all.length === 0) {
          return this.tracks = [];
        } else {
          searchParams = SearchFilter.buildParams();
          return Track.search({
            search: searchParams,
            sort: SortableColumn.buildParams()
          }, (response) => {
            this.tracks = response;
            if (this.tracks.length === 100) {
              return this.offset = 100;
            } else {
              return this.offset = 0;
            }
          });
        }
      };
      this.loadMore = function() {
        var searchParams;
        searchParams = SearchFilter.buildParams();
        return Track.search({
          search: searchParams,
          from: this.offset,
          sort: SortableColumn.buildParams()
        }, (response) => {
          this.tracks = this.tracks.concat(response);
          if (response.length === 100) {
            return this.offset += 100;
          } else {
            return this.offset = 0;
          }
        });
      };
      this.getSuggestions = function(searchInput) {
        if (searchInput.length === 0 || typeof searchInput === 'object') {
          return;
        }
        return Suggestion.query({
          search: searchInput
        }).$promise;
      };
      this.selectSuggestion = function(selectedItem, model, label) {
        SearchFilter.reset();
        if (selectedItem.searchInField) {
          SearchFilter.add(selectedItem.name, `${selectedItem.type}.name`);
        } else {
          SearchFilter.add(selectedItem.id, `${selectedItem.type}.id`, `${selectedItem.name}`, true);
        }
        return this.searchTerm = '';
      };
      if (this.filters.length > 0) {
        this.doSearch();
      }
      return;
    }

  };

export default TrackSearchController
