/**
* Datetime picker that manages datetime properties
* charcoal/admin/property/input/datetimepicker
*
* Require:
* - eonasdan-bootstrap-datetimepicker
*
* @param  {Object}  opts  Options for input property
*/

Charcoal.Admin.Property_Input_Datetimepicker = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/datetimepicker';

    // Property_Input_Datetimepicker properties
    this.input_id = null;
    this.datetimepicker_selector = null;
    this.datetimepicker_options = null;

    this.set_properties(opts).create_datetimepicker();
};
Charcoal.Admin.Property_Input_Datetimepicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Datetimepicker.prototype.constructor = Charcoal.Admin.Property_Input_Datetimepicker;
Charcoal.Admin.Property_Input_Datetimepicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Datetimepicker.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.datetimepicker_selector = opts.data.datetimepicker_selector || this.datetimepicker_selector;
    this.datetimepicker_options = opts.data.datetimepicker_options || this.datetimepicker_options;

    var default_opts = {

    };

    this.datetimepicker_options = $.extend({}, default_opts, this.datetimepicker_options);

    return this;
};

Charcoal.Admin.Property_Input_Datetimepicker.prototype.create_datetimepicker = function ()
{
    console.log(this.datetimepicker_options);
    $(this.datetimepicker_selector).datetimepicker(this.datetimepicker_options);
};
