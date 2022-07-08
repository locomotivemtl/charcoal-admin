/* eslint-disable no-multiple-empty-lines */
/* globals Promise,MediaStream,commonL10n,audioPropertyL10n */
/**
 * Interface for saving audio messages
 * Property_Input_Audio JavaScript class
 * charcoal/admin/property/input/audio-recorder
 *
 * ## States
 *
 * 0. Idle
 * 1. Playing
 *     1. Running
 *     2. Paused
 * 2. Recording
 *     1. Running
 *     2. Paused
 *
 * ## Initialization
 *
 * ```
 * get_user_media
 * ├── error
 * │   └── reportError
 * ╪
 * └── success
 *     ├── bind_element_events
 *     ├── bind_stream_events
 *     ├── setup_stream
 *     │   └── visualize
 *     └── toggle_transport_toolbar
 * ```
 *
 * ## Capture
 *
 * ```
 * stop_capture
 * ├── toggle_transport_toolbar
 * ├── render_recorded_audio
 * │   └── draw_waveform
 * └── export_recorded_audio
 *     ├── error
 *     │   └── reportError
 *     ╪
 *     └── success
 *         ├── reportError
 *         ╪
 *         └── dispatch_recorded_audio
 * ```
 *
 * ## Constraints
 *
 * ```json
 * {
 *     mandatory: {
 *         googEchoCancellation: false,
 *         googAutoGainControl:  false,
 *         googNoiseSuppression: false,
 *         googHighpassFilter:   false
 *     },
 *     optional: []
 * }
 * ```
 *
 * ## References
 *
 * @see https://github.com/cwilso/AudioRecorder
 * @see https://github.com/mattdiamond/Recorderjs
 *
 * ## Notes
 *
 * - In Firefox, base64-encoded audio will sometimes report a very long duration.
 *   - {@link https://bugzilla.mozilla.org/show_bug.cgi?id=1441829 Bug #1441829}
 *   - {@link https://bugzilla.mozilla.org/show_bug.cgi?id=1416976 Bug #1416976}
 *
 * @method Property_Input_Audio_Recorder
 * @param Object opts
 */
