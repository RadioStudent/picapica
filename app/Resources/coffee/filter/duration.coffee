"use strict"

Duration = () ->
    (seconds) ->
        if seconds
            hours = Math.floor(seconds / 3600)
            minutes = Math.floor(seconds % 3600 / 60)
            seconds = seconds % 60
            timeString = ""

            if hours > 0
                timeString = "#{hours}:"
                if minutes < 10
                    timeString += "0"

            timeString += "#{minutes}:"

            if seconds < 10
                timeString += "0"

            timeString += seconds

        else
            return "/"

module.exports = Duration
