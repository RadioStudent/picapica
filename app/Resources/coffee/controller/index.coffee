"use strict"

require "angular"
    .module "picapicaApp"
        .controller "AppCtrl",         require "./app"
        .controller "TrackSearchCtrl", require "./track-search"
        .controller "PlaylistCtrl",    require "./playlist"
