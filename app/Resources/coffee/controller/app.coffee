"use strict"

class AppCtrl
    constructor: ($mdSidenav) ->
        @toggleLeft = ->
            $mdSidenav('left').toggle()

        return

module.exports = AppCtrl
