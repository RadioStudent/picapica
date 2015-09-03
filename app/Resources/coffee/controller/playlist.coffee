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
        @terms = Term.query()
        @trackLists = TrackList.query()
        @currentTrackListId = null
        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on "playlist.update", =>
            @totalDuration = _.pluck(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        @selectTrackList = ->
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
            if @trackList.id
                TrackList.update {id: @trackList.id}, @trackList
            else
                TrackList.save @trackList

        @addComment = (track) ->
            track.comment = ''
        @removeComment = (track) ->
            delete track.comment

        return

module.exports = PlaylistCtrl