;(function ($, Admin, window, document, undefined) {
    'use strict';

    var DEBUG_KEY = '[Property_Input_Audio_Recorder]',
        DATA_KEY  = 'charcoal.property.audio.recorder',
        EVENT_KEY = '.' + DATA_KEY,
        Event = {
            CLICK: 'click' + EVENT_KEY
        },
        PropState = {
            IDLE:      0,
            READY:     1,
            LIVE:      2
        },
        MediaMode = {
            IDLE:      3,
            PLAYBACK:  4,
            CAPTURE:   5
        },
        MediaState = {
            IDLE:      6,
            LOCKED:    7,
            BUSY:      8,
            PAUSED:    9
        },
        VisualState = {
            NONE:      10,
            WAVEFORM:  11,
            FREQUENCY: 12
        },
        Selector = {
            // Buttons
            BTN_RECORD:  '.js-recording-record',
            BTN_PLAY:    '.js-recording-playback',
            BTN_STOP:    '.js-recording-stop',
            BTN_RESET:   '.js-recording-reset',

            // Visualizers
            VISUALIZER:   '.js-recording-visualizer',
            ELAPSED:      '.js-recording-time-elapsed',
            DURATION:     '.js-recording-time-duration'
        },
        GRAPH_LINE_CAP     = 'round',
        GRAPH_IDLE_COLOR   = '#EBEDF0', // #DEE2E6
        GRAPH_ACTIVE_COLOR = '#DEE2E6', // #CED4DA
        GRAPH_BAR_SPACING  = 5,
        GRAPH_BAR_WIDTH    = 2,
        audioApiSupported  = null,
        recorderAvailable  = null;

    function PropertyInput(opts) {
        this.EVENT_NAMESPACE  = EVENT_KEY;
        this.PROPERTY_IDLE    = PropState.IDLE;
        this.PROPERTY_READY   = PropState.READY;
        this.PROPERTY_LIVE    = PropState.LIVE;
        this.MODE_IDLE        = MediaMode.IDLE;
        this.MODE_PLAYBACK    = MediaMode.PLAYBACK;
        this.MODE_CAPTURE     = MediaMode.CAPTURE;
        this.MEDIA_IDLE       = MediaState.IDLE;
        this.MEDIA_LOCKED     = MediaState.LOCKED;
        this.MEDIA_BUSY       = MediaState.BUSY;
        this.MEDIA_PAUSED     = MediaState.PAUSED;
        this.VISUAL_NONE      = VisualState.NONE;
        this.VISUAL_WAVEFORM  = VisualState.WAVEFORM;
        this.VISUAL_FREQUENCY = VisualState.FREQUENCY;

        Admin.Property.call(this, opts);

        this.data    = opts.data;
        this.data.id = opts.id;

        this.readyState  = PropState.IDLE;
        this.mediaMode   = MediaMode.IDLE;
        this.mediaState  = MediaState.IDLE;
        this.mediaVisual = VisualState.NONE;

        this.status        = null;
        this.mediaPromise  = null;
        this.mediaStream   = null;
        this.audioRecorder = null;
        this.audioContext  = null;
        this.analyserNode  = null;
        this.canvasElement = null;
        this.canvasContext = null;
        this.isFirefox     = window.navigator.userAgent.toLowerCase().indexOf('firefox') > -1;

        // Play/Record Trackers
        this.recordingStartDate  = null;
        this.recordingDuration   = 0;
        this.playbackCurrentTime = 0;
        this.playbackDuration    = 0;

        // Timeout/Interval/Animation IDs
        this.workerPostMessageTimeoutID = null;
        this.drawVisualizationFrameID   = null;

        if (typeof this.data.worker_timeout === 'undefined') {
            this.data.worker_timeout = 5000;
        }

        if (typeof this.data.buffer_length === 'undefined') {
            this.data.buffer_length = 4096;
        }

        if (typeof this.data.sample_rate === 'undefined') {
            this.data.sample_rate = 2;
        }

        if (typeof this.data.num_channels === 'undefined') {
            this.data.num_channels = 2;
        }

        if (typeof this.data.mime_type === 'undefined') {
            this.data.mime_type = 'audio/wav';
        }

        this.init();
    }

    PropertyInput.EVENT_NAMESPACE = EVENT_KEY;

    PropertyInput.prototype = Object.create(Admin.Property.prototype);
    PropertyInput.prototype.constructor = PropertyInput;
    PropertyInput.prototype.parent = Admin.Property.prototype;

    /**
     * Initialize the input property.
     *
     * @return {void}
     */
    PropertyInput.prototype.init = function () {
        var $el = this.element();

        this.$hidden = $('#' + this.data.hidden_input_id).or('input[type="hidden"]', $el);

        if (this.$hidden.length === 0) {
            console.error(DEBUG_KEY, 'Missing hidden input to store captured audio');
            return;
        }

        var $visualizer   = $(Selector.VISUALIZER, $el),
            $recordButton = $(Selector.BTN_RECORD, $el),
            $playButton   = $(Selector.BTN_PLAY, $el),
            $stopButton   = $(Selector.BTN_STOP, $el),
            $resetButton  = $(Selector.BTN_RESET, $el),
            $elapsed      = $(Selector.ELAPSED, $el),
            $duration     = $(Selector.DURATION, $el);

        if ($visualizer.length === 0) {
            console.error(DEBUG_KEY, 'Missing visualizer element to graph audio');
            return;
        }

        this.canvasElement = $visualizer[0];
        this.canvasContext = this.canvasElement.getContext('2d');

        this.recorder_elements = {
            $visualizer:   $visualizer,
            $recordButton: $recordButton,
            $playButton:   $playButton,
            $stopButton:   $stopButton,
            $resetButton:  $resetButton,
            $buttons:      $([]).add($recordButton).add($playButton).add($stopButton).add($resetButton),
            $elapsed:      $elapsed,
            $duration:     $duration
        };

        this.disable();

        if (isAudioApiSupported()) {
            this.boot();
        } else {
            var msg = audioPropertyL10n.unsupportedAPI;
            reportError(msg, true);

            if (typeof this.data.on_stream_error === 'function') {
                this.data.on_stream_error(new Error(msg));
            }
        }
    };

    /**
     * Initialize the Web Audio API and recording/exporting plugin.
     *
     * 1. Loads the recorder plugin script if missing.
     * 2. Prompts the user for permission to use a media input.
     *
     * @return {void}
     */
    PropertyInput.prototype.boot = function () {
        switch (recorderAvailable) {
            case false:
                var msg = audioPropertyL10n.missingRecorderPlugin;

                reportError(msg, true);

                if (typeof this.data.on_stream_error === 'function') {
                    this.data.on_stream_error(new Error(msg));
                }
                return;

            case true:
                if (Admin.debug()) {
                    console.log(DEBUG_KEY, 'Recorder Plugin Ready');
                }
                this.get_user_media();
                return;

            case null:
                if (this.data.recorder_plugin_url) {
                    /**
                     * @promise #loadRecorderScript
                     * @type    {jqXHR}
                     */
                    var jqxhr = $.getCachedScript(this.data.recorder_plugin_url);

                    jqxhr
                        .then(onAfterLoadRecorderScript.bind(this))
                        .done(onLoadRecorderScript.bind(this))
                        .catch(onErrorRecorderScript.bind(this));

                    // Prevents {@see this.boot()} from stacking.
                    recorderAvailable = jqxhr;
                } else {
                    onErrorRecorderScript.call(this, void 0, 'error', audioPropertyL10n.missinRecorderUrl);
                }
                return;
        }
    };

    /**
     * Prompt the user for permission to use a media input.
     *
     * This method supports both the legacy `navigator.getUserMedia()` method
     * and the newer `navigator.mediaDevices.getUserMedia()` promise-based method.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/API/Navigator/getUserMedia
     * @link https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
     *
     * @param  {Boolean} [retry] - Whether to prompt the user for permission.
     * @return {Promise}
     */
    PropertyInput.prototype.get_user_media = function (retry) {
        if (!this.mediaPromise || retry === true) {
            var mediaPromise, constraints;

            constraints = {
                audio: true,
                video: false
            };

            if (typeof this.data.stream_constraints === 'object') {
                constraints.audio = this.data.stream_constraints;
            }

            /**
             * @promise #promptUserMedia
             * @type    {Promise}
             */
            mediaPromise = window.navigator.mediaDevices.getUserMedia(constraints);
            mediaPromise.then(onStartedMediaStream.bind(this)).catch(onErrorMediaStream.bind(this));

            this.mediaPromise = mediaPromise;
        }

        return this.mediaPromise;
    };

    /**
     * Stop microphone access.
     *
     * @return {void}
     */
    PropertyInput.prototype.stop_user_media = function () {
        if (!this.mediaStream) {
            return;
        }

        var tracks;

        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Stopping Stream…');
        }

        tracks = this.mediaStream.getTracks();
        tracks.forEach(function (track) {
            track.stop();
        });

        // Check for earmark from {@see this.bind_stream_events}
        if (typeof this.mediaStream[this.id()] !== 'boolean') {
            onStreamEnded.call(this);
        }
    };

    /**
     * Retrieve the audio player.
     *
     * @param  {Boolean} [reload] - Whether to reload the audio player.
     * @return {?SimpleAudioElement}
     */
    PropertyInput.prototype.get_player = function (reload) {
        if (!this.audioPlayer || reload === true) {
            try {
                this.audioPlayer = this.create_player();
            } catch (err) {
                reportError(err, true);
                return null;
            }
        }

        return this.audioPlayer;
    };

    /**
     * Create an audio player.
     *
     * @param  {Object} [config] - A configuration object.
     * @return {?SimpleAudioElement}
     */
    PropertyInput.prototype.create_player = function (config) {
        var player, settings, errorHandler, playableHandler;

        errorHandler = (function (event) {
            var audioElement, err;

            audioElement = event.target;

            if (Admin.debug()) {
                console.log(DEBUG_KEY, '[Playback]', 'Audio Failed');
            }

            err = normalizeMediaElementError(audioElement.error);

            this.mediaMode  = MediaMode.IDLE;
            this.mediaState = MediaState.IDLE;
            this.toggle_transport_toolbar();
            this.update_time();
            this.update_duration();

            reportError(err, true);
        }).bind(this);

        playableHandler = (function (event) {
            var audioElement = event.target;

            if (Admin.debug()) {
                console.log(DEBUG_KEY, '[Playback]', 'Audio Ready');
            }

            if (audioElement.readyState !== 0) {
                if (this.mediaMode === MediaMode.IDLE) {
                    this.mediaMode  = MediaMode.PLAYBACK;
                    this.mediaState = MediaState.IDLE;
                    this.toggle_transport_toolbar();
                }

                this.update_time();
                this.update_duration();
            }
        }).bind(this);

        settings = {
            properties: {
                type: this.data.mime_type
            },
            listeners: {
                error:          errorHandler,
                ended:          this.stop_playback.bind(this),
                loadedmetadata: playableHandler,
                timeupdate:     this.update_time.bind(this)
            }
        };

        Object.assign(settings, config);

        player = new window.SimpleAudioElement(settings);

        return player;
    };

    /**
     * Retrieve the audio recorder.
     *
     * @param  {AudioNode} source   - The node whose output you wish to capture.
     * @param  {Object}    [config] - A configuration object.
     * @return {?Recorder}
     */
    PropertyInput.prototype.get_recorder = function (source, config) {
        if (!this.audioRecorder || source) {
            try {
                this.audioRecorder = this.create_recorder(source, config);
            } catch (err) {
                reportError(err, true);
                return null;
            }
        }

        return this.audioRecorder;
    };

    /**
     * Create an audio recorder.
     *
     * @param  {AudioNode} source   - The node whose output you wish to capture.
     * @param  {Object}    [config] - A configuration object.
     * @throws {Error} If the source or options are invalid or if an internal API is obsolete.
     * @return {Recorder}
     */
    PropertyInput.prototype.create_recorder = function (source, config) {
        var recorder, errorHandler, audioProcessHandler;

        recorder = new window.Recorder(source, config);

        if (recorder.worker instanceof window.Worker) {
            errorHandler = (function (event) {
                if (Admin.debug()) {
                    console.log(DEBUG_KEY, 'Recorder Worker Failed', event);
                }

                if (this.readyState === PropState.LIVE) {
                    this.readyState = PropState.READY;
                }

                this.disable();

                if (typeof this.data.on_stream_error === 'function') {
                    this.data.on_stream_error(event);
                }
            }).bind(this);

            recorder.worker.onerror        = errorHandler;
            recorder.worker.onmessageerror = errorHandler;
        }

        if (recorder.node) {
            if (typeof recorder.node.onaudioprocess === 'function') {
                audioProcessHandler = recorder.node.onaudioprocess;
                recorder.node.onaudioprocess = (function (event) {
                    if (!recorder.recording) {
                        return;
                    }

                    this.update_time(this.get_record_time());

                    audioProcessHandler.call(recorder, event);
                }).bind(this);
            } else {
                throw new Error('Missing onaudioprocess handler on Recorder');
            }
        }

        return recorder;
    };

    /**
     * Add event listeners to the transport toolbar buttons.
     *
     * @todo Maybe dissociate playback events from recording events,
     *     in case getUserMedia is rejected.
     *
     * @return {void}
     */
    PropertyInput.prototype.bind_element_events = function () {
        var $el, recordHandler, playHandler, stopHandler, resetHandler;

        $el = this.element();

        recordHandler = (function (event) {
            event.preventDefault();

            this.toggle_capture();
        }).bind(this);
        playHandler   = (function (event) {
            event.preventDefault();

            this.toggle_playback();
        }).bind(this);
        stopHandler   = (function (event) {
            event.preventDefault();

            this.stop();
        }).bind(this);
        resetHandler  = (function (event) {
            event.preventDefault();

            this.stop(false);
            this.clear();
        }).bind(this);

        $el.on(Event.CLICK, Selector.BTN_RECORD, recordHandler);
        $el.on(Event.CLICK, Selector.BTN_PLAY,   playHandler);
        $el.on(Event.CLICK, Selector.BTN_STOP,   stopHandler);
        $el.on(Event.CLICK, Selector.BTN_RESET,  resetHandler);
    };

    /**
     * Add event listeners to the media stream and its tracks.
     *
     * @return {void}
     */
    PropertyInput.prototype.bind_stream_events = function () {
        if (!(this.mediaStream instanceof MediaStream)) {
            throw new Error('Invalid or missing media stream');
        }

        var addTrackHandler, endedTrackHandler, endedTrackCalled;

        endedTrackCalled  = 0;
        endedTrackHandler = (function (/*event*/) {
            endedTrackCalled++;

            if (endedTrackCalled === 1) {
                onStreamEnded.call(this);
            }
        }).bind(this);

        addTrackHandler = (function (event) {
            if (event.track) {
                event.track.addEventListener('ended', endedTrackHandler);
            }
        }).bind(this);

        this.mediaStream.getTracks().forEach(function (track) {
            track.addEventListener('ended', endedTrackHandler);
        });

        this.mediaStream.addEventListener('addtrack', addTrackHandler);

        // Earmark the MediaStream as having event listeners
        this.mediaStream[this.id()] = true;
    };

    /**
     * Prepare the capture/visualization for the stream of media content.
     *
     * @throws {Error} If the stream is invalid or missing.
     * @return {void}
     */
    PropertyInput.prototype.setup_stream = function () {
        if (!(this.mediaStream instanceof MediaStream)) {
            throw new Error('Invalid or missing media stream');
        }

        var audioInput, inputPoint, zeroGain;

        this.audioContext = new window.AudioContext();

        inputPoint = this.audioContext.createGain();
        audioInput = this.audioContext.createMediaStreamSource(this.mediaStream);
        audioInput.connect(inputPoint);

        this.analyserNode = this.audioContext.createAnalyser();
        this.analyserNode.fftSize = 2048;
        inputPoint.connect(this.analyserNode);

        this.get_recorder(inputPoint);

        zeroGain = this.audioContext.createGain();
        zeroGain.gain.value = 0.0;
        inputPoint.connect(zeroGain);
        zeroGain.connect(this.audioContext.destination);

        this.visualize(VisualState.FREQUENCY);
    };



    // Capture/Playback
    // -------------------------------------------------------------------------

    /**
     * Stop the capture/playback immediately.
     *
     * Releases the pause button if depressed and the cursor is moved to the beginning of the track.
     *
     * @param  {Boolean} [serve] - Whether to process the buffer (TRUE) or discard it (FALSE).
     * @return {void}
     */
    PropertyInput.prototype.stop = function (serve) {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, 'Stop');
        }

        switch (this.mediaMode) {
            case MediaMode.PLAYBACK:
                this.stop_playback();
                break;

            case MediaMode.CAPTURE:
                this.stop_capture(serve);
                break;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }
    };

    /**
     * Discard any audio from the player and recorder immediately.
     *
     * @return {void}
     */
    PropertyInput.prototype.clear = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, 'Clear');
        }

        if (this.mediaMode !== MediaMode.IDLE || this.mediaState !== MediaState.IDLE) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode or State');
                console.groupEnd();
            }
            return;
        }

        this.mediaMode   = MediaMode.IDLE;
        this.mediaState  = MediaState.LOCKED;
        this.mediaVisual = VisualState.NONE;
        this.toggle_transport_toolbar();

        this.update_time(null);
        this.update_duration(null);
        this.clear_visualization();
        this.clear_playback();
        this.clear_capture();

        this.$hidden.val(void 0);

        this.mediaState  = MediaState.IDLE;
        this.visualize(VisualState.FREQUENCY);
        this.toggle_transport_toolbar();

        if (Admin.debug()) {
            console.groupEnd();
        }
    };

    /**
     * Determine if an audio recording is available for playback.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {Boolean}
     */
    PropertyInput.prototype.has_recording = function () {
        var player, audioElement;

        player = this.get_player();
        if (player.getElement) {
            audioElement = player.getElement();

            if (typeof audioElement.src === 'string') {
                return (audioElement.src.length > 0);
            }
        }

        throw new Error('Missing audio player. Can not detect recording.');
    };

    /**
     * Determine if the component is capturing audio.
     *
     * @param  {Boolean} [busyOnly] - Whether the "pause" state is included (TRUE) or ignored (FALSE).
     * @return {Boolean}
     */
    PropertyInput.prototype.is_capturing = function (busyOnly) {
        if (this.mediaMode !== MediaMode.CAPTURE) {
            return false;
        }

        return this.is_busy(busyOnly);
    };

    /**
     * Determine if the component is playing audio.
     *
     * @param  {Boolean} [busyOnly] - Whether the "pause" state is included (TRUE) or ignored (FALSE).
     * @return {Boolean}
     */
    PropertyInput.prototype.is_playing = function (busyOnly) {
        if (this.mediaMode !== MediaMode.PLAYBACK) {
            return false;
        }

        return this.is_busy(busyOnly);
    };

    /**
     * Determine if the component is either capturing or playing audio.
     *
     * @param  {Boolean} [busyOnly] - Whether the "pause" state is included (TRUE) or ignored (FALSE).
     * @return {Boolean}
     */
    PropertyInput.prototype.is_busy = function (busyOnly) {
        if (busyOnly === true) {
            return (this.mediaState === MediaState.BUSY);
        } else {
            return (this.mediaState !== MediaState.IDLE);
        }
    };



    // Capture
    // -------------------------------------------------------------------------

    /**
     * Start/Pause the capture of a media stream.
     *
     * @return {void}
     */
    PropertyInput.prototype.toggle_capture = function () {
        // if (Admin.debug()) {
        //     console.group(DEBUG_KEY, '[Capture]', 'Toggle');
        // }

        if (this.mediaMode === MediaMode.PLAYBACK) {
            this.stop_playback();
        }

        if (this.has_recording()) {
            if (Admin.debug()) {
                console.log(DEBUG_KEY, '[Capture]', 'Restart Recording');
            }
            this.clear();
        } else if (Admin.debug()) {
            console.log(DEBUG_KEY, '[Capture]', this.recordingStartDate ? 'Resume Recording' : 'New Recording');
        }

        this.mediaMode = MediaMode.CAPTURE;

        if (this.mediaState === MediaState.BUSY) {
            this.pause_capture();
        } else {
            this.start_capture();
        }

        // if (Admin.debug()) {
        //     console.groupEnd();
        // }
    };

    /**
     * Start/Resume the capture of the media stream.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.start_capture = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Capture]', 'Start');
        }

        if (this.mediaMode !== MediaMode.CAPTURE) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode');
                console.groupEnd();
            }
            return;
        }

        this.mediaState = MediaState.BUSY;
        this.visualize(VisualState.FREQUENCY);
        this.toggle_transport_toolbar();

        var recorder = this.get_recorder();
        if (recorder.record) {
            if (!recorder.recording) {
                this.recordingStartDate = Date.now();
            }

            recorder.record();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio recorder. Can not capture audio.');
    };

    /**
     * Pause the capture temporarily.
     *
     * The cursor does not lose its place.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.pause_capture = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Capture]', 'Pause');
        }

        if (this.mediaMode !== MediaMode.CAPTURE || this.mediaState !== MediaState.BUSY) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode or Idle');
                console.groupEnd();
            }
            return;
        }

        this.mediaState = MediaState.PAUSED;
        this.toggle_transport_toolbar();

        var recorder = this.get_recorder();
        if (recorder.stop) {
            if (recorder.recording && this.recordingStartDate) {
                this.recordingDuration += diffInSeconds(this.recordingStartDate);
            }

            recorder.stop();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio recorder. Can not pause audio capture.');
    };

    /**
     * Stop the capture immediately.
     *
     * Releases the pause button if depressed and the cursor is moved to the beginning of the track.
     *
     * @param  {Boolean} [serve] - Whether to process the buffer (TRUE) or discard it (FALSE).
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.stop_capture = function (serve) {
        serve = (serve !== false);

        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Capture]', 'Stop', serve);
        }

        if (this.mediaMode !== MediaMode.CAPTURE) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode');
                console.groupEnd();
            }
            return;
        }

        this.mediaMode  = MediaMode.IDLE;
        this.mediaState = MediaState.LOCKED;
        this.toggle_transport_toolbar();
        // this.update_time(0);

        var recorder = this.get_recorder();
        if (recorder.stop) {
            if (recorder.recording && this.recordingStartDate) {
                this.recordingDuration += diffInSeconds(this.recordingStartDate);
                this.recordingStartDate = null;

                if (Admin.debug()) {
                    console.log('Recorded Time: ', this.recordingDuration);
                }
            }

            recorder.stop();

            if (serve) {
                var workerTimeoutCallback;

                recorder.getBuffer(this.render_recorded_audio.bind(this));
                recorder.exportWAV(this.export_recorded_audio.bind(this));

                workerTimeoutCallback = (function () {
                    if (Admin.debug()) {
                        console.error(DEBUG_KEY, '[Capture]', 'Web Worker Timeout');
                    }

                    if (this.readyState === PropState.LIVE) {
                        this.readyState = PropState.READY;
                    }

                    this.status = 'Timeout';
                    this.disable();

                    var msg = audioPropertyL10n.captureFailed + ' ' + commonL10n.workerTimedout;
                    reportError(msg, true);

                    if (typeof this.data.on_stream_error === 'function') {
                        this.data.on_stream_error(new Error(commonL10n.workerTimedout));
                    }
                }).bind(this);

                this.workerPostMessageTimeoutID = window.setTimeout(workerTimeoutCallback, this.data.worker_timeout);
            } else {
                this.mediaState = MediaState.IDLE;
                this.visualize(VisualState.FREQUENCY);
                this.toggle_transport_toolbar();
                this.update_time(0);
            }

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio recorder. Can not stop audio capture.');
    };

    /**
     * Discard any captured audio immediately.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.clear_capture = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Capture]', 'Clear');
        }

        var recorder = this.get_recorder();
        if (recorder.clear) {
            this.recordingDuration  = 0;
            this.recordingStartDate = null;

            recorder.clear();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio recorder. Can not discard recorded audio.');
    };

    /**
     * Render the recorded buffer as a waveform visualization.
     *
     * @param  {Array} buffers - The recorded buffer as an array of Float32Array typed arrays
     *     (for each audio channel).
     * @throws {Error} If the buffer is invalid.
     * @return {void}
     */
    PropertyInput.prototype.render_recorded_audio = function (buffers) {
        if (!(Array.isArray(buffers) && buffers.length > 0)) {
            throw new Error('Invalid or empty audio buffer');
        }

        if (Admin.debug()) {
            console.log(DEBUG_KEY, '[Capture]', 'Illustrating Recording…');
        }

        var canvas, context, multiplier, amplitude, dataArray;

        canvas  = this.canvasElement;
        context = this.canvasContext;

        dataArray  = buffers[0];
        multiplier = Math.ceil(dataArray.length / canvas.width);
        amplitude  = (canvas.height / 2);

        context.lineCap   = GRAPH_LINE_CAP;
        context.fillStyle = GRAPH_IDLE_COLOR;

        this.clear_visualization();
        this.mediaVisual = VisualState.WAVEFORM;

        drawWaveformGraph(
            canvas.width,
            canvas.height,
            context,
            multiplier,
            amplitude,
            dataArray
        );
    };

    /**
     * Process the recorded blob as a data URI.
     *
     * @param  {Blob} blob - A Blob object containing the recording in WAV format.
     * @throws {Error} If the blob is invalid.
     * @return {void}
     */
    PropertyInput.prototype.export_recorded_audio = function (blob) {
        if (!((blob instanceof window.Blob) && blob.size > 0)) {
            throw new Error('Invalid or empty audio blob');
        }

        if (Admin.debug()) {
            console.log(DEBUG_KEY, '[Capture]', 'Exporting Recording…');
        }

        if (this.workerPostMessageTimeoutID) {
            window.clearTimeout(this.workerPostMessageTimeoutID);
            this.workerPostMessageTimeoutID = null;
        }

        var reader, successHandler, errorHandler;

        reader = new window.FileReader();

        successHandler = (function (event) {
            if (reader.result) {
                if (Admin.debug()) {
                    console.log(DEBUG_KEY, '[Capture]', 'Export Completed');
                }

                this.dispatch_recorded_audio(reader.result);
            } else {
                errorHandler(event);
            }
        }).bind(this);

        errorHandler = (function (event) {
            if (Admin.debug()) {
                console.log(DEBUG_KEY, '[Capture]', 'Export Failed');
            }

            reportError(event, true);
        }).bind(this);

        reader.addEventListener('loadend', successHandler);
        reader.addEventListener('error', errorHandler);

        reader.readAsDataURL(blob);
    };

    /**
     * Dispatch the recorded data URI to any related form control and audio player.
     *
     * @param  {String} data - A Base64 encoding of the recording.
     * @throws {Error} If the recorded audio is invalid or the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.dispatch_recorded_audio = function (data) {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Capture]', 'Dispatch Recording');
        }

        if (!data) {
            if (Admin.debug()) {
                console.groupEnd();
            }

            throw new Error('Invalid or empty audio data');
        }

        // Write the data to an input for saving
        this.$hidden.val(data);

        // Save the data for playback
        var player = this.get_player();
        if (player.src) {
            player.src(data);
            player.load();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio player. Can not load recorded audio.');
    };



    // Playback
    // -------------------------------------------------------------------------

    /**
     * Start/Pause the playback of a media source.
     *
     * @return {void}
     */
    PropertyInput.prototype.toggle_playback = function () {
        // if (Admin.debug()) {
        //     console.group(DEBUG_KEY, '[Playback]', 'Toggle');
        // }

        if (this.mediaMode === MediaMode.CAPTURE) {
            this.stop_capture();
        }

        this.mediaMode = MediaMode.PLAYBACK;

        if (this.mediaState === MediaState.BUSY) {
            this.pause_playback();
        } else {
            this.start_playback();
        }

        // if (Admin.debug()) {
        //     console.groupEnd();
        // }
    };

    /**
     * Start/Resume the playback of the media stream.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.start_playback = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Playback]', 'Start');
        }

        if (this.mediaMode !== MediaMode.PLAYBACK) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode');
                console.groupEnd();
            }
            return;
        }

        if (!this.has_recording()) {
            if (Admin.debug()) {
                console.log('Bail:', 'Missing Recording');
                console.groupEnd();
            }
            return;
        }

        this.mediaState = MediaState.BUSY;
        this.visualize(VisualState.WAVEFORM);
        this.toggle_transport_toolbar();

        var player = this.get_player();
        if (player.play) {
            var playPromise = player.play();
            if (playPromise instanceof Promise) {
                if (Admin.debug()) {
                    console.log('Playing…');
                }

                var errorHandler = (function (err) {
                    if (Admin.debug()) {
                        console.error(DEBUG_KEY, '[Playback]', 'Play/Resume Failed');
                    }

                    err = normalizeMediaStreamError(err);

                    reportError(err, true);

                    // console.groupEnd();
                }).bind(this);

                playPromise.catch(errorHandler);
            }

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio player. Can not play recorded audio.');
    };

    /**
     * Pause the playback temporarily.
     *
     * The cursor does not lose its place.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.pause_playback = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Playback]', 'Pause');
        }

        if (this.mediaMode !== MediaMode.PLAYBACK || this.mediaState !== MediaState.BUSY) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode or Idle');
                console.groupEnd();
            }
            return;
        }

        this.mediaState = MediaState.PAUSED;
        this.toggle_transport_toolbar();

        var player = this.get_player();
        if (player.pause) {
            player.pause();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio player. Can not pause recorded audio.');
    };

    /**
     * Stop the playback immediately.
     *
     * Releases the pause button if depressed and the cursor is moved to the beginning of the track.
     *
     * @throws {Error} If the audio player is unavailable.
     * @return {void}
     */
    PropertyInput.prototype.stop_playback = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Playback]', 'Stop');
        }

        if (this.mediaMode !== MediaMode.PLAYBACK) {
            if (Admin.debug()) {
                console.log('Bail:', 'Bad Mode');
                console.groupEnd();
            }
            return;
        }

        this.mediaMode  = MediaMode.IDLE;
        this.mediaState = MediaState.IDLE;
        this.toggle_transport_toolbar();

        var player = this.get_player();
        if (player.stop) {
            player.stop();

            if (Admin.debug()) {
                console.groupEnd();
            }

            return;
        }

        if (Admin.debug()) {
            console.groupEnd();
        }

        throw new Error('Missing audio player. Can not stop recorded audio.');
    };

    /**
     * Discard any recorded audio immediately.
     *
     * This method deletes the <audio> element; assigning an empty value to its src
     * will attempt to load the base URL which evidently fails.
     *
     * @return {void}
     */
    PropertyInput.prototype.clear_playback = function () {
        if (Admin.debug()) {
            console.group(DEBUG_KEY, '[Playback]', 'Clear');
        }

        this.audioPlayer = null;

        if (Admin.debug()) {
            console.groupEnd();
        }
    };



    // Visualizer
    // -------------------------------------------------------------------------

    /**
     * Start the visualization for the stream of media content.
     *
     * @todo Change visual based on {@see this.mediaMode}.
     *
     * @param  {Number} visual - The visual state.
     * @throws {TypeError} If the visual state is invalid.
     * @return {void}
     */
    PropertyInput.prototype.visualize = function (visual) {
        if (this.readyState !== PropState.LIVE || this.mediaVisual === visual) {
            return;
        }

        switch (visual) {
            case VisualState.NONE:
                this.clear_visualization();
                this.mediaVisual = visual;
                return;

            case VisualState.WAVEFORM:
                this.create_waveform_visualization();
                this.mediaVisual = visual;
                return;

            case VisualState.FREQUENCY:
                this.create_frequency_visualization();
                this.mediaVisual = visual;
                return;
        }

        throw new TypeError('Invalid visualization mode');
    };

    /**
     * Create a waveform/oscilloscope graph.
     *
     * @return {void}
     */
    PropertyInput.prototype.create_waveform_visualization = function () {
        this.pause_visualization();

        var draw, canvas, context, analyser, multiplier, amplitude, bufferLength, dataArray;

        canvas   = this.canvasElement;
        context  = this.canvasContext;
        analyser = this.analyserNode;

        bufferLength = analyser.fftSize;
        dataArray    = new window.Uint8Array(bufferLength);
        multiplier   = Math.ceil(dataArray.length / canvas.width);
        amplitude    = (canvas.height / 2);

        draw = function () {
            this.drawVisualizationFrameID = window.requestAnimationFrame(draw.bind(this));

            analyser.getByteFrequencyData(dataArray);

            var fillColor = this.is_playing(true) ? GRAPH_ACTIVE_COLOR : GRAPH_IDLE_COLOR;

            context.lineCap   = GRAPH_LINE_CAP;
            context.fillStyle = fillColor;

            drawWaveformGraph(
                canvas.width,
                canvas.height,
                context,
                multiplier,
                amplitude,
                dataArray
            );
        };

        draw.call(this);
    };

    /**
     * Create a frequency bar graph.
     *
     * @return {void}
     */
    PropertyInput.prototype.create_frequency_visualization = function () {
        this.pause_visualization();

        var draw, canvas, context, analyser, multiplier, numBars, bufferLength, dataArray;

        canvas   = this.canvasElement;
        context  = this.canvasContext;
        analyser = this.analyserNode;

        numBars      = Math.round(canvas.width / GRAPH_BAR_SPACING);
        bufferLength = analyser.frequencyBinCount;
        multiplier   = (bufferLength / numBars);
        dataArray    = new window.Uint8Array(bufferLength);

        draw = function () {
            this.drawVisualizationFrameID = window.requestAnimationFrame(draw.bind(this));

            analyser.getByteFrequencyData(dataArray);

            var fillColor = this.is_capturing(true) ? GRAPH_ACTIVE_COLOR : GRAPH_IDLE_COLOR;

            context.lineCap   = GRAPH_LINE_CAP;
            context.fillStyle = fillColor;

            drawFrequencyGraph(
                canvas.width,
                canvas.height,
                context,
                multiplier,
                numBars,
                dataArray
            );
        };

        draw.call(this);
    };

    /**
     * Clear the visualizer.
     *
     * @return {void}
     */
    PropertyInput.prototype.clear_visualization = function () {
        this.pause_visualization();

        var canvas  = this.canvasElement,
            context = this.canvasContext;

        context.clearRect(0, 0, canvas.width, canvas.height);
    };

    /**
     * Cancel the visualizer's animation frame request.
     *
     * @return {void}
     */
    PropertyInput.prototype.pause_visualization = function () {
        if (this.drawVisualizationFrameID) {
            window.cancelAnimationFrame(this.drawVisualizationFrameID);
            this.drawVisualizationFrameID = null;
        }
    };



    // Timer
    // -------------------------------------------------------------------------

    /**
     * Retrieve the component's recording time.
     *
     * @return {Number}
     */
    PropertyInput.prototype.get_record_time = function () {
        if (this.recordingStartDate) {
            return this.recordingDuration + diffInSeconds(this.recordingStartDate);
        }

        return this.recordingDuration;
    };

    /**
     * Retrieve the component's current time in seconds.
     *
     * @return {?Number}
     */
    PropertyInput.prototype.get_current_time = function () {
        switch (this.mediaMode) {
            case MediaMode.IDLE:
                return 0;

            case MediaMode.PLAYBACK:
                var audio = this.get_player().getElement();
                return audio.currentTime;

            case MediaMode.CAPTURE:
                return this.get_record_time();
        }

        return null;
    };

    /**
     * Retrieve the component's current duration in seconds.
     *
     * @return {?Number}
     */
    PropertyInput.prototype.get_duration = function () {
        switch (this.mediaMode) {
            case MediaMode.IDLE:
            case MediaMode.PLAYBACK:
                var audio = this.get_player().getElement();
                return this.fix_duration(Number(audio.duration));

            case MediaMode.CAPTURE:
                return this.get_record_time();
        }

        return null;
    };

    /**
     * Fix playback duration if inconsistent with recorded duration.
     *
     * If there's more than 1 second difference, return the smaller duration.
     * This bypasses a bug in Firefox where it can't correctly calculate the length
     * of Base64-encoded WAV audio (returning values like 24347.886893 seconds).
     *
     * @param  {Number} seconds - The duration in seconds.
     * @return {?Number}
     */
    PropertyInput.prototype.fix_duration = function (seconds) {
        if (this.recordingDuration > 0) {
            var diff = Math.abs(seconds - this.recordingDuration);
            if (diff > 1000) {
                return Math.min(seconds, this.recordingDuration);
            }
        }

        return seconds;
    };

    /**
     * Update the component's current time reference.
     *
     * @param  {Number} [seconds] - The current time in seconds.
     * @return {void}
     */
    PropertyInput.prototype.update_time = function (seconds) {
        var updateTimeCallback;

        // TODO: Move to RAF
        updateTimeCallback = (function () {
            var time;

            if (typeof seconds !== 'number') {
                seconds = this.get_current_time();
            }

            this.playbackCurrentTime = seconds;

            time = formatTime(seconds);

            this.recorder_elements.$elapsed.text(time);
        }).bind(this);

        window.setTimeout(updateTimeCallback);
    };

    /**
     * Update the component's duration reference.
     *
     * @param  {Number} [seconds] - The duration in seconds.
     * @return {void}
     */
    PropertyInput.prototype.update_duration = function (seconds) {
        var updateDurationCallback;

        // TODO: Move to RAF
        updateDurationCallback = (function () {
            var time;

            if (typeof seconds !== 'number') {
                seconds = this.get_duration();
            }

            this.playbackDuration = seconds;

            time = formatTime(seconds);

            this.recorder_elements.$duration.text(time);
        }).bind(this);

        window.setTimeout(updateDurationCallback, this.isFirefox ? 100 : 10);
    };



    // Toolbar
    // -------------------------------------------------------------------------

    /**
     * Disable the transport toolbar buttons.
     *
     * @return {void}
     */
    PropertyInput.prototype.disable_transport_toolbar = function () {
        this.recorder_elements.$buttons.disable();
    };

    /**
     * Toggle the transport toolbar buttons.
     *
     * @param  {Number} [state] - The target media state.
     * @param  {Number} [mode]  - The target media mode.
     * @throws {TypeError} If the media state is invalid.
     * @return {void}
     */
    PropertyInput.prototype.toggle_transport_toolbar = function (state, mode) {
        // if (Admin.debug()) {
        //     console.group(DEBUG_KEY, '[Toolbar]', 'Toggle');
        // }

        var $el, elems, hasRec;

        if (typeof state === 'undefined') {
            state = this.mediaState;
        }

        if (typeof mode === 'undefined') {
            mode = this.mediaMode;
        }

        // if (Admin.debug()) {
        //     console.log('Mode:', mode);
        //     console.log('State:', state);
        // }

        $el    = this.element();
        elems  = this.recorder_elements;
        hasRec = this.has_recording();

        elems.$buttons.disable();
        $el.removeClass('is-recording is-playing is-paused');

        switch (state) {
            case MediaState.LOCKED:

                // if (Admin.debug()) {
                //     console.groupEnd();
                // }
                return;

            case MediaState.IDLE:
                elems.$recordButton.enable();

                if (hasRec) {
                    elems.$playButton.enable();
                    elems.$resetButton.enable();
                }

                // if (Admin.debug()) {
                //     console.groupEnd();
                // }
                return;

            case MediaState.BUSY:
            case MediaState.PAUSED:
                if (state === MediaState.PAUSED) {
                    $el.addClass('is-paused');
                }

                if (mode === MediaMode.PLAYBACK) {
                    $el.addClass('is-playing');

                    elems.$playButton.enable();
                    elems.$stopButton.enable();
                    elems.$resetButton.enable();
                } else if (mode === MediaMode.CAPTURE) {
                    $el.addClass('is-recording');

                    elems.$recordButton.enable();
                    elems.$stopButton.enable();
                    elems.$resetButton.enable();
                }

                // if (Admin.debug()) {
                //     console.groupEnd();
                // }
                return;
        }

        // if (Admin.debug()) {
        //     console.groupEnd();
        // }

        throw new TypeError('Invalid toolbar state');
    };



    // Property
    // -------------------------------------------------------------------------

    /**
     * Enable the property input and any related components.
     *
     * @return {void}
     */
    PropertyInput.prototype.enable = function () {
        if (isAudioApiSupported()) {
            this.boot();
        }
    };

    /**
     * Disable the property input and any related components.
     *
     * @return {void}
     */
    PropertyInput.prototype.disable = function () {
        this.disable_transport_toolbar();
        this.clear_visualization();
        this.stop_user_media();
    };

    /**
     * Destroy the property input and any related components.
     *
     * @return {void}
     */
    PropertyInput.prototype.destroy = function () {
        this.disable();

        this.element().off(this.EVENT_NAMESPACE);
    };



    // Event Listeners
    // -------------------------------------------------------------------------

    /**
     * Fired once the audio recorder library has been loaded (but not necessarily executed).
     *
     * @resolves #promise:loadRecorderScript
     *
     * @this   PropertyInput
     * @param  {String} script - The contents of the script.
     * @param  {String} status - The status of the request.
     * @param  {jqXHR}  jqxhr  - The jQuery XHR object.
     * @return {Deferred}
     */
    function onAfterLoadRecorderScript(script, status, jqxhr) {
        /* jshint validthis:true */
        /**
         * @promise #loadedRecorderScript
         * @type    {Deferred}
         */
        var deferred = $.Deferred();

        if (isRecorderLoaded()) {
            return deferred.resolveWith(this, arguments);
        } else {
            return deferred.rejectWith(this, [ jqxhr, 'error', 'Script Unavailable' ]);
        }
    }

    /**
     * Fired once the audio recorder library has been loaded successfully (but not necessarily executed).
     *
     * @resolves #promise:loadedRecorderScript
     *
     * @this   PropertyInput
     * @param  {String} [script] - The contents of the script.
     * @param  {String} [status] - The status of the request.
     * @param  {jqXHR}  [jqxhr]  - The jQuery XHR object.
     * @return {void}
     */
    function onLoadRecorderScript(/*script, status, jqxhr*/) {
        /* jshint validthis:true */
        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Recorder Plugin Loaded');
        }

        recorderAvailable = true;

        this.get_user_media();
    }

    /**
     * Fired if the request for the audio recorder library fails.
     *
     * @rejects #promise:loadedRecorderScript
     *
     * @this   PropertyInput
     * @param  {jqXHR}  jqXHR       - The jQuery XHR object.
     * @param  {String} status      - The type of error that occurred.
     * @param  {String} errorThrown - The textual portion of the HTTP status.
     * @return {void}
     */
    function onErrorRecorderScript(jqxhr, status, errorThrown) {
        /* jshint validthis:true */
        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Recorder Plugin Failed');
        }

        recorderAvailable = false;

        this.status = 'Error';

        var msg = audioPropertyL10n.missingRecorderPlugin;
        if (errorThrown) {
            msg += ' ' + commonL10n.reason + ' ' + errorThrown;
        }

        reportError(msg, true);

        if (typeof this.data.on_stream_error === 'function') {
            this.data.on_stream_error(new Error(msg));
        }
    }

    /**
     * Fired when the user grants permission for a media input.
     *
     * @resolves #promise:promptUserMedia
     *
     * @this   PropertyInput
     * @param  {MediaStream} stream - The object representing a stream of media content.
     * @return {void}
     */
    function onStartedMediaStream(stream) {
        /* jshint validthis:true */
        this.mediaStream = stream;

        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Streaming Started');
        }

        if (this.readyState === PropState.IDLE) {
            this.readyState = PropState.READY;

            this.bind_element_events();
        }

        if (this.readyState === PropState.READY) {
            this.readyState = PropState.LIVE;
            this.status = 'OK';

            this.bind_stream_events();
            this.setup_stream();
        }

        this.toggle_transport_toolbar();

        if (typeof this.data.on_stream_ready === 'function') {
            this.data.on_stream_ready(stream);
        }
    }

    /**
     * Fired if the user denies permission, or matching media is not available.
     *
     * @rejects #promise:promptUserMedia
     *
     * @this   PropertyInput
     * @param  {Error} err - The error object.
     * @return {void}
     */
    function onErrorMediaStream(err) {
        /* jshint validthis:true */
        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Streaming Failed');
        }

        if (this.readyState === PropState.LIVE) {
            this.readyState = PropState.READY;
        }

        err = normalizeMediaStreamError(err);
        this.status = err.name || 'Error';
        this.disable();

        reportError(err, true);

        if (typeof this.data.on_stream_error === 'function') {
            this.data.on_stream_error(err);
        }
    }

    /**
     * Finalize the end of the stream.
     *
     * @see PropertyInput.prototype.bind_stream_events
     * @see PropertyInput.prototype.stop_user_media
     *
     * @this   PropertyInput
     * @return {void}
     */
    function onStreamEnded() {
        /* jshint validthis:true */
        if (Admin.debug()) {
            console.log(DEBUG_KEY, 'Streaming Ended');
        }

        if (this.readyState === PropState.LIVE) {
            this.readyState = PropState.READY;
        }

        this.status = 'Ended';
        this.disable();

        if (typeof this.data.on_stream_ended === 'function') {
            this.data.on_stream_ended(event);
        }

        this.mediaStream[this.id()] = false;
        this.mediaStream  = null;
        this.mediaPromise = null;
    }



    // -------------------------------------------------------------------------



    /**
     * Determine if audio recorder/exporter is loaded and ready.
     *
     * @return {Boolean}
     */
    function isRecorderLoaded() {
        return (typeof window.Recorder === 'function');
    }

    /**
     * Determine if audio recorder/exporter is available.
     *
     * @return {Boolean}
     */
    function isRecorderAvailable() {
        if (typeof recorderAvailable !== 'boolean') {
            return false;
        }

        return recorderAvailable;
    }

    /**
     * Determine if audio recording is supported by the browser.
     *
     * This method will attempt to shim any vendor-features.
     *
     * @return {Boolean}
     */
    function isAudioApiSupported() {
        if (typeof audioApiSupported === 'boolean') {
            return audioApiSupported;
        }

        audioApiSupported = true;

        // Older browsers might not implement mediaDevices at all, so we set an empty object first
        if (window.navigator.mediaDevices === undefined) {
            window.navigator.mediaDevices = {};
        }

        if (!window.navigator.mediaDevices.getUserMedia) {
            // Some browsers partially implement mediaDevices. We can't just assign an object
            // with getUserMedia as it would overwrite existing properties.
            // Here, we will just add the getUserMedia property if it's missing.
            window.navigator.mediaDevices.getUserMedia = function (constraints) {
                // First get ahold of the legacy getUserMedia, if present
                var getUserMedia = window.navigator.webkitGetUserMedia || window.navigator.mozGetUserMedia;

                // Some browsers just don't implement it - return a rejected promise with an error
                // to keep a consistent interface
                if (!getUserMedia) {
                    return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
                }

                // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
                return new Promise(function (resolve, reject) {
                    getUserMedia.call(navigator, constraints, resolve, reject);
                });
            };
        }

        if (!window.cancelAnimationFrame) {
            if (window.webkitCancelAnimationFrame) {
                window.cancelAnimationFrame = window.webkitCancelAnimationFrame;
            } else if (window.mozCancelAnimationFrame) {
                window.cancelAnimationFrame = window.mozCancelAnimationFrame;
            } else {
                audioApiSupported = false;
                console.error(DEBUG_KEY, 'The window.mozCancelAnimationFrame method is missing.');
            }
        }

        if (!window.requestAnimationFrame) {
            if (window.webkitRequestAnimationFrame) {
                window.requestAnimationFrame = window.webkitRequestAnimationFrame;
            } else if (window.mozRequestAnimationFrame) {
                window.requestAnimationFrame = window.mozRequestAnimationFrame;
            } else {
                audioApiSupported = false;
                console.error(DEBUG_KEY, 'The window.requestAnimationFrame method is missing.');
            }
        }

        if (!window.AudioContext) {
            if (window.webkitAudioContext) {
                window.AudioContext = window.webkitAudioContext;
            } else {
                audioApiSupported = false;
                console.error(DEBUG_KEY, 'The window.AudioContext interface is missing.');
            }
        }

        if (!window.URL) {
            if (window.webkitURL) {
                window.URL = window.webkitURL;
            } else {
                audioApiSupported = false;
                console.warning(DEBUG_KEY, 'The window.URL interface is missing.');
            }
        }

        if (!window.FileReader) {
            audioApiSupported = false;
            console.warning(DEBUG_KEY, 'The window.FileReader interface is missing.');
        }

        if (!window.Uint8Array) {
            audioApiSupported = false;
            console.warning(DEBUG_KEY, 'The window.Uint8Array interface is missing.');
        }

        return audioApiSupported;
    }

    /**
     * Send an error message to the console and optionally
     * display a notice in an alert dialog.
     *
     * @param  {Object} err   - The related event or error.
     * @param  {String} [msg] - The message to display.
     * @return {void}
     */
    function reportError(err, msg) {
        if (msg === true) {
            if (err instanceof Error) {
                msg = err.message;
            } else if (typeof err === 'string') {
                msg = err;
            } else {
                msg = audioPropertyL10n.captureFailed + ' ' + commonL10n.errorOccurred;
            }
        }

        if (err) {
            console.error(DEBUG_KEY, err);
        }

        if (msg) {
            window.alert(msg);
        }
    }

    /**
     * Normalize any non-confirming MediaStreamError names.
     *
     * @param  {Error} err - The media stream error.
     * @return {Error}
     */
    function normalizeMediaElementError(err) {
        /*
        switch (e.target.error.code) {
            case e.target.error.MEDIA_ERR_ABORTED:
                err.message = 'You aborted the video playback.';
                break;
            case e.target.error.MEDIA_ERR_NETWORK:
                err.message = 'A network error caused the audio download to fail.';
                break;
            case e.target.error.MEDIA_ERR_DECODE:
                err.message = 'The audio playback was aborted due to a corruption problem or because the video used features your browser did not support.';
                break;
            case e.target.error.MEDIA_ERR_SRC_NOT_SUPPORTED:
                err.message = 'The video audio not be loaded, either because the server or network failed or because the format is not supported.';
                break;
            default:
                err.message = 'An unknown error occurred.';
                break;
        }
        */

        return err;
    }

    /**
     * Normalize any non-confirming MediaStreamError names.
     *
     * @param  {Error} err - The media stream error.
     * @return {Error}
     */
    function normalizeMediaStreamError(err) {
        if (err.name) {
            switch (err.name) {
                case 'DevicesNotFoundError':
                    err.name = 'NotFoundError';
                    break;

                case 'TrackStartError':
                    err.name = 'NotReadableError';
                    break;

                case 'ConstraintNotSatisfiedError':
                    err.name = 'OverconstrainedError';
                    break;

                case 'PermissionDeniedError':
                    err.name = 'NotAllowedError';
                    break;
            }
        }

        return err;
    }

    /**
     * Draw frequency bar graph on a canvas.
     *
     * @param  {Number}           width      - The width of the canvas.
     * @param  {Number}           height     - The height of the canvas.
     * @param  {RenderingContext} context    - The context of the canvas.
     * @param  {Number}           multiplier - The frequency range multiplier.
     * @param  {Number}           numBars    - The number of bars to display.
     * @param  {TypedArray}       dataArray  - The audio data to draw.
     * @return {void}
     */
    function drawFrequencyGraph(width, height, context, multiplier, numBars, dataArray) {
        var magnitude, offset, i, j;

        context.clearRect(0, 0, width, height);

        // Draw rectangle for each frequency bin.
        for (i = 1; i <= numBars; ++i) {
            magnitude = 0;
            offset    = Math.floor(i * multiplier);

            // gotta sum/average the block, or we miss narrow-bandwidth spikes
            for (j = 0; j < multiplier; j++) {
                magnitude += dataArray[offset + j];
            }

            magnitude = (magnitude / multiplier);

            context.fillRect((i * GRAPH_BAR_SPACING), height, GRAPH_BAR_WIDTH, -magnitude);
        }
    }

    /**
     * Draw waveform/oscilloscope on a canvas.
     *
     * @param  {Number}           width      - The width of the canvas.
     * @param  {Number}           height     - The height of the canvas.
     * @param  {RenderingContext} context    - The context of the canvas.
     * @param  {Number}           multiplier - The frequency range multiplier.
     * @param  {Number}           amplitude  - The maximum range.
     * @param  {TypedArray}       dataArray  - The audio data to draw.
     * @return {void}
     */
    function drawWaveformGraph(width, height, context, multiplier, amplitude, dataArray) {
        var datum, max, min, i, j;

        context.clearRect(0, 0, width, height);

        for (i = 0; i < width; i++) {
            min = 1.0;
            max = -1.0;

            for (j = 0; j < multiplier; j++) {
                datum = dataArray[(i * multiplier) + j];
                if (datum < min) {
                    min = datum;
                }

                if (datum > max) {
                    max = datum;
                }
            }

            context.fillRect(i, ((1 + min) * amplitude), 1, Math.max(1, ((max - min) * amplitude)));
        }
    }

    /**
     * Format the time value from a given number of seconds.
     *
     * @param  {Number} sec - The time in seconds.
     * @return {String}
     */
    function formatTime(sec) {
        if (typeof sec !== 'number') {
            return '--:--';
        }

        if (sec === 0) {
            return '00:00';
        }

        var time, min;

        min = (Math.floor(sec / 60) | 0);
        sec = ((Math.round(sec) % 60) | 0);

        time = minSecStr(min) + ':' + minSecStr(sec);

        return time;
    }

    /**
     * Format the given value a fixed number of digits padded with leading zeroes.
     *
     * @param  {Number} n - A number.
     * @return {String}
     */
    function minSecStr(n) {
        return (n < 10 ? '0' : '') + n;
    }

    /**
     * Retrieve the difference in seconds from now.
     *
     * @param  {Date} time - A date/time value.
     * @return {Number}
     */
    function diffInSeconds(time) {
        return ((Date.now() - time) * 0.001);
    }

    PropertyInput.is_recorder_available = isRecorderAvailable;
    PropertyInput.is_audio_supported    = isAudioApiSupported;

    Admin.Property_Input_Audio_Recorder = PropertyInput;

}(jQuery, Charcoal.Admin, window, document));
