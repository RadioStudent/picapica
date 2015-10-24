class PlaylistController
    constructor: (CurrentTrackList, TrackList, Terms, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        @trackLists = TrackList.query()
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
                    @trackLists = TrackList.query =>
                        TrackList.get {id: response.id}, (newTrackList) =>
                            _.assign @trackList, newTrackList
                            @loaders.save = off
                            $rootScope.$broadcast 'tracklist.update'
            else
                TrackList.update {id: @trackList.id}, _.clone(@trackList), =>
                    @trackLists = TrackList.query =>
                        @loaders.save = off

        @addComment = (track) -> track.comment = ''
        @removeComment = (track) -> delete track.comment

        return

module.exports = PlaylistController
