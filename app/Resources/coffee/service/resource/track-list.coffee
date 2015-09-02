"use strict"

TrackList = ($resource) ->
    $resource "api/v1/tracklists/:id", null,
        update:
            method: 'PUT'

module.exports = TrackList
