class AlbumEditorController {
      constructor(Artist, Album, $routeParams, $scope, $http) {
        this.updateAlbumFid = this.updateAlbumFid.bind(this);
        this.parseTrack = this.parseTrack.bind(this);
        this.removeTrack = this.removeTrack.bind(this);
        this.saveAlbum = this.saveAlbum.bind(this);
        this.albumId = $routeParams.albumId;
        $scope.titlePrepend = this.albumId ? "Edit" : "New";
        $scope.markNewTag = function(tag) {
          if (!tag.id) {
            tag.id = Math.floor(Date.now());
            return tag.new = true;
          }
        }
        this.RArtist = Artist;
        this.RAlbum = Album;
        this.album = {
          id: null,
          fid: '',
          fidPrepend: '',
          fidNumber: '',
          title: '',
          albumArtist: '',
          albumArtistModel: null,
          label: '',
          year: '',
          tracks: []
        };
        $scope.loadHerkunft = (query) => {
          return $http.get('/api/v1/herkunfts?query=' + query);
        };
        $scope.loadLabels = (query) => {
          return $http.get('/api/v1/labels?query=' + query);
        };
        $scope.loadGenres = (query) => {
          return $http.get('/api/v1/genres?query=' + query);
        };
        if (this.albumId) {
          this.loadAlbum(this.albumId);
        }
      }

      updateAlbumFid() {
        return this.album.fid = this.album.fidPrepend + ' ' + this.album.fidNumber;
      }

      digitize(n) {
        if (n < 10) {
          return "0" + n;
        } else {
          return n;
        }
      }

      addTrack() {
        var fields, last, number, ref;
        ref = this.album.tracks, [last] = [].slice.call(ref, -1);
        number = 'A/01';
        if (last && last.fid) {
          fields = last.fid.split(/\//);
          number = fields[0] + '/' + this.digitize(parseInt(fields[1]) + 1);
        }
        return this.album.tracks.push({
          fid: number,
          title: '',
          artist: '',
          artistModel: null,
          length: ''
        });
      }

      getArtistSuggestions(searchInput) {
        var searchParams, searchQuery;
        if (searchInput.length === 0 || typeof searchInput === 'object') {
          return;
        }
        searchParams = {
          "autocomplete": searchInput
        };
        searchQuery = JSON.stringify([searchParams]);
        return this.RArtist.query({
          search: searchQuery
        }).$promise;
      }

      selectAlbumArtist($item, $model, $label) {
        return this.album.albumArtistModel = {
          id: $item.id,
          name: $item.name
        };
      }

      selectTrackArtist($item, $model, $label, $index) {
        return this.album.tracks[$index].artistModel = {
          id: $item.id,
          name: $item.name
        };
      }

      parseTrack(track) {
        var fidSplit;
        fidSplit = track.fid.split("-");
        return {
          id: track.id,
          fid: fidSplit[fidSplit.length - 1],
          title: track.name,
          artist: track.artistName,
          artistModel: {
            id: track.artistId,
            name: track.artistName
          },
          length: track.duration
        };
      }

      loadAlbum(albumId) {
        return this.RAlbum.get({
          id: albumId
        }, (data) => {
          var fidSplit;
          fidSplit = data.fid.split(" ");
          this.album.id = data.id;
          this.album.fid = data.fid;
          this.album.fidPrepend = fidSplit[0];
          this.album.fidNumber = fidSplit[1];
          this.album.title = data.name;
          this.album.albumArtist = data.albumArtistName;
          this.album.albumArtistModel = data.artists[0];
          this.album.label = data.label;
          this.album.year = data.year;
          this.album.tracks = data.tracks.map(this.parseTrack);
          this.album.herkunft = data.herkunft;
          this.album.labels = data.labels;
          return this.album.genres = data.genres;
        });
      }

      removeTrack(index) {
        var head, tail;
        head = this.album.tracks.slice(0, index);
        tail = this.album.tracks.slice(index + 1, this.album.tracks.length);
        return this.album.tracks = [...head, ...tail];
      }

      saveAlbum() {
        return this.RAlbum.save(JSON.stringify(this.album), this.handleSuccess, this.handleError);
      }

      handleSuccess() {
        return alert('Album uspešno shranjen!');
      }

      handleError(resp) {
        if (resp.data[0]) {
          return alert('Napaka pri shranjevanju: ' + resp.data[0].error.message);
        } else if (resp.data.error) {
          return alert('Napaka pri shranjevanju: ' + resp.data.error.message);
        } else {
          return alert('Napaka pri shranjevanju');
        }
      }

    };

AlbumEditorController.prototype.fidGroups = ['CD', 'CDYU', 'CDJ', 'CDDE', 'CDWR', 'CDFG', 'CDKO', 'CDK', 'CDEX', 'CDFO', 'CDG', 'RŠPYU', 'RŠP', 'LP', 'LPYU', 'LPJ', 'LPRE', 'LPK', 'LPEX', 'LPAM', 'LPYF', 'SG', 'SGYU', 'SGFG', 'KNJ', 'TR'];

export default AlbumEditorController
