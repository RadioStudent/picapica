require 'angular'
    .module 'picapicaApp'
        .service 'SearchFilter',     require './search-filter'
        .service 'SortableColumn',   require './sortable-column'
        .service 'Authorization',    require './authorization'
        .factory 'CurrentTrackList', require './current-track-list'
        .service 'Terms',            require './terms'
        .factory 'Track',            require './resource/track'
        .factory 'TrackList',        require './resource/track-list'
        .factory 'TrackListSync',    require './resource/track-list-sync'
        .factory 'Suggestion',       require './resource/suggestion'
        .factory 'Term',             require './resource/term'
        .factory 'Artist',           require './resource/artist'
        .factory 'Album',            require './resource/album'
