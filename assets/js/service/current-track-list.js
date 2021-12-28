// Generated by CoffeeScript 2.6.1
(function() {
  module.exports = function($rootScope, $q, _, TrackList) {
    var CurrentTrackList;
    return new (CurrentTrackList = class CurrentTrackList {
      constructor() {
        this.handleSuccess = this.handleSuccess.bind(this);
        this.reset();
      }

      toggleTrack(track) {
        if (track.mp3 && !track.fid) {
          _.remove(this.tracks, (function(t) {
            return t.mp3 && !t.fid;
          }));
        } else if (this.hasTrack(track)) {
          _.remove(this.tracks, (function(t) {
            return t.fid === track.fid;
          }));
        } else {
          this.tracks.push(angular.copy(track));
        }
        return $rootScope.$broadcast('tracklist.update');
      }

      hasTrack(track) {
        return _.some(this.tracks, {
          fid: track.fid
        });
      }

      addCommentToTrack(track) {
        return track.comment = '';
      }

      removeCommentFromTrack(track) {
        return delete track.comment;
      }

      reset() {
        var emptyTrackList;
        emptyTrackList = {
          id: '-1',
          date: new Date().toISOString(),
          name: 'New playlist',
          termId: 1,
          comment: '',
          tracks: []
        };
        return _.assign(this, emptyTrackList);
      }

      save() {
        if (this.id === '-1') {
          return TrackList.save({}, this, this.handleSuccess);
        } else {
          return TrackList.update({
            id: this.id
          }, this, this.handleSuccess);
        }
      }

      handleSuccess(resource) {
        return this.id = resource.id;
      }

    })();
  };

}).call(this);
