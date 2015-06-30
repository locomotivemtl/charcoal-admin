/**
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
    this.audio_context     = null;
    this.audio_recorder    = null;
    this.animation_frame   = null;
    this.analyser_context  = null;
    this.canvas_width      = 0;
    this.canvas_height     = 0;
    this.recording_index   = 0;
    this.current_recording = null;
    this.audio_player      = null;
    this.$playback_button  = $('.btn-play');

    this.init(opts);
};

Charcoal.Admin.Property_Audio.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Audio.prototype.constructor = Charcoal.Admin.Property_Audio;
Charcoal.Admin.Property_Audio.prototype.parent = Charcoal.Admin.Property.prototype;
Charcoal.Admin.Property_Audio.prototype.admin = new Charcoal.Admin();

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

    $('.btn-record').on('click',function (e) {
        that.toggle_recording(e.target);
    });

    that.$playback_button.on('click',function () {

        // Test for existing recording first
        if (that.recording_index !== 0 && that.audio_player !== null){
            that.toggle_playback();
        }
    });

};

/**
 * Setup audio recording and analyser displays once audio stream is captured
 * @method got_stream
 * @param MediaStream stream
 */
Charcoal.Admin.Property_Audio.prototype.got_stream = function (stream) {

    var that = this;

    this.audio_context = new window.AudioContext();
    this.audio_player  = new window.Audio_Player({
        on_ended: function () {
            that.toggle_playback();
        }
    });

    var input_point = this.audio_context.createGain(),
        audio_node = this.audio_context.createMediaStreamSource(stream),
        zero_gain  = null;

    audio_node.connect(input_point);

    window.analyserNode = this.audio_context.createAnalyser();

    window.analyserNode.fftSize = 2048;

    input_point.connect(window.analyserNode);

    this.audio_recorder = new window.Recorder(input_point);

    zero_gain = this.audio_context.createGain();

    zero_gain.gain.value = 0.0;

    input_point.connect(zero_gain);

    zero_gain.connect(this.audio_context.destination);

    this.update_analysers();
};

/**
 * Toggle recording of audio and manage button state
 * @method toggle_recording
 * @param Node button
 */
Charcoal.Admin.Property_Audio.prototype.toggle_recording = function (button) {

    var that = this;

    // Stop recording
    if (button.classList.contains('-is-recording')) {
        that.audio_recorder.stop();
        button.classList.remove('-is-recording');
        that.audio_recorder.get_buffers(function (buffers) {
            that.got_buffers(buffers);
        });
    // Start recording
    } else {
        if (!that.audio_recorder) {
            return;
        }
        button.classList.add('-is-recording');
        that.audio_recorder.clear();
        that.audio_recorder.record();
    }
};

/**
 * Toggle playback of recorded audio
 * @method toggle_playback
 * @param Node button
 */
Charcoal.Admin.Property_Audio.prototype.toggle_playback = function () {

    // Stop playback
    if (this.audio_player.is_playing()) {

        this.audio_player.pause();
        this.$playback_button.removeClass('-is-playing');
        this.$playback_button.text(this.$playback_button.attr('data-label-play'));

    // Start playback
    } else {

        if (!this.audio_player) {
            return;
        }

        this.audio_player.play();
        this.$playback_button.addClass('-is-playing');
        this.$playback_button.text(this.$playback_button.attr('data-label-pause'));

    }

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

        this.element    = new window.Audio();

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
