/* global Selectize */
Selectize.define('charcoal_item', function (options) {
    options = $.extend({
        classField: 'class',
        colorField: 'color',
    }, options);

    var self = this;

    this.settings.onItemAdd = (function (/*value, $item*/) {
        var original = null;

        // check if onItemAdd exists as it is an optional callback function
        if (self.settings.hasOwnProperty('onItemAdd')) {
            original = self.settings.onItemAdd;
        }

        return function (value, $item) {
            var option = self.options[value];
            if (option.hasOwnProperty(options.colorField)) {
                $item.css('background-color', option[options.colorField]);
            }

            if (option.hasOwnProperty(options.classField)) {
                $item.addClass(option[options.classField]);
            }

            if (original) {
                return original.apply(this, arguments);
            }
        };
    })();

});
