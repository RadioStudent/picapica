"use strict"

class PlaylistCtrl
    constructor: (SelectedTracks, $rootScope, _) ->
        @tracks = SelectedTracks.all
        @toggleInPlaylist = SelectedTracks.toggle

        $rootScope.$on "playlist.update", (event) =>
            return
            #@totalDuration = _(@tracks).values().pluck('duration').reduce((a, b) -> a + b) or 0
        $rootScope.$emit "playlist.update"

        return

module.exports = PlaylistCtrl
