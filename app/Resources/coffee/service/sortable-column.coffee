"use strict"

SortableColumn = ($rootScope) ->
    buildParams = () ->
        obj = {}

        if service.active > -1
            activeColumn = service.all[service.active]
            obj[activeColumn.name] = activeColumn.sortOrder

        return JSON.stringify(obj)

    sort = (column, index) ->
        service.active = index
        if column.sortOrder is "asc"
            column.sortOrder = "desc"
        else
            column.sortOrder = "asc"
        $rootScope.$broadcast "filters.update"

    service =
        active: -1
        sort: sort
        all: [
            { name: "fid",        label: "#" }
            { name: "artistName", label: "Artist" }
            { name: "name",       label: "Title" }
            { name: "album",      label: "Album" }
            { name: "date",       label: "Year" }
            { name: "duration",   label: "Duration" }
        ]
        buildParams: buildParams

    return service

module.exports = SortableColumn
