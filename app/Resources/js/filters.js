'use strict';

/* Services */

var picapicaFilters = angular.module('picapicaFilters', []);

picapicaFilters.filter('secondsToTime', function() {
    return function(seconds) {
        if(seconds) {
            var hours = Math.floor(seconds / 3600),
                minutes = Math.floor(seconds % 3600 / 60),
                seconds = seconds % 60,
                timeString = '';

            timeString += hours > 0 ? hours + ':' : '';
            timeString += (hours > 0 && minutes < 10 ? '0' + minutes : minutes) + ':';
            timeString += seconds < 10 ? '0' + seconds : seconds;

            return timeString;
        } else {
            return '/';
        }
    };
});
