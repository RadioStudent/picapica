"use strict"

Track = ($resource) ->
    $resource "api/v1/tracks", null,
        search:
            params:
                size: 100
            isArray: true

module.exports = Track
