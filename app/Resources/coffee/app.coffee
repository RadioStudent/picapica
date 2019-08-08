'use strict'

angular   = require 'angular'
resource  = require 'angular-resource'
route     = require 'angular-route'
sanitize  = require 'angular-sanitize'
bootstrap = require 'angular-bootstrap-npm/dist/angular-bootstrap-tpls'
require 'angular-ui-sortable'

angular.module 'picapicaApp', [route, sanitize, resource, bootstrap, 'ui.sortable']
    .config [
        '$routeProvider'
        ($routeProvider) ->
            $routeProvider
                .when '/search',
                    templateUrl: 'partials/track-search.tpl.html'
                .when '/playlist/:tracklistId?',
                    templateUrl: 'partials/playlist.tpl.html'
                .when '/album-editor/:albumId?',
                    templateUrl: 'partials/album-editor.tpl.html'
                .otherwise
                    redirectTo: '/search'
    ]
    .constant('_', require 'lodash')

require './controller'
require './directive'
require './service'
require './filter'
