/* global Clipboard */
/**
 * Selectize Picker
 *
 * Require
 * - selectize.js
 */

Charcoal.Admin.Property_Input_Selectize_Tags = function (opts) {
    this.input_type = 'charcoal/admin/property/input/selectize/tags';

    // Property_Input_Selectize_Tags properties
    this.input_id   = null;
    this.obj_type   = null;
    this.copy_items = false;
    this.title      = null;
    this.multiple   = false;
    this.separator  = ',';
    this._tags      = null;

    this.selectize          = null;
    this.selectize_selector = null;
    this.selectize_options  = {};

    this.clipboard = null;

    this.set_properties(opts).init();
};
Charcoal.Admin.Property_Input_Selectize_Tags.prototype             = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Selectize_Tags.prototype.constructor = Charcoal.Admin.Property_Input_Selectize_Tags;
Charcoal.Admin.Property_Input_Selectize_Tags.prototype.parent      = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init = function () {
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }

    this.init_selectize();
    this.init_clipboard();
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.set_properties = function (opts) {
    this.input_id   = opts.id || this.input_id;
    this.obj_type   = opts.data.obj_type || this.obj_type;
    this.copy_items = opts.data.copy_items || this.copy_items;
    this.title      = opts.data.title || this.title;

    this.multiple  = opts.data.multiple || this.multiple;
    this.separator = opts.data.multiple_separator || this.multiple_separator || ',';

    this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
    this.selectize_options  = opts.data.selectize_options || this.selectize_options;

    this.$input = $(this.selectize_selector || '#' + this.input_id);

    // var selectedItems = this.tags_initialized();
    var plugins;
    if (this.multiple) {
        plugins = [
            // 'restore_on_backspace',
            'remove_button',
            'drag_drop',
            'charcoal_item'
        ];
    } else {
        plugins = [
            'charcoal_item'
        ];
    }

    var objType      = this.obj_type;
    var default_opts = {
        plugins: plugins,
        formData: {},
        delimiter: this.separator,
        persist: false,
        preload: true,
        openOnFocus: true,
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
                var $item  = self.getItem(value);

                if (option.color) {
                    $item.css('background-color', option.color/*[options.colorField]*/);
                }
            });
        }
    };

    if (objType) {
        default_opts.create = this.create_tag.bind(this);
        default_opts.load   = this.load_tags.bind(this);
    } else {
        default_opts.plugins.push('create_on_enter');
        default_opts.create = function (input) {
            return {
                value: input,
                text: input
            };
        };
    }

    this.selectize_options = $.extend({}, default_opts, this.selectize_options);

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

    return this;
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.create_tag = function (input, callback) {
    var type      = this.obj_type;
    var id        = this.id;
    var title     = this.title;
    var settings  = this.selectize_options;
    var form_data = {};

    if ($.isEmptyObject(settings.formData)) {
        form_data = {
            name: input
        };
    } else {
        form_data = $.extend({}, settings.formData);
        $.each(form_data, function (key, value) {
            if (value === ':input') {
                form_data[key] = input;
            }
        });
    }

    var data = {
        title: title,
        size: BootstrapDialog.SIZE_WIDE,
        cssClass: '-quick-form',
        dialog_options: {
            onhide: function () {
                callback({
                    return: false
                });
            }
        },
        widget_type: 'charcoal/admin/widget/quickForm',
        widget_options: {
            obj_type: type,
            obj_id: id,
            form_data: form_data
        }
    };

    this.dialog(data, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id: response.widget_id,
                type: 'charcoal/admin/widget/quick-form',
                data: {
                    obj_type: type
                },
                obj_id: id,
                save_callback: function (response) {
                    var label = response.obj.id;
                    if ('name' in response.obj && response.obj.name) {
                        label = response.obj.name[Charcoal.Admin.lang()] || response.obj.name;
                    }

                    callback({
                        value: response.obj.id,
                        text:  label,
                        color: response.obj.color,
                        class: 'new'
                    });
                    BootstrapDialog.closeAll();
                }
            });
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.load_tags = function (query, callback) {
    var type = this.obj_type;

    if (!query.length) {
        return callback();
    }

    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: type
        },
        type: 'GET',
        error: function () {
            callback();
        },
        success: function (res) {
            var items = [];
            for (var item in res.collection) {
                item = res.collection[item];
                var label = item.id;
                if ('name' in item && item.name) {
                    label = item.name[Charcoal.Admin.lang()] || item.name;
                }

                items.push({
                    value: item.id,
                    text:  label,
                    color: item.color
                });
            }
            callback(items);
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.onKeyDown = function (event) {
    var self   = this;
    var isTemp = false;
    var IS_MAC = /Mac/.test(navigator.userAgent);

    if (self.isLocked) {
        if (event.keyCode !== 9) {
            event.preventDefault();
        }
    }

    if ($.type(self.isCmdDown) === 'undefined') {
        isTemp         = true;
        self.isCmdDown = event[IS_MAC ? 'metaKey' : 'ctrlKey'];
    }

    if (self.isCmdDown && event.keyCode === 67) {
        if (isTemp) {
            self.isCmdDown = undefined;
        }

        if (self.$activeItems.length) {
            var values = [], i = 0, n = self.$activeItems.length;
            for (; i < n; i++) {
                values.push($(self.$activeItems[i]).attr('data-value'));
                /** @todo Select Active Values */
                document.execCommand('copy');
            }
        }

        return;
    }

    if ((self.isFull() || self.isInputHidden) && !(IS_MAC ? event.metaKey : event.ctrlKey)) {
        event.preventDefault();
        return;
    }
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_selectize = function () {
    var $select    = this.$input.selectize(this.selectize_options);
    this.selectize = $select[0].selectize;

    /*
     if (this.copy_items) {
     var that = this;
     this.selectize.$control.on('keydown', function () {
     return that.onKeyDown.apply(that.selectize, arguments);
     });
     }
     */
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_clipboard = function () {
    if (!this.copy_items) {
        return;
    }

    var selectize  = this.selectize;
    this.clipboard = new Clipboard(this.selectize_selector + '_copy', {
        text: function (/*trigger*/) {
            /*
             if (selectize.$activeItems.length) {
             console.log(selectize.$activeItems);
             }
             */

            return selectize.$input.val();
        }
    });
};
