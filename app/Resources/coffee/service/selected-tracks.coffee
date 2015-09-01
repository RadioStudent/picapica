"use strict"

SelectedTracks = ($rootScope, _) ->
    service =
        toggle: (track) ->
            if service.isActive track
                _.remove service.all, 'fid', track.fid
            else
                service.all.push angular.copy(track)

            $rootScope.$broadcast 'playlist.update'

        isActive: (track) ->
            _.some service.all, 'fid', track.fid

        all: []

module.exports = SelectedTracks
