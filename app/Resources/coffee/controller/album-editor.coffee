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
            artist: ''
            artistModel: null
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

    selectTrackArtist: ($item, $model, $label, $index) ->
        @album.tracks[$index].artistModel =
            id: $item.id
            name: $item.name

    saveAlbum: () ->
        promise = @RAlbum.save JSON.stringify(@album), @handleSuccess, @handleError

    handleSuccess: () ->
        alert 'Album uspešno shranjen!'
        #location.reload()

    handleError: (resp) ->
        if resp.data[0]
            alert 'Napaka pri shranjevanju: ' + resp.data[0].error.message
        else if resp.data.error
            alert 'Napaka pri shranjevanju: ' + resp.data.error.message
        else
            alert 'Napaka pri shranjevanju'


module.exports = AlbumEditorController
