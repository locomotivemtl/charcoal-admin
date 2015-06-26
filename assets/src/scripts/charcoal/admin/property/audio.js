/**
* charcoal/admin/property/audio
*
* Require:
* - jQuery
*
* @see https://github.com/cwilso/AudioRecorder
* @see https://github.com/mattdiamond/Recorderjs
*
*/

Charcoal.Admin.Property_Audio = function (opts)
{
    // Common Property properties
    this.property_type = 'charcoal/admin/property/audio';

    // Property_Audio properties
    this.audioContext    = new window.AudioContext() || new window.webkitAudioContext();
    this.audioInput      = null;
    this.realAudioInput  = null;
    this.inputPoint      = null;
    this.audioRecorder   = null;
    this.rafID           = null;
    this.analyserContext = null;
    this.canvasWidth     = 0;
    this.canvasHeight    = 0;
    this.recIndex        = 0;
    window.analyserNode  = null;

    this.init(opts);
};

Charcoal.Admin.Property_Audio.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Audio.prototype.constructor = Charcoal.Admin.Property_Audio;
Charcoal.Admin.Property_Audio.prototype.parent = Charcoal.Admin.Property.prototype;
Charcoal.Admin.Property_Audio.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Property_Audio.prototype.init = function (opts)
{
    // Set properties
    var data = $.extend(true, {}, this.default_data(), opts);
    this.set_data(data);

    this.bind_events();
    this.initAudio();
};

Charcoal.Admin.Property_Audio.prototype.default_data = function ()
{
    return {
        obj_type: '',
        input_id: null
    };
};

Charcoal.Admin.Property_Audio.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type;
    this.input_id = data.input_id;
    return this;
};

Charcoal.Admin.Property_Audio.prototype.bind_events = function ()
{
    this.bind_obj_events();
};

Charcoal.Admin.Property_Audio.prototype.bind_obj_events = function ()
{
    var that = this;

    $('.btn-record').on('click',function (e) {
        that.toggleRecording(e.target);
    });
};

Charcoal.Admin.Property_Audio.prototype.gotBuffers = function (buffers) {
    var canvas = window.document.getElementById('wavedisplay'),
        that   = this;

    that.drawBuffer(canvas.width, canvas.height, canvas.getContext('2d'), buffers[0]);

    // the ONLY time gotBuffers is called is right after a new recording is completed -
    // so here's where we should set up the download.
    that.audioRecorder.exportWAV(function (blob) {
        that.doneEncoding(blob);
    });
};

Charcoal.Admin.Property_Audio.prototype.saveToInput = function (data) {
    if (data){
        var input = window.document.getElementById(this.input_id);
        if (input){
            input.value = data;
        }
    }
};

