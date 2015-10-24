'use strict'

durationFilter   = require('../app/Resources/coffee/filter/duration')()
hasCommentFilter = require('../app/Resources/coffee/filter/has-comment')()

describe 'filter', ->
    describe 'duration', ->
        it 'should convert seconds to H:MM:SS', ->
            expect(durationFilter 3600).toBe '1:00:00'
        it 'should always pad zeros to M:SS', ->
            expect(durationFilter 1).toBe '0:01'
        it 'should return "/" if no value is passed', ->
            expect(durationFilter null).toBe '/'
    describe 'hasComment', ->
        it 'should return true if track has comment property of type string', ->
            track =
                comment: 'This track has comment'
            expect(hasCommentFilter track).toBe true
        it 'should return false if track doesn\'t have comment property', ->
            track = {}
            expect(hasCommentFilter track).toBe false
        it 'should return false if track has comment property of type other than string', ->
            track1 =
                comment: 1
            track2 =
                comment: true
            track3 =
                comment:
                    value: 'comment is object'
            expect(hasCommentFilter track1).toBe false
            expect(hasCommentFilter track2).toBe false
            expect(hasCommentFilter track3).toBe false
