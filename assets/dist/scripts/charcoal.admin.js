var Charcoal = Charcoal || {};
Charcoal.Admin = function ()
{
    // This is a singleton
    if (arguments.callee.singleton_instance) {
        return arguments.callee.singleton_instance;
    }
    arguments.callee.singleton_instance = this;

    this.url = '';
    this.admin_path = '';

    this.admin_url = function ()
    {
        return this.url + this.admin_path + '/';
    };

};
;/**
* charcoal/admin/property
*/

Charcoal.Admin.Property = function (opts)
{
    window.alert('Property ' + opts);
};

Charcoal.Admin.Property.prototype.admin = new Charcoal.Admin();
;/**
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
    /*
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
*/

    this.audioContext = new window.AudioContext() || new window.webkitAudioContext();
    this.audioInput = null;
    this.realAudioInput = null;
    this.inputPoint = null;
    this.audioRecorder = null;
    this.rafID = null;
    this.analyserContext = null;
    this.canvasWidth = 0;
    this.canvasHeight = 0;
    this.recIndex = 0;
    window.analyserNode = null;

    this.init(opts);
};

Charcoal.Admin.Property_Audio.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Audio.prototype.constructor = Charcoal.Admin.Property_Audio;
Charcoal.Admin.Property_Audio.prototype.parent = Charcoal.Admin.Property.prototype;
Charcoal.Admin.Property_Audio.prototype.admin = new Charcoal.Admin();

//Charcoal.Admin.Property_Audio.prototype.init = function (opts)
Charcoal.Admin.Property_Audio.prototype.init = function ()
{
    // Set properties
    //var data = $.extend(true, {}, this.default_data(), opts);
    //this.set_data(data);

    this.bind_events();
    this.initAudio();
};

Charcoal.Admin.Property_Audio.prototype.default_data = function ()
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

Charcoal.Admin.Property_Audio.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type || '';
    this.widget_id = data.widget_id || null;
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

Charcoal.Admin.Property_Audio.prototype.saveAudio = function () {
    this.audioRecorder.exportWAV(this.doneEncoding);
    // could get mono instead by saying
    // audioRecorder.exportMonoWAV( doneEncoding );
};

Charcoal.Admin.Property_Audio.prototype.gotBuffers = function (buffers) {
    var canvas = window.document.getElementById('wavedisplay');

    this.drawBuffer(canvas.width, canvas.height, canvas.getContext('2d'), buffers[0]);

    // the ONLY time gotBuffers is called is right after a new recording is completed -
    // so here's where we should set up the download.
    this.audioRecorder.exportWAV(this.doneEncoding);
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
    window.Recorder.setupDownload(blob, 'myRecording' + ((this.recIndex < 10)?'0':'') + this.recIndex + '.wav');
    this.recIndex++;
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
        window.console.log(!that.audioRecorder);
        if (!that.audioRecorder) {
            return;
        }
        window.console.log(that.audioRecorder);
        button.classList.add('-is-recording');
        that.audioRecorder.clear();
        that.audioRecorder.record();
    }
};

Charcoal.Admin.Property_Audio.prototype.convertToMono = function (input) {
    var splitter = this.audioContext.createChannelSplitter(2);
    var merger = this.audioContext.createChannelMerger(2);

    input.connect(splitter);
    splitter.connect(merger, 0, 0);
    splitter.connect(merger, 0, 1);
    return merger;
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

    // analyzer draw code here
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

//Charcoal.Admin.Property_Audio.prototype.toggleMono = function ( input ){
Charcoal.Admin.Property_Audio.prototype.toggleMono = function () {
    if (this.audioInput !== this.realAudioInput) {
        this.audioInput.disconnect();
        this.realAudioInput.disconnect();
        this.audioInput = this.realAudioInput;
    } else {
        this.realAudioInput.disconnect();
        this.audioInput = this.convertToMono(this.realAudioInput);
    }

    this.audioInput.connect(this.inputPoint);
};

Charcoal.Admin.Property_Audio.prototype.gotStream = function (stream) {
    this.inputPoint = this.audioContext.createGain();

    // Create an AudioNode from the stream.
    this.realAudioInput = this.audioContext.createMediaStreamSource(stream);
    this.audioInput = this.realAudioInput;
    this.audioInput.connect(this.inputPoint);

    //    audioInput = convertToMono( input );

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

    Recorder.setupDownload = function (blob, filename) {
        var url = (window.URL || window.webkitURL).createObjectURL(blob);
        var link = window.document.getElementById('save');
        link.href = url;
        link.download = filename || 'output.wav';
    };

    window.Recorder = Recorder;

})(window);
;/**
* charcoal/admin/template
*/

