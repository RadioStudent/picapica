'use strict';

/* App Module */

var picapicaApp = angular.module('picapicaApp', [
    'ngRoute',
    'ngSanitize',
    'ngAnimate',
    'mgcrea.ngStrap',
    'picapicaControllers',
    'picapicaServices',
    'picapicaFilters'
]);

picapicaApp
    .config(['$routeProvider',
        function($routeProvider) {
            $routeProvider.
                when('/search', {
                    templateUrl: 'partials/track-search.tpl.html',
                    controller: 'TrackSearchCtrl'
                }).
                otherwise({
                    redirectTo: '/search'
                });
        }
    ])
    .config(function($typeaheadProvider) {
        angular.extend($typeaheadProvider.defaults, {
            filter: false,
            limit: 33,
            minLength: 3,
            template: 'partials/_autocomplete.tpl.html',
            html: true
        });
    });
