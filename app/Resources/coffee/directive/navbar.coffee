"use strict"

ppNavbar = ($window) ->
    templateUrl: "/partials/_navbar.tpl.html"
    scope:
        activeItem: '='
    link: (scope, element) ->
        scope.username = $window.username

module.exports = ppNavbar