Charcoal.Admin.Property_Audio.prototype.drawBuffer = function (width, height, context, data) {
    var step = Math.ceil(data.length / width);
    var amp = height / 2;
    context.fillStyle = 'silver';
    context.clearRect(0,0,width,height);
    for (var i = 0; i < width; i++){
        var min = 1.0;
        var max = -1.0;
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

Charcoal.Admin.Property_Audio.prototype.doneEncoding = function (blob) {

    var reader = new window.FileReader(),
        data   = null,
        that   = this;

    reader.readAsDataURL(blob);

    reader.onloadend = function () {
        data = reader.result;

        that.recIndex++;

        that.saveToInput(data);
    };

};

Charcoal.Admin.Property_Audio.prototype.toggleRecording = function (button) {
    var that = this;

    if (button.classList.contains('-is-recording')) {
        // stop recording
        that.audioRecorder.stop();
        button.classList.remove('-is-recording');
        that.audioRecorder.getBuffers(function (buffers) {
            that.gotBuffers(buffers);
        });
    } else {
        // start recording
        if (!that.audioRecorder) {
            return;
        }
        button.classList.add('-is-recording');
        that.audioRecorder.clear();
        that.audioRecorder.record();
    }
};

Charcoal.Admin.Property_Audio.prototype.cancelAnalyserUpdates = function () {
    window.cancelAnimationFrame(this.rafID);
    this.rafID = null;
};

//Charcoal.Admin.Property_Audio.prototype.updateAnalysers = function ( time ){
Charcoal.Admin.Property_Audio.prototype.updateAnalysers = function () {

    var that = this;

    if (!that.analyserContext) {
        var canvas = window.document.getElementById('analyser');
        that.canvasWidth = canvas.width;
        that.canvasHeight = canvas.height;
        that.analyserContext = canvas.getContext('2d');
    }

    // Drawing Analyzer
    {
        var SPACING = 3;
        var BAR_WIDTH = 1;
        var numBars = Math.round(that.canvasWidth / SPACING);
        var freqByteData = new window.Uint8Array(window.analyserNode.frequencyBinCount);

        window.analyserNode.getByteFrequencyData(freqByteData);

        that.analyserContext.clearRect(0, 0, that.canvasWidth, that.canvasHeight);
        that.analyserContext.fillStyle = '#F6D565';
        that.analyserContext.lineCap = 'round';
        var multiplier = window.analyserNode.frequencyBinCount / numBars;

        // Draw rectangle for each frequency bin.
        for (var i = 0; i < numBars; ++i) {
            var magnitude = 0;
            var offset = Math.floor(i * multiplier);
            // gotta sum/average the block, or we miss narrow-bandwidth spikes
            for (var j = 0; j < multiplier; j++){
                magnitude += freqByteData[offset + j];
            }
            magnitude = magnitude / multiplier;
            //var magnitude2 = freqByteData[i * multiplier];
            that.analyserContext.fillStyle = 'hsl( ' + Math.round((i * 360) / numBars) + ', 100%, 50%)';
            that.analyserContext.fillRect(i * SPACING, that.canvasHeight, BAR_WIDTH, -magnitude);
        }
    }

    that.rafID = window.requestAnimationFrame(function () {
        that.updateAnalysers();
    });
};

Charcoal.Admin.Property_Audio.prototype.gotStream = function (stream) {
    this.inputPoint = this.audioContext.createGain();

    // Create an AudioNode from the stream.
    this.realAudioInput = this.audioContext.createMediaStreamSource(stream);
    this.audioInput = this.realAudioInput;
    this.audioInput.connect(this.inputPoint);

    window.analyserNode = this.audioContext.createAnalyser();
    window.analyserNode.fftSize = 2048;
    this.inputPoint.connect(window.analyserNode);

    this.audioRecorder = new window.Recorder(this.inputPoint);

    var zeroGain = this.audioContext.createGain();
    zeroGain.gain.value = 0.0;
    this.inputPoint.connect(zeroGain);
    zeroGain.connect(this.audioContext.destination);
    this.updateAnalysers();
};

Charcoal.Admin.Property_Audio.prototype.initAudio = function () {
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
    }, function (stream) {
        that.gotStream(stream);
    }, function (e) {
        window.alert('Error getting audio. Try plugging in a microphone');
        window.console.log(e);
    });
};

(function (window) {

    var WORKER_PATH = '../../assets/admin/scripts/vendors/recorderWorker.js';

    var Recorder = function (source, cfg) {
        var config = cfg || {};
        var bufferLen = config.bufferLen || 4096;
        this.context = source.context;
        if (!this.context.createScriptProcessor){
            this.node = this.context.createJavaScriptNode(bufferLen, 2, 2);
        } else {
            this.node = this.context.createScriptProcessor(bufferLen, 2, 2);
        }

        var worker = new window.Worker(config.workerPath || WORKER_PATH);
        worker.postMessage({
            command: 'init',
            config: {
                sampleRate: this.context.sampleRate
            }
        });
        var recording = false,
        currCallback;

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

        this.getBuffers = function (cb) {
            currCallback = cb || config.callback;
            worker.postMessage({ command: 'getBuffers' });
        };

        this.exportWAV = function (cb, type) {
            currCallback = cb || config.callback;
            type = type || config.type || 'audio/wav';
            if (!currCallback){
                throw new Error('Callback not set');
            }
            worker.postMessage({
                command: 'exportWAV',
                type: type
            });
        };

        this.exportMonoWAV = function (cb, type) {
            currCallback = cb || config.callback;
            type = type || config.type || 'audio/wav';
            if (!currCallback){
                throw new Error('Callback not set');
            }
            worker.postMessage({
                command: 'exportMonoWAV',
                type: type
            });
        };

        worker.onmessage = function (e) {
            var blob = e.data;
            currCallback(blob);
        };

        source.connect(this.node);
        // if the script node is not connected to an output the "onaudioprocess" event is not triggered in chrome.
        this.node.connect(this.context.destination);
    };

    window.Recorder = Recorder;

})(window);
