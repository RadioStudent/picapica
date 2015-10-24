require 'angular'
    .module 'picapicaApp'
        .filter 'duration',   require './duration'
        .filter 'hasComment', require './has-comment'
