class AlbumEditorController
    constructor: (Artist, Album) ->
        @RArtist = Artist
        @RAlbum = Album

        @album =
            fid: ''
            title: ''
            albumArtist: ''
            albumArtistModel: null
            label: ''
            year: ''
            tracks: []

    digitize: (n) ->
      if n < 10
        return "0" + n

      return n

    addTrack: () ->
        [..., last] = @album.tracks

        number = 'A/01'
        if last and last.fid
            fields = last.fid.split /\//
            number = fields[0] + '/' + this.digitize(parseInt(fields[1]) + 1)

        @album.tracks.push
            fid: number
            title: ''
            artist: ''#artist: if last and last.artist then last.artist else @album.albumArtist
            length: ''

    getArtistSuggestions: (searchInput) ->
        return if searchInput.length is 0 or typeof searchInput is 'object'

        searchParams =
            "autocomplete": searchInput

        searchQuery = JSON.stringify [searchParams]

        @RArtist.query(search: searchQuery).$promise

    selectAlbumArtist: ($item, $model, $label) ->
        @album.albumArtistModel =
            id: $item.id
            name: $item.name
        console.log @album

    selectTrackArtist: ($item, $model, $label, $index) ->
        @album.tracks[$index].artistModel =
            id: $item.id
            name: $item.name
        console.log @album

    saveAlbum: () ->
        @RAlbum.save data: JSON.stringify @album


module.exports = AlbumEditorController
