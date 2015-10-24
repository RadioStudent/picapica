CurrentTrackList = ($rootScope, _) ->
    emptyTrackList =
        id      : '-1'
        date    : new Date().toISOString()
        name    : 'New playlist'
        termId  : 1
        comment : ''
        tracks  : []

    new class TrackList
        constructor: ->
            @reset()

        toggleTrack: (track) ->
            if @hasTrack track
                _.remove @tracks, 'fid', track.fid
            else
                @tracks.push angular.copy(track)

            $rootScope.$broadcast 'tracklist.update'

        hasTrack: (track) ->
            _.some @tracks, 'fid', track.fid

        reset: ->
            _.assign @, emptyTrackList

module.exports = CurrentTrackList
