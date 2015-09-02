"use strict"

class PlaylistCtrl
    constructor: (SelectedTracks, $rootScope, _) ->
        @tracks = SelectedTracks.all
        @toggleInPlaylist = SelectedTracks.toggle

        $rootScope.$on "playlist.update", (event) =>
            return
            #@totalDuration = _(@tracks).values().pluck('duration').reduce((a, b) -> a + b) or 0
        $rootScope.$emit "playlist.update"

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'dd. MM. yyyy'
            opened: no
            open: => @datepicker.opened = yes

        @times = [{ id: 1, label:  '7:00 - 11:00', group: 'Weekday' }
                  { id: 2, label: '11:00 - 15:00', group: 'Weekday' }
                  { id: 3, label: '15:00 - 19:00', group: 'Weekday' }
                  { id: 4, label: '12:00 - 18:00', group: 'Weekend' }
                  { id: 5, label: '12:00 - 19:00', group: 'Weekend' }
                  { id: 6, label: '13:00 - 19:00', group: 'Weekend' }]

        @selectedDate = null
        @selectedTime = null

        return

module.exports = PlaylistCtrl
