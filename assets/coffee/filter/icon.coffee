Icon = () ->
    (type) ->
        types =
            artist      : 'user'
            'artist.id' : 'user'
            album       : 'cd'
            'album.id'  : 'cd'
            track       : 'music'
            'track.id'  : 'music'
            search      : 'search'

        "<span class=\"glyphicon glyphicon-#{types[type]}\"></span>"

module.exports = Icon
