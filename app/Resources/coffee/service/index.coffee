"use strict"

require "angular"
    .module "picapicaApp"
        .service "SearchFilter", require "./search-filter"
        .factory "Track", require "./track"
