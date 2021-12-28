Capitalise = () ->
    (string) ->
        throw new TypeError('Capitalise filter parameter must be a string.') if typeof string isnt 'string'
        string[0].toUpperCase() + string.slice 1

module.exports = Capitalise
