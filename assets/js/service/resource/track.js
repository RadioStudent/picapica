// Generated by CoffeeScript 2.6.1
(function() {
  var Track;

  Track = function($resource) {
    return $resource('api/v1/tracks', null, {
      search: {
        params: {
          size: 100
        },
        isArray: true
      }
    });
  };

  module.exports = Track;

}).call(this);
