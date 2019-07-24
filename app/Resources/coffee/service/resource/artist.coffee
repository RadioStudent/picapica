Artist = ($resource) ->
    $resource 'api/v1/artists', null,
        search:
            params:
                size: 100
            isArray: true

module.exports = Artist
