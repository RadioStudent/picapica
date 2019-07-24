formatDate = (isoString) ->
    dateObject = new Date(isoString)
    dateString =
        dateObject.getDate() + '. ' +
        (dateObject.getMonth() + 1) + '. ' +
        dateObject.getFullYear()

    return dateString


class PlaylistController
    constructor: (CurrentTrackList, TrackList, Terms, $filter, $rootScope, $q, _) ->
        @trackList = CurrentTrackList
        @trackLists = TrackList.query()
        @terms = Terms
        @loaders = { save: off }

        @printMetadata = {
            author: '',
            date: '',
            term: ''
        }

        @datepicker =
            options:
                formatYear: 'yy'
                startingDay: 1
            format: 'yyyy-MM-dd'
            opened: no
            open: => @datepicker.opened = yes

        $rootScope.$on 'tracklist.update', refresh

        @selectTrackList = ->
            if @trackList.id is '-1'
                @trackList.reset()
                refresh()
            else
                TrackList.get {id: @trackList.id}, (newTrackList) =>
                    _.assign @trackList, newTrackList
                    refresh()

        @save = ->
            @loaders.save = on
            @trackList.save().then () =>
                refresh()
                @trackLists = TrackList.query () => @loaders.save = off

        refresh = =>
            # Refresh duration meter at the bottom
            @totalDuration = _.map(@trackList.tracks, 'duration').filter(Number).reduce Math.sum

            # Refresh hidden metadata for printing
            term = _.find(@terms, {id: @trackList.termId})
            @printMetadata = {
                author: CurrentTrackList.authorName,
                date: formatDate(CurrentTrackList.date),
                term: term.time + ' (' + term.name + ')'
                duration: $filter('duration')(@totalDuration)
            }

        return

module.exports = PlaylistController
