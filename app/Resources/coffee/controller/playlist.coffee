"use strict"

class PlaylistCtrl
    constructor: (CurrentTrackList, TrackList, Term, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        @trackLists = TrackList.query()
        @toggleTrackInPlaylist = @trackList.toggleTrack
        @terms = Term.query()

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on "tracklist.update", =>
            @totalDuration = _.pluck(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        @selectTrackList = ->
            if @trackList.list.id is '-1'
                @trackList.reset()
                $rootScope.$broadcast "tracklist.update"
            else
                @trackList.list = TrackList.get {id: @trackList.list.id}, ->
                    $rootScope.$broadcast "tracklist.update"

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
