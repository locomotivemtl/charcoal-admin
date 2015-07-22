/* ========================================================================
 * bootstrap-switch - v3.3.2
 * http://www.bootstrap-switch.org
 * ========================================================================
 * Copyright 2012-2013 Mattia Larentis
 *
 * ========================================================================
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================================
 */

(function(){var t=[].slice;!function(e,i){"use strict";var n;return n=function(){function t(t,i){null==i&&(i={}),this.$element=e(t),this.options=e.extend({},e.fn.bootstrapSwitch.defaults,{state:this.$element.is(":checked"),size:this.$element.data("size"),animate:this.$element.data("animate"),disabled:this.$element.is(":disabled"),readonly:this.$element.is("[readonly]"),indeterminate:this.$element.data("indeterminate"),inverse:this.$element.data("inverse"),radioAllOff:this.$element.data("radio-all-off"),onColor:this.$element.data("on-color"),offColor:this.$element.data("off-color"),onText:this.$element.data("on-text"),offText:this.$element.data("off-text"),labelText:this.$element.data("label-text"),handleWidth:this.$element.data("handle-width"),labelWidth:this.$element.data("label-width"),baseClass:this.$element.data("base-class"),wrapperClass:this.$element.data("wrapper-class")},i),this.$wrapper=e("<div>",{"class":function(t){return function(){var e;return e=[""+t.options.baseClass].concat(t._getClasses(t.options.wrapperClass)),e.push(t.options.state?""+t.options.baseClass+"-on":""+t.options.baseClass+"-off"),null!=t.options.size&&e.push(""+t.options.baseClass+"-"+t.options.size),t.options.disabled&&e.push(""+t.options.baseClass+"-disabled"),t.options.readonly&&e.push(""+t.options.baseClass+"-readonly"),t.options.indeterminate&&e.push(""+t.options.baseClass+"-indeterminate"),t.options.inverse&&e.push(""+t.options.baseClass+"-inverse"),t.$element.attr("id")&&e.push(""+t.options.baseClass+"-id-"+t.$element.attr("id")),e.join(" ")}}(this)()}),this.$container=e("<div>",{"class":""+this.options.baseClass+"-container"}),this.$on=e("<span>",{html:this.options.onText,"class":""+this.options.baseClass+"-handle-on "+this.options.baseClass+"-"+this.options.onColor}),this.$off=e("<span>",{html:this.options.offText,"class":""+this.options.baseClass+"-handle-off "+this.options.baseClass+"-"+this.options.offColor}),this.$label=e("<span>",{html:this.options.labelText,"class":""+this.options.baseClass+"-label"}),this.$element.on("init.bootstrapSwitch",function(e){return function(){return e.options.onInit.apply(t,arguments)}}(this)),this.$element.on("switchChange.bootstrapSwitch",function(e){return function(){return e.options.onSwitchChange.apply(t,arguments)}}(this)),this.$container=this.$element.wrap(this.$container).parent(),this.$wrapper=this.$container.wrap(this.$wrapper).parent(),this.$element.before(this.options.inverse?this.$off:this.$on).before(this.$label).before(this.options.inverse?this.$on:this.$off),this.options.indeterminate&&this.$element.prop("indeterminate",!0),this._init(),this._elementHandlers(),this._handleHandlers(),this._labelHandlers(),this._formHandler(),this._externalLabelHandler(),this.$element.trigger("init.bootstrapSwitch")}return t.prototype._constructor=t,t.prototype.state=function(t,e){return"undefined"==typeof t?this.options.state:this.options.disabled||this.options.readonly?this.$element:this.options.state&&!this.options.radioAllOff&&this.$element.is(":radio")?this.$element:(this.options.indeterminate&&this.indeterminate(!1),t=!!t,this.$element.prop("checked",t).trigger("change.bootstrapSwitch",e),this.$element)},t.prototype.toggleState=function(t){return this.options.disabled||this.options.readonly?this.$element:this.options.indeterminate?(this.indeterminate(!1),this.state(!0)):this.$element.prop("checked",!this.options.state).trigger("change.bootstrapSwitch",t)},t.prototype.size=function(t){return"undefined"==typeof t?this.options.size:(null!=this.options.size&&this.$wrapper.removeClass(""+this.options.baseClass+"-"+this.options.size),t&&this.$wrapper.addClass(""+this.options.baseClass+"-"+t),this._width(),this._containerPosition(),this.options.size=t,this.$element)},t.prototype.animate=function(t){return"undefined"==typeof t?this.options.animate:(t=!!t,t===this.options.animate?this.$element:this.toggleAnimate())},t.prototype.toggleAnimate=function(){return this.options.animate=!this.options.animate,this.$wrapper.toggleClass(""+this.options.baseClass+"-animate"),this.$element},t.prototype.disabled=function(t){return"undefined"==typeof t?this.options.disabled:(t=!!t,t===this.options.disabled?this.$element:this.toggleDisabled())},t.prototype.toggleDisabled=function(){return this.options.disabled=!this.options.disabled,this.$element.prop("disabled",this.options.disabled),this.$wrapper.toggleClass(""+this.options.baseClass+"-disabled"),this.$element},t.prototype.readonly=function(t){return"undefined"==typeof t?this.options.readonly:(t=!!t,t===this.options.readonly?this.$element:this.toggleReadonly())},t.prototype.toggleReadonly=function(){return this.options.readonly=!this.options.readonly,this.$element.prop("readonly",this.options.readonly),this.$wrapper.toggleClass(""+this.options.baseClass+"-readonly"),this.$element},t.prototype.indeterminate=function(t){return"undefined"==typeof t?this.options.indeterminate:(t=!!t,t===this.options.indeterminate?this.$element:this.toggleIndeterminate())},t.prototype.toggleIndeterminate=function(){return this.options.indeterminate=!this.options.indeterminate,this.$element.prop("indeterminate",this.options.indeterminate),this.$wrapper.toggleClass(""+this.options.baseClass+"-indeterminate"),this._containerPosition(),this.$element},t.prototype.inverse=function(t){return"undefined"==typeof t?this.options.inverse:(t=!!t,t===this.options.inverse?this.$element:this.toggleInverse())},t.prototype.toggleInverse=function(){var t,e;return this.$wrapper.toggleClass(""+this.options.baseClass+"-inverse"),e=this.$on.clone(!0),t=this.$off.clone(!0),this.$on.replaceWith(t),this.$off.replaceWith(e),this.$on=t,this.$off=e,this.options.inverse=!this.options.inverse,this.$element},t.prototype.onColor=function(t){var e;return e=this.options.onColor,"undefined"==typeof t?e:(null!=e&&this.$on.removeClass(""+this.options.baseClass+"-"+e),this.$on.addClass(""+this.options.baseClass+"-"+t),this.options.onColor=t,this.$element)},t.prototype.offColor=function(t){var e;return e=this.options.offColor,"undefined"==typeof t?e:(null!=e&&this.$off.removeClass(""+this.options.baseClass+"-"+e),this.$off.addClass(""+this.options.baseClass+"-"+t),this.options.offColor=t,this.$element)},t.prototype.onText=function(t){return"undefined"==typeof t?this.options.onText:(this.$on.html(t),this._width(),this._containerPosition(),this.options.onText=t,this.$element)},t.prototype.offText=function(t){return"undefined"==typeof t?this.options.offText:(this.$off.html(t),this._width(),this._containerPosition(),this.options.offText=t,this.$element)},t.prototype.labelText=function(t){return"undefined"==typeof t?this.options.labelText:(this.$label.html(t),this._width(),this.options.labelText=t,this.$element)},t.prototype.handleWidth=function(t){return"undefined"==typeof t?this.options.handleWidth:(this.options.handleWidth=t,this._width(),this._containerPosition(),this.$element)},t.prototype.labelWidth=function(t){return"undefined"==typeof t?this.options.labelWidth:(this.options.labelWidth=t,this._width(),this._containerPosition(),this.$element)},t.prototype.baseClass=function(){return this.options.baseClass},t.prototype.wrapperClass=function(t){return"undefined"==typeof t?this.options.wrapperClass:(t||(t=e.fn.bootstrapSwitch.defaults.wrapperClass),this.$wrapper.removeClass(this._getClasses(this.options.wrapperClass).join(" ")),this.$wrapper.addClass(this._getClasses(t).join(" ")),this.options.wrapperClass=t,this.$element)},t.prototype.radioAllOff=function(t){return"undefined"==typeof t?this.options.radioAllOff:(t=!!t,t===this.options.radioAllOff?this.$element:(this.options.radioAllOff=t,this.$element))},t.prototype.onInit=function(t){return"undefined"==typeof t?this.options.onInit:(t||(t=e.fn.bootstrapSwitch.defaults.onInit),this.options.onInit=t,this.$element)},t.prototype.onSwitchChange=function(t){return"undefined"==typeof t?this.options.onSwitchChange:(t||(t=e.fn.bootstrapSwitch.defaults.onSwitchChange),this.options.onSwitchChange=t,this.$element)},t.prototype.destroy=function(){var t;return t=this.$element.closest("form"),t.length&&t.off("reset.bootstrapSwitch").removeData("bootstrap-switch"),this.$container.children().not(this.$element).remove(),this.$element.unwrap().unwrap().off(".bootstrapSwitch").removeData("bootstrap-switch"),this.$element},t.prototype._width=function(){var t,e;return t=this.$on.add(this.$off),t.add(this.$label).css("width",""),e="auto"===this.options.handleWidth?Math.max(this.$on.width(),this.$off.width()):this.options.handleWidth,t.width(e),this.$label.width(function(t){return function(i,n){return"auto"!==t.options.labelWidth?t.options.labelWidth:e>n?e:n}}(this)),this._handleWidth=this.$on.outerWidth(),this._labelWidth=this.$label.outerWidth(),this.$container.width(2*this._handleWidth+this._labelWidth),this.$wrapper.width(this._handleWidth+this._labelWidth)},t.prototype._containerPosition=function(t,e){return null==t&&(t=this.options.state),this.$container.css("margin-left",function(e){return function(){var i;return i=[0,"-"+e._handleWidth+"px"],e.options.indeterminate?"-"+e._handleWidth/2+"px":t?e.options.inverse?i[1]:i[0]:e.options.inverse?i[0]:i[1]}}(this)),e?setTimeout(function(){return e()},50):void 0},t.prototype._init=function(){var t,e;return t=function(t){return function(){return t._width(),t._containerPosition(null,function(){return t.options.animate?t.$wrapper.addClass(""+t.options.baseClass+"-animate"):void 0})}}(this),this.$wrapper.is(":visible")?t():e=i.setInterval(function(n){return function(){return n.$wrapper.is(":visible")?(t(),i.clearInterval(e)):void 0}}(this),50)},t.prototype._elementHandlers=function(){return this.$element.on({"change.bootstrapSwitch":function(t){return function(i,n){var o;return i.preventDefault(),i.stopImmediatePropagation(),o=t.$element.is(":checked"),t._containerPosition(o),o!==t.options.state?(t.options.state=o,t.$wrapper.toggleClass(""+t.options.baseClass+"-off").toggleClass(""+t.options.baseClass+"-on"),n?void 0:(t.$element.is(":radio")&&e("[name='"+t.$element.attr("name")+"']").not(t.$element).prop("checked",!1).trigger("change.bootstrapSwitch",!0),t.$element.trigger("switchChange.bootstrapSwitch",[o]))):void 0}}(this),"focus.bootstrapSwitch":function(t){return function(e){return e.preventDefault(),t.$wrapper.addClass(""+t.options.baseClass+"-focused")}}(this),"blur.bootstrapSwitch":function(t){return function(e){return e.preventDefault(),t.$wrapper.removeClass(""+t.options.baseClass+"-focused")}}(this),"keydown.bootstrapSwitch":function(t){return function(e){if(e.which&&!t.options.disabled&&!t.options.readonly)switch(e.which){case 37:return e.preventDefault(),e.stopImmediatePropagation(),t.state(!1);case 39:return e.preventDefault(),e.stopImmediatePropagation(),t.state(!0)}}}(this)})},t.prototype._handleHandlers=function(){return this.$on.on("click.bootstrapSwitch",function(t){return function(e){return e.preventDefault(),e.stopPropagation(),t.state(!1),t.$element.trigger("focus.bootstrapSwitch")}}(this)),this.$off.on("click.bootstrapSwitch",function(t){return function(e){return e.preventDefault(),e.stopPropagation(),t.state(!0),t.$element.trigger("focus.bootstrapSwitch")}}(this))},t.prototype._labelHandlers=function(){return this.$label.on({"mousedown.bootstrapSwitch touchstart.bootstrapSwitch":function(t){return function(e){return t._dragStart||t.options.disabled||t.options.readonly?void 0:(e.preventDefault(),e.stopPropagation(),t._dragStart=(e.pageX||e.originalEvent.touches[0].pageX)-parseInt(t.$container.css("margin-left"),10),t.options.animate&&t.$wrapper.removeClass(""+t.options.baseClass+"-animate"),t.$element.trigger("focus.bootstrapSwitch"))}}(this),"mousemove.bootstrapSwitch touchmove.bootstrapSwitch":function(t){return function(e){var i;if(null!=t._dragStart&&(e.preventDefault(),i=(e.pageX||e.originalEvent.touches[0].pageX)-t._dragStart,!(i<-t._handleWidth||i>0)))return t._dragEnd=i,t.$container.css("margin-left",""+t._dragEnd+"px")}}(this),"mouseup.bootstrapSwitch touchend.bootstrapSwitch":function(t){return function(e){var i;if(t._dragStart)return e.preventDefault(),t.options.animate&&t.$wrapper.addClass(""+t.options.baseClass+"-animate"),t._dragEnd?(i=t._dragEnd>-(t._handleWidth/2),t._dragEnd=!1,t.state(t.options.inverse?!i:i)):t.state(!t.options.state),t._dragStart=!1}}(this),"mouseleave.bootstrapSwitch":function(t){return function(){return t.$label.trigger("mouseup.bootstrapSwitch")}}(this)})},t.prototype._externalLabelHandler=function(){var t;return t=this.$element.closest("label"),t.on("click",function(e){return function(i){return i.preventDefault(),i.stopImmediatePropagation(),i.target===t[0]?e.toggleState():void 0}}(this))},t.prototype._formHandler=function(){var t;return t=this.$element.closest("form"),t.data("bootstrap-switch")?void 0:t.on("reset.bootstrapSwitch",function(){return i.setTimeout(function(){return t.find("input").filter(function(){return e(this).data("bootstrap-switch")}).each(function(){return e(this).bootstrapSwitch("state",this.checked)})},1)}).data("bootstrap-switch",!0)},t.prototype._getClasses=function(t){var i,n,o,s;if(!e.isArray(t))return[""+this.options.baseClass+"-"+t];for(n=[],o=0,s=t.length;s>o;o++)i=t[o],n.push(""+this.options.baseClass+"-"+i);return n},t}(),e.fn.bootstrapSwitch=function(){var i,o,s;return o=arguments[0],i=2<=arguments.length?t.call(arguments,1):[],s=this,this.each(function(){var t,a;return t=e(this),a=t.data("bootstrap-switch"),a||t.data("bootstrap-switch",a=new n(this,o)),"string"==typeof o?s=a[o].apply(a,i):void 0}),s},e.fn.bootstrapSwitch.Constructor=n,e.fn.bootstrapSwitch.defaults={state:!0,size:null,animate:!0,disabled:!1,readonly:!1,indeterminate:!1,inverse:!1,radioAllOff:!1,onColor:"primary",offColor:"default",onText:"ON",offText:"OFF",labelText:"&nbsp;",handleWidth:"auto",labelWidth:"auto",baseClass:"bootstrap-switch",wrapperClass:"wrapper",onInit:function(){},onSwitchChange:function(){}}}(window.jQuery,window)}).call(this);;var Charcoal = Charcoal || {};
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
    $(document).on('click', '.js-accordion-header', function(event) {
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
    var data = $.extend(true, {}, opts);
    this.set_data(data);

    this.bind_events();
};

Charcoal.Admin.Widget_Form.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type || null;
    this.obj_id = data.obj_id || null;
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
        var $form = $(this).parents('form');

        var form_data = new FormData($form[0]);
        form_data.append('obj_type', that.obj_type);
        form_data.append('obj_id', that.obj_id);

        $.ajax({
            url: url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: form_data,
            success: function (response) {
                window.console.debug(response);
                if (response.success) {
                    window.alert('Save successful!');
                } else {
                    window.alert('Error. Could not save object.');
                }
            },
            error: function () {
                window.alert('Error attempting to save form.');
            }
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
