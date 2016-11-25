/**
 * Selectize Picker
 *
 * Require
 * - selectize.js
 */

Charcoal.Admin.Property_Input_Selectize_Tags = function (opts) {
    this.input_type = 'charcoal/admin/property/input/selectize/tags';

    // Property_Input_Selectize_Tags properties
    this.input_id     = null;
    this.type         = null;
    this.title        = null;
    this.multiple     = false;
    this.separator    = ',';
    this._tags        = null;

    this.selectize_selector = null;
    this.selectize_options  = {};

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
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;
    this.type     = opts.data.obj_type || this.type;
    this.title    = opts.data.title || this.title;

    this.multiple  = opts.data.multiple || this.multiple;
    this.separator = opts.data.multiple_separator || this.multiple_separator || ',';

    this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
    this.selectize_options  = opts.data.selectize_options  || this.selectize_options;

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

    var default_opts = {
        plugins: plugins,
        delimiter: this.separator,
        persist: false,
        preload: true,
        openOnFocus: true,
        create: this.create_tag.bind(this),
        createFilter: function (input) {
            for (var item in this.options) {
                if (this.options[item].text === input) {
                    return false;
                }
            }
            return true;
        },
        load: this.load_tags.bind(this),
        onInitialize: function () {
            var self = this;
            self.sifter.iterator(this.items, function (value) {
                var option = self.options[value];
                var $item = self.getItem(value);
                $item.css('background-color', option.color/*[options.colorField]*/);
            });
        }
    };

    this.selectize_options = $.extend({}, default_opts, this.selectize_options);

    return this;
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.create_tag = function (input, callback) {
    var type  = this.type;
    var id    = this.id;
    var title = this.title;

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
            form_data: {
                name: input
            }
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
                    callback({
                        value: response.obj.id,
                        text:  response.obj.name[Charcoal.Admin.lang],
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
    var self = this;

    if (!query.length) {
        return callback();
    }
    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: self.type
        },
        type: 'GET',
        error: function () {
            callback();
        },
        success: function (res) {
            var items = [];
            for (var item in res.collection) {
                item = res.collection[item];
                items.push({
                    value: item.id,
                    text:  item.name[Charcoal.Admin.lang],
                    color: item.color
                });
            }
            callback(items);
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_selectize = function () {
    var selectize = $(this.selectize_selector).selectize(this.selectize_options);
    console.log(selectize);
};

