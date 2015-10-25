class PlaylistController
    constructor: (CurrentTrackList, TrackList, Terms, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        TrackList.query (newTrackLists) =>
            @trackLists = newTrackLists
        @terms = Terms
        @loaders =
            save: off

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on 'tracklist.update', =>
            @totalDuration = _.pluck(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        @selectTrackList = ->
            if @trackList.id is '-1'
                @trackList.reset()
                $rootScope.$broadcast 'tracklist.update'
            else
                TrackList.get {id: @trackList.id}, (newTrackList) =>
                    _.assign @trackList, newTrackList
                    $rootScope.$broadcast 'tracklist.update'

        @save = =>
            @loaders.save = on

            if @trackList.id is '-1'
                TrackList.save {}, @trackList, (response) =>
                    TrackList.query (newTrackLists) =>
                        @trackLists = newTrackLists
                        TrackList.get {id: response.id}, (newTrackList) =>
                            _.assign @trackList, newTrackList
                            @loaders.save = off
                            $rootScope.$broadcast 'tracklist.update'
            else
                TrackList.update {id: @trackList.id}, @trackList, =>
                    TrackList.query (newTrackLists) =>
                        fromClient = _.findWhere @trackLists,   {id: @trackList.id}
                        fromServer = _.findWhere newTrackLists, {id: @trackList.id}
                        _.assign fromClient, fromServer
                        # @trackLists = newTrackLists
                        @loaders.save = off

        @addComment = (track) -> track.comment = ''
        @removeComment = (track) -> delete track.comment

        return

module.exports = PlaylistController
