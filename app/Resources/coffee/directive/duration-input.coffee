durationInput = () ->
    restrict: 'A',
    require: 'ngModel'
    link: (scope, element, attrs, ngModel) ->
        return unless ngModel

        digitize = (n) -> if (n < 10) then ("0" + n) else n

        ngModel.$parsers.push (value) ->
            split = value.split(':')
            return parseInt(split[0]) * 60 + parseInt(split[1])

        ngModel.$formatters.push (value) -> parseInt(value / 60) + ":" + digitize(value % 60)

module.exports = durationInput
