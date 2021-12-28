import app from '../app'

import SearchFilter from './search-filter'
import SortableColumn from './sortable-column'
import Authorization from './authorization'
import CurrentTrackList from './current-track-list'
import Terms from './terms'
import Track from './resource/track'
import TrackList from './resource/track-list'
import TrackListSync from './resource/track-list-sync'
import Suggestion from './resource/suggestion'
import Term from './resource/term'
import Artist from './resource/artist'
import Album from './resource/album'

app
  .service('SearchFilter', SearchFilter)
  .service('SortableColumn', SortableColumn)
  .service('Authorization', Authorization)
  .factory('CurrentTrackList', CurrentTrackList)
  .service('Terms', Terms)
  .factory('Track', Track)
  .factory('TrackList', TrackList)
  .factory('TrackListSync', TrackListSync)
  .factory('Suggestion', Suggestion)
  .factory('Term', Term)
  .factory('Artist', Artist)
  .factory('Album', Album)
