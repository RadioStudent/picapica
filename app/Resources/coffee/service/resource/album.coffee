Album = ($resource) ->
    $resource 'api/v1/albums/:id', null,
        search:
            params:
                size: 100
            isArray: true

module.exports = Album
