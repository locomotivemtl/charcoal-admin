/**
 * Interface for saving audio messages
 * Property_Input_Audio JavaScript class
 * charcoal/admin/property/input/audio
 *
 * @see https://github.com/cwilso/AudioRecorder
 * @see https://github.com/mattdiamond/Recorderjs
 *
 * @method Property_Input_Audio
 * @param Object opts
 */
Charcoal.Admin.Property_Input_Audio = function (data)
{
    // Input type
    data.input_type = 'charcoal/admin/property/input/audio';

    Charcoal.Admin.Property.call(this, data);

    // Properties for each audio type
    this.text_properties       = {};
    this.recording_properties = {};
    this.file_properties      = {};

    // Types that have been initialized
    this.initialized_types = [];

    // Navigation
    this.active_pane = data.data.active_pane || 'text';

    // Recorder
    this._recorder = undefined;

    this.init();
};

Charcoal.Admin.Property_Input_Audio.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Audio.prototype.constructor = Charcoal.Admin.Property_Input_Audio;
Charcoal.Admin.Property_Input_Audio.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Set properties
 * @method init
 */
Charcoal.Admin.Property_Input_Audio.prototype.init = function ()
{
    var _data = this.data;

    // Shouldn't happen at this point
    if (typeof _data.id === 'undefined') {
        console.error('Missing ID');
    }

    // Text properties
    // ====================
    // Elements
    this.text_properties.$voice_message = $('.js-text-voice-message', this.element());

    // Recording properties
    // ====================
    this.recording_properties.audio_context     = null;
    this.recording_properties.audio_recorder    = null;
    this.recording_properties.animation_frame   = null;
    this.recording_properties.analyser_context  = null;
    this.recording_properties.canvas_width      = 0;
    this.recording_properties.canvas_height     = 0;
    this.recording_properties.recording_index   = 0;
    this.recording_properties.current_recording = null;
    this.recording_properties.audio_player      = null;
    this.recording_properties.hidden_input_id   = _data.data.hidden_input_id;
    // Elements
    this.recording_properties.$analyser_canvas      = $('.js-recording-analyser', this.element());
    this.recording_properties.$waves_canvas         = $('.js-recording-waves', this.element());
    this.recording_properties.record_button_class   = 'js-recording-record';
    this.recording_properties.$record_button        = $('.js-recording-record', this.element());
    this.recording_properties.stop_button_class     = 'js-recording-stop';
    this.recording_properties.$stop_button          = $('.js-recording-stop', this.element());
    this.recording_properties.playback_button_class = 'js-recording-playback';
    this.recording_properties.$playback_button      = $('.js-recording-playback', this.element());
    this.recording_properties.reset_button_class    = 'js-recording-reset';
    this.recording_properties.$reset_button         = $('.js-recording-reset', this.element());
    this.recording_properties.$timer                = $('.js-recording-timer', this.element());

    // File properties
    // ====================
    // Elements
    this.file_properties.$file_audio = $('.js-file-audio', this.element());
    this.file_properties.$file_reset = $('.js-file-reset', this.element());
    this.file_properties.$file_input = $('.js-file-input', this.element());
    this.file_properties.$file_input_hiden = $('.js-file-input-hidden', this.element());
    this.file_properties.reset_button_class = 'js-file-reset';

    //var current_value = this.element().find('input[type=hidden]').val();

    //if (current_value) {
    // Do something with current values
    //}

    // Set active nav and bind listeners for controls.
    this.set_nav(this.active_pane).bind_nav_controls();

};

/**
 * Create tabular navigation
 */
Charcoal.Admin.Property_Input_Audio.prototype.bind_nav_controls = function ()
{
    // Scope
    var that = this;

    this.element().on('click', '.js-toggle-pane', function ()
    {
        var $toggle = $(this);
        that.set_nav($toggle.attr('data-pane'));
    });
};

/**
 * Display active pane
 * @param   {Object}          $toggle  Pane toggle button (jQuery Object)
 * @param   {String}          pane     Ident of pane to activate
 * @return  {ThisExpression}
 */
