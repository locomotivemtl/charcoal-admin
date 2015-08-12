/**
* Switch looking input that manages boolean properties
* charcoal/admin/property/input/switch
*
* Require:
* - jQuery
* - bootstrapSwitch
*
* @param  {Object}  opts Options for input property
*/

Charcoal.Admin.Property_Input_Switch = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/switch';

    // Property_Input_Switch properties
    this.input_id = null;
    this.input_selector = null;
    this.switch_selector = null;

    this.set_properties(opts).create_switch();
};
Charcoal.Admin.Property_Input_Switch.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Switch.prototype.constructor = Charcoal.Admin.Property_Input_Switch;
Charcoal.Admin.Property_Input_Switch.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Switch.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.input_selector = opts.data.input_selector || this.input_selector;
    this.switch_selector = opts.data.switch_selector || this.switch_selector;

    return this;
};

Charcoal.Admin.Property_Input_Switch.prototype.create_switch = function ()
{
    var that = this;

    $(that.switch_selector).bootstrapSwitch({
        onSwitchChange: function (event, state) {
            $(that.input_selector).val((state) ? 1 : 0);
        }
    });
};
