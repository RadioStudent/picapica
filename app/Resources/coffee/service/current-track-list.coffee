"use strict"

emptyTrackList =
    id: '-1'
    date: new Date().toISOString()
    name: 'New playlist'
    termId: 1
    comment: ''
    tracks: []

CurrentTrackList = ($rootScope, _) ->
    service =
        toggleTrack: (track) ->
            if service.hasTrack track
                _.remove service.list.tracks, 'fid', track.fid
            else
                service.list.tracks.push angular.copy(track)

            $rootScope.$broadcast 'tracklist.update'

        hasTrack: (track) ->
            _.some service.list.tracks, 'fid', track.fid

        reset: ->
            service.list = _.clone emptyTrackList

        list: _.clone emptyTrackList

        all: []

module.exports = CurrentTrackList
