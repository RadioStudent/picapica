"use strict"

angular   = require "angular"
resource  = require "angular-resource"
route     = require "angular-route"
sanitize  = require "angular-sanitize"
bootstrap = require "angular-bootstrap-npm/dist/angular-bootstrap"
material  = require "angular-material"

angular.module "picapicaApp", [route, sanitize, resource, bootstrap, material]
    .config ($routeProvider) ->
        $routeProvider
            .when "/search",
                templateUrl: "partials/track-search.tpl.html"
            .when "/playlist",
                templateUrl: "partials/playlist.tpl.html"
            .otherwise
                redirectTo: "/search"
    .config ($mdIconProvider) ->
        $mdIconProvider
           .iconSet 'navigation', 'images/icons/navigation-icons.svg'
    .constant('_', require "lodash")

require "./controller"
require "./directive"
require "./service"
require "./filter"
