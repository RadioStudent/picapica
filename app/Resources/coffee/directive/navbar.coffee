ppNavbar = ($window, Authorization) ->
    templateUrl: '/partials/_navbar.tpl.html'
    scope:
        activeItem: '='
    link: (scope, element) ->
        scope.username = $window.username
        scope.user_roles = Authorization.user_roles
        scope.haveRole = Authorization.haveRole

module.exports = ppNavbar
