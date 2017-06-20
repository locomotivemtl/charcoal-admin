/* global Clipboard */
/**
 * Selectize Picker
 * Search.
 *
 * Require
 * - selectize.js
 */

;(function () {

    var Selectize = function (opts) {
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
        this.selectize_property_ident = null;
        this.selectize_obj_type = null;
        this.selectize_templates = {};

        this.clipboard = null;
        this.allow_update = null;

        this.set_properties(opts).init();
    };
    Selectize.prototype = Object.create(Charcoal.Admin.Property.prototype);
    Selectize.constructor = Charcoal.Admin.Property_Input_Selectize;
    Selectize.parent = Charcoal.Admin.Property.prototype;

    Selectize.prototype.init = function () {
        // if (typeof $.fn.sortable !== 'function') {
        //     var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        //     Charcoal.Admin.loadScript(url, this.init.bind(this));
        //
        //     return this;
        // }

        this.init_selectize();
        this.init_clipboard();
        this.init_allow_update();
        this.init_allow_create();

        var self = this;

        this.selectize.on('update_item', function (e) {
            self.create_item(null, e.callback, {
                id: e.value,
                step: 0
            });
        });
    };

    Selectize.prototype.set_properties = function (opts) {
        this.input_id = opts.id || this.input_id;
        this.obj_type = opts.data.obj_type || this.obj_type;

        // Enables the copy button
        this.copy_items = opts.data.copy_items || this.copy_items;
        this.allow_update = opts.data.allow_update || this.allow_update;
        this.allow_create = opts.data.allow_create || this.allow_create;
        this.title = opts.data.title || this.title;
        this.translations = opts.data.translations || this.translations;
        this.pattern = opts.data.pattern || this.pattern;
        this.multiple = opts.data.multiple || this.multiple;
        this.separator = opts.data.multiple_separator || this.multiple_separator || ',';
        this.form_ident = opts.data.form_ident || this.form_ident;

        this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
        this.selectize_options = opts.data.selectize_options || this.selectize_options;
        this.choice_obj_map = opts.data.choice_obj_map || this.choice_obj_map;
        this.selectize_property_ident = opts.data.selectize_property_ident || this.selectize_property_ident;
        this.selectize_obj_type = opts.data.selectize_obj_type || this.selectize_obj_type;
        this.selectize_templates = opts.data.selectize_templates || this.selectize_templates;

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
            labelField: 'label',
            searchField: ['value', 'label'],
            dropdownParent: this.$input.closest('.form-field'),
            render: {},
            createFilter: function (input) {
                for (var item in this.options) {
                    item = this.options[item];
                    if (item.label === input) {
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
            }
        };

        if (this.selectize_templates.item) {
            default_opts.render.item = function (item, escape) {
                if (item.item_render) {
                    return '<div class="item">' + item.item_render + '</div>';
                }
                return '<div class="item">' + escape(item[default_opts.labelField]) + '</div>';
            };
        }

        if (this.selectize_templates.option) {
            default_opts.render.option = function (option, escape) {
                if (option.option_render) {
                    return '<div class="option">' + option.option_render + '</div>';
                }
                return '<div class="option">' + escape(option[default_opts.labelField]) + '</div>';
            };
        }

        if (objType) {
            default_opts.create = this.create_item.bind(this);
            default_opts.load = this.load_items.bind(this);
        } else {
            default_opts.plugins.create_on_enter = {};
            default_opts.create = function (input) {
                return {
                    value: input,
                    label: input
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

        this.selectize_options = $.extend(true, {}, default_opts, this.selectize_options);

        return this;
    };

    Selectize.prototype.create_item = function (input, callback, opts) {
        var form_data = {};
        opts = opts || {};
        var pattern = this.pattern;
        var self = this;
        var type = this.obj_type;
        var title = this.title;
        var translations = this.translations;
        var settings = this.selectize_options;
        var step = opts.step || 0;
        var form_ident = this.form_ident;
        var submit_label = null;
        var id = opts.id || null;
        var selectize_property_ident = this.selectize_property_ident;
        var selectize_obj_type = this.selectize_obj_type;

        // Get the form ident
        if (form_ident && typeof form_ident === 'object') {
            if (!id && form_ident.create) {
                // The object must be created using 2 pop-up
                form_ident = form_ident.create;
                title += ' - ' + translations.statusTemplate.replaceMap({
                        '[[ current ]]': 1,
                        '[[ total ]]': 2
                    });
                step = 1;
                submit_label = 'Next';
            } else if (id && form_ident.update) {
                form_ident = form_ident.update;

                if (step === 2) {
                    title += ' - ' + translations.statusTemplate.replaceMap({
                            '[[ current ]]': 2,
                            '[[ total ]]': 2
                        });
                    submit_label = 'Finish';
                }
            } else {
                form_ident = null;
            }
        }

        if ($.isEmptyObject(settings.formData)) {
            if (pattern) {
                if (input) {
                    form_data[pattern] = input;
                }
            } else {
                if (input) {
                    form_data[this.choice_obj_map.label] = input;
                }
            }
            form_data.form_ident = form_ident;
            form_data.submit_label = submit_label;
        } else if (input) {
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

        if (step > 0) {
            data.type = BootstrapDialog.TYPE_PRIMARY;
        }

        var dialog = this.dialog(data, function (response) {
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
                    extra_form_data: {
                        selectize_obj_type: selectize_obj_type,
                        selectize_prop_ident: selectize_property_ident
                    },
                    save_action: 'selectize/save',
                    update_action: 'selectize/update',
                    suppress_feedback: (step === 1),
                    save_callback: function (response) {

                        var callbackOptions = {
                            class: 'new'
                        };

                        var selectizeResponse = response.selectize[0];

                        if (selectizeResponse) {
                            $.extend(true, callbackOptions, selectizeResponse);
                        }

                        callback(callbackOptions);

                        dialog.close();
                        if (step === 1) {
                            self.create_item(input, callback, {
                                id: selectizeResponse.value,
                                step: 2
                            });
                        }
                    }
                });

                // Re render.
                // This is not good.
                Charcoal.Admin.manager().render();
            }
        });
    };

    Selectize.prototype.load_items = function (query, callback) {
        var type = this.obj_type;
        var selectize_property_ident = this.selectize_property_ident;
        var selectize_obj_type = this.selectize_obj_type;

        var form_data = {
            obj_type: type,
            selectize_obj_type: selectize_obj_type,
            selectize_prop_ident: selectize_property_ident
        };

        $.ajax({
            url: Charcoal.Admin.admin_url() + 'selectize/load',
            data: form_data,
            type: 'GET',
            error: function () {
                callback();
            },
            success: function (response) {
                var items = [];

                var selectizeResponse = response.selectize;

                for (var item in selectizeResponse) {
                    if (selectizeResponse.hasOwnProperty(item)) {
                        item = selectizeResponse[item];

                        items.push(item);
                    }
                }
                callback(items);
            }
        });
    };

    Selectize.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

    Selectize.prototype.init_selectize = function () {
        var $select = this.$input.selectize(this.selectize_options);

        this.selectize = $select[0].selectize;
    };

    Selectize.prototype.init_allow_create = function () {
        if(!this.allow_create) {
            return;
        }

        var selectize = this.selectize;
        var $createButton = $(this.selectize_selector + '_create');
        var self = this;

        $createButton.on('click', function () {
            self.create_item(null, function (item) {
                // Create the item.
                if (item && item.value) {
                    selectize.addOption(item);
                    selectize.addItem(item.value);
                }
            });
        });
    };

    Selectize.prototype.init_allow_update = function () {
        switch (this.selectize.settings.mode) {
            case 'single' :
                this.allow_update_single();
                break;
            case 'multiple' :
                this.allow_update_multiple();
                break;
        }
    };

    Selectize.prototype.allow_update_single = function () {
        if (!this.allow_update) {
            return;
        }

        var selectize = this.selectize;
        var $updateButton = $(this.selectize_selector + '_update');
        var self = this;

        $updateButton.on('click', function () {
            var selectedItem = selectize.items;
            if (selectedItem) {
                self.create_item(null, function (item) {
                    // Update the item.
                    if (item && item.value) {
                        selectize.updateOption(selectedItem[0], item);
                    }
                }, {
                    id: selectedItem[0],
                    step: 0
                });
            }
        });
    };

    Selectize.prototype.allow_update_multiple = function () {
        if (!this.allow_update) {
            return;
        }

        var selectize = this.selectize;
        var $updateButton = $(this.selectize_selector + '_update');
        var id = null;
        var self = this;

        // Start by disabling update button.
        $updateButton[0].disabled = true;

        $updateButton.on('click', function () {
            if (id) {
                self.create_item(null, function (item) {
                    // Update the item.
                    if (item && item.value) {
                        selectize.updateOption(id, item);
                    }
                }, {
                    id: id,
                    step: 0
                });
            }
        });

        selectize.on('blur', function () {
            setTimeout(function () {
                $updateButton[0].disabled = true;
            }, 500);
        });

        selectize.$control.on('mousedown', '*:not(input)', function (e) {
            id = $(e.target).eq(0).data('value');

            if (selectize.$control.find('.active:not(input)')) {
                $updateButton[0].disabled = false;
            }
        });
    };

    Selectize.prototype.init_clipboard = function () {
        if (!this.copy_items) {
            return;
        }

        var selectize = this.selectize;

        this.clipboard = new Clipboard(this.selectize_selector + '_copy', {
            text: function () {
                return selectize.$input.val();
            }
        });
    };

    Charcoal.Admin.Property_Input_Selectize = Selectize;

}(jQuery, document));
