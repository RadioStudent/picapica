"use strict"

transform = ($sce) ->
    (data) ->
        results = angular.fromJson data
        for key, value of results
            if results[key].length > 0 and key != "query"
                for result in results[key]
                    result.type = key.substring(0, key.length - 1);
                    prepend = ""
                    highlights

                    if result.type != "artist"
                        prepend = result.albumArtistName + " - "

                    if typeof result.elastica_highlights["name.autocomplete"] != "undefined" and result.elastica_highlights["name.autocomplete"].length > 0
                        highlights = result.elastica_highlights["name.autocomplete"]

                    result.label = prepend + (highlights or result.name)

                singularType = key.substring(0, key.length - 1)
                results[key].push
                    searchInField: true
                    type: singularType
                    name: results.query
                    label: $sce.trustAsHtml "All tracks with #{singularType} <strong>#{results.query}</strong>"

        results = results.artists.concat results.albums, results.tracks
        results[results.length - 1].last = true if results.length > 0
        results

Suggestion = ($resource, $sce) ->
    $resource "api/v1/ac", null,
        query:
            isArray: true
            transformResponse: transform $sce

module.exports = Suggestion
