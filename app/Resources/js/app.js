'use strict';

/* App Module */

var picapicaApp = angular.module('picapicaApp', [
    'ngRoute',
    'mgcrea.ngStrap',
    'picapicaControllers',
    'picapicaServices'
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
            delay: 250,
            trigger: 'click',
            template: 'partials/_autocomplete.tpl.html',
            limit: 30
        });
    });
