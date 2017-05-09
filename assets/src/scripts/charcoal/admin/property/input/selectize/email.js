/**
 * Selectize Picker
 * List version.
 *
 * Require
 * - selectize.js
 */

;(function () {
    var Email = function (opts) {
        this.input_type = 'charcoal/admin/property/input/selectize';

        // Property_Input_Selectize properties
        this.input_id = null;
        this.obj_type = null;
        this.copy_items = false;
        this.title = null;
        this.translations = null;

        // Pattern refers to the form property that matches the text inputted through selectize.
        this.pattern = null;
        this.multiple = false;
        this.separator = ',';

        this.selectize = null;
        this.selectize_selector = null;
        this.form_ident = null;
        this.selectize_options = {};
        this.choice_obj_map = {};

        this.clipboard = null;
        this.allow_update = false;

        this.set_properties(opts).init();
    };
    Email.prototype = Object.create(Charcoal.Admin.Property_Input_Selectize.prototype);
    Email.constructor = Charcoal.Admin.Property_Input_Selectize;
    Email.parent =  Object.create(Charcoal.Admin.Property_Input_Selectize.prototype);

    Email.prototype.set_properties = function (opts) {
        this.input_id = opts.id || this.input_id;
        this.obj_type = opts.data.obj_type || this.obj_type;

        // Enables the copy button
        this.copy_items = opts.data.copy_items || this.copy_items;
        this.allow_update = opts.data.allow_update || this.allow_update;
        this.title = opts.data.title || this.title;
        this.translations = opts.data.translations || this.translations;
        this.pattern = opts.data.pattern || this.pattern;
        this.multiple = opts.data.multiple || this.multiple;
        this.separator = opts.data.multiple_separator || this.multiple_separator || ',';
        this.form_ident = opts.data.form_ident || this.form_ident;

        this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
        this.selectize_options = opts.data.selectize_options || this.selectize_options;
        this.choice_obj_map = opts.data.choice_obj_map || this.choice_obj_map;

        this.$input = $(this.selectize_selector || '#' + this.input_id);

        var plugins;
        if (this.multiple) {
            plugins = {
                // 'restore_on_backspace',
                drag_drop: {},
                charcoal_item: {}
            };

        } else {
            plugins = {
                charcoal_item: {}
            };
        }

        var objType = this.obj_type;
        var default_opts = {
            plugins: plugins,
            formData: {},
            delimiter: this.separator,
            persist: true,
            preload: 'focus',
            openOnFocus: true,
            searchField: ['value', 'text', 'email'],
            dropdownParent: this.$input.closest('.form-field'),

            createFilter: function (input) {
                for (var item in this.options) {
                    item = this.options[item];
                    if (item.text === input) {
                        return false;
                    }
                }
                return true;
            },
            onInitialize: function () {
                var self = this;
                self.sifter.iterator(this.items, function (value) {
                    var option = self.options[value];
                    var $item = self.getItem(value);

                    if (option.color) {
                        $item.css('background-color', option.color/*[options.colorField]*/);
                    }
                });
            },
            render: {
                item: function (item, escape) {
                    return '<div class="item">' +
                        (item.text ? '<span class="name">' + escape(item.text) + '</span>' : '') +
                        (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                            '</div>';
                },
                option: function (item, escape) {
                    return '<div class="option">' +
                        (item.text ? '<span class="name">' + escape(item.text) + '</span>' : '') +
                        (item.email ? '<span class="caption">' + escape(item.email) + '</span>' : '') +
                        '</div>';
                }
            }
        };

        if (objType) {
            default_opts.create = this.create_item.bind(this);
            default_opts.load = this.load_items.bind(this);
        } else {
            default_opts.plugins.create_on_enter = {};
            default_opts.create = function (input) {
                return {
                    value: input,
                    text: input
                };
            };
        }

        if (this.selectize_options.splitOn) {
            var splitOn = this.selectize_options.splitOn;
            if ($.type(splitOn) === 'array') {
                for (var i = splitOn.length - 1; i >= 0; i--) {
                    switch (splitOn[i]) {
                        case 'comma':
                            splitOn[i] = '\\s*,\\s*';
                            break;

                        case 'tab':
                            splitOn[i] = '\\t+';
                            break;

                        default:
                            splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    }
                }

                splitOn = splitOn.join('|');
            }

            this.selectize_options.splitOn = new RegExp(splitOn);
        }

        this.selectize_options = $.extend(true,{}, default_opts, this.selectize_options);

        return this;
    };

    Charcoal.Admin.Property_Input_Selectize_Email = Email;

}(jQuery, document));
