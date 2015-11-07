describe 'Track Search Controller', ->

    beforeEach angular.mock.module('picapicaApp')

    beforeEach inject ($controller) ->
        @createController = () ->
            $controller('TrackSearchController')

    describe 'it has these methods', ->
        it 'should blabla', ->
            controller = @createController()
