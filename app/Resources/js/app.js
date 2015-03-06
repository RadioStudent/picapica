'use strict';

/* App Module */

var picapicaApp = angular.module('picapicaApp', [
    'ngRoute',
    'ngSanitize',
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
            minLength: 3,
            trigger: 'focus',
            template: 'partials/_autocomplete.tpl.html',
            limit: 30
        });
    });
