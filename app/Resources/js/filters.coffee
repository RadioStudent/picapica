"use strict"

# Filters
angular = require "angular"

picapicaFilters = angular.module "picapicaFilters", []

picapicaFilters.filter "secondsToTime", () ->
    (seconds) ->
        if seconds
            hours = Math.floor(seconds / 3600)
            minutes = Math.floor(seconds % 3600 / 60)
            seconds = seconds % 60
            timeString = ""

            if hours > 0
                timeString = "#{hours}:"

            if hours > 0 and minutes < 10
                timeString += "0"

            timeString += "#{minutes}:"

            if seconds < 10
                timeString += "0"

            timeString += seconds

        else
            return "/"
