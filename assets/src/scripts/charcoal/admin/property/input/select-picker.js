/**
 * Select Picker
 *
 * Require
 * - silviomoreto/bootstrap-select
 */

Charcoal.Admin.Property_Input_SelectPicker = function (opts) {
    this.input_type = 'charcoal/admin/property/input/select';

    // Property_Input_SelectPicker properties
    this.input_id = null;

    this.select_selector = null;
    this.select_options  = null;

    this.set_properties(opts).create_select();
};

Charcoal.Admin.Property_Input_SelectPicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_SelectPicker.prototype.constructor = Charcoal.Admin.Property_Input_SelectPicker;
Charcoal.Admin.Property_Input_SelectPicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_SelectPicker.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.select_selector = opts.data.select_selector || this.select_selector;
    this.select_options  = opts.data.select_options  || this.select_options;

    var default_opts = {};

    this.select_options = $.extend({}, default_opts, this.select_options);

    return this;
};

Charcoal.Admin.Property_Input_SelectPicker.prototype.create_select = function () {
    $(this.select_selector).selectpicker(this.select_options);

    return this;
};
