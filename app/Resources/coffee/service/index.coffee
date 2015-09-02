"use strict"

require "angular"
    .module "picapicaApp"
        .service "SearchFilter",   require "./search-filter"
        .service "SortableColumn", require "./sortable-column"
        .service "SelectedTracks", require "./selected-tracks"
        .factory "Track",          require "./resource/track"
        .factory "Suggestion",     require "./resource/suggestion"
