/**
 * Color picker
 *
 * Require
 * - jquery-minicolors
 */

Charcoal.Admin.Property_Input_ColorPicker = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/colorpicker';

    this.input_id = null;

    this.colorpicker_selector = null;
    this.colorpicker_options  = null;

    this.set_properties(opts).create_colorpicker();
};

Charcoal.Admin.Property_Input_ColorPicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_ColorPicker.prototype.constructor = Charcoal.Admin.Property_Input_ColorPicker;
Charcoal.Admin.Property_Input_ColorPicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_ColorPicker.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;

    this.colorpicker_selector = opts.data.colorpicker_selector || this.colorpicker_selector;
    this.colorpicker_options  = opts.data.colorpicker_options  || this.colorpicker_options;

    var default_opts = {};

    this.colorpicker_options = $.extend({}, default_opts, this.colorpicker_options);

    return this;
};

Charcoal.Admin.Property_Input_ColorPicker.prototype.create_colorpicker = function ()
{
    $(this.colorpicker_selector).minicolors(this.colorpicker_options);

    return this;
};
