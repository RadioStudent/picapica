"use strict"

#App Module
angular =   require "angular"
resource =  require "angular-resource"
route =     require "angular-route"
sanitize =  require "angular-sanitize"

require "angular-ui-bootstrap/ui-bootstrap-tpls"

picapicaApp = angular.module "picapicaApp", [
    route
    sanitize
    "ui.bootstrap"
    "picapicaServices"
    "picapicaFilters"
]

picapicaApp.config [
    "$routeProvider"
    ($routeProvider) ->
        $routeProvider
            .when "/search",
                templateUrl: "partials/track-search.tpl.html"
            .otherwise
                redirectTo: "/search"
]

require "./controller"
require "./services"
require "./filters"
