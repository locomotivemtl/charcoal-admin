/**
* charcoal/admin/property/input/switch
*
* Require:
* - jQuery
* - bootstrapSwitch
*/
Charcoal.Admin.Property_Input_Switch = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/switch';

    this.input_id = opts.input_id || null;

    var defaults = {
        input_selector: null,
        switch_selector: null
    };

    this.options = $.extend({}, defaults, opts.data);

    this.create_switch();
};
Charcoal.Admin.Property_Input_Switch.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Switch.prototype.constructor = Charcoal.Admin.Property_Input_Switch;
Charcoal.Admin.Property_Input_Switch.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Switch.prototype.create_switch = function ()
{
    var that = this;

    $(that.options.switch_selector).bootstrapSwitch({
        onSwitchChange: function (event, state) {
            $(that.options.input_selector).val((state) ? 1 : 0);
        }
    });
};