Charcoal.Admin.Property_Input_Audio.prototype.set_nav = function (pane)
{
    if (pane) {
        var $toggles = $('.js-toggle-pane'),
            $panes = $('.js-pane'),
            $pane = $panes.filter('[data-pane="' + pane + '"]');

        // Already active
        if (!$pane.hasClass('-active')) {

            // Find which toggle and set as active
            var $toggle = $toggles.filter('[data-pane="' + pane + '"]');
            $toggles.removeClass('-active');
            $toggle.addClass('-active');

            // Hide all
            $panes.removeClass('-active');
            $panes.addClass('hidden');

            // Show one
            $pane.removeClass('hidden');
            $pane.addClass('-active');

            // Activate the pane's content
            this.prepare_pane(pane);
        }
    }

    return this;
};

/**
 * Prepare pane content on its first display
 * @param  {String}  pane  Ident of pane to activate
 */
Charcoal.Admin.Property_Input_Audio.prototype.prepare_pane = function (pane)
{
    var function_name = 'init_' + pane,
        check_function = Charcoal.Admin.Property_Input_Audio.prototype[function_name];

    // Magic init of a pane if a function exists for the pane param
    if (typeof(check_function) === 'function') {
        this[function_name]();
    }
};

/**
 * Mainly allows us to target focus to the textarea
 */
Charcoal.Admin.Property_Input_Audio.prototype.init_text = function () {

    // Don't reinitialized this pane
    if (this.initialized_types.indexOf('text') !== -1) {
        return;
    }

    this.initialized_types.push('text');

    var message = this.text_properties.$voice_message.val();

    message = this.text_strip_tags(message);

    this.text_properties.$voice_message.val(message);

};

/**
 * @see http://phpjs.org/functions/strip_tags/
 */
Charcoal.Admin.Property_Input_Audio.prototype.text_strip_tags = function (input, allowed) {

    allowed = ((String(allowed) || '')
        .toLowerCase()
        .match(/<[a-z][a-z0-9]*>/g) || [])
        .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)

    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

    return input.replace(commentsAndPhpTags, '')
        .replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
};

/**
 * Mainly allows us to bind reset click
 */
Charcoal.Admin.Property_Input_Audio.prototype.init_file = function () {
    // Don't reinitialized this pane
    if (this.initialized_types.indexOf('file') !== -1) {
        return;
    }
    this.initialized_types.push('file');
    this.file_bind_events();
};

/**
 * Bind file events
 */
Charcoal.Admin.Property_Input_Audio.prototype.file_bind_events = function ()
{

    var that = this;
    that.element().on('click', '.' + that.file_properties.reset_button_class, function () {
        that.file_reset_input();
    });
};

/**
 * Reset the file input
 */
Charcoal.Admin.Property_Input_Audio.prototype.file_reset_input = function () {
    this.file_properties.$file_audio.attr('src', '').addClass('hide');
    this.file_properties.$file_reset.addClass('hide');
    this.file_properties.$file_input
        .removeClass('hide')
        .wrap('<form>').closest('form').get(0).reset();
    this.file_properties.$file_input.unwrap();
    this.file_properties.$file_input_hiden.val('');
};

/**
 * Check for browser capabilities
 */
Charcoal.Admin.Property_Input_Audio.prototype.init_recording = function () {

    // Don't reinitialized this pane
    if (this.initialized_types.indexOf('recording') !== -1) {
        return;
    }

    var that = this;

    that.initialized_types.push('recording');

    if (!window.navigator.getUserMedia){
        window.navigator.getUserMedia =
            window.navigator.webkitGetUserMedia ||
            window.navigator.mozGetUserMedia;
    }
    if (!window.navigator.cancelAnimationFrame){
        window.navigator.cancelAnimationFrame =
            window.navigator.webkitCancelAnimationFrame ||
            window.navigator.mozCancelAnimationFrame;
    }
    if (!window.navigator.requestAnimationFrame){
        window.navigator.requestAnimationFrame =
            window.navigator.webkitRequestAnimationFrame ||
            window.navigator.mozRequestAnimationFrame;
    }
    if (!window.AudioContext){
        window.AudioContext =
            window.AudioContext ||
            window.webkitAudioContext;
    }

    window.navigator.getUserMedia(
        {
            audio: {
                mandatory: {
                    googEchoCancellation:false,
                    googAutoGainControl:false,
                    googNoiseSuppression:false,
                    googHighpassFilter:false
                },
                optional: []
            },
        },
        function (stream) {
            that.recording_bind_events();
            that.recording_got_stream(stream);
        },
        function (e) {
            window.alert('Error getting audio. Try plugging in a microphone');
            window.console.log(e);
        }
    );
};

