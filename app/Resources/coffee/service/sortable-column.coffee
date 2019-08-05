SortableColumn = ($rootScope) ->
    buildParams = ->
        obj = {}

        if service.active > -1
            activeColumn = service.all[service.active]
            obj[activeColumn.name] = activeColumn.sortOrder

        JSON.stringify obj

    sort = (column, index) ->

        # Clear existing sort
        for col, i in service.all
            if i isnt index
                delete col.sortOrder

        service.active = index
        column.sortOrder = if column.sortOrder is 'asc' then 'desc' else 'asc'

        $rootScope.$broadcast 'filters.update'

    service =
        active: -1
        sort: sort
        all: [
            { name: 'fid.raw'   , label: 'FID' }
            { name: 'artistName', label: 'Artist' }
            { name: 'name'      , label: 'Title' }
            { name: 'album'     , label: 'Album' }
            { name: 'date'      , label: 'Year' }
            { name: 'duration'  , label: 'Length' }
        ]
        buildParams: buildParams

module.exports = SortableColumn
