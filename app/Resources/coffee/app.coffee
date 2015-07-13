"use strict"

angular   = require "angular"
resource  = require "angular-resource"
route     = require "angular-route"
sanitize  = require "angular-sanitize"
bootstrap = require "angular-bootstrap-npm/dist/angular-bootstrap"

angular.module "picapicaApp", [route, sanitize, resource, bootstrap]
    .config [
        "$routeProvider"
        ($routeProvider) ->
            $routeProvider
                .when "/search",
                    templateUrl: "partials/track-search.tpl.html"
                .when "/playlist",
                    templateUrl: "partials/playlist.tpl.html"
                .otherwise
                    redirectTo: "/search"
    ]
    .constant('_', require "lodash")

require "./controller"
require "./directive"
require "./service"
require "./filter"
