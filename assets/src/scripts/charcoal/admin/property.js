/**
 * charcoal/admin/property
 * Should mimic the PHP equivalent AbstractProperty
 * This will prevent multiple directions in property implementation
 * by giving multiple usefull methods such as ident, val, etc.
 */
Charcoal.Admin.Property = function (opts)
{
    this._ident      = undefined;
    this._val        = undefined;
    this._type       = undefined;
    this._input_type = undefined;

    if (typeof opts.ident === 'string') {
        this.set_ident(opts.ident);
    }

    if (typeof opts.val !== 'undefined') {
        this.set_val(opts.val);
    }

    if (typeof opts.type !== 'undefined') {
        this.set_type(opts.type);
    }

    if (typeof opts.input_type !== 'undefined') {
        this.set_input_type(opts.input_type);
    }

    this.data = opts;

    return this;
};

/**
 * Setters
 * The following are all defined setters we wanna use for all properties
 */
Charcoal.Admin.Property.prototype.set_ident = function (ident)
{
    this._ident = ident;
};
Charcoal.Admin.Property.prototype.set_val = function (val)
{
    this._val = val;
};
Charcoal.Admin.Property.prototype.set_type = function (type)
{
    this._type = type;
};
Charcoal.Admin.Property.prototype.set_input_type = function (input_type)
{
    this._input_type = input_type;
};

/**
 * Getters
 * The following are defined getters
 */
Charcoal.Admin.Property.prototype.ident = function () {
    return this._ident;
};
Charcoal.Admin.Property.prototype.val = function () {
    return this._val;
};
Charcoal.Admin.Property.prototype.type = function () {
    return this._type;
};
Charcoal.Admin.Property.prototype.input_type = function () {
    return this._input_type;
};
/**
 * Return the DOMElement element
 * @return {jQuery Object} $( '#' + this.data.id );
 * If not set, creates it
 */
Charcoal.Admin.Property.prototype.element = function ()
{
    if (!this._element) {
        if (!this.data.id) {
            // Error...
            return false;
        }
        this._element = $('#' + this.data.id);
    }
    return this._element;
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
Charcoal.Admin.Property.prototype.validate = function ()
{
    // Validate the current
};

/**
 * Default save action
 * @return this (chainable)
 */
Charcoal.Admin.Property.prototype.save = function ()
{
    // Default action = nothing
    return this;
};

/**
 * Error handling
 * @param  {Mixed} data  Could be a simple message, an array, wtv.
 * @return {thisArg}     Chainable.
 */
Charcoal.Admin.Property.prototype.error = function (data)
{
    window.console.error(data);
};
