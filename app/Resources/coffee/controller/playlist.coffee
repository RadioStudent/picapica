class PlaylistController
    constructor: (CurrentTrackList, TrackList, Terms, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        @trackLists = TrackList.query()
        @terms = Terms
        @loaders = { save: off }

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on 'tracklist.update', refreshDuration

        @selectTrackList = ->
            if @trackList.id is '-1'
                @trackList.reset()
                $refreshDuration()
            else
                TrackList.get {id: @trackList.id}, (newTrackList) =>
                    _.assign @trackList, newTrackList
                    refreshDuration()

        @save = ->
            @loaders.save = on
            @trackList.save().then () =>
                refreshDuration()
                @trackLists = TrackList.query () => @loaders.save = off

        refreshDuration = =>
            @totalDuration = _.pluck(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        return

module.exports = PlaylistController
