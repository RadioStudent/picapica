'use strict';

/* App Module */
var angular =   require('angular'),
    resource =  require('angular-resource'),
    route =     require('angular-route'),
    sanitize =  require('angular-sanitize'),
    animate =   require('angular-animate');

require('angular-strap/dist/angular-strap.js');
require('angular-strap/dist/angular-strap.tpl.js');

var picapicaApp = angular.module('picapicaApp', [
    route,
    sanitize,
    animate,
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

require('./controllers')
require('./services')
require('./filters')
