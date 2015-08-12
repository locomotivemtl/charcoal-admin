var Charcoal = Charcoal || {};
/**
* Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
* It gives access to private properties and public methods
* @return  {object}  Charcoal.Admin
*/
Charcoal.Admin = (function ()
{
    'use strict';

    var options = {
            base_url: null,
            admin_path: null,
        },
        manager;

    /**
    * Object function that acts as a container for public methods
    */
    function Admin()
    {
    }

    /**
    * Set data that can be used by public methods
    * @param  {object}  data  Object containing data that needs to be set
    */
    Admin.set_data = function (data)
    {
        options = $.extend(true, options, data);
    };

    /**
    * Generates the admin URL used by forms and other objects
    * @return  {string}  URL for admin section
    */
    Admin.admin_url = function ()
    {
        return options.base_url + options.admin_path + '/';
    };

    /**
    * Provides an access to our instanciated ComponentManager
    * @return  {object}  ComponentManager instance
    */
    Admin.manager = function ()
    {
        if (typeof(manager) === 'undefined') {
            manager = new Charcoal.Admin.ComponentManager();
        }

        return manager;
    };

    /**
    * Convert an object namespace string into a usable object name
    * @param   {string}  name  String that respects the namespace structure : charcoal/admin/property/input/switch
    * @return  {string}  name  String that respects the object name structure : Property_Input_Switch
    */
    Admin.get_object_name = function (name)
    {
        // Getting rid of Charcoal.Admin namespacing
        var string_array = name.split('/');
        string_array = string_array.splice(2,string_array.length);

        // Uppercasing
        string_array.forEach(function (element, index, array) {
            array[index] = element.charAt(0).toUpperCase() + element.slice(1);
        });

        name = string_array.join('_');

        return name;
    };

    return Admin;

}());
;/**
* charcoal/admin/component_manager
*/

Charcoal.Admin.ComponentManager = function ()
{
    var that = this;

    that.components = {};

    $(document).on('ready', function () {
        that.render();
    });
};

