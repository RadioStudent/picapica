"use strict"

emptyPlaylist =
    authorId: 1
    date: ""
    name: ""
    termId: null
    tracks: []

class PlaylistCtrl
    constructor: (SelectedTracks, TrackList, Term, $rootScope, $q, _) ->
        @toggleInPlaylist = SelectedTracks.toggle

        $rootScope.$on "playlist.update", =>
            @totalDuration = _.pluck(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        @terms = Term.query()
        @trackLists = TrackList.query()
        @currentTrackListId = -1

        @selectTrackList = () ->
            if @currentTrackListId is '-1'
                @trackList = _.clone emptyPlaylist
                deferred = $q.defer()
                deferred.resolve()
                promise = deferred.promise
            else
                @trackList = TrackList.get {id: @currentTrackListId}
                promise = @trackList.$promise

            promise.then =>
                SelectedTracks.all = @trackList.tracks
                $rootScope.$broadcast "playlist.update"

        @save = =>
            TrackList.update {id: @trackList.id}, @trackList

        return

module.exports = PlaylistCtrl
