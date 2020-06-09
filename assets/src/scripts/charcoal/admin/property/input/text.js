/**
 * Basic text input that also manages multiple (split) values
 * charcoal/admin/property/input/text
 *
 * Require:
 * - jQuery
 *
 * @param  {Object}  opts Options for input property
 */

Charcoal.Admin.Property_Input_Text = function (opts) {
    Charcoal.Admin.Property.call(this, opts);

    this.input_type = 'charcoal/admin/property/input/text';
    this.opts       = opts;
    this.data       = opts.data;

    // Required
    this.set_input_id(this.opts.id);

    // Dispatches the data
    this.set_data(this.data);

    // Run the plugin or whatever is necessary
    this.initialisation = true;
    this.init();
    this.initialisation = false;

    return this;
};
Charcoal.Admin.Property_Input_Text.prototype             = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Text.prototype.constructor = Charcoal.Admin.Property_Input_Text;
Charcoal.Admin.Property_Input_Text.prototype.parent      = Charcoal.Admin.Property.prototype;

/**
 * Set multiple values required
 * @param {Object} data Data passed from the template
 */
Charcoal.Admin.Property_Input_Text.prototype.set_data = function (data) {
    // Input desc
    this.set_input_name(data.input_name);
    this.set_input_val(data.input_val);

    // Input definition
    this.set_readonly(data.readonly);
    this.set_required(data.required);
    this.set_min_length(data.min_length);
    this.set_max_length(data.max_length);
    this.set_size(data.size);

    // Multiple
    this.set_multiple(data.multiple);
    this.set_multiple_separator(data.multiple_separator);

    var min = (data.multiple_options) ? data.multiple_options.min : 0;
    var max = (data.multiple_options) ? data.multiple_options.max : 0;

    this.set_multiple_min(min);
    this.set_multiple_max(max);

    var split = (data.multiple_options) ? data.multiple_options.split_on : null;

    this.set_split_on(split);
    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.init = function () {
    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input = $('#' + this.input_id);

    if (this.multiple) {
        this.init_multiple();
    }
};

/**
 * When multiple
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.init_multiple = function () {
    // New input
    this.chars_new    = [ 13 ];
    // Check to delete current input
    this.chars_remove = [ 8, 46 ];
    // Navigate.
    this.char_next    = [ 40 ];
    this.char_prev    = [ 38 ];

    this.currentValAmount = 1;

    // Add to container.
    // input.wrap('<div></div>');
    this.$container = this.$input.parent('div');

    // OG input keyboard events.
    this.bind_keyboard_events(this.$input);

    // Initial split.
    this.split_val(this.$input);

    if (this.multiple_min) {
        var additionalFields = this.multiple_min - this.currentValAmount;
        for (; additionalFields > 0; additionalFields--) {
            this.add_item();
        }
    }

    return this;
};
/**
 * Split the value with separator
 * If the input is specified, splits relative to the input
 * @param  {String} val  Value
 * @param  {[type]} input [description]
 * @return {DOMElement|false}
 */
Charcoal.Admin.Property_Input_Text.prototype.split_val = function (input) {
    var separator = this.split_on || this.multiple_separator;
    input         = input || this.$input;
    var val       = input.val();

    var split = val.split(separator);
    var i     = 0;
    var total = split.length;

    if (total === 1) {
        // Nothing to split.
        return false;
    }

    for (; i < total; i++) {
        if (i === 0) {
            input.val(split[i]);
        } else {
            if (this.initialisation || !this.multiple_max || this.currentValAmount < this.multiple_max) {
                input = this.insert_item(input, split[i]);
            } else {
                var next = input.next('input');
                if (next.length && !next.innerHTML) {
                    this.remove_item(next);
                    input = this.insert_item(input, split[i]);
                }
            }
        }
    }

    return input;
};

Charcoal.Admin.Property_Input_Text.prototype.bind_keyboard_events = function (input) {
    // Scope
    var that = this;

    var chars_new    = this.chars_new;
    var chars_remove = this.chars_remove;
    var char_next    = this.char_next;
    var char_prev    = this.char_prev;

    // Bind the keyboard events
    input.on('keydown', function (e) {

        var keyCode = e.keyCode;
        if (chars_new.indexOf(keyCode) > -1) {
            if (!that.multiple_max || that.currentValAmount < that.multiple_max) {
                e.preventDefault();
                var clone = that.insert_item($(this));
                clone.focus();
            }
        }

        if (chars_remove.indexOf(keyCode) > -1) {

            if (!that.multiple_min || that.currentValAmount > that.multiple_min) {
                // Delete keys (8 is backspage, 46 is "del")
                if ($(this).val() === '') {
                    e.preventDefault();
                    that.remove_item($(this));
                }
            }
        }

        if (char_prev.indexOf(keyCode) > -1) {
            e.preventDefault();
            // Up arrow key (Navigate to previous item if it exists)
            $(this).prev('input').focus();
        }
        if (char_next.indexOf(keyCode) > -1) {
            e.preventDefault();
            // Down arrow key
            $(this).next('input').focus();
        }
    });

    input.on('keyup', function () {
        var clone = that.split_val($(this));
        if (clone) {
            clone.focus();
        }
    });
};

/**
 * Insert a clone relative to an element
 * @param  {jQueryObject} elem      Input element
 * @param  {String|undefined} val   Should we have a value already in that input.
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.insert_item = function (elem, val) {
    var clone = this.input_clone(val);
    clone.insertAfter(elem);
    this.bind_keyboard_events(clone);

    this.currentValAmount++;

    return clone;
};

/**
 * Add an item (append)
 * @param {String|undefined} val    If the input already as a value
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.add_item = function (val) {
    var clone = this.input_clone(val);
    this.$container.append(clone);
    this.bind_keyboard_events(clone);

    this.currentValAmount++;

    return clone;
};
/**
 * Remove specific item
 * Sets focus to the prev item (or next if previous doesn'T exist)
 * Won't remove the LAST input standing.
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item = function (item) {
    var prev = item.prev('input');
    var next = item.next('input');

    if (!prev.length && !next.length) {
        // Don't remove the last one
        return false;
    }

    if (prev.length) {
        prev.focus();
    } else if (next.length) {
        next.focus();
    }

    this.remove_item_listeners(item);
    item.remove();

    this.currentValAmount--;

    return this;
};
/**
 * Remove listeners from an item
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item_listeners = function (item) {
    item.off('keydown');
    item.off('keyup');

    return this;
};

/**
 * Create a clone of the OG input
 * @param  {String} val Optional parameter - Value of the input.
 * @return {jQueryObject}     The actual "clone", which isn't really a clone.
 */
Charcoal.Admin.Property_Input_Text.prototype.input_clone = function (val) {
    var input      = this.$input;
    var classes    = input.attr('class');
    var type       = input.attr('type');
    var min_length = this.min_length;
    var max_length = this.max_length;
    // var size = this.size;
    var required   = this.required;
    var readonly   = this.readonly;
    var input_name = this.input_name;

    var clone = $('<input />');

    if (type) {
        clone.attr('type', type);
    }
    if (classes) {
        clone.attr('class', classes);
    }
    if (min_length) {
        clone.attr('minlength', min_length);
    }
    if (max_length) {
        clone.attr('maxlength', max_length);
    }
    if (required) {
        clone.attr('required', 'required');
    }
    if (readonly) {
        clone.attr('readonly', 'readonly');
    }
    if (val) {
        clone.val(val);
    }
    clone.attr('name', input_name);

    return clone;
};

/**
 * SETTERS
 */
/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_id = function (input_id) {
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_name = function (input_name) {
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_val = function (input_val) {
    this.input_val = input_val;
    return this;
};

/**
 * Is the input in readOnly mode?
 * @param {Boolean|undefined} readonly Defines if input is in readonly mode or not
 */
Charcoal.Admin.Property_Input_Text.prototype.set_readonly = function (readonly) {
    if (!readonly) {
        readonly = false;
    }
    this.readonly = readonly;
    return this;
};

/**
 * Is the input required?
 * @param {Boolean|undefined} required Defines if input is required
 */
Charcoal.Admin.Property_Input_Text.prototype.set_required = function (required) {
    if (!required) {
        required = false;
    }
    this.required = required;
    return this;
};

/**
 * The input min length
 * @param {Integer} min_length Min length of the input.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_min_length = function (min_length) {
    if (!min_length) {
        min_length = 0;
    }
    this.min_length = min_length;
    return this;
};

/**
 * The input max length
 * @param {Integer} max_length Max length of the input.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_max_length = function (max_length) {
    if (!max_length) {
        max_length = 0;
    }
    this.max_length = max_length;
    return this;
};

/**
 * Size of the input
 * @param {Integer} size Not sure about this one.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_size = function (size) {
    if (!size) {
        size = 0;
    }
    this.size = size;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple = function (multiple) {
    if (!multiple) {
        multiple = false;
    }
    this.multiple = multiple;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple_min Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_min = function (multiple_min) {
    if (!multiple_min) {
        multiple_min = false;
    }
    this.multiple_min = multiple_min;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple_max Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_max = function (multiple_max) {
    if (!multiple_max) {
        multiple_max = false;
    }
    this.multiple_max = multiple_max;
    return this;
};

/**
 * Multiple separator
 * @param {String} separator Multiple separator || undefined.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_separator = function (separator) {
    if (!separator) {
        // Default
        separator = ',';
    }
    this.multiple_separator = separator;
    return this;
};

/**
 * Split delimiter
 * @param {String} separator Multiple separator || undefined.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_split_on = function (splitOn) {
    if (!splitOn) {
        splitOn = this.multiple_separator;
    } else {
        if ($.type(splitOn) === 'array') {
            for (var i = splitOn.length - 1; i >= 0; i--) {
                switch (splitOn[i]) {
                    case 'comma':
                        splitOn[i] = '\\s*,\\s*';
                        break;

                    case 'tab':
                        splitOn[i] = '\\t+';
                        break;

                    case 'newline':
                        splitOn[i] = '[\\n\\r]+';
                        break;

                    default:
                        splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                }
            }

            splitOn = splitOn.join('|');
        }

        splitOn = new RegExp(splitOn);
    }

    this.split_on = splitOn;
    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.destroy = function () {
}
