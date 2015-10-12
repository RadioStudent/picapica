'use strict'

require 'angular'
    .module 'picapicaApp'
        .controller 'TrackSearchCtrl', require './track-search'
        .controller 'PlaylistCtrl',    require './playlist'
        .controller 'AlbumEditorCtrl', require './album-editor'
