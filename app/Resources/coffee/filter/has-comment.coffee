'use strict'

HasComment = () ->
    (track) -> typeof track.comment is 'string'

module.exports = HasComment
