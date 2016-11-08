/**
* TinyMCE implementation for WYSIWYG inputs
* charcoal/admin/property/input/tinymce
*
* Require:
* - jQuery
* - tinyMCE
*
* @param  {Object}  opts Options for input property
*/

Charcoal.Admin.Property_Input_DualSelect = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/dualselect';

    // Property_Input_DualSelect properties
    this.input_id = null;
    this.dualinput_options = {};
    this._dualselect = null;

    this.set_properties(opts).init();
};
Charcoal.Admin.Property_Input_DualSelect.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_DualSelect.prototype.constructor = Charcoal.Admin.Property_Input_DualSelect;
Charcoal.Admin.Property_Input_DualSelect.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.init = function ()
{
    this.create_dualinput();
};

Charcoal.Admin.Property_Input_DualSelect.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.dualinput_options = opts.dualinput_options || opts.data.dualinput_options || this.dualinput_options;

    var id = '#' + this.input_id;

    var default_options = {
        keepRenderingSort: false
    };

    if (opts.data.dualinput_options.searchable) {
        this.dualinput_options.search = {
            left: id + '_searchLeft',
            right: id + '_searchRight'
        };
    }

    this.dualinput_options = $.extend({}, default_options, this.dualinput_options);
    return this;
};

Charcoal.Admin.Property_Input_DualSelect.prototype.create_dualinput = function ()
{
    $('#' + this.input_id).multiselect(this.dualinput_options);
};

/**
 * Sets the dualselect into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} dualselect The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.set_dualselect = function (dualselect)
{
    this._dualselect = dualselect;
    return this;
};

/**
 * Returns the dualselect object
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.dualselect = function ()
{
    return this._dualselect;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.destroy = function ()
{
    var dualselect = this.dualselect();

    if (dualselect) {
        dualselect.remove();
    }
};
