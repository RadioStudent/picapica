"use strict"

# Services
angular = require "angular"

picapicaServices = angular.module "picapicaServices", ["ngResource"]

picapicaServices.factory "Track", ["$resource", ($resource) ->
    $resource "api/v1/tracks", search: "@searchTerm",
        search:
            params:
                size: 100
            isArray: true
]

picapicaServices.service "Filters", ["$rootScope", ($rootScope) ->
    service =
        all: []

        add: (filter) ->
            service.all.push filter
            $rootScope.$broadcast("filters.update")

        remove: (index) ->
            service.all.splice index, 1

        reset: () ->
            service.all = []

    return service
]
