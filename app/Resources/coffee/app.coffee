"use strict"

angular =   require "angular"
resource =  require "angular-resource"
route =     require "angular-route"
sanitize =  require "angular-sanitize"

require "angular-ui-bootstrap/ui-bootstrap-tpls"

angular.module "picapicaApp", [route, sanitize, resource, "ui.bootstrap"]
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

require "./controller"
require "./directive"
require "./service"
require "./filter"
