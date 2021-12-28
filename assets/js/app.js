import angular from 'angular'
import resource from 'angular-resource'
import route from 'angular-route'
import sanitize from 'angular-sanitize'
import bootstrap from 'angular-bootstrap-npm/dist/angular-bootstrap-tpls'
import _ from 'lodash'
  
import 'jquery'
import 'angular-ui-sortable'
import 'ng-tags-input'
import 'angular-location-update'

const app = angular.module('picapicaApp', [route, sanitize, resource, bootstrap,  'ui.sortable', 'ngTagsInput', 'ngLocationUpdate'])
  .config([
    '$routeProvider',
    function ($routeProvider) {
      return $routeProvider.when('/search', {
        templateUrl: 'partials/track-search.tpl.html'
      }).when('/playlist/:tracklistId?', {
        templateUrl: 'partials/playlist.tpl.html'
      }).when('/album-editor/:albumId?', {
        templateUrl: 'partials/album-editor.tpl.html'
      }).when('/about', {
      templateUrl: 'partials/about.tpl.html'
      }).otherwise({
        redirectTo: '/search'
      });
    }
  ]).constant('_', _)

export default app
