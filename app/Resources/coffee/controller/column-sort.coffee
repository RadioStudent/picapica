"use strict"

class ColumnSortCtrl
    constructor: () ->
        active = -1

        @columns = [
            { name: "fid",        label: "#" }
            { name: "artistName", label: "Artist" }
            { name: "name",       label: "Title" }
            { name: "album",      label: "Album" }
            { name: "date",       label: "Year" }
            { name: "duration",   label: "Duration" }
        ];

        @sortByColumn = (column, index) ->
            active = index
            
            if column.sortOrder is "asc"
                column.sortOrder = "desc"
            else
                column.sortOrder = "asc"

        @buildSortParams = () ->
            obj = {}

            if active > -1
                activeColumn = @columns[active]
                obj[activeColumn.name] = activeColumn.sortOrder

            return JSON.stringify(obj)

module.exports = ColumnSortCtrl
