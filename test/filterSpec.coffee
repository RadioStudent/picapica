capitaliseFilter = require('../app/Resources/coffee/filter/capitalise')()
durationFilter   = require('../app/Resources/coffee/filter/duration')()
hasCommentFilter = require('../app/Resources/coffee/filter/has-comment')()
iconFilter       = require('../app/Resources/coffee/filter/icon')()

describe 'filter', ->
    describe 'capitalise', ->
        it 'should capitalise strings', ->
            expect(capitaliseFilter 'abc').toBe 'Abc'
        it 'should throw TypeError unless a string is passed', ->
            expect(-> capitaliseFilter 123).toThrow(new TypeError 'Capitalise filter parameter must be a string.')
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
    describe 'icon', ->
        it 'should generate html with appropriate icon class', ->
            expect(iconFilter 'artist').toBe '<span class="glyphicon glyphicon-user"></span>'
