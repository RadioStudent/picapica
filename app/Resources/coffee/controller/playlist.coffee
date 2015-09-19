"use strict"

class PlaylistCtrl
    constructor: (CurrentTrackList, TrackList, Term, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        @trackLists = TrackList.query()
        @toggleTrackInPlaylist = @trackList.toggleTrack
        @terms = Term.query()
        @loaders =
            save: off

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on "tracklist.update", =>
            @totalDuration = _.pluck(@trackList.list.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        @selectTrackList = ->
            if @trackList.list.id is '-1'
                @trackList.reset()
                $rootScope.$broadcast "tracklist.update"
            else
                @trackList.list = TrackList.get {id: @trackList.list.id}, ->
                    $rootScope.$broadcast "tracklist.update"

        @save = =>
            @loaders.save = on
            if @trackList.list.id is '-1'
                TrackList.save({}, @trackList.list).$promise.then =>
                    @loaders.save = off
            else
                TrackList.update({id: @trackList.list.id}, @trackList.list).$promise.then =>
                    @loaders.save = off

        @addComment = (track) ->
            track.comment = ''
        @removeComment = (track) ->
            delete track.comment

        return

module.exports = PlaylistCtrl
