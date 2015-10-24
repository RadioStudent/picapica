IconGenerator = ($rootScope, $sce) ->
    types =
        artist     : 'user'
        'artist.id': 'user'
        album      : 'cd'
        'album.id' : 'cd'
        track      : 'music'
        'track.id' : 'music'
        search     : 'search'

    service =
        forType: (type) ->
            $sce.trustAsHtml "<span class=\"glyphicon glyphicon-#{types[type]}\"></span>"

module.exports = IconGenerator
