/**
 * Abstract Component
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Component}
 */
Charcoal.Admin.Component = function (opts) {
    /* jshint ignore:start */
    this._element;
    this._id;
    this._type;
    this._opts;
    /* jshint ignore:end */

    if (opts) {
        if (opts.element) {
            this.set_element(opts.element);
        } else if (typeof opts.id === 'string') {
            this.set_element('#' + opts.id);
            this.set_id(opts.id);
        }

        if (typeof opts.type === 'string') {
            this.set_type(opts.type);
        }

        this.set_opts(opts);
    }

    return this;
};

/**
 * @param  {Element} element - The jQuery/DOM element related to the component instance.
 * @throws {TypeError} If the element argument is not a valid jQuery element.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_element = function (element) {
    if (!(element instanceof jQuery)) {
        element = $(element);
    }

    if (element.length !== 1) {
        throw new TypeError('Component Element must be a DOM Element');
    }

    this.set_id(element.attr('id'));
    this._element = element;
    return this;
};

/**
 * @return {?jQuery} The related jQuery element.
 */
Charcoal.Admin.Component.prototype.element = function () {
    return this._element;
};

/**
 * @param  {String} id - The component instance ID.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_id = function (id) {
    this._id = id;
    return this;
};

/**
 * @return {?String} The component instance ID.
 */
Charcoal.Admin.Component.prototype.id = function () {
    return this._id;
};

/**
 * @param  {String} type - The component type or subtype.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_type = function (type) {
    this._type = type;
    return this;
};

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Component.prototype.type = function () {
    return this._type;
};

/**
 * @param  {Object} opts - The component instance options.
 * @throws {TypeError} If the options argument is invalid.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_opts = function (opts) {
    if (typeof opts === 'object') {
        this._opts = opts;
    } else {
        throw new TypeError('Component Options must be an object');
    }
    return this;
};

/**
 * @param  {String} key - The data key.
 * @param  {*}      val - The data value.
 * @throws {TypeError} If the data key argument is invalid.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.add_opts = function (key, val) {
    if (typeof key === 'string') {
        this._opts[key] = val;
    } else {
        throw new TypeError('Component Options Key must be a string');
    }
    return this;
};

/**
 * @param  {String} [key] - The optional data key.
 * @return {Object|?*}
 *     If `key` is provided, the key's value is returned or NULL.
 *     If `key` is not provided, the component instance options is returned.
 */
Charcoal.Admin.Component.prototype.opts = function (key) {
    if (typeof key === 'string') {
        if (typeof this._opts[key] === 'undefined') {
            return null;
        }
        return this._opts[key];
    }

    return this._opts;
};

/**
 * @return {this}
 */
Charcoal.Admin.Component.prototype.init = function () {
    // Do nothing
    return this;
};

/**
 * @return {void}
 */
Charcoal.Admin.Component.prototype.destroy = function () {
    // Do nothing
};

/**
 * Stub: Determines if the component is a candidate for validation.
 *
 * @param  {Component} [scope] - The parent component that calls for validation.
 * @return {boolean}
 */
// Charcoal.Admin.Component.prototype.will_validate = function (scope) {
//     return (scope && !$.contains(scope.element()[0], this.element()[0]));
// };

/**
 * Stub: Validates the component.
 *
 * Each component is expected to add their own feedback if their
 * value is invalid or errored (via `validate` or `save`).
 *
 * @param  {Component} [scope] - The parent component that calls for validation.
 * @return {boolean} If `false`, the component is invalid.
 */
// Charcoal.Admin.Component.prototype.validate = function (scope) {
//     return true;
// };

/**
 * Stub: Determines if the component is a candidate for saving.
 *
 * @param  {Component} [scope] - The parent component that calls for save.
 * @return {boolean}
 */
// Charcoal.Admin.Component.prototype.will_save = function (scope) {
//     return (scope && !$.contains(scope.element()[0], this.element()[0]));
// };

/**
 * Stub: Prepares the component to be saved.
 *
 * This method can be used to serialize a complex value
 * or trigger a separate process (such as uploading a file).
 *
 * Each component is expected to add their own feedback if their
 * value is invalid or errored (via `validate` or `save`).
 *
 * @param  {Component} [scope] - The parent component that calls for save.
 * @return {boolean} If `false`, the component could not save.
 */
// Charcoal.Admin.Component.prototype.save = function (scope) {
//     return true;
// };
