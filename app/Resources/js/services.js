'use strict';

/* Services */

var picapicaServices = angular.module('picapicaServices', ['ngResource']);

picapicaServices.factory('Track', ['$resource', function($resource){
    return $resource('api/v1/tracks', {search: '@searchTerm'}, {
        search: {
            params: {
                size: 100
            },
            isArray: true
        }
    });
}]);
