module.exports = ($rootScope, $q, _, CurrentTrackList, TrackList, Terms, $filter, $routeParams, $scope, Authorization) ->

    new class PlaylistController
        constructor: ->
            @trackList = CurrentTrackList
            @trackLists = TrackList.query()
            @loaders = { save: off }
            @RTerms = Terms

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

            $rootScope.$on 'tracklist.update', @refresh

            if $routeParams.tracklistId
                @trackList.id = $routeParams.tracklistId
                @selectTrackList()


            $scope.haveRole = Authorization.haveRole

            return

        formatDate: (isoString) ->
            dateObject = new Date(isoString)
            dateString =
                dateObject.getDate() + '. ' +
                (dateObject.getMonth() + 1) + '. ' +
                dateObject.getFullYear()

            return dateString

        selectTrackList: () ->
            if @trackList.id is '-1'
                @trackList.reset()
                @refresh()
            else
                TrackList.get {id: @trackList.id}, (newTrackList) =>
                    _.assign @trackList, newTrackList
                    window.location.replace('#!/playlist/' + newTrackList.id)
                    @refresh()

        save: () ->
            @loaders.save = on
            @trackList.save().then () =>
                @refresh()
                @trackLists = TrackList.query () => @loaders.save = off

        refresh: () =>
            # Refresh duration meter at the bottom
            @totalDuration = _.map(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

            # Refresh hidden metadata for printing
            term = _.find(Terms, {id: @trackList.termId})
            @printMetadata = {
                author: @trackList.authorName,
                date: @formatDate(@trackList.date),
                term: term.time + ' (' + term.name + ')'
                duration: $filter('duration')(@totalDuration)
            }

        addMp3Track: () =>
            @trackList.tracks.push mp3: true, duration: 0
