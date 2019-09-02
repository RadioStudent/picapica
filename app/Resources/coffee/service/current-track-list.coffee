module.exports = ($rootScope, $q, _, TrackList) ->

    new class CurrentTrackList
        constructor: ->
            @reset()

        toggleTrack: (track) ->
            if track.mp3 and not track.fid
                _.remove @tracks, ((t) -> t.mp3 and not t.fid)
            else if @hasTrack track
                _.remove @tracks, ((t) -> t.fid is track.fid)
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
            if @id is '-1'
                return TrackList.save {}, @, @handleSuccess
            else
                return TrackList.update {id: @id}, @, @handleSuccess

        handleSuccess: (resource) =>
            @id = resource.id
