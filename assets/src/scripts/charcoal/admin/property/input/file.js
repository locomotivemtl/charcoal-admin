/**
 * Upload File Property Control
 */

Charcoal.Admin.Property_Input_File = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.file';
    Charcoal.Admin.Property.call(this, opts);
    this.input_type = 'charcoal/admin/property/input/file';

    this.opts   = opts;
    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(this.opts.id).init();
};

Charcoal.Admin.Property_Input_File.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_File.prototype.constructor = Charcoal.Admin.Property_Input_File;
Charcoal.Admin.Property_Input_File.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_File.prototype.init = function () {
    if (!this.input_id) {
        return;
    }

    this.$input  = $('#' + this.input_id);
    this.$file   = $('#' + this.data.file_input_id).or('input[type="file"]', this.$input);
    this.$hidden = $('#' + this.data.hidden_input_id).or('input[type="hidden"]', this.$input);

    this.$previewFile = this.$input.find('.js-preview-file');
    this.$previewText = this.$input.find('.js-preview-text');

    if (!window.elFinderCallback) {
        window.elFinderCallback = {};
    }

    this.set_listeners();
};

Charcoal.Admin.Property_Input_File.prototype.set_listeners = function () {
    if (typeof this.$input === 'undefined') {
        return;
    }

    this.$input
        .on('click' + this.EVENT_NAMESPACE, '.js-remove-file', this.remove_file.bind(this))
        .on('click' + this.EVENT_NAMESPACE, '.js-elfinder', this.load_elfinder.bind(this));

    this.$file.on('change' + this.EVENT_NAMESPACE, this.change_file.bind(this));

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);
};

Charcoal.Admin.Property_Input_File.prototype.remove_file = function (event) {
    event.preventDefault();

    this.$hidden.val('');
    this.$file.val('');

    this.$previewFile.empty();
    this.$previewText.empty();

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');
};

Charcoal.Admin.Property_Input_File.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewFile.empty();
    this.$previewText.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var file = event.target.files[0];

        console.log('[Property_Input_File.change_file]', file);

        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
    }
};

Charcoal.Admin.Property_Input_File.prototype.load_elfinder = function (event) {
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

Charcoal.Admin.Property_Input_File.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewFile.empty();
    this.$previewText.empty();

    if (file && file.url) {
        var path = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');

        console.log('[Property_Input_File.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
    }
};

/**
 * SETTERS
 */

/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_id = function (input_id) {
    this.input_id = input_id;
    return this;
};

/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_name = function (input_name) {
    this.input_name = input_name;
    return this;
};

/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_val = function (input_val) {
    this.input_val = input_val;
    return this;
};

Charcoal.Admin.Property_Input_File.prototype.destroy = function () {
    this.$input.off(this.EVENT_NAMESPACE);
    this.$file.off(this.EVENT_NAMESPACE);
};
