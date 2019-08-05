module.exports = ($window) ->
    new class Authorization
        constructor: ->
            @userRoles = $window.user_roles or []

        haveRole: (role) => @userRoles.indexOf(role) isnt -1
