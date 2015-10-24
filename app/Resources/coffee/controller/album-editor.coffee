class AlbumEditorController
    constructor: (_) ->
        @album =
            value: 'some name'
            artist:
                value: 'some artist'
                copy: on
            label:
                value: 'some label'
                copy: on
            year:
                value: 1999
                copy: on
            tracks: []

        @trackFields = [ 'title', 'artist', 'label', 'year']

        @addTrack = () ->
            @album.tracks.push
                title: ''
                artist: if @album.artist.copy then @album.artist.value else ''
                label:  if @album.label.copy  then @album.label.value  else ''
                year:   if @album.year.copy   then @album.year.value   else ''

        @defaultValueChanged = (field) ->
            if @album[field].copy
                @album.tracks.forEach (track) => track[field] = @album[field].value

        return

module.exports = AlbumEditorController
