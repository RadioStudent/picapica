'use strict';

/* App Module */

var picapicaApp = angular.module('picapicaApp', [
    'ngRoute',
    'picapicaControllers',
    'picapicaServices'
]);

picapicaApp.config(['$routeProvider',
    function($routeProvider) {
        $routeProvider.
            when('/search', {
                templateUrl: 'partials/track-search.html',
                controller: 'TrackSearchCtrl'
            }).
            otherwise({
                redirectTo: '/search'
            });
    }
]);