/**
 * Bind recording events
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_bind_events = function ()
{
    var that = this;

    that.element().on('click', '.' + that.recording_properties.record_button_class, function () {
        that.recording_manage_recorder();
    });

    that.element().on('click', '.' + that.recording_properties.stop_button_class, function () {
        that.recording_manage_recorder('stop');
    });

    that.element().on('click', '.' + that.recording_properties.playback_button_class, function () {
        // Test for existing recording first
        if (that.recording_properties.recording_index !== 0 && that.recording_properties.audio_player !== null){
            that.recording_toggle_playback();
        }
    });

    that.element().on('click', '.' + that.recording_properties.reset_button_class, function () {
        that.recording_reset_audio();
    });

};

/**
 * Setup audio recording and analyser displays once audio stream is captured
 * @param MediaStream stream
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_got_stream = function (stream) {

    var that = this;

    that.recording_properties.audio_context = new window.AudioContext();
    that.recording_properties.audio_player  = new window.Audio_Player({
        on_ended: function () {
            that.recording_manage_button_states('pause_playback');
        },
        on_timeupdate: function (audio) {
            that.recording_render_timer(audio);
        },
        on_loadedmetadata: function (audio) {
            that.recording_render_timer(audio);
        }
    });

    var input_point = that.recording_properties.audio_context.createGain(),
        audio_node = that.recording_properties.audio_context.createMediaStreamSource(stream),
        zero_gain  = null;

    audio_node.connect(input_point);
    window.analyserNode = that.recording_properties.audio_context.createAnalyser();
    window.analyserNode.fftSize = 2048;
    input_point.connect(window.analyserNode);
    that.recording_properties.audio_recorder = new window.Recorder(input_point);
    zero_gain = that.recording_properties.audio_context.createGain();
    zero_gain.gain.value = 0.0;
    input_point.connect(zero_gain);
    zero_gain.connect(that.recording_properties.audio_context.destination);
    that.recording_update_analysers();
};

/**
 * Manage button states while recording audio
 * @param  {string}  action  What action needs to be managed
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_manage_button_states = function (action) {

    switch (action){

        case 'start_recording' :
            /**
             * General actions
             * - Clear previous recordings if any
             *
             * Record button
             * - Label = Pause
             * - Color = Red
             */
            this.recording_properties.$record_button.addClass('is-recording');
            /**
             * Stop record button
             * - Enable (will save and complete recording)
             */
            this.recording_properties.$stop_button.prop('disabled',false);
            /**
             * Playback button
             * - Disable (no playing while recording)
             */
            this.recording_properties.$playback_button.prop('disabled',true);
            /**
             * Reset button
             * - Enable
             */
            this.recording_properties.$reset_button.prop('disabled',false);

        break;

        case 'pause_recording' :
            /**
             * Record button
             * - Label = Record
             * - Color = Default
             */
            this.recording_properties.$record_button.removeClass('is-recording');
            /**
             * Stop record button
             * - Enable (will save and complete recording)
             */
            this.recording_properties.$stop_button.prop('disabled',false);
            /**
             * Playback button
             * - Disable (no playing while recording)
             *   - Unless you want to hear what you had recorded previously - do we want this?
             */
            this.recording_properties.$playback_button.prop('disabled',true);
            /**
             * Reset button
             * - Enable
             */
            this.recording_properties.$reset_button.prop('disabled',false);

        break;

        case 'stop_recording' :
            /**
             * Record button
             * - Label = Record
             * - Color = Default
             */
            this.recording_properties.$record_button.removeClass('is-recording');
            /**
             * Stop record button
             * - Disable
             */
            this.recording_properties.$stop_button.prop('disabled',true);
            /**
             * Playback button
             * - Enable
             */
            this.recording_properties.$playback_button.prop('disabled',false);
            /**
             * Reset button
             * - Enable
             */
            this.recording_properties.$reset_button.prop('disabled',false);

        break;

        case 'start_playback' :
            /**
             * Record button
             *
             * Stop record button
             *
             * Playback button
             * - Label = Pause
             * - Color = Green
             */
            this.recording_properties.$playback_button.addClass('is-playing');
            /**
             * Reset button
             */

        break;

        case 'pause_playback' :
            /**
             * Record button
             *
             * Stop record button
             *
             * Playback button
             * - Label = Play
             * - Color = Default
             */
            this.recording_properties.$playback_button.removeClass('is-playing');
            /**
             * Reset button
             */

        break;

        case 'reset' :
            /**
             * Record button
             * - Label = Record
             * - Color = Default
             */
            this.recording_properties.$record_button.removeClass('is-recording');
            /**
             * Stop record button
             * - Disable
             */
            this.recording_properties.$stop_button.prop('disabled',true);
            /**
             * Playback button
             * - Disable
             * - Label = Play
             * - Color = Default
             */
            this.recording_properties.$playback_button.prop('disabled', true).removeClass('is-playing');
            /**
             * Reset button
             * - Disable
             */
            this.recording_properties.$reset_button.prop('disabled',true);

        break;
    }
};

