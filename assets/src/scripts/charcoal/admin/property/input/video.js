/* globals videoPropertyL10n */
/**
 * Upload Video Property Control
 */

Charcoal.Admin.Property_Input_Video = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.video';
    this.input_type = 'charcoal/admin/property/input/video';
    Charcoal.Admin.Property.call(this, opts);

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_Video.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Video.prototype.constructor = Charcoal.Admin.Property_Input_Video;
Charcoal.Admin.Property_Input_Video.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Video.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var reader, file;

        file   = event.target.files[0];
        reader = new FileReader();

        reader.addEventListener('loadend', (function () {
            var video = document.createElement('video');

            console.log('[Property_Input_Video.change_file]', file);

            video.innerHTML = videoPropertyL10n.unsupportedElement;
            video.style     = 'max-width: 100%';
            video.controls  = true;
            video.title     = file.name;
            video.src       = reader.result;
            video.load();

            this.$input.find('.hide-if-no-file').removeClass('d-none');
            this.$input.find('.show-if-no-file').addClass('d-none');

            this.$previewFile.append(video);
            this.$previewText.html(file.name);
        }).bind(this), false);

        reader.readAsDataURL(file);
    }
};

Charcoal.Admin.Property_Input_Video.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (file && file.url) {
        var path, $video;

        path    = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');
        $video = $('<video controls src="' + file.url + '" class="js-file-video" style="max-width: 100%">' + videoPropertyL10n.unsupportedElement + '</video>');

        console.log('[Property_Input_Video.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
        this.$previewFile.append($video);
    }
};
