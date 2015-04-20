'use strict'

# Services
angular = require "angular"

picapicaServices = angular.module "picapicaServices", ["ngResource"]

picapicaServices.factory 'Track', ['$resource', ($resource) ->
    $resource 'api/v1/tracks', search: '@searchTerm',
        search:
            params:
                size: 100
            isArray: true
]
