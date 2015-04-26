"use strict"

class ColumnSortCtrl
    constructor: () ->
        @columns = [
            { name: "fid",        label: "#" }
            { name: "artistName", label: "Artist" }
            { name: "name",       label: "Title" }
            { name: "album",      label: "Album" }
            { name: "date",       label: "Year" }
            { name: "duration",   label: "Duration" }
        ];

        @sortByColumn = (column) ->
            if column.sortOrder is "asc"
                column.sortOrder = "desc"
            else
                column.sortOrder = "asc"

        @buildSortParams = () ->
            obj = {}

            for column in @columns
                if column.sortOrder
                    obj[column.name] = column.sortOrder

            return JSON.stringify(obj)

module.exports = ColumnSortCtrl
