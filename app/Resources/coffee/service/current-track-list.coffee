module.exports = ($rootScope, $q, _, TrackList) ->
    new class CurrentTrackList
        constructor: ->
            @reset()

        toggleTrack: (track) ->
            if @hasTrack track
                _.remove @tracks, 'fid', track.fid
            else
                @tracks.push angular.copy(track)

            $rootScope.$broadcast 'tracklist.update'

        hasTrack: (track) ->
            _.some @tracks, fid: track.fid

        addCommentToTrack: (track) ->
            track.comment = ''

        removeCommentFromTrack: (track) ->
            delete track.comment

        reset: ->
            emptyTrackList =
                id      : '-1'
                date    : new Date().toISOString()
                name    : 'New playlist'
                termId  : 1
                comment : ''
                tracks  : []
            _.assign @, emptyTrackList

        save: ->
            fetching = $q.defer()

            if @id is '-1'
                TrackList.save {}, @, (response) =>
                    @id = response.id
                    fetching.resolve()
            else
                TrackList.update {id: @id}, @, =>
                    fetching.resolve()

            fetching.promise
