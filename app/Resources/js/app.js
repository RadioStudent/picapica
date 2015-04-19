'use strict';

/* App Module */
var angular =   require('angular'),
    resource =  require('angular-resource'),
    route =     require('angular-route'),
    sanitize =  require('angular-sanitize');

require('angular-ui-bootstrap/ui-bootstrap-tpls');

var picapicaApp = angular.module('picapicaApp', [
    route,
    sanitize,
    'ui.bootstrap',
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
    ]);

require('./controllers')
require('./services')
require('./filters')
