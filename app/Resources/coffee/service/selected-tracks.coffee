"use strict"

SelectedTracks = () ->
    service =
        toggle: (track) ->
            if service.isActive track
                delete service.all[track.id]
            else
                service.all[track.id] = angular.copy track

        isActive: (track) ->
            return service.all.hasOwnProperty track.id

        all: {}

module.exports = SelectedTracks
