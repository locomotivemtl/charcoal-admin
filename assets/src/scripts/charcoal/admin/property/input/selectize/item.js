/* global Selectize */
Selectize.define('charcoal_item', function (options) {
    options = $.extend({
        classField: 'class',
        colorField: 'color',
    }, options);

    var self = this;
    var original = null;

    this.refreshItem = function (value, $item) {
        var option = self.options[value];

        if (option.hasOwnProperty(options.colorField)) {
            if (option[options.colorField]) {
                $item.addClass('has-color');
                $item.css('border-left-color', option[options.colorField]);
            }
            // $item.css('background-color', option[options.colorField]);
        }

        if (option.hasOwnProperty(options.classField)) {
            $item.addClass(option[options.classField]);
        }

        if (original) {
            return original.apply(this, arguments);
        }
    };

    this.refreshOption = function (value) {
        var option = self.options[value];
        self.refreshOptions(false);

        // Get all options including disabled ones
        var $option = self.getElementWithValue(value, self.$dropdown_content.find('.option'));

        if (option.hasOwnProperty(options.colorField)) {
            if (option[options.colorField]) {
                $option.addClass('has-color');
                $option.css('border-left-color', option[options.colorField]);
            }
        }

        if (original) {
            return original.apply(this, arguments);
        }
    };

    this.settings.onOptionAdd = (function () {
        original = null;

        // check if onItemAdd exists as it is an optional callback function
        if (self.settings.hasOwnProperty('onOptionAdd')) {
            original = self.settings.onOptionAdd;
        }

        return self.refreshOption;
    })();

    this.settings.onItemAdd = (function (/*value, $item*/) {
        original = null;

        // check if onItemAdd exists as it is an optional callback function
        if (self.settings.hasOwnProperty('onItemAdd')) {
            original = self.settings.onItemAdd;
        }

        return self.refreshItem;
    })();

});
