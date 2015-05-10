"use strict"

class PlaylistCtrl
    constructor: (SelectedTracks) ->
        @tracks = SelectedTracks.all
        @toggleInPlaylist = SelectedTracks.toggle

        return

module.exports = PlaylistCtrl
