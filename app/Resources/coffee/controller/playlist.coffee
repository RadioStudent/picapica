module.exports = ($rootScope, $q, _, CurrentTrackList, TrackList, TrackListSync, Terms, $filter, $routeParams, $scope, Authorization, $location, $uibModal) ->

    new class PlaylistController
        constructor: ->
            @trackList = CurrentTrackList
            @trackLists = TrackList.query()
            @loaders = { save: off }
            @RTerms = Terms
            @$location = $location

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
                @loadTrackList()


            $scope.haveRole = Authorization.haveRole

            return

        formatDate: (isoString) ->
            dateObject = new Date(isoString)
            dateString =
                dateObject.getDate() + '. ' +
                (dateObject.getMonth() + 1) + '. ' +
                dateObject.getFullYear()

            return dateString

        loadTrackList: () ->
            if @trackList.id is '-1'
                @trackList.reset()
                @refresh()
            else
                return unless @trackList.id
                TrackList.get {id: @trackList.id}, (newTrackList) =>
                    _.assign @trackList, newTrackList
                    @$location.update_path('/playlist/' + newTrackList.id, true)
                    @refresh()

        save: () ->
            @loaders.save = on
            @trackList.save().$promise.then(@handleSuccess, @handleError)

        handleSuccess: () =>
            @loaders.save = off
            @refresh()
            #@trackLists = TrackList.query () => @loaders.save = off

        handleError: (resp) =>
            @loaders.save = off
            if resp.data[0]
                alert 'Napaka pri shranjevanju: ' + resp.data[0].error.message
            else if resp.data.error
                alert 'Napaka pri shranjevanju: ' + resp.data.error.message
            else
                alert 'Napaka pri shranjevanju'

        refresh: () =>
            # Refresh duration meter at the bottom
            @totalDuration = _.map(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0
            Terms.$promise.then () => @resetMetadata()

        getTotalDuration: () => _.map(@trackList.tracks, 'duration').filter(Number).reduce ((a, b) -> a + b), 0

        resetMetadata: () =>
            term = _.find(Terms, {id: @trackList.termId})
            @printMetadata = {
                author: @trackList.authorName,
                date: @formatDate(@trackList.date),
                term: term.time + ' (' + term.name + ')'
                duration: $filter('duration')(@getTotalDuration())
            }

        addMp3Track: () =>
            @trackList.tracks.push mp3: true, duration: 0

        # Copy playlist text to clipboard
        copyClipboard: () =>
            reduceTrack = (longText, track, idx) =>
                data = [idx+1 + " " + track.fid, track.artistName, track.name, track.albumName, track.year, $filter('duration')(track.duration)].map (val) => if val then val else "/"

                trackTxt = data.join "\t"

                if track.comment
                    trackTxt += "\n\t" + track.comment

                longText + trackTxt + "\n"

            text = @trackList.tracks.reduce reduceTrack, ""
            text = ['# FID', 'ARTIST', 'TITLE', 'ALBUM', 'YEAR', 'LENGTH'].join("\t") + "\n" + text

            clipboard = document.getElementById 'clipboard-container'
            clipboard.innerHTML = text

            clipboard.select()
            clipboard.focus()
            document.execCommand("copy")

        triggerPrint: () -> window.print()

        syncToWebsite: () ->
            trackList = @trackList
            closeSync = @closeSync

            $uibModal.open
                animation: true
                size: 'lg'
                templateUrl: 'partials/playlist-sync-modal.tpl.html'
                controller: ($scope) ->
                    $scope.trackList = trackList
                    $scope.duration = $filter('duration')

                    $scope.syncPlaylist = () =>
                        sync = new TrackListSync
                            id: trackList.id
                            body: document.getElementById('tracklist-sync').innerHTML

                        syncError = () -> alert 'There was an error syncing to website'

                        sync.$save @closeSync, syncError

        closeSync: () ->
            $('#syncModal').hide()
