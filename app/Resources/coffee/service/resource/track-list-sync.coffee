TrackListSync = ($resource) ->
    $resource 'api/v1/tracklists/:id/sync', { id: '@id' },
        save:
            method: 'PUT'

module.exports = TrackListSync
