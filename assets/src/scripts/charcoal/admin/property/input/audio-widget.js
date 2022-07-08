/**
 * Interface for saving audio messages
 * Property_Input_Audio JavaScript class
 * charcoal/admin/property/input/audio
 *
 * @method Property_Input_Audio_Widget
 * @param Object opts
 */
Charcoal.Admin.Property_Input_Audio_Widget = function (opts) {
    this.EVENT_NAMESPACE = Charcoal.Admin.Property_Input_Audio_Widget.EVENT_NAMESPACE;

    Charcoal.Admin.Property.call(this, opts);

    this.data    = opts.data;
    this.data.id = opts.id;

    this.init();
};

Charcoal.Admin.Property_Input_Audio_Widget.EVENT_NAMESPACE = '.charcoal.property.audio.widget';

Charcoal.Admin.Property_Input_Audio_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Audio_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Audio_Widget;
Charcoal.Admin.Property_Input_Audio_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init = function () {
    var $el = this.element();

    // Properties for each audio type
    // Since all components can be destroyed, we need to make sure they're initialized with the widget.
    this.text_component    = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/textarea',
        property_class: null
    };
    this.capture_component = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/audio-recorder',
        property_class: Charcoal.Admin.Property_Input_Audio_Recorder
    };
    this.upload_component  = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/audio',
        property_class: Charcoal.Admin.Property_Input_Audio
    };

    // Navigation
    this.active_pane = this.data.active_pane || 'text';

    this.$input_text   = $('#' + this.data.text_input_id).or('.js-text-voice-message', $el);
    this.$input_file   = $('#' + this.data.upload_input_id).or('.js-file-input', $el);
    this.$input_hidden = $('#' + this.data.hidden_input_id).or('.js-file-input-hidden', $el);

    if (this.$input_hidden.length === 0) {
        console.error('Missing hidden input to store audio');
    }

    this.bind_events();

    if (this.active_pane) {
        // This ensures the current pane is initialized even if it's already showing.
        // It fixes an issue with AdminManager::render()
        this.init_pane($('#' + this.data.id + '_' + this.active_pane + '_tab'));
        $('#' + this.data.id + '_' + this.active_pane + '_tab').tab('show');
    }
};

/**
 * Create tabular navigation
 */
Charcoal.Admin.Property_Input_Audio_Widget.prototype.bind_events = function () {
    var that = this;

    this.element().on('shown.bs.tab', '[data-toggle="tab"]', function (event) {
        that.init_pane(event.target, false);
    });

    return this;
};

/**
 * Show the selected tab.
 *
 * @param  {String|jQuery} pane - The pane to show.
 * @return {this}
 */
Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_pane = function (pane) {
    if (typeof pane !== 'string') {
        pane = $(pane).attr('data-pane');
    }

    if (pane) {
        var fn;

        this.active_pane = pane;

        fn = 'init_' + pane;
        if (typeof(this[fn]) === 'function') {
            this[fn]();
        }
    }

    return this;
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_text = function () {
    var component = this.text_component;

    if (component.enabled) {
        return;
    }

    if (!component.property) {
        component.enabled = true;

        if (!component.property_class) {
            return;
        }

        if (!this.data.text_input_id) {
            console.error('[Property_Input_Audio_Widget]', 'Missing text-to-speech input');
            return;
        }

        component.property = new component.property_class({
            id:   this.data.text_input_id,
            type: component.property_type
        });
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_upload = function () {
    var component = this.upload_component;

    if (component.enabled) {
        return;
    }

    if (!component.property) {
        component.enabled = true;

        if (!component.property_class) {
            return;
        }

        if (!(this.data.upload_input_id && this.data.hidden_input_id)) {
            console.error('[Property_Input_Audio_Widget]', 'Missing file or hidden input');
            return;
        }

        component.property = new component.property_class({
            id:   this.data.upload_input_id,
            type: component.property_type,
            data: {
                hidden_input_id: this.data.hidden_input_id,
                input_name:      this.data.input_name,
                dialog_title:    this.data.dialog_title,
                elfinder_url:    this.data.elfinder_url
            }
        });
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_capture = function () {
    var component = this.capture_component;

    if (component.enabled) {
        return;
    }

    if (component.property) {
        if (component.property_class.is_audio_supported()) {
            console.info('[Property_Input_Audio_Widget]', 'New request for user permission to use media input');
            component.property.get_user_media(true);
        }
    } else {
        if (!component.property_class) {
            return;
        }

        if (!component.property_class.is_recorder_available() && !this.data.recorder_plugin_url) {
            console.error('[Property_Input_Audio_Widget]', 'Missing recorder library');
            return;
        }

        if (!this.data.hidden_input_id) {
            console.error('[Property_Input_Audio_Widget]', 'Missing hidden input');
            return;
        }

        var readyCallback, endedCallback, promptCallback;

        readyCallback  = (function () {
            component.enabled = true;
        }).bind(this);

        endedCallback  = (function () {
            component.enabled = false;
        }).bind(this);

        promptCallback = (function (event) {
            event.preventDefault();

            if (this.active_pane === 'capture') {
                this.init_capture();
            }
        }).bind(this);

        component.property = new component.property_class({
            id:   this.data.capture_input_id,
            type: component.property_type,
            data: {
                recorder_plugin_url: this.data.recorder_plugin_url,
                hidden_input_id:     this.data.hidden_input_id,
                on_stream_ready:     readyCallback,
                on_stream_ended:     endedCallback,
                on_stream_error:     endedCallback
            }
        });

        this.element().on('click.' + this.EVENT_NAMESPACE, '[data-pane="capture"]', promptCallback);
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.destroy = function () {
    this.element().off(this.EVENT_NAMESPACE);

    if (this.text_component.property) {
        this.text_component.property.destroy();
    }

    if (this.upload_component.property) {
        this.upload_component.property.destroy();
    }

    if (this.capture_component.property) {
        this.capture_component.property.destroy();
    }
};