/**
 * Manage display of play time
 * @param  {Object}  audio_element
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_render_timer = function (audio_element) {

    var formatted_time = '';

    // If no element is passed, set to default values
    if (audio_element) {
        var mins = 0,
            secs = 0,
            remaining_time = audio_element.duration - audio_element.currentTime;

        mins = Math.floor(remaining_time / 60);
        secs = Math.round(remaining_time) % 60;

        formatted_time += (mins < 10 ? '0' : '') + String(mins) + ':';
        formatted_time += (secs < 10 ? '0' : '') + String(secs);
    } else {
        formatted_time = '00:00';
    }

    this.recording_properties.$timer.text(formatted_time);
};

/**
 * Manage recording of audio and button states
 * @param   {String}           state  Recording state
 * @return  {EmptyExpression}
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_manage_recorder = function (state) {

    var that = this;

    if (state === 'stop') {
        that.recording_properties.audio_recorder.stop();
        that.recording_properties.audio_recorder.get_buffers(function (buffers) {
            that.recording_got_buffers(buffers);
            that.recording_properties.audio_recorder.clear();
            that.recording_display_canvas('waves');
        });
        that.recording_manage_button_states('stop_recording');
        return;
    }
    if (that.recording_properties.audio_recorder.is_recording()) {
        that.recording_properties.audio_recorder.stop();
        that.recording_manage_button_states('pause_recording');
    // Start recording
    } else {
        if (!that.recording_properties.audio_recorder) {
            return;
        }
        that.recording_properties.audio_recorder.record();
        that.recording_manage_button_states('start_recording');
        that.recording_display_canvas('analyser');
    }
};

/**
 * Toggle playback of recorded audio
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_toggle_playback = function () {
    // Stop playback
    if (this.recording_properties.audio_player.is_playing()) {

        this.recording_properties.audio_player.pause();
        this.recording_manage_button_states('pause_playback');

    // Start playback
    } else {

        if (!this.recording_properties.audio_player) {
            return;
        }
        this.recording_properties.audio_player.play();
        this.recording_manage_button_states('start_playback');
    }
};

/**
 * Reset the recorder and player
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_reset_audio = function () {

    // Visuals
    var analyser = this.recording_properties.$analyser_canvas[0],
        analyser_context = analyser.getContext('2d');

    analyser_context.clearRect(0, 0, analyser.canvas_width, analyser.canvas_height);

    var wavedisplay = this.recording_properties.$waves_canvas[0],
        wavedisplay_context = wavedisplay.getContext('2d');

    wavedisplay_context.clearRect(0, 0, wavedisplay.canvas_width, wavedisplay.canvas_height);

    // Medias
    this.recording_properties.audio_player.load();
    this.recording_properties.audio_player.src('');

    this.recording_properties.audio_recorder.stop();
    this.recording_properties.audio_recorder.clear();

    // Buttons
    this.recording_manage_button_states('reset');

    // Canvases
    this.recording_display_canvas('analyser');

    // Timer
    this.recording_render_timer();

    // Input val
    var input = document.getElementById(this.recording_properties.hidden_input_id);
    if (input){
        input.value = '';
    }
};

/**
 * Audio is recorded and can be output
 * The ONLY time recording_got_buffers is called is right after a new recording is completed
 * @param  {Array}  buffers
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_got_buffers = function (buffers) {
    var canvas = this.recording_properties.$waves_canvas[0],
        that   = this;

    that.recording_draw_buffer(canvas.width, canvas.height, canvas.getContext('2d'), buffers[0]);

    that.recording_properties.audio_recorder.export_wav(function (blob) {
        that.recording_done_encoding(blob);
    });
};

/**
 * Draw recording as waves in canvas
 * @param  {Integer}           width
 * @param  {Integer}           height
 * @param  {RenderingContext}  context
 * @param  {Array}             data
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_draw_buffer = function (width, height, context, data) {
    var step = Math.ceil(data.length / width),
        amp = height / 2;

    context.fillStyle = '#DDDDDD';
    context.clearRect(0,0,width,height);

    for (var i = 0; i < width; i++){
        var min = 1.0,
            max = -1.0;

        for (var j = 0; j < step; j++) {
            var datum = data[(i * step) + j];
            if (datum < min){
                min = datum;
            }
            if (datum > max){
                max = datum;
            }
        }
        context.fillRect(i,(1 + min) * amp,1,Math.max(1,(max -min) * amp));
    }
};

/**
 * Convert the blob into base64 data
 * @param  Blob  blob  Audio data blob
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_done_encoding = function (blob) {

    var reader = new window.FileReader(),
        data   = null,
        that   = this;

    reader.readAsDataURL(blob);

    reader.onloadend = function () {
        data = reader.result;
        that.recording_properties.recording_index++;
        that.recording_manage_audio_data(data);
    };

};

/**
 * Manage base64 audio data
 * @param  {String}  data  Base64 audio data
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_manage_audio_data = function (data) {
    if (data){

        // Write the data to an input for saving
        var input = document.getElementById(this.recording_properties.hidden_input_id);
        if (input){
            input.value = data;
        }

        // Save the data for playback
        this.recording_properties.audio_player.src(data);
        this.recording_properties.audio_player.load();
    }
};

/**
 * Manage display of canvas
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_display_canvas = function (canvas) {
    switch (canvas) {
        case 'waves':
            this.recording_properties.$analyser_canvas.addClass('hidden');
            this.recording_properties.$waves_canvas.removeClass('hidden');
        break;
        default:
            this.recording_properties.$analyser_canvas.removeClass('hidden');
            this.recording_properties.$waves_canvas.addClass('hidden');
        break;
    }
};

/**
 * Stop refreshing the analyser
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_cancel_analyser_update = function () {
    window.cancelAnimationFrame(this.recording_properties.animation_frame);
    this.recording_properties.animation_frame = null;
};

/**
 * Update analyser graph according to microphone input
 */
