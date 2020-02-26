/**
 * Base Property Input (charcoal/admin/property/input)
 *
 * Should mimic the PHP equivalent AbstractProperty
 * This will prevent multiple directions in property implementation
 * by giving multiple usefull methods such as ident, val, etc.
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Property}
 */
Charcoal.Admin.Property = function (opts) {
    Charcoal.Admin.Component.call(this, opts);

    /* jshint ignore:start */
    this._ident;
    this._val;
    this._input_type;
    this.data;
    /* jshint ignore:end */

    if (opts) {
        if (typeof opts.ident === 'string') {
            this.set_ident(opts.ident);
        }

        if (typeof opts.val !== 'undefined') {
            this.set_val(opts.val);
        }

        if (typeof opts.input_type !== 'undefined') {
            this.set_input_type(opts.input_type);
        }

        this.data = opts;
    }

    return this;
};

Charcoal.Admin.Property.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Property.prototype.constructor = Charcoal.Admin.Property;
Charcoal.Admin.Property.prototype.parent = Charcoal.Admin.Component.prototype;

/**
 * @override Charcoal.Admin.Property.prototype.element
 *
 * @return {?jQuery} The related jQuery element.
 */
Charcoal.Admin.Property.prototype.element = function () {
    if (!this._element) {
        if (!this.id()) {
            return null;
        }
        this.set_element('#' + this.id());
    }

    return this._element;
};

/**
 * @param  {String} ident - The component instance identifier.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_ident = function (ident) {
    this._ident = ident;
    return this;
};

/**
 * @return {?String} The component instance identifier.
 */
Charcoal.Admin.Property.prototype.ident = function () {
    return this._ident;
};

/**
 * @param  {String} input_type - The component form control type.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_input_type = function (input_type) {
    this._input_type = input_type;
    return this;
};

/**
 * @return {?String} The component form control type.
 */
Charcoal.Admin.Property.prototype.input_type = function () {
    return this._input_type;
};

/**
 * @param  {*} val - The component instance value.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_val = function (val) {
    this._val = val;
    return this;
};

/**
 * @return {?String} The component instance value.
 */
Charcoal.Admin.Property.prototype.val = function () {
    return this._val;
};

/**
 * Default validate action
 * Validate should return the validation feedback with a
 * success and / or message
 * IdeaS:
 * Use a validation object that has all necessary methods for
 * strings (max_length, min_length, etc)
 *
 * @return Object validation feedback
 */
Charcoal.Admin.Property.prototype.validate = function () {
    // Validate the current
    return {};
};

/**
 * Default save action
 *
 * @return {this}
 */
Charcoal.Admin.Property.prototype.save = function () {
    // Default action = nothing
    return this;
};

/**
 * Error handling
 *
 * @param  {*} data - Could be a simple message, an array, wtv.
 * @return {void}
 */
Charcoal.Admin.Property.prototype.error = function (data) {
    window.console.error(data);
};
