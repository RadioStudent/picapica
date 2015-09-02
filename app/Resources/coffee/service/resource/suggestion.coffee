"use strict"

transform = (data) ->
    results = angular.fromJson data
    for key, value of results
        if results[key].length > 0 and key != "query"
            type = key.slice 0, -1
            for result in results[key]
                result.type = type
                prepend = if result.type is "artist" then "" else "#{result.albumArtistName} - "

                if result.elastica_highlights["name.autocomplete"]?.length > 0
                    highlights = result.elastica_highlights["name.autocomplete"]

                result.label = prepend + (highlights or result.name)

            results[key].push
                searchInField: yes
                type: type
                name: results.query
                label: "All tracks with #{type} <strong>#{results.query}</strong>"

    results = results.artists.concat results.albums, results.tracks
    results[results.length - 1]?.last = yes
    results

Suggestion = ($resource) ->
    $resource "api/v1/ac", null,
        query:
            isArray: true
            transformResponse: transform

module.exports = Suggestion