Charcoal.Admin.ComponentManager.prototype.add_property_input = function (opts)
{
    this.add_component('property_inputs', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_widget = function (opts)
{
    this.add_component('widgets', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_template = function (opts)
{
    this.add_component('templates', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_component = function (component_type, opts)
{
    // Figure out which component to instanciate
    var ident = Charcoal.Admin.get_object_name(opts.type);

    // Make sure it exists first
    if (typeof(Charcoal.Admin[ident]) === 'function') {

        opts.ident = ident;

        // Check if component type array exists in components array
        this.components[component_type] = this.components[component_type] || [];
        this.components[component_type].push(opts);

    } else {
        console.log('Was not able to store ' + ident + ' in ' + component_type + ' sub-array');
    }

};

Charcoal.Admin.ComponentManager.prototype.render = function ()
{

    for (var component_type in this.components) {

        for (var i = 0, len = this.components[component_type].length; i < len; i++) {

            var component_data = this.components[component_type][i];

            try {
                var component = new Charcoal.Admin[component_data.ident](component_data);
                this.components[component_type][i] = component;
            } catch (error) {
                console.log('Was not able to instanciate ' + component_data.ident);
            }
        }

    }
};
;/**
* charcoal/admin/property
*/

Charcoal.Admin.Property = function (opts)
{
    window.alert('Property ' + opts);
};
;/**
 * charcoal/admin/property/audio
 * Require:
 * - jQuery
 * @see https://github.com/cwilso/AudioRecorder
 * @see https://github.com/mattdiamond/Recorderjs
 * @method Property_Audio
 * @param Object opts
 */
Charcoal.Admin.Property_Audio = function (opts)
{
    // Common Property properties
    this.property_type = 'charcoal/admin/property/audio';

    // Property_Audio properties
    this.audio_context       = null;
    this.audio_recorder      = null;
    this.animation_frame     = null;
    this.analyser_context    = null;
    this.canvas_width        = 0;
    this.canvas_height       = 0;
    this.recording_index     = 0;
    this.current_recording   = null;
    this.audio_player        = null;
    this.$record_button      = $('.btn-record:first');
    this.$stop_record_button = $('.btn-stop-record:first');
    this.$playback_button    = $('.btn-play:first');
    this.$reset_button       = $('.btn-reset:first');

    this.init(opts);
};

Charcoal.Admin.Property_Audio.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Audio.prototype.constructor = Charcoal.Admin.Property_Audio;
Charcoal.Admin.Property_Audio.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Return default data
 * @method default_data
 * @return Object default_data
 */
Charcoal.Admin.Property_Audio.prototype.default_data = function ()
{
    return {
        obj_type: '',
        input_id: null
    };
};

/**
 * Set data
 * @method set_data
 * @param Object data
 * @return ThisExpression
 */
Charcoal.Admin.Property_Audio.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type;
    this.input_id = data.input_id;
    return this;
};

/**
 * Merge options with defaults and initialize the property
 * @method init
 * @param Object opts
 */
Charcoal.Admin.Property_Audio.prototype.init = function (opts)
{
    // Set properties
    var data = $.extend(true, {}, this.default_data(), opts);
    this.set_data(data);

    this.init_audio();
};

/**
 * Check for browser capabilities
 * @method init_audio
 */
Charcoal.Admin.Property_Audio.prototype.init_audio = function () {
    var that = this;

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
        that.bind_events();
        that.got_stream(stream);
    },
    function (e) {
        window.alert('Error getting audio. Try plugging in a microphone');
        window.console.log(e);
    });
};

/**
 * Bind events
 * @method bind_events
 */
Charcoal.Admin.Property_Audio.prototype.bind_events = function ()
{
    var that = this;

    that.$record_button.on('click',function () {
        that.manage_recording();
    });

    that.$stop_record_button.on('click',function () {
        that.manage_recording('stop');
    });

    that.$playback_button.on('click',function () {
        // Test for existing recording first
        if (that.recording_index !== 0 && that.audio_player !== null){
            that.toggle_playback();
        }
    });

    that.$reset_button.on('click',function () {
        that.reset_audio();
    });

};

/**
 * Setup audio recording and analyser displays once audio stream is captured
 * @method got_stream
 * @param MediaStream stream
 */
Charcoal.Admin.Property_Audio.prototype.got_stream = function (stream) {

    var that = this;

    that.audio_context = new window.AudioContext();
    that.audio_player  = new window.Audio_Player({
        on_ended: function () {
            that.manage_button_states('pause_playback');
        }
    });

    var input_point = that.audio_context.createGain(),
        audio_node = that.audio_context.createMediaStreamSource(stream),
        zero_gain  = null;

    audio_node.connect(input_point);
    window.analyserNode = that.audio_context.createAnalyser();
    window.analyserNode.fftSize = 2048;
    input_point.connect(window.analyserNode);
    that.audio_recorder = new window.Recorder(input_point);
    zero_gain = that.audio_context.createGain();
    zero_gain.gain.value = 0.0;
    input_point.connect(zero_gain);
    zero_gain.connect(that.audio_context.destination);
    that.update_analysers();
};

Charcoal.Admin.Property_Audio.prototype.manage_button_states = function (action) {

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
            this.$record_button.text(this.$record_button.attr('data-label-pause'));
            this.$record_button.addClass('-is-recording');
            /**
             * Stop record button
             * - Enable (will save and complete recording)
             */
            this.$stop_record_button.prop('disabled',false);
            /**
             * Playback button
             * - Disable (no playing while recording)
             */
            this.$playback_button.prop('disabled',true);
            /**
             * Reset button
             * - Enable
             */
            this.$reset_button.prop('disabled',false);

        break;

        case 'pause_recording' :
            /**
             * Record button
             * - Label = Record
             * - Color = Default
             */
            this.$record_button.text(this.$record_button.attr('data-label-record'));
            this.$record_button.removeClass('-is-recording');
            /**
             * Stop record button
             * - Enable (will save and complete recording)
             */
            this.$stop_record_button.prop('disabled',false);
            /**
             * Playback button
             * - Disable (no playing while recording)
             *   - Unless you want to hear what you had recorded previously - do we want this?
             */
            this.$playback_button.prop('disabled',true);
            /**
             * Reset button
             * - Enable
             */
            this.$reset_button.prop('disabled',false);

        break;

        case 'stop_recording' :
            /**
             * Record button
             * - Label = Record
             * - Color = Default
             */
            this.$record_button.text(this.$record_button.attr('data-label-record'));
            this.$record_button.removeClass('-is-recording');
            /**
             * Stop record button
             * - Disable
             */
            this.$stop_record_button.prop('disabled',true);
            /**
             * Playback button
             * - Enable
             */
            this.$playback_button.prop('disabled',false);
            /**
             * Reset button
             * - Enable
             */
            this.$reset_button.prop('disabled',false);

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
            this.$playback_button.text(this.$playback_button.attr('data-label-pause'));
            this.$playback_button.addClass('-is-playing');
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
            this.$playback_button.text(this.$playback_button.attr('data-label-play'));
            this.$playback_button.removeClass('-is-playing');
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
            this.$record_button.text(this.$record_button.attr('data-label-record'));
            this.$record_button.removeClass('-is-recording');
            /**
             * Stop record button
             * - Disable
             */
            this.$stop_record_button.prop('disabled',true);
            /**
             * Playback button
             * - Disable
             * - Label = Play
             * - Color = Default
             */
            this.$playback_button.prop('disabled',true);
            this.$playback_button.text(this.$playback_button.attr('data-label-play'));
            this.$playback_button.removeClass('-is-playing');
            /**
             * Reset button
             * - Disable
             */
            this.$reset_button.prop('disabled',true);

        break;
    }
};

/**
 * Manage recording of audio and button states
 * @method toggle_recording
 * @param Node button
 */
Charcoal.Admin.Property_Audio.prototype.manage_recording = function (state) {

    var that = this;

    if (state === 'stop'){
        that.audio_recorder.stop();
        that.audio_recorder.get_buffers(function (buffers) {
            that.got_buffers(buffers);
            that.audio_recorder.clear();
        });
        that.manage_button_states('stop_recording');
        return;
    }
    if (that.audio_recorder.is_recording()) {
        that.audio_recorder.stop();
        that.manage_button_states('pause_recording');
    // Start recording
    } else {
        if (!that.audio_recorder) {
            return;
        }
        that.audio_recorder.record();
        that.manage_button_states('start_recording');
    }
};

/**
 * Toggle playback of recorded audio
 * @method toggle_playback
 */
Charcoal.Admin.Property_Audio.prototype.toggle_playback = function () {

    // Stop playback
    if (this.audio_player.is_playing()) {

        this.audio_player.pause();
        this.manage_button_states('pause_playback');

    // Start playback
    } else {

        if (!this.audio_player) {
            return;
        }
        this.audio_player.play();
        this.manage_button_states('start_playback');

    }

};

/**
 * Reset the recorder and player
 * @method toggle_playback
 */
Charcoal.Admin.Property_Audio.prototype.reset_audio = function () {

    // Visuals
    var analyser = window.document.getElementById('analyser'),
        analyser_context = analyser.getContext('2d');

    analyser_context.clearRect(0, 0, analyser.canvas_width, analyser.canvas_height);

    var wavedisplay = window.document.getElementById('wavedisplay'),
        wavedisplay_context = wavedisplay.getContext('2d');

    wavedisplay_context.clearRect(0, 0, wavedisplay.canvas_width, wavedisplay.canvas_height);

    // Medias
    this.audio_player.load();
    this.audio_player.src('');

    this.audio_recorder.stop();
    this.audio_recorder.clear();

    // Buttons
    this.manage_button_states('reset');

};

/**
 * Audio is recorded and can be output
 * The ONLY time got_buffers is called is right after a new recording is completed
 * @method got_buffers
 * @param array buffers
 */
Charcoal.Admin.Property_Audio.prototype.got_buffers = function (buffers) {
    var canvas = window.document.getElementById('wavedisplay'),
        that   = this;

    that.draw_buffer(canvas.width, canvas.height, canvas.getContext('2d'), buffers[0]);

    that.audio_recorder.export_wav(function (blob) {
        that.done_encoding(blob);
    });
};

/**
 * Draw recording as waves in canvas
 * @method draw_buffer
 * @param int width
 * @param int height
 * @param RenderingContext context
 * @param array data
 */
Charcoal.Admin.Property_Audio.prototype.draw_buffer = function (width, height, context, data) {
    var step = Math.ceil(data.length / width),
        amp = height / 2;

    context.fillStyle = 'silver';
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
 * @method done_encoding
 * @param Blob blob
 */
Charcoal.Admin.Property_Audio.prototype.done_encoding = function (blob) {

    var reader = new window.FileReader(),
        data   = null,
        that   = this;

    reader.readAsDataURL(blob);

    reader.onloadend = function () {
        data = reader.result;
        that.recording_index++;
        that.manage_audio_data(data);
    };

};

/**
 * Manage base64 audio data
 * @method save_to_input
 * @param string data
 */
Charcoal.Admin.Property_Audio.prototype.manage_audio_data = function (data) {
    if (data){

        // Write the data to an input for saving
        var input = window.document.getElementById(this.input_id);
        if (input){
            input.value = data;
        }

        // Save the data for playback
        this.audio_player.src(data);
        this.audio_player.load();

        this.$playback_button.removeClass('-is-hidden');
    }
};

/**
 * Stop refreshing the analyser
 * @method cancel_analyser_update
 */
Charcoal.Admin.Property_Audio.prototype.cancel_analyser_update = function () {
    window.cancelAnimationFrame(this.animation_frame);
    this.animation_frame = null;
};

/**
 * Update analyser graph according to microphone input
 * @method update_analysers
 */
Charcoal.Admin.Property_Audio.prototype.update_analysers = function () {

    var that = this;

    if (!that.analyserContext) {
        var canvas = window.document.getElementById('analyser');
        that.canvas_width = canvas.width;
        that.canvas_height = canvas.height;
        that.analyserContext = canvas.getContext('2d');
    }

    // Drawing Analyzer
    {
        var spacing      = 3,
            bar_width    = 1,
            numBars      = Math.round(that.canvas_width / spacing),
            freqByteData = new window.Uint8Array(window.analyserNode.frequencyBinCount),
            multiplier   = 0;

        window.analyserNode.getByteFrequencyData(freqByteData);
        multiplier = window.analyserNode.frequencyBinCount / numBars;

        that.analyserContext.clearRect(0, 0, that.canvas_width, that.canvas_height);
        that.analyserContext.fillStyle = '#F6D565';
        that.analyserContext.lineCap = 'round';

        for (var i = 0; i < numBars; ++i) {

            var magnitude = 0,
                offset = Math.floor(i * multiplier);

            for (var j = 0; j < multiplier; j++){
                magnitude += freqByteData[offset + j];
            }
            magnitude = magnitude / multiplier;
            that.analyserContext.fillStyle = 'hsl( ' + Math.round((i * 360) / numBars) + ', 100%, 50%)';
            that.analyserContext.fillRect(i * spacing, that.canvas_height, bar_width, -magnitude);
        }
    }

    that.animation_frame = window.requestAnimationFrame(function () {
        that.update_analysers();
    });
};

(function (window) {

    var WORKER_PATH = '../../assets/admin/scripts/vendors/recorderWorker.js';

    /**
     * Recorder worker that handles saving microphone input to buffers
     * @method Recorder
     * @param GainNode source
     * @param Object cfg
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
     * @method Audio_Player
     * @param GainNode source
     * @param Object cfg
     */
    var Audio_Player = function (cfg) {

        this.callbacks = {
            on_ended: cfg.on_ended || function () {},
            on_pause: cfg.on_pause || function () {},
            on_playing: cfg.on_playing || function () {},
            on_timeupdate: cfg.on_timeupdate || function () {}
        };

        this.element = new window.Audio();

        this.play = function () {
            this.element.play();
        };

        this.pause = function () {
            this.element.pause();
        };

        this.load = function () {
            this.element.load();
        };

        this.src = function (data) {
            this.element.src = data;
        };

        this.is_playing = function () {
            return !this.element.paused && !this.element.ended && this.element.currentTime > 0;
        };

        var that = this;

        /*
         * Events
         */

        that.element.addEventListener('ended', function () {
            that.callbacks.on_ended();
        });

        that.element.addEventListener('pause', function () {
            that.callbacks.on_pause();
        });

        that.element.addEventListener('playing', function () {
            that.callbacks.on_playing();
        });

        that.element.addEventListener('timeupdate', function () {
            that.callbacks.on_timeupdate();
        });

    };

    window.Audio_Player = Audio_Player;

})(window);
;/**
* Switch looking input that manages boolean properties
* charcoal/admin/property/input/switch
*
* Require:
* - jQuery
* - bootstrapSwitch
*
* @param  {Object}  opts Options for input property
*/

Charcoal.Admin.Property_Input_Switch = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/switch';

    // Widget_Form properties
    this.input_id = opts.input_id || null;

    var defaults = {
        input_selector: null,
        switch_selector: null
    };

    this.options = $.extend({}, defaults, opts.data);

    this.create_switch();
};
Charcoal.Admin.Property_Input_Switch.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Switch.prototype.constructor = Charcoal.Admin.Property_Input_Switch;
Charcoal.Admin.Property_Input_Switch.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Switch.prototype.create_switch = function ()
{
    var that = this;

    $(that.options.switch_selector).bootstrapSwitch({
        onSwitchChange: function (event, state) {
            $(that.options.input_selector).val((state) ? 1 : 0);
        }
    });
};
;/**
* charcoal/admin/property/input/tinymce
*
* Require:
* - jQuery
* - tinyMCE
*/
Charcoal.Admin.Property_Input_Tinymce = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/tinymce';

    // Input properties
    this.input_id = null;
    this.editor_options = null;

    this.init(opts);
};
Charcoal.Admin.Property_Input_Tinymce.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Tinymce.prototype.constructor = Charcoal.Admin.Property_Input_Tinymce;
Charcoal.Admin.Property_Input_Tinymce.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Tinymce.prototype.init = function (opts)
{
    this.input_id = opts.input_id || null;
    this.editor_options = opts.editor_options || {};

    var default_opts = {
        //language: 'fr_FR',

        // Plugins
        plugins: [
            'advlist',
            'anchor',
            'autolink',
            'autoresize',
            //'autosave',
            //'bbcode',
            'charmap',
            'code',
            'colorpicker',
            'contextmenu',
            //'directionality',
            //'emoticons',
            //'fullpage',
            'fullscreen',
            'hr',
            'image',
            //'imagetools',
            //'insertdatetime',
            //'layer',
            //'legacyoutput',
            'link',
            'lists',
            //'importcss',
            'media',
            'nonbreaking',
            'noneditable',
            //'pagebreak',
            'paste',
            //'preview',
            'print',
            //'save',
            'searchreplace',
            //'spellchecker',
            'tabfocus',
            'table',
            //'template',
            //'textcolor',
            //'textpattern',
            'visualblocks',
            'visualchars',
            'wordcount'
        ],

        // Toolbar
        toolbar: 'undo redo | ' +
        'styleselect | ' +
        'bold italic | ' +
        'forecolor backcolor | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | ' +
        'link image anchor',

        // General
        browser_spellcheck: true,
        end_container_on_empty_block: true,

        // Cleanup / Output
        allow_conditional_comments: true,
        convert_fonts_to_spans: true,
        forced_root_block: 'p',
        //forced_root_block_attrs: {},
        // remove_trailing_brs: true

        // Content style
        //body_id: "",
        //body_class: "",
        //content_css:"",
        //content_style:"",

        // URL
        allow_script_urls: false,
        document_base_url: '{{base_url}}',
        relative_urls: true,
        remove_script_host: false,

        // Plugins options
        autoresize_min_height: '150px',
        autoresize_max_height: '400px',
        //code_dialog_width: '400px',
        //code_dialog_height: '400px',
        contextmenu: 'link image inserttable | cell row column deletetable',
        //image_list: [],
        image_advtab: true,
        //image_class_list: [],
        //imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
        //link_list: [],
        //target_list: [],
        //rel_list: [],
        //link_class_list: [],
        importcss_append: true,
        //importcss_file_filter: "",
        //importcss_selector_filter: ".my-prefix-",
        //importcss_groups: [],
        // importcss_merge_classes: false,
        media_alt_source: false,
        media_poster: true,
        media_dimensions: true,
        //media_filter_html: false,
        nonbreaking_force_tab: false,
        //pagebreak_separator: ""
        paste_data_images: true,
        paste_as_text: true,
        //paste_preprocess: function(plugin, args) { },
        //paste_postprocess: function(plugin, args) { },
        //paste_word_valid_elements: "",
        //paste_webkit_styles: "",
        //paste_retain_style_properties: "",
        paste_merge_formats: true,
        //save_enablewhendirty: true,
        //save_onsavecallback: function() { },
        //save_oncancelcallback: function() { },
        //table_clone_elements: "",
        table_grid: true,
        table_tab_navigation: true,
        //table_default_attributes: {},
        //table_default_styles: {},
        //table_class_list: [],
        //table_cell_class_list: []
        //table_row_class_list: [],
        //templates: [].
        //textpattern_patterns: [],
        visualblocks_default_state: false

    };

    var tinymce_opts = $.extend({}, default_opts, this.editor_options);

    tinymce_opts.selector = '#' + this.input_id;

    tinymce.init(tinymce_opts); // jshint ignore:line
};
;/**
* charcoal/admin/template
*/

Charcoal.Admin.Template = function (opts)
{
    window.alert('Template ' + opts);
};
;/**
* charcoal/admin/template/login
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

//Charcoal.Admin.Template_Login = new Charcoal.Admin.Widget();        // Here's where the inheritance occurs

Charcoal.Admin.Template_Login = function (opts)
{
    // Common Template properties
    this.template_type = 'charcoal/admin/template/login';

    this.init(opts);
};

Charcoal.Admin.Template_Login.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Login.prototype.constructor = Charcoal.Admin.Template_Login;
Charcoal.Admin.Template_Login.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Login.prototype.init = function (opts)
{
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Login.prototype.bind_events = function ()
{

    $('.js-login-submit').on('click', function (e) {
        e.preventDefault();

        var form = $(this).parents('form');
        var url = Charcoal.Admin.admin_url() + 'action/json/login';
        var data = form.serialize();
        $.post(url, data, function (response) {
            window.console.debug(response);
            if (response.success) {
                window.location.href = response.next_url;
            } else {
                //window.alert('Error');
                BootstrapDialog.show({
                    title: 'Login error',
                    message: 'Authentication failed. Please try again.',
                    type: BootstrapDialog.TYPE_DANGER
                });
            }
        }).fail(function () {
            //window.alert('Error');
            BootstrapDialog.show({
                title: 'Login error',
                message: 'Authentication failed. Please try again.',
                type: BootstrapDialog.TYPE_DANGER
            });
        });
    });
};
;Charcoal.Admin.Template_MenuHeader = function ()
{
    // toggle-class.js
    // ==========================================================================
    $('.js-toggle-class').click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var dataClass = $this.data('class');
        var dataTarget = $this.data('target');

        $(dataTarget).toggleClass(dataClass);
    });

    // accordion.js
    // ==========================================================================
    $(document).on('click', '.js-accordion-header', function (event) {
        event.preventDefault();

        var $this = $(this);

        $this.toggleClass('is-open')
             .siblings('.js-accordion-content')
             .stop()
             .slideToggle();
    });
};
;/**
* charcoal/admin/widget
*/

Charcoal.Admin.Widget = function (opts)
{
    window.alert('Widget ' + opts);
};

Charcoal.Admin.Widget.prototype.reload = function (cb)
{
    var that = this;

    var url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };
    $.post(url, data, cb);
};
;/**
* Form widget that manages data sending
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - bootstrapSwitch
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Form = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id = null;
    this.obj_id = null;
    this.form_selector = null;

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts)
{
    this.widget_id = opts.id || this.widget_id;
    this.obj_id = opts.data.obj_id || this.obj_id;
    this.form_selector = opts.data.form_selector || this.form_selector;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    $(that.form_selector).on('submit', function (e) {
        e.preventDefault();
        that.submit_form(this);
    });
};

Charcoal.Admin.Widget_Form.prototype.submit_form = function (form)
{
    var form_data = new FormData(form),
        url,
        is_new_object;

    if (this.obj_id) {
        url = Charcoal.Admin.admin_url() + 'action/json/object/update';
        is_new_object = false;
    } else {
        url = Charcoal.Admin.admin_url() + 'action/json/object/save';
        is_new_object = true;
    }

    $.ajax({
        url: url,
        type: 'POST',
        processData: false,
        contentType: false,
        data: form_data,
        success: function (response) {
            console.debug(response);
            if (response.success) {
                if (!is_new_object) {
                    BootstrapDialog.show({
                        title: 'Save successful!',
                        message: 'Object was successfully saved to storage.',
                        type: BootstrapDialog.TYPE_SUCCESS
                    });
                } else {
                    window.location.href =
                        Charcoal.Admin.admin_url() +
                        'object/edit?obj_type=' + this.obj_type +
                        '&obj_id=' + response.obj_id;
                }
            } else {
                BootstrapDialog.show({
                    title: 'Error. Could not save object.',
                    message: 'An error occurred and the object could not be saved.',
                    type: BootstrapDialog.TYPE_DANGER
                });
            }
        },
        error: function () {
            BootstrapDialog.show({
                title: 'Error. Could not save object.',
                message: 'An error occurred and the object could not be saved.',
                type: BootstrapDialog.TYPE_DANGER
            });
        }
    });
};
;/**
* charcoal/admin/widget/table
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

//Charcoal.Admin.Widget_Table = new Charcoal.Admin.Widget();        // Here's where the inheritance occurs

Charcoal.Admin.Widget_Table = function (opts)
{
    // Common Widget properties
    this.widget_type = 'charcoal/admin/widget/table';

    // Widget_Table properties
    this.obj_type = null;
    this.widget_id = null;
    this.properties = null;
    this.properties_options = null;
    this.filters = null;
    this.orders = null;
    this.pagination = null;
    this.filters = null;

    this.init(opts);
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Table.prototype.init = function (opts)
{
    // Set properties
    var data = $.extend(true, {}, this.default_data(), opts);
    this.set_data(data);

    this.bind_events();
};

Charcoal.Admin.Widget_Table.prototype.default_data = function ()
{
    return {
        obj_type:   '',
        widget_id:  null,
        properties: null,
        properties_options: null,
        filters:    null,
        orders:     null,
        pagination:{
            page:           1,
            num_per_page:   50
        }

    };
};

Charcoal.Admin.Widget_Table.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type || '';
    this.widget_id = data.widget_id || null;
    return this;
};

Charcoal.Admin.Widget_Table.prototype.bind_events = function ()
{
    this.bind_obj_events();
    this.bind_list_events();
    this.bind_sublist_events();
};

Charcoal.Admin.Widget_Table.prototype.bind_obj_events = function ()
{
    var that = this;

    $('.obj-edit').on('click', function (e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');
        window.alert('Edit ' + obj_id);
    });
    $('.obj-quick-edit').on('click', function (e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');

        var url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
        var data = {
            widget_type: 'charcoal/admin/widget/objectForm',
            widget_options: {
                obj_type: that.obj_type,
                obj_id: obj_id
            }
        };
        $.post(url, data, function (response) {
            var dlg = BootstrapDialog.show({
                title: 'Quick Edit',
                message: '...',
                nl2br: false
            });
            if (response.success) {
                dlg.setMessage(response.widget_html);
            } else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        });

    });
    $('.obj-inline-edit').on('click', function (e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var obj_id = row.data('id');
        var url = Charcoal.Admin.admin_url() + 'action/json/widget/table/inline';
        var data = {
            obj_type: that.obj_type,
            obj_id: obj_id
        };
        $.post(url, data, function (response) {
            if (response.success) {
                var inline_properties = response.inline_properties;
                var p;
                for (p in inline_properties) {
                    var td = row.find('.property-' + p);
                    td.html(inline_properties[p]);
                }
            }
        });
    });
    $('.obj-delete').on('click', function (e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');
        if (window.confirm('Are you sure you want to delete this object?')) {
            var url = Charcoal.Admin.admin_url() + 'action/json/object/delete';
            var data = {
                obj_type: that.obj_type,
                obj_id: obj_id
            };
            $.post(url, data, function (response) {
                if (response.success) {
                    that.reload();
                } else {
                    window.alert('Delete failed.');
                }
            });
        }
    });

};

Charcoal.Admin.Widget_Table.prototype.bind_list_events = function ()
{
    var that = this;

    $('.list-quick-create').on('click', function (e) {
        e.preventDefault();
        var url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
        var data = {
            widget_type: 'charcoal/admin/widget/objectForm',
            widget_options: {
                obj_type: that.obj_type,
                obj_id: 0
            }
        };
        $.post(url, data, function (response) {
            var dlg = BootstrapDialog.show({
                title: 'Quick Create',
                message: '...',
                nl2br: false
            });
            if (response.success) {
                dlg.setMessage(response.widget_html);
            } else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        });

    });
};

Charcoal.Admin.Widget_Table.prototype.bind_sublist_events = function ()
{
    var that = this;

    $('.sublist-inline-edit').on('click', function (e) {
        e.preventDefault();
        var sublist = that.sublist();
        //console.debug(sublist);
        var url = Charcoal.Admin.admin_url() + 'action/json/widget/table/inlinemulti';
        var data = {
            obj_type: that.obj_type,
            obj_ids: sublist.obj_ids
        };
        $.post(url, data, function (response) {
            //console.debug(response);
            if (response.success) {
                var objects = response.objects;
                //console.debug(objects);
                //console.debug(objects.length);
                for (var i = 0;i <= objects.length -1;i++) {
                    //console.debug(i);
                    window.console.debug(objects[i]);
                    var inline_properties = objects[i].inline_properties;
                    var row = $(sublist.elems[i]).parents('tr');

                    var p = 0;
                    for (p in inline_properties) {
                        var td = row.find('.property-' + p);
                        td.html(inline_properties[p]);
                    }
                }
            }
        });

    });
};

Charcoal.Admin.Widget_Table.prototype.sublist = function ()
{
    //var that = this;

    var selected = $('.select-row:checked');
    var ret = {
        elems: [],
        obj_ids: []
    };
    selected.each(function (i, el) {
        ret.obj_ids.push($(el).parents('tr').data('id'));
        ret.elems.push(el);
    });
    return ret;
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function ()
{
    return {
        obj_type:   this.obj_type,
        properties: this.properties,
        properties_options: this.properties_options,
        filters:    this.filters,
        orders:     this.orders,
        pagination: this.pagination
    };
};

Charcoal.Admin.Widget_Table.prototype.reload = function ()
{
    var that = this;

    var url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };
    $.post(url, data, function (response) {
        //console.debug(that.elem_id);
        if (response.success && response.widget_html) {
            //console.debug(response.widget_html);
            $('#' + that.widget_id).replaceWith(response.widget_html);
            that.widget_id = response.widget_id;
            // Rebind events
            that.bind_events();
        }

    });

};
;Charcoal.Admin.Widget_Wysiwyg = function ()
{
    $('.js-wysiwyg').summernote({
        height: 300
    });
};
