'use strict'

describe 'services', ->

    beforeEach angular.mock.module('picapicaApp')

    beforeEach inject (IconGenerator, $sce) ->
        @sce = $sce
        @iconGenerator = IconGenerator

    describe 'Icon Generator', ->
        it 'should generate html with appropriate icon class', ->
            expect(@sce.getTrustedHtml(@iconGenerator.forType('artist'))).toBe '<span class="glyphicon glyphicon-user"></span>'