Charcoal.Admin.Property_Input_Audio.prototype.recording_update_analysers = function () {

    if (!this.recording_properties.analyser_context) {
        this.recording_properties.analyser_context = this.recording_properties.$analyser_canvas[0].getContext('2d');
    }

    var that = this,
        _context = that.recording_properties.analyser_context,
        _canvas = that.recording_properties.$analyser_canvas[0];

    that.recording_properties.canvas_width = _canvas.width;
    that.recording_properties.canvas_height = _canvas.height;

    _context.lineCap = 'round';

    // Draw analyzer only if recording
    if (that.recording_properties.audio_recorder.is_recording()) {
        var spacing      = 5,
            bar_width    = 2,
            num_bars     = Math.round(that.recording_properties.canvas_width / spacing),
            freqByteData = new window.Uint8Array(window.analyserNode.frequencyBinCount),
            multiplier   = 0;

        window.analyserNode.getByteFrequencyData(freqByteData);
        multiplier = window.analyserNode.frequencyBinCount / num_bars;

        _context.clearRect(
            0,
            0,
            that.recording_properties.canvas_width,
            that.recording_properties.canvas_height
        );

        for (var i = 0; i < num_bars; ++i) {

            var magnitude = 0,
                offset = Math.floor(i * multiplier);

            for (var j = 0; j < multiplier; j++){
                magnitude += freqByteData[offset + j];
            }

            magnitude = magnitude / multiplier;

            _context.fillStyle =
                //'hsl(' + Math.round((i * 360) / num_bars) + ', 100%, 50%)';
                _context.fillStyle = '#DDDDDD';

            _context.fillRect(
                i * spacing,
                that.recording_properties.canvas_height,
                bar_width,
                -magnitude
            );
        }
    } else {
        _context.clearRect(
            0,
            0,
            that.recording_properties.canvas_width,
            that.recording_properties.canvas_height
        );
    }

    that.recording_properties.animation_frame = window.requestAnimationFrame(function () {
        that.recording_update_analysers();
    });
};

