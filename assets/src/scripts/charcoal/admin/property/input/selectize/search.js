/**
 * Selectize Picker
 * Search.
 *
 * Require
 * - selectize.js
 */

Charcoal.Admin.Property_Input_Selectize_Search = function (opts) {
    this.input_type = 'charcoal/admin/property/input/selectize/search';

    // Property_Input_Selectize_Search properties
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
Charcoal.Admin.Property_Input_Selectize_Search.prototype             = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Selectize_Search.prototype.constructor = Charcoal.Admin.Property_Input_Selectize_Search;
Charcoal.Admin.Property_Input_Selectize_Search.prototype.parent      = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Selectize_Search.prototype.init = function () {

    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    this.init_selectize();
};

Charcoal.Admin.Property_Input_Selectize_Search.prototype.set_properties = function (opts) {
    this.input_id   = opts.id || this.input_id;
    this.obj_type   = opts.data.obj_type || this.obj_type;
    this.copy_items = opts.data.copy_items || this.copy_items;
    this.title      = opts.data.title || this.title;

    this.multiple  = opts.data.multiple || this.multiple;
    this.separator = opts.data.multiple_separator || this.multiple_separator || ',';

    this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
    this.selectize_options  = opts.data.selectize_options || this.selectize_options;

    this.$input = $(this.selectize_selector || '#' + this.input_id);

    var plugins;
    if (this.multiple) {
        plugins = [
            // 'restore_on_backspace',
            'remove_button',
            'drag_drop'
        ];
    }

    var default_opts = {
        plugins: plugins,
        formData: {},
        delimiter: this.separator,
        persist: false,
        preload: true,
        openOnFocus: true
    };

    this.selectize_options = $.extend({}, default_opts, this.selectize_options);

    return this;
};

Charcoal.Admin.Property_Input_Selectize_Search.prototype.init_selectize = function () {
    var $select    = this.$input.selectize(this.selectize_options);
    this.selectize = $select[0].selectize;
};
