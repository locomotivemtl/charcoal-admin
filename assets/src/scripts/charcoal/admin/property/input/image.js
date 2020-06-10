/**
 * Upload Image Property Control
 */

Charcoal.Admin.Property_Input_Image = function (opts) {
    Charcoal.Admin.Property.call(this, opts);
    this.EVENT_NAMESPACE = '.charcoal.property.image';
    this.input_type = 'charcoal/admin/property/input/image';

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_Image.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Image.prototype.constructor = Charcoal.Admin.Property_Input_Image;
Charcoal.Admin.Property_Input_Image.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Image.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var reader, file;

        file   = event.target.files[0];
        reader = new FileReader();

        reader.addEventListener('loadend', (function () {
            var image = new Image();

            console.log('[Property_Input_Image.change_file]', file);

            image.style = 'max-width: 100%';
            image.title = file.name;
            image.src   = reader.result;
            image.load();

            this.$input.find('.hide-if-no-file').removeClass('d-none');
            this.$input.find('.show-if-no-file').addClass('d-none');

            this.$previewFile.append(image);
            this.$previewText.html(file.name);
        }).bind(this), false);

        reader.readAsDataURL(file);
    }
};

Charcoal.Admin.Property_Input_Image.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (file && file.url) {
        var path, $image;

        path    = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');
        $image = $('<img src="' + file.url + '" style="max-width: 100%">');

        console.log('[Property_Input_Image.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
        this.$previewFile.append($image);
    }
};
