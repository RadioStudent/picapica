import angular from 'angular'

import app from '../app'

import TrackSearch from './track-search'
import Playlist from './playlist'
import AlbumEditor from './album-editor'

app
  .controller('TrackSearchController', TrackSearch)
  .controller('PlaylistController', Playlist)
  .controller('AlbumEditorController', AlbumEditor)
