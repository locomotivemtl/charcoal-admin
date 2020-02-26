/**
 * Upload Image Property Control
 */

Charcoal.Admin.Property_Input_Image = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.image';
    this.input_type = 'charcoal/admin/property/input/image';

    this.opts   = opts;
    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(this.opts.id).init();
};

Charcoal.Admin.Property_Input_Image.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Image.prototype.constructor = Charcoal.Admin.Property_Input_Image;
Charcoal.Admin.Property_Input_Image.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Image.prototype.change_file = function (event) {
    var img, target, file, src;

    img = new File();

    target = event.dataTransfer || event.target;
    file   = target && target.files && target.files[0];
    src    = URL.createObjectURL(file);

    img.src = src;

    this.$input.find('.hide-if-no-file').removeClass('d-none');
    this.$input.find('.show-if-no-file').addClass('d-none');
    this.$input.find('.form-control-plaintext').html(file);
    this.$preview.empty().append(img);
};

Charcoal.Admin.Property_Input_Image.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    if (file && file.path) {
        var $img = $('<img src="' + file.url + '" style="max-width: 100%">');

        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$input.find('.form-control-plaintext').html(file.name);
        this.$hidden.val(decodeURI(file.url).replace(Charcoal.Admin.base_url(), ''));
        this.$preview.empty().append($img);
    }
};
