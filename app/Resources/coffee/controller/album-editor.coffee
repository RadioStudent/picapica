class AlbumEditorController
    constructor: (Artist, Album, $routeParams, $scope) ->
        @albumId = $routeParams.albumId

        $scope.titlePrepend = if @albumId then "Edit" else "New"

        @RArtist = Artist
        @RAlbum = Album

        @album =
            id: null
            fid: ''
            fidPrepend: ''
            fidNumber: ''
            title: ''
            albumArtist: ''
            albumArtistModel: null
            label: ''
            year: ''
            tracks: []

        if @albumId
            #@album = this.loadAlbum(@albumId).then data ->
            #    console.log "GOT", data
            @loadAlbum(@albumId)

    fidGroups: ['CD', 'CDYU', 'CDJ', 'CDDE', 'CDWR', 'CDFG', 'CDKO', 'CDK', 'CDEX', 'CDFO', 'CDG', 'RŠPYU', 'RŠP', 'LP', 'LPYU', 'LPJ', 'LPRE', 'LPK', 'LPEX', 'LPAM', 'LPYF', 'SG', 'SGYU', 'SGFG', 'KNJ', 'TR']

    updateAlbumFid: () =>
        @album.fid = @album.fidPrepend + ' ' + @album.fidNumber

    digitize: (n) -> if (n < 10) then ("0" + n) else n

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

    parseTrack: (track) =>
        fidSplit = track.fid.split "-"

        return
            id: track.id
            fid: fidSplit[fidSplit.length - 1]
            title: track.name
            artist: track.artistName
            artistModel:
                id: track.artistId
                name: track.artistName
            length: track.duration

    loadAlbum: (albumId) ->
        @RAlbum.get { id: albumId }, (data) =>
            fidSplit = data.fid.split " "

            @album.id = data.id
            @album.fid = data.fid
            @album.fidPrepend = fidSplit[0]
            @album.fidNumber = fidSplit[1]
            @album.title = data.name
            @album.albumArtist = data.albumArtistName
            @album.albumArtistModel = data.artists[0]
            @album.label = data.label
            @album.year = data.year
            @album.tracks = data.tracks.map @parseTrack

    removeTrack: (index) =>
        head = @album.tracks.slice 0, index
        tail = @album.tracks.slice index + 1, @album.tracks.length

        @album.tracks = [...head, ...tail]

    saveAlbum: () =>
        @RAlbum.save JSON.stringify(@album), @handleSuccess, @handleError

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
