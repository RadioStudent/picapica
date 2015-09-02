"use strict"

class PlaylistCtrl
    constructor: (SelectedTracks, TrackList, Term, $rootScope, _) ->
        @tracks = null
        @toggleInPlaylist = SelectedTracks.toggle

        $rootScope.$on "playlist.update", =>
            @tracks = SelectedTracks.all
            @totalDuration = _.pluck(@tracks, 'duration').filter(Number).reduce((a, b) -> a + b) or 0

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes


        @terms = Term.query()
        @trackList = TrackList.get {id: 1}, =>
            TrackList.update {id: 1}, @trackList

            SelectedTracks.all = @trackList.tracks
            $rootScope.$broadcast "playlist.update"

        return

module.exports = PlaylistCtrl
