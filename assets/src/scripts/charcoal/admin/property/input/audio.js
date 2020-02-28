/* globals audioPropertyL10n */
/**
 * Upload Audio Property Control
 */

Charcoal.Admin.Property_Input_Audio = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.audio';
    this.input_type = 'charcoal/admin/property/input/audio';

    this.opts   = opts;
    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(this.opts.id).init();
};

Charcoal.Admin.Property_Input_Audio.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Audio.prototype.constructor = Charcoal.Admin.Property_Input_Audio;
Charcoal.Admin.Property_Input_Audio.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Audio.prototype.change_file = function (event) {
    var audio, target, file, src;

    audio = new File();

    target = event.dataTransfer || event.target;
    file   = target && target.files && target.files[0];
    src    = URL.createObjectURL(file);

    audio.src = src;

    this.$input.find('.hide-if-no-file').removeClass('d-none');
    this.$input.find('.show-if-no-file').addClass('d-none');
    this.$input.find('.form-control-plaintext').html(file);
    this.$preview.empty().append(audio);
};

Charcoal.Admin.Property_Input_Audio.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    if (file && file.url) {
        var $audio = $('<audio controls src="' + file.url + '" class="js-file-audio">' + audioPropertyL10n.unsupportedElement + '</audio>');

        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$input.find('.form-control-plaintext').html(file.name);
        this.$hidden.val(decodeURI(file.url).replace(Charcoal.Admin.base_url(), ''));
        this.$preview.empty().append($audio);
    }
};
