/* globals audioPropertyL10n */
/**
 * Upload Audio Property Control
 */

Charcoal.Admin.Property_Input_Audio = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.audio';
    this.input_type = 'charcoal/admin/property/input/audio';
    Charcoal.Admin.Property.call(this, opts);

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_Audio.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Audio.prototype.constructor = Charcoal.Admin.Property_Input_Audio;
Charcoal.Admin.Property_Input_Audio.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Audio.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var reader, file;

        file   = event.target.files[0];
        reader = new FileReader();

        reader.addEventListener('loadend', (function () {
            var audio = new Audio();

            console.log('[Property_Input_Audio.change_file]', file);

            audio.innerHTML = audioPropertyL10n.unsupportedElement;
            audio.controls  = true;
            audio.title     = file.name;
            audio.src       = reader.result;
            audio.load();

            this.$input.find('.hide-if-no-file').removeClass('d-none');
            this.$input.find('.show-if-no-file').addClass('d-none');

            this.$previewFile.append(audio);
            this.$previewText.html(file.name);
        }).bind(this), false);

        reader.readAsDataURL(file);
    }
};

Charcoal.Admin.Property_Input_Audio.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (file && file.url) {
        var path, $audio;

        path    = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');
        $audio = $('<audio controls src="' + file.url + '" class="js-file-audio">' + audioPropertyL10n.unsupportedElement + '</audio>');

        console.log('[Property_Input_Audio.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
        this.$previewFile.append($audio);
    }
};
