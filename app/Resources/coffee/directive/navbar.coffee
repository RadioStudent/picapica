ppNavbar = ($window) ->
    templateUrl: '/partials/_navbar.tpl.html'
    scope:
        activeItem: '='
    link: (scope, element) ->
        scope.username = $window.username
        scope.user_roles = $window.user_roles
        scope.haveRole = (role) -> scope.user_roles.indexOf(role) isnt -1

module.exports = ppNavbar
