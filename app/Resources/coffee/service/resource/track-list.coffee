transformResponse = (response) ->
    response = angular.fromJson response

    return unless response.tracks

    response.tracks = response.tracks.map (track) ->
        track.comment = null if track.comment is ''
        track

    response

transformRequest = (request) ->
    request = angular.copy request # Get rid of references

    request.tracks = request.tracks.map (track) ->
        track.comment = '' if not track.comment
        track

    angular.toJson request

TrackList = ($resource) ->
    $resource 'api/v1/tracklists/:id', {},
        update:
            method: 'PUT'
            transformRequest: transformRequest
        get:
            transformResponse: transformResponse
        save:
            method: 'POST'
            transformRequest: transformRequest

module.exports = TrackList
