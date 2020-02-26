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

Charcoal.Admin.Property_Input_DualSelect = function (opts) {
    this.input_type = 'charcoal/admin/property/input/dualselect';

    // Property_Input_DualSelect properties
    this.input_id = null;

    this.dualselect_selector = null;
    this.dualselect_options  = {};

    // The instance of Multiselect
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
Charcoal.Admin.Property_Input_DualSelect.prototype.init = function () {
    this.create_dualselect();
};

Charcoal.Admin.Property_Input_DualSelect.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.dualselect_selector = opts.dualselect_selector || opts.data.dualselect_selector || this.dualselect_selector;
    this.dualselect_options  = opts.dualselect_options  || opts.data.dualselect_options  || this.dualselect_options;

    var default_options = {
        keepRenderingSort: false
    };

    if (opts.data.dualselect_options.searchable) {
        this.dualselect_options.search = {
            left:  this.dualselect_selector + '_searchLeft',
            right: this.dualselect_selector + '_searchRight'
        };
    }

    this.dualselect_options = $.extend({}, default_options, this.dualselect_options);

    return this;
};

Charcoal.Admin.Property_Input_DualSelect.prototype.create_dualselect = function () {
    $(this.dualselect_selector).multiselect(this.dualselect_options);

    return this;
};

/**
 * Sets the dualselect into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} dualselect The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.set_dualselect = function (dualselect) {
    this._dualselect = dualselect;
    return this;
};

/**
 * Returns the dualselect object
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.dualselect = function () {
    return this._dualselect;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.destroy = function () {
    var dualselect = this.dualselect();

    if (dualselect) {
        dualselect.remove();
    }
};
