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

Charcoal.Admin.Property_Input_Image = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/image';
    this.opts = opts;
    this.data = opts.data;

    // Required
    this.set_input_id(this.opts.id);

    // Run the plugin or whatever is necessary
    this.init();
    return this;
};
Charcoal.Admin.Property_Input_Image.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Image.prototype.constructor = Charcoal.Admin.Property_Input_Image;
Charcoal.Admin.Property_Input_Image.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Image.prototype.init = function ()
{
    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input = $('#' + this.input_id);
    this.$file = this.$input.find('[type=file]');
    this.$preview = this.$input.find('.js-preview');

    this.set_listeners();
};

Charcoal.Admin.Property_Input_Image.prototype.set_listeners = function ()
{
    // window.alert('test');
    if (typeof this.$input === 'undefined') {
        return this;
    }

    var that = this;
    this.$input.on('click', '.js-remove-image', function (e) {
        e.preventDefault();

        that.$input.find('input[type=hidden]').val('');
        that.$input.find('img').remove();
    });

    this.$file.on('change', function (event) {
        var img = new Image();

        if (that.$preview.children('img').length) {
            that.$preview.children('img').remove();
        }

        var target = event.dataTransfer || event.target;
        var file = target && target.files && target.files[0];
        var s = URL.createObjectURL(file);
        img.src = s;
        that.$preview.append(img);
    });
};

/**
 * SETTERS
 */
/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Image.prototype.set_input_id = function (input_id)
{
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Image.prototype.set_input_name = function (input_name)
{
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Image.prototype.set_input_val = function (input_val)
{
    this.input_val = input_val;
    return this;
};
