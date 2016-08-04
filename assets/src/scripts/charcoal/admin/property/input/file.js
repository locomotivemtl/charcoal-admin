/**
 * Upload File Property Control
 */

Charcoal.Admin.Property_Input_File = function (opts)
{
    this.EVENT_NAMESPACE = '.charcoal.property.file';
    this.input_type = 'charcoal/admin/property/input/file';

    this.opts = opts;
    this.data = opts.data;
    this.dialog = null;

    // Required
    this.set_input_id(this.opts.id);

    // Run the plugin or whatever is necessary
    this.init();

    return this;
};

Charcoal.Admin.Property_Input_File.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_File.prototype.constructor = Charcoal.Admin.Property_Input_File;
Charcoal.Admin.Property_Input_File.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_File.prototype.init = function ()
{
    // console.log('Init', this.input_type);

    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input   = $('#' + this.input_id);
    this.$file    = this.$input.find('input[type="file"]');
    this.$hidden  = this.$input.find('input[type="hidden"]');
    this.$preview = this.$input.find('.js-preview');

    this.set_listeners();
};

Charcoal.Admin.Property_Input_File.prototype.set_listeners = function ()
{
    // console.log('Events', this.input_type);

    if (typeof this.$input === 'undefined') {
        return this;
    }

    this.$input
        .on('click' + this.EVENT_NAMESPACE, '.js-remove-file', this.remove_file.bind(this))
        .on('click' + this.EVENT_NAMESPACE, '.js-elfinder', this.load_elfinder.bind(this));

    this.$file.on('change' + this.EVENT_NAMESPACE, this.change_file.bind(this));

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);
};

Charcoal.Admin.Property_Input_File.prototype.remove_file = function (event)
{
    // console.log('Remove File');

    event.preventDefault();

    this.$hidden.val('');
    this.$input.find('.form-control-static').empty();
    this.$input.find('.hide-if-no-file').addClass('hidden');
};

Charcoal.Admin.Property_Input_File.prototype.change_file = function (event)
{
    // console.log('Change File');

    var target, file, src;

    target = event.dataTransfer || event.target;
    file   = target && target.files && target.files[0];
    src    = URL.createObjectURL(file);

    this.$input.find('.hide-if-no-file').removeClass('hidden');
    this.$input.find('.form-control-static').html(file);
    this.$preview.empty();
};

Charcoal.Admin.Property_Input_File.prototype.load_elfinder = function (event)
{
    // console.log('Load elFinder');

    event.preventDefault();

    this.dialog = BootstrapDialog.show({
        title:      this.data.dialog_title || '',
        size:       BootstrapDialog.SIZE_WIDE,
        cssClass:  '-elfinder',
        message:   $(
            '<iframe name="' + this.input_id + '-elfinder" width="100%" height="400px" frameborder="0" ' +
            'src="' + this.data.elfinder_url + '"></iframe>'
        )
    });
};

Charcoal.Admin.Property_Input_File.prototype.elfinder_callback = function (file/*, elf */)
{
    // console.group('elFinder Callback (File)');
    // console.log('elFinder', elf);
    // console.log('Selected File', file);

    if (this.dialog) {
        this.dialog.close();
    }

    if (file && file.path) {
        this.$input.find('.hide-if-no-file').removeClass('hidden');
        this.$input.find('.form-control-static').html(file.name);
        this.$hidden.val(file.path);
        this.$preview.empty();
    }
    // console.groupEnd();
};

/**
 * SETTERS
 */
/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_id = function (input_id)
{
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_name = function (input_name)
{
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_val = function (input_val)
{
    this.input_val = input_val;
    return this;
};