(function (window) {

    var WORKER_PATH = '../../assets/admin/scripts/vendors/recorderWorker.js';

    /**
     * Recorder worker that handles saving microphone input to buffers
     * @param  {GainNode}  source
     * @param  {Object}    cfg
     */
    var Recorder = function (source, cfg) {
        var config        = cfg || {},
            buffer_length = config.buffer_length || 4096,
            worker        = new window.Worker(config.workerPath || WORKER_PATH),
            recording     = false,
            current_callback;

        this.context = source.context;

        if (!this.context.createScriptProcessor){
            this.node = this.context.createJavaScriptNode(buffer_length, 2, 2);
        } else {
            this.node = this.context.createScriptProcessor(buffer_length, 2, 2);
        }

        worker.postMessage({
            command: 'init',
            config: {
                sampleRate: this.context.sampleRate
            }
        });

        this.node.onaudioprocess = function (e) {
            if (!recording){
                return;
            }
            worker.postMessage({
                command: 'record',
                buffer: [
                e.inputBuffer.getChannelData(0),
                e.inputBuffer.getChannelData(1)
                ]
            });
        };

        this.configure = function (cfg) {
            for (var prop in cfg){
                if (cfg.hasOwnProperty(prop)){
                    config[prop] = cfg[prop];
                }
            }
        };

        this.record = function () {
            recording = true;
        };

        this.stop = function () {
            recording = false;
        };

        this.clear = function () {
            worker.postMessage({ command: 'clear' });
        };

        this.get_buffers = function (cb) {
            current_callback = cb || config.callback;
            worker.postMessage({ command: 'getBuffers' });
        };

        this.export_wav = function (cb, type) {
            current_callback = cb || config.callback;
            type = type || config.type || 'audio/wav';
            if (!current_callback){
                throw new Error('Callback not set');
            }
            worker.postMessage({
                command: 'exportWAV',
                type: type
            });
        };

        this.is_recording = function () {
            return recording;
        };

        worker.onmessage = function (e) {
            var blob = e.data;
            current_callback(blob);
        };

        source.connect(this.node);
        // If the script node is not connected to an output the "onaudioprocess" event is not triggered in chrome.
        this.node.connect(this.context.destination);
    };

    window.Recorder = Recorder;

})(window);

(function (window) {

    /**
     * Enhanced HTMLAudioElement player
     * @param    Object   cfg
     */
    var Audio_Player = function (cfg) {

        this.callbacks = {
            on_ended: cfg.on_ended || function () {},
            on_pause: cfg.on_pause || function () {},
            on_playing: cfg.on_playing || function () {},
            on_timeupdate: cfg.on_timeupdate || function () {},
            on_loadedmetadata: cfg.on_loadedmetadata || function () {}
        };

        this._element = new window.Audio();

        this.play = function () {
            this.element().play();
        };

        this.pause = function () {
            this.element().pause();
        };

        this.load = function () {
            this.element().load();
        };

        this.src = function (data) {
            this.element().src = data;
        };

        this.is_playing = function () {
            return !this.element().paused && !this.element().ended && this.element().currentTime > 0;
        };

        this.element = function () {
            return this._element;
        };

        var that = this;

        /*
         * Events
         */

        that.element().addEventListener('ended', function () {
            that.callbacks.on_ended();
        });

        that.element().addEventListener('pause', function () {
            that.callbacks.on_pause();
        });

        that.element().addEventListener('playing', function () {
            that.callbacks.on_playing();
        });

        that.element().addEventListener('timeupdate', function () {
            that.callbacks.on_timeupdate(that.element());
        });

        that.element().addEventListener('loadedmetadata', function () {
            that.callbacks.on_loadedmetadata(that.element());
        });

    };

    window.Audio_Player = Audio_Player;

})(window);