Charcoal.Admin.Template = function (opts)
{
    window.alert('Template ' + opts);
};

Charcoal.Admin.Template.prototype.admin = new Charcoal.Admin();
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
Charcoal.Admin.Template_Login.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Template_Login.prototype.init = function (opts)
{
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Login.prototype.bind_events = function ()
{
    var that = this;

    $('.login-submit').on('click', function (e) {
        e.preventDefault();

        var form = $(this).parents('form');
        var url = that.admin.admin_url() + 'action/json/login';
        var data = form.serialize();
        $.post(url, data, function (response) {
            window.console.debug(response);
            if (response.success) {
                window.location.href = response.next_url;
            } else {
                window.alert('Error');
            }
        }).fail(function () {
            window.alert('Error');
        });
    });
};
;Charcoal.Admin.Template_MenuHeader = function ()
{
    $('[data-toggle="class"]').click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var dataClass = $this.data('class');
        var dataTarget = $this.data('target');

        $(dataTarget).toggleClass(dataClass);
    });
};
;/**
* charcoal/admin/widget
*/

Charcoal.Admin.Widget = function (opts)
{
    window.alert('Widget ' + opts);
};

Charcoal.Admin.Widget.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Widget.prototype.reload = function (cb)
{
    var that = this;

    var url = that.admin.admin_url() + 'action/json/widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };
    $.post(url, data, cb);
};
;/**
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

//Charcoal.Admin.Widget_Form = new Charcoal.Admin.Widget();        // Here's where the inheritance occurs

Charcoal.Admin.Widget_Form = function (opts)
{
    // Common Widget properties
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.obj_type = null;
    this.obj_id = null;

    this.init(opts);

};

Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;
Charcoal.Admin.Widget_Form.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Widget_Form.prototype.init = function (opts)
{
    // Set properties
    var data = $.extend(true, {}, this.default_data(), opts);
    this.set_data(data);

    this.bind_events();
};

Charcoal.Admin.Widget_Form.prototype.default_data = function ()
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

Charcoal.Admin.Widget_Form.prototype.set_data = function (data)
{
    window.console.debug(data);
    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    $('.form-submit').on('click', function (e) {
        e.preventDefault();

        var url;
        if (that.obj_id) {
            url = that.admin.admin_url() + 'action/json/object/update';
        } else {
            url = that.admin.admin_url() + 'action/json/object/save';
        }
        var f = $(this).parents('form');
        var data = {
            obj_type: that.obj_type,
            obj_id: that.obj_id,
            obj_data: f.serialize()
        };
        $.post(url, data, function (response) {
            window.console.debug(response);
            if (response.success) {
                window.alert('Save successful!');
            } else {
                window.alert('Error. Could not save object.');
            }
        }).fail(function () {
            window.alert('Error attempting to save form.');
        });
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
Charcoal.Admin.Widget_Table.prototype.admin = new Charcoal.Admin();

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

        var url = that.admin.admin_url() + 'action/json/widget/load';
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
        var url = that.admin.admin_url() + 'action/json/widget/table/inline';
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
            var url = that.admin.admin_url() + 'action/json/object/delete';
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
        var url = that.admin.admin_url() + 'action/json/widget/load';
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
        var url = that.admin.admin_url() + 'action/json/widget/table/inlinemulti';
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

    var url = that.admin.admin_url() + 'action/json/widget/load';
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
