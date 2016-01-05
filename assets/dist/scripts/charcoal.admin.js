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
        manager,
        feedback;

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
    * Provides an access to our instanciated Feedback object
    * You can set the data already in as a parameter when necessary.
    * @return {Object} Feedback instance
    */
    Admin.feedback = function (data)
    {
        if (typeof feedback === 'undefined') {
            feedback = new Charcoal.Admin.Feedback();
        }
        feedback.add_data(data);

        return feedback;
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

            // Camel case when splitted by '-'
            // Joined back with '_'
            var substr_array = element.split('-');
            if (substr_array.length > 1) {
                substr_array.forEach(function (e, i) {
                    substr_array[ i ] = e.charAt(0).toUpperCase() + e.slice(1);
                });
                element = substr_array.join('_');
            }

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
        console.error('Was not able to store ' + ident + ' in ' + component_type + ' sub-array');
    }
};

/**
* @todo Document
*/
Charcoal.Admin.ComponentManager.prototype.render = function ()
{

    for (var component_type in this.components) {

        for (var i = 0, len = this.components[component_type].length; i < len; i++) {

            var component_data = this.components[component_type][i];

            try {
                var component = new Charcoal.Admin[component_data.ident](component_data);
                this.components[component_type][i] = component;

                // Automatic supra class call
                switch (component_type) {
                    case 'widgets' :
                        // Automatic call on superclass
                        Charcoal.Admin.Widget.call(component, component_data);
                        component.init();
                    break;
                }

            } catch (error) {
                console.error('Was not able to instanciate ' + component_data.ident);
                console.error(error);
            }
        }

    }
};

/**
* This is called by the widget.form on form submit
* Called save because it's calling the save method on the properties' input
* @see admin/widget/form.js submit_form()
* @return boolean Success (in case of validation)
*/
Charcoal.Admin.ComponentManager.prototype.prepare_submit = function ()
{
    // Get inputs
    var inputs = (typeof this.components.property_inputs !== 'undefined') ? this.components.property_inputs : [];

    if (!inputs.length) {
        // No inputs? Move on
        return true;
    }

    var length = inputs.length;
    var input;

    // Loop for validation
    var k = 0;
    for (; k < length; k++) {
        input = inputs[ k ];
        if (typeof input.validate === 'function') {
            input.validate();
        }
    }

    // We should add a check if the validation passed right here, before saving

    // Loop for save
    var i = 0;
    for (; i < length; i++) {
        input = inputs[ i ];
        if (typeof input.save === 'function') {
            input.save();
        }
    }

    return true;
};
;/**
* charcoal/admin/feedback
* Class that deals with all the feedbacks throughout the admin
* Feedbacks uses the LEVEL concept which could be:
* - `success`
* - `warning`
* - `error`
*
* It uses BootstrapDialog to display all of this.
*
*/

/**
* @return this
*/
Charcoal.Admin.Feedback = function ()
{
    this.msgs = [];
    this.actions = [];

    this.context_definitions = {
        success: {
            title: 'Succès!',
            type: BootstrapDialog.TYPE_SUCCESS
        },
        warning: {
            title: 'Attention!',
            type: BootstrapDialog.TYPE_WARNING
        },
        error: {
            title: 'Une erreur s\'est produite!',
            type: BootstrapDialog.TYPE_DANGER
        }
    };
    return this;
};

/**
* Expects and array of object that looks just like this:
* [
*   { 'level' : 'success', 'msg' : 'Good job!' },
*   { 'level' : 'success', 'msg' : 'Good job!' }
* ]
*
* You can add other parameters as well.
*
* You can set a context, in order to display in a SEPARATE popup
* The default context would be GLOBAL.
* Example of context:
* - `save`
* - `update`
* - `edit`
* - `refresh`
* - `display`
* etc.
*
*
* This will class all success object by level in order to display a FULL LIST
* once the call method is...called
* @param {object} data
* @param {string} context // OR OBJECT? { name : 'global', title : '' }
* @return this
*/
Charcoal.Admin.Feedback.prototype.add_data = function (data/*, context*/)
{
    if (typeof data !== 'object') {
        // Bad values.
        return this;
    }

    // if (typeof context === 'object' &&
    //(typeof context.name === 'undefined' || typeof context.title === 'undefined')) {
    //     return this;
    // }

    // if (!context) {
    //     // Default context
    //     context = { name : 'global' };
    // }

    // if (typeof this.msgs[ context ] === 'undefined') {
    //     this.msgs[ context ] = [];
    // }

    // Add to all msgs
    this.msgs = this.msgs.concat(data);

    // Chainable
    return this;
};

/**
* A context is basicly a DIFFERENT POPUP
* That way, you can separate feedback even if there on the same level
* @return this
*/
Charcoal.Admin.Feedback.prototype.add_context = function (context) {
    if (!context) {
        return this;
    }

    if (typeof context.name === 'undefined' || typeof context.title === 'undefined') {
        return this;
    }

    this.context_definitions[ context.name ] = context;
    // for (var k in context) {
    //     if (typeof context[ k ].title === 'undefined') {
    //         // WRONG
    //         return this;
    //         break;
    //     }
    // }

    return this;
};

/**
* Actions in the dialog box
*/
Charcoal.Admin.Feedback.prototype.add_action = function (opts)
{
    this.actions.push(opts);
};

/**
* Outputs the results of all feedback accumulated on the page load
* @return this
*/
Charcoal.Admin.Feedback.prototype.call = function ()
{
    if (!this.msgs) {
        return this;
    }

    var i = 0;
    var total = this.msgs.length;

    var ret = {};

    for (; i < total; i++) {
        if (typeof this.msgs[ i ].level === 'undefined') {
            continue;
        }

        if (typeof ret[ this.msgs[i].level ] === 'undefined') {
            ret[ this.msgs[i].level ] = [];
        }
        ret[ this.msgs[i].level ].push(this.msgs[i].msg);
    }

    for (var level in ret) {
        if (typeof this.context_definitions[ level ] === 'undefined') {
            continue;
        }

        var buttons = [];

        if (this.actions.length) {
            var k = 0;
            var count = this.actions.length;
            for (; k < count; k++) {
                var action = this.actions[ k ];
                buttons.push({
                    label: action.label,
                    action: action.callback
                });
            }
        }

        BootstrapDialog.show({
            title: this.context_definitions[ level ].title,
            message: ret[ level ].join('<br/>'),
            type: this.context_definitions[ level ].type,
            buttons: buttons
        });

    }

    // Reset
    this.reset();

    return this;
};

/**
* Resets the feedback object
* When you call the feedback, no need to keep it in memory
* @return this (chainable)
*/
Charcoal.Admin.Feedback.prototype.reset = function ()
{
    this.msgs = [];
};
;/**
* charcoal/admin/property
* Should mimic the PHP equivalent AbstractProperty
* This will prevent multiple directions in property implementation
* by giving multiple usefull methods such as ident, val, etc.
*/

Charcoal.Admin.Property = function (opts)
{
    this._ident = undefined;
    this._val = undefined;
    this._type = undefined;
    this._input_type = undefined;

    if (typeof opts.ident === 'string') {
        this.set_ident(opts.ident);
    }

    if (typeof opts.val !== 'undefined') {
        this.set_val(opts.val);
    }

    if (typeof opts.type !== 'undefined') {
        this.set_type(opts.type);
    }

    if (typeof opts.input_type !== 'undefined') {
        this.set_input_type(opts.input_type);
    }

    this.data = opts;

    return this;
};

/**
* Setters
* The following are all defined setters we wanna use for all properties
*/
Charcoal.Admin.Property.prototype.set_ident = function (ident)
{
    this._ident = ident;
};
Charcoal.Admin.Property.prototype.set_val = function (val)
{
    this._val = val;
};
Charcoal.Admin.Property.prototype.set_type = function (type)
{
    this._type = type;
};
Charcoal.Admin.Property.prototype.set_input_type = function (input_type)
{
    this._input_type = input_type;
};

/**
* Getters
* The following are defined getters
*/
Charcoal.Admin.Property.prototype.ident         = function () {
    return this._ident;
};
Charcoal.Admin.Property.prototype.val           = function () {
    return this._val;
};
Charcoal.Admin.Property.prototype.type          = function () {
    return this._type;
};
Charcoal.Admin.Property.prototype.input_type    = function () {
    return this._input_type;
};
/**
* Return the DOMElement element
* @return {jQuery Object} $( '#' + this.data.id );
* If not set, creates it
*/
Charcoal.Admin.Property.prototype.element = function ()
{
    if (!this._element) {
        if (!this.data.id) {
            // Error...
            return false;
        }
        this._element = $('#' + this.data.id);
    }
    return this._element;
};

/**
* Default validate action
* Validate should return the validation feedback with a
* success and / or message
* IdeaS:
* Use a validation object that has all necessary methods for
* strings (max_length, min_length, etc)
*
* @return Object validation feedback
*/
Charcoal.Admin.Property.prototype.validate = function ()
{
    // Validate the current
};

/**
* Default save action
* @return this (chainable)
*/
Charcoal.Admin.Property.prototype.save = function ()
{
    // Default action = nothing
    return this;
};
;/**
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
    //

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
;/**
* Datetime picker that manages datetime properties
* charcoal/admin/property/input/datetimepicker
*
* Require:
* - eonasdan-bootstrap-datetimepicker
*
* @param  {Object}  opts  Options for input property
*/

Charcoal.Admin.Property_Input_Datetimepicker = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/datetimepicker';

    // Property_Input_Datetimepicker properties
    this.input_id = null;
    this.datetimepicker_selector = null;
    this.datetimepicker_options = null;

    this.set_properties(opts).create_datetimepicker();
};
Charcoal.Admin.Property_Input_Datetimepicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Datetimepicker.prototype.constructor = Charcoal.Admin.Property_Input_Datetimepicker;
Charcoal.Admin.Property_Input_Datetimepicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Datetimepicker.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.datetimepicker_selector = opts.data.datetimepicker_selector || this.datetimepicker_selector;
    this.datetimepicker_options = opts.data.datetimepicker_options || this.datetimepicker_options;

    var default_opts = {

    };

    this.datetimepicker_options = $.extend({}, default_opts, this.datetimepicker_options);

    return this;
};

Charcoal.Admin.Property_Input_Datetimepicker.prototype.create_datetimepicker = function ()
{
    $(this.datetimepicker_selector).datetimepicker(this.datetimepicker_options);
};
;/***
* `charcoal/admin/property/input/map-widget`
* Property_Input_Map_Widget Javascript class
*
*/
Charcoal.Admin.Property_Input_Map_Widget = function (data)
{
    // Input type
    data.input_type = 'charcoal/admin/property/input/map-widget';

    Charcoal.Admin.Property.call(this, data);

    // Scope
    var that = this;

    // Controller
    this._controller = undefined;
    // Create uniq ident for every entities on the map
    this._object_inc = 0;

    if (typeof google === 'undefined') {
        // If google is undefined,
        window._tmp_google_onload_function = function () {
            that.init();
        };

        $.getScript(
            'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' +
            '&language=fr&callback=_tmp_google_onload_function',
            function () {}
        );
    } else {
        that.init();
    }

};

Charcoal.Admin.Property_Input_Map_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Map_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Map_Widget;
Charcoal.Admin.Property_Input_Map_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Map_Widget.prototype.init = function ()
{
    if (typeof window._tmp_google_onload_function !== 'undefined') {
        delete window._tmp_google_onload_function;
    }
    if (typeof BB === 'undefined' || typeof google === 'undefined') {
        // We don't have what we need
        console.error('Plugins not loaded');
        return false;
    }

    var _data = this.data;

    // Shouldn't happen at that point
    if (typeof _data.id === 'undefined') {
        console.error('Missing ID');
    }

    var default_styles = {
        strokeColor: '#000000',
        strokeOpacity: 0.8,
        strokeWeight: 3,
        fillColor: '#ffffff',
        fillOpacity: 0.35,
        hover: {
            strokeColor: '#000000',
            strokeOpacity: 1,
            strokeWeight: 2,
            fillColor: '#ffffff',
            fillOpacity: 0.5
        },
        focused: {
            fillOpacity: 0.8
        }
    };

    var map_options = {
        default_styles: default_styles,
        use_clusterer: false,
        map: {
            center: {
                x: 45.3712923,
                y: -73.9820994
            },
            zoom: 14,
            mapType: 'roadmap',
            coordsType: 'inpage', // array, json? (vs ul li)
            map_mode: 'default'
        }
    };

    map_options = $.extend(true, map_options, _data.data);

    // Get current map state from DB
    // This is located in the hidden input
    var current_value = this.element().find('input[type=hidden]').val();

    if (current_value) {
        // Parse the value
        var places = JSON.parse(current_value);

        // Merge places with default styles
        var merged_places = {};
        var index = 0;
        for (var ident in places) {
            index++;
            merged_places[ ident ] = places[ ident ];
            merged_places[ ident ].styles = $.extend(places[ ident ].styles, default_styles);
        }

        if (merged_places) {
            map_options.places = merged_places;
        }

        if (index) {
            this._object_inc = index;
        }
    }

    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-maker-map').get(0),
        map_options
    );

    // Init new map instance
    this.controller().init().ready(
        function (ctrl) {
            ctrl.fit_bounds();
            ctrl.remove_focus();
        }
    );

    this.controller().set_styles([{ featureType:'poi',elementType:'all',stylers:[{ visibility:'off' }] }]);

    this.controller().remove_focus();

    // Start listeners for controls.
    this.controls();

};

/**
* Return {BB.gmap.controller}
*/
Charcoal.Admin.Property_Input_Map_Widget.prototype.controller = function ()
{
    return this._controller;
};

/**
* This is to prevent any ident duplication
* Return {Int} Object index
*/
Charcoal.Admin.Property_Input_Map_Widget.prototype.object_index = function ()
{
    return ++this._object_inc;
};

/**
* Return {BB.gmap.controller}
*/
Charcoal.Admin.Property_Input_Map_Widget.prototype.controls = function ()
{
    // Scope
    var that = this;

    var key = 'object';

    this.element().on('click', '.js-add-marker', function (e)
    {
        e.preventDefault();

        // Find uniq item ident
        var object_id = key + that.object_index();
        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        // Start creation of a new object
        that.controller().create_new('marker', object_id);
    });

    this.element().on('click', '.js-display-marker-toolbox', function (e) {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            // Little helper
            that.hide_marker_toolbar();
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        // Little helper
        that.display_marker_toolbar();
    });

    this.element().on('click', '.js-add-line', function (e)
    {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        var object_id = key + that.object_index();

        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }
        that.controller().create_new('line', object_id);
    });

    this.element().on('click', '.js-add-polygon', function (e)
    {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        var object_id = key + that.object_index();

        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        that.controller().create_new('polygon', object_id);
    });

    this.element().on('click', '.js-add_place_by_address', function (e) {
        e.preventDefault();

        var value = that.element().find('.js-address').val();
        if (!value) {
            // No value specified, no need to go further
            return false;
        }

        that.controller().add_place_by_address('object' + that.object_index(), value, {
            type: 'marker',
            draggable: true,
            editable: true,
            // After loading the marker object
            loaded_callback: function (marker) {
                that.controller().map().setCenter(marker.object().getPosition());
            }
        });

    });

    that.controller().on('focus', function (obj) {
        var type = obj.data('type');

        that.element().find('.js-add-polygon').removeClass('-active');
        that.element().find('.js-display-marker-toolbox').removeClass('-active');
        // that.element().find('.js-add-marker').removeClass('-active');
        that.element().find('.js-add-line').removeClass('-active');

        switch (type) {
            case 'marker' :
                that.element().find('.js-display-marker-toolbox').addClass('-active');
            break;

            case 'polygon' :
                that.element().find('.js-add-polygon').addClass('-active');
            break;

            case 'line' :
                that.element().find('.js-add-line').addClass('-active');
            break;
        }
    });

};

Charcoal.Admin.Property_Input_Map_Widget.prototype.display_marker_toolbar = function ()
{
    // Displays the tool bar.
    $('.c-map-maker ').addClass('maker_header-open');
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.hide_marker_toolbar = function ()
{
    // Displays the tool bar.
    $('.c-map-maker ').removeClass('maker_header-open');
};

/**
* I believe this should fit the PHP model
* Added the save() function to be called on form submit
* Could be inherited from a global Charcoal.Admin.Property Prototype
* Extra ideas:
* - save
* - validate
* @return this (chainable)
*/
Charcoal.Admin.Property_Input_Map_Widget.prototype.save = function ()
{
    // Get raw map datas
    var raw = this.controller().export();

    // We might wanna save ONLY the places values
    var places = (typeof raw.places === 'object') ? raw.places : {};

    // Affect to the current property's input
    // I see no reason to have more than one input hidden here.
    // Split with classes or data if needed
    this.element().find('input[type=hidden]').val(JSON.stringify(places));

    return this;
};
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

    // Property_Input_Switch properties
    this.input_id = null;
    this.input_selector = null;
    this.switch_selector = null;

    this.set_properties(opts).create_switch();
};
Charcoal.Admin.Property_Input_Switch.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Switch.prototype.constructor = Charcoal.Admin.Property_Input_Switch;
Charcoal.Admin.Property_Input_Switch.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Switch.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.input_selector = opts.data.input_selector || this.input_selector;
    this.switch_selector = opts.data.switch_selector || this.switch_selector;

    return this;
};

Charcoal.Admin.Property_Input_Switch.prototype.create_switch = function ()
{
    var that = this;

    $(that.switch_selector).bootstrapSwitch({
        onSwitchChange: function (event, state) {
            $(that.input_selector).val((state) ? 1 : 0);
        }
    });
};
;/**
* TinyMCE implementation for WYSIWYG inputs
* charcoal/admin/property/input/tinymce
*
* Require:
* - jQuery
* - tinyMCE
*
* @param  {Object}  opts Options for input property
*/

Charcoal.Admin.Property_Input_Tinymce = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/tinymce';

    // Property_Input_Tinymce properties
    this.input_id = null;
    this.editor_options = null;

    this.set_properties(opts).create_tinymce();
};
Charcoal.Admin.Property_Input_Tinymce.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Tinymce.prototype.constructor = Charcoal.Admin.Property_Input_Tinymce;
Charcoal.Admin.Property_Input_Tinymce.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Tinymce.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.editor_options = opts.editor_options || this.editor_options;

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

    this.editor_options = $.extend({}, default_opts, this.editor_options);
    this.editor_options.selector = '#' + this.input_id;

    // Ensures the hidden input is always up-to-date (can be saved via ajax at any time)
    this.editor_options.setup = function (editor) {
        editor.on('change', function () {
            window.tinymce.triggerSave();
        });
    };

    return this;
};

Charcoal.Admin.Property_Input_Tinymce.prototype.create_tinymce = function ()
{
    window.tinymce.init(this.editor_options);
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
        var url = Charcoal.Admin.admin_url() + 'login';
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
* This should be the base for all widgets
* It is still possible to add a widget without passing
* throught this class, but not suggested
*
* Interface:
* ## Setters
* - `set_opts`
* - `set_id`
* - `set_element`
* - `set_type`
*
* ## Getters
* - `opts( ident )`
* - `id()`
* - `element()`
* - `type()`
*
* ## Others
* - `init()`
* - `reload( callback )`
*/

Charcoal.Admin.Widget = function (opts)
{
    this._element = undefined;
    this._id = undefined;
    this._type = undefined;
    this._opts = undefined;

    if (!opts) {
        return this;
    }

    if (typeof opts.id === 'string') {
        this.set_element($('#' + opts.id));
        this.set_id(opts.id);
    }

    if (typeof opts.type === 'string') {
        this.set_type(opts.type);
    }

    this.set_opts(opts);

    return this;
};

/**
* Set options
* @param {Object} opts
* @return this (chainable)
*/
Charcoal.Admin.Widget.prototype.set_opts = function (opts)
{
    this._opts = opts;

    return this;
};

/**
* If a ident is specified, the method tries to return
* the options pointed out.
* If no ident is specified, the method returns
* the whole opts object
*
* @param {String} ident | falcultative
* @return {Object|Mixed|false}
*/
Charcoal.Admin.Widget.prototype.opts = function (ident)
{
    if (typeof ident === 'string') {
        if (typeof this._opts[ ident ] === 'undefined') {
            return false;
        }
        return this._opts[ ident ];
    }

    return this._opts;
};

/**
* Default init
* @return this (chainable)
*/
Charcoal.Admin.Widget.prototype.init = function ()
{
    // Default init. Nothing!
    return this;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_id = function (id)
{
    this._id = id;
};

Charcoal.Admin.Widget.prototype.id = function ()
{
    return this._id;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_type = function (type)
{
    //
    this._type = type;

    // Should we update anything? Change the container ID or replace it?
    // Maybe reinit the plugin?
};

Charcoal.Admin.Widget.prototype.type = function ()
{
    return this._type;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_element = function (elem)
{
    this._element = elem;

    return this;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.element = function ()
{
    return this._element;
};

Charcoal.Admin.Widget.prototype.reload = function (cb)
{
    var that = this;

    var url = Charcoal.Admin.admin_url() + 'widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    $.post(url, data, function (response) {
        if (typeof response.widget_id === 'string') {
            that.set_id(response.widget_id);
            that.element().fadeOut();
            setTimeout(function () {
                that.element().replaceWith(response.widget_html);
                that.set_element($('#' + that.id()));

                // Pure dompe.
                that.element().hide().fadeIn();
                that.init();
            }, 600);
        }
        // Callback
        cb(response);
    });

};

/**
* Load the widget into a dialog
*/
Charcoal.Admin.Widget.prototype.dialog = function (dialog_opts)
{
    //var that = this;

    var title = dialog_opts.title || '',
        type = dialog_opts.type || BootstrapDialog.TYPE_DEFAULT;

    BootstrapDialog.show({
        title: title,
        type: type,
        nl2br: false,
        message: function (dialog) {
            console.debug(dialog);
            var url = Charcoal.Admin.admin_url() + 'widget/load',
                data = {
                    widget_type:    dialog_opts.widget_type//that.widget_type//,
                    //widget_options: that.widget_options()
                },
                $message = $('<div>Loading...</div>');

            $.ajax({
                method: 'POST',
                url: url,
                data: data
            }).done(function (response) {
                console.debug(response);
                if (response.success) {
                    dialog.setMessage(response.widget_html);
                } else {
                    dialog.setType(BootstrapDialog.TYPE_DANGER);
                    dialog.setMessage('Error');
                }
            });
            return $message;
        }

    });
};
;/**
* Form widget that manages data sending
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Form = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id = null;
    this.obj_type = null;
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
    this.obj_type = opts.data.obj_type || this.obj_type;
    this.obj_id = opts.data.obj_id || this.obj_id;
    this.form_selector = opts.data.form_selector || this.form_selector;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    // Submit the form via ajax
    $(that.form_selector).on('submit', function (e) {
        e.preventDefault();
        that.submit_form(this);
    });

    // Any delete button should trigger the delete-object method.
    $('.js-obj-delete').on('click', function (e) {
        e.preventDefault();
        that.delete_object(this);
    });

    $('.js-reset-form').on('click', function (e) {
        e.preventDefault();
        console.debug(this);
        $(that.form_selector)[0].reset();
    });
};

Charcoal.Admin.Widget_Form.prototype.submit_form = function (form)
{
    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    var that = this,
        form_data = new FormData(form),
        url,
        is_new_object;

    if (that.obj_id) {
        url = Charcoal.Admin.admin_url() + 'object/update';
        is_new_object = false;
    } else {
        url = Charcoal.Admin.admin_url() + 'object/save';
        is_new_object = true;
    }

    $.ajax({
        url: url,
        type: 'POST',
        processData: false,
        contentType: false,
        data: form_data,
        success: function (response) {
            if (response.success) {

                // Default, add feedback to list
                Charcoal.Admin.feedback().add_data(response.feedbacks);

                if (response.next_url) {
                    // @todo "dynamise" the label
                    Charcoal.Admin.feedback().add_action({
                        label: 'Continuer',
                        callback: function () {
                            window.location.href =
                                Charcoal.Admin.admin_url() +
                                response.next_url;
                        }
                    });
                }

                if (!is_new_object) {
                    Charcoal.Admin.feedback().call();
                } else {
                    if (response.next_url) {
                        window.location.href =
                            Charcoal.Admin.admin_url() +
                            response.next_url;
                    } else {
                        window.location.href =
                            Charcoal.Admin.admin_url() +
                            'object/edit?obj_type=' + that.obj_type +
                            '&obj_id=' + response.obj_id;
                    }
                }
            } else {
                Charcoal.Admin.feedback().add_data(
                    [{
                        level: 'An error occurred and the object could not be saved.',
                        msg: 'error'
                    }]
                );
                Charcoal.Admin.feedback().call();
            }
        },
        error: function () {
            Charcoal.Admin.feedback().add_data(
                [{
                    level: 'An error occurred and the object could not be saved.',
                    msg: 'error'
                }]
            );
            Charcoal.Admin.feedback().call();
        }
    });
};

/**
* Handle the "delete" button / action.
*/
Charcoal.Admin.Widget_Form.prototype.delete_object = function (form)
{
    var that = this;
    console.debug(form);
    BootstrapDialog.confirm({
        title: 'Confirmer la suppression',
        type: BootstrapDialog.TYPE_DANGER,
        message:'Êtes-vous sûr de vouloir supprimer cet objet? Cette action est irréversible.',
        btnOKLabel: 'Supprimer',
        btnCancelLabel: 'Annuler',
        callback: function (result) {
            if (result) {
                var url = Charcoal.Admin.admin_url() + 'object/delete';
                var data = {
                    obj_type: that.obj_type,
                    obj_id: that.obj_id
                };
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data
                }).done(function (response) {
                    console.debug(response);
                    if (response.success) {
                        var url = Charcoal.Admin.admin_url() + 'object/collection?obj_type=' + that.obj_type;
                        window.location.href = url;
                    } else {
                        window.alert('Erreur. Impossible de supprimer cet objet.');
                    }
                });
            }
        }
    });

};
;/**
* Map sidebar
*
* According lat, lon or address must be specified
* Styles might be defined as well.
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Map = function ()
{
    this._controller = undefined;
    this.widget_type = 'charcoal/admin/widget/map';

    return this;
};

Charcoal.Admin.Widget_Map.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Map.prototype.constructor = Charcoal.Admin.Widget_Map;
Charcoal.Admin.Widget_Map.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
* Called automatically by the component manager
* Instantiation of pretty much every thing you want!
*
* @return this
*/
Charcoal.Admin.Widget_Map.prototype.init = function ()
{
    var that = this;

    if (typeof google === 'undefined') {
        // If google is undefined,
        window._tmp_google_onload_function = function () {
            that.activate_map();
        };

        $.getScript(
            'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' +
            '&language=fr&callback=_tmp_google_onload_function',
            function () {}
        );
    } else {
        that.activate_map();
    }

    return this;
};

Charcoal.Admin.Widget_Map.prototype.activate_map = function ()
{
    var default_styles = {
        strokeColor: '#000000',
        strokeOpacity: 0.8,
        strokeWeight: 3,
        fillColor: '#ffffff',
        fillOpacity: 0.35,
        hover: {
            strokeColor: '#000000',
            strokeOpacity: 1,
            strokeWeight: 2,
            fillColor: '#ffffff',
            fillOpacity: 0.5
        },
        focused: {
            fillOpacity: 0.8
        }
    };

    var map_options = {
        default_styles: default_styles,
        use_clusterer: false,
        map: {
            center: {
                x: this.opts('coords')[0],
                y: this.opts('coords')[1]
            },
            zoom: 14,
            mapType: 'roadmap',
            coordsType: 'inpage', // array, json? (vs ul li)
            map_mode: 'default'
        },
        places:{
            first:{
                type: 'marker',
                coords: this.coords(),
            }
        }
    };

    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-maker-map').get(0),
        map_options
    );

    this.controller().set_styles([{ featureType:'poi',elementType:'all',stylers:[{ visibility:'off' }] }]);

    this.controller().remove_focus();
    this.controller().init();

};

Charcoal.Admin.Widget_Map.prototype.controller = function ()
{
    return this._controller;
};

Charcoal.Admin.Widget_Map.prototype.coords = function ()
{
    return this.opts('coords');
};
;/**
* Search widget used for filtering a list
* charcoal/admin/widget/search
*
* Require:
* - jQuery
*
* @param  {Object}  opts Options for widget
*/
Charcoal.Admin.Widget_Search = function (opts)
{
    this._elem = undefined;

    if (!opts) {
        // No chance
        return false;
    }

    if (typeof opts.id === 'undefined') {
        return false;
    }

    this.set_element($('#' + opts.id));

    if (typeof opts.data !== 'object') {
        return false;
    }

    this.opts = opts;

    return this;
};

Charcoal.Admin.Widget_Search.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Search.prototype.constructor = Charcoal.Admin.Widget_Search;
Charcoal.Admin.Widget_Search.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
* Whats the widget that should be refreshed?
* A list, a table? Definition of a widget includes:
* - Widget type
*/
Charcoal.Admin.Widget_Search.prototype.set_remote_widget = function ()
{
    // Do something about this.
};

Charcoal.Admin.Widget_Search.prototype.init = function ()
{
    var $elem = this.element();

    var that = this;

    // Submit
    $elem.on('click', '.js-search', function (e) {
        e.preventDefault();
        that.submit();
    });

    // Undo
    $elem.on('click', '.js-undo', function (e) {
        e.preventDefault();
        that.undo();
    });
};

/**
* Submit the search filters as expected to all widgets
* @return this (chainable);
*/
Charcoal.Admin.Widget_Search.prototype.submit = function ()
{
    var manager = Charcoal.Admin.manager();
    var widgets = manager.components.widgets;

    var i = 0;
    var total = widgets.length;
    for (; i < total; i++) {
        this.dispatch(widgets[i]);
    }

    return this;
};

/**
* Resets the search filters
* @return this (chainable);
*/
Charcoal.Admin.Widget_Search.prototype.undo = function ()
{
    this.element().find('input').val('');
    this.submit();
    return this;
};

/**
* Dispatches the event to all widgets that can listen to it
* @return this (chainable)
*/
Charcoal.Admin.Widget_Search.prototype.dispatch = function (widget)
{

    if (!widget) {
        return this;
    }

    if (typeof widget.add_filter !== 'function') {
        return this;
    }

    var $input = this.element().find('input');
    var val = $input.val();

    var properties = this.opts.data.list || [];

    var i = 0;
    var total = properties.length;

    // Dumb loop
    for (; i < total; i++) {
        var single_filter = {};
        single_filter[ properties[i] ] = {};
        single_filter[ properties[i] ].val = '%' + val + '%';
        single_filter[ properties[i] ].property = properties[i];
        single_filter[ properties[i] ].operator = 'LIKE';
        single_filter[ properties[i] ].operand = 'OR';

        widget.add_filter(single_filter);
    }

    //    widget.add_search(val, properties);

    widget.reload();

    return this;
};
;/**
* Table widget used for listing collections of objects
* charcoal/admin/widget/table
*
* Require:
* - jQuery
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Table = function ()
{
    this.widget_type = 'charcoal/admin/widget/table';

    // Widget_Table properties
    this.obj_type = null;
    this.widget_id = null;
    this.table_selector = null;
    // this.properties = null;
    this.properties_options = null;
    this.filters = null;
    this.orders = [];
    this.pagination = {
        page: 1,
        num_per_page: 50
    };
    this.table_rows = [];

};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
* Necessary for a widget.
*/
Charcoal.Admin.Widget_Table.prototype.init = function ()
{
    this.set_properties().create_rows().bind_events();
};

Charcoal.Admin.Widget_Table.prototype.set_properties = function ()
{
    var opts = this.opts();

    this.obj_type = opts.data.obj_type || this.obj_type;
    this.widget_id = opts.id || this.widget_id;
    this.table_selector = '#' + this.widget_id;
    this.properties = opts.data.properties || this.properties;
    this.properties_options = opts.data.properties_options || this.properties_options;
    this.filters = opts.data.filters || this.filters;
    this.orders = opts.data.orders || this.orders;
    this.pagination = opts.data.pagination || this.pagination;

    // @todo remove the hardcoded shit
    this.collection_ident = opts.data.collection_ident || 'default';

    return this;
};

Charcoal.Admin.Widget_Table.prototype.create_rows = function ()
{
    var rows = $('.js-table-row');

    for (var i = 0, len = rows.length; i < len; i++) {
        var element = rows[i],
            row = new Charcoal.Admin.Widget_Table.Table_Row(this,element);
        this.table_rows.push(row);
    }

    return this;
};

Charcoal.Admin.Widget_Table.prototype.bind_events = function ()
{
    var that = this;

    // The "quick create" event button loads the objectform widget
    $('.js-list-quick-create', that.table_selector).on('click', function (e) {
        e.preventDefault();
        var url = Charcoal.Admin.admin_url() + 'widget/load',
            data = {
                widget_type: 'charcoal/admin/widget/objectform',
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

    $('.js-sublist-inline-edit').on('click', function (e) {
        e.preventDefault();

        var sublist = that.sublist(),
            url = Charcoal.Admin.admin_url() + 'widget/table/inlinemulti',
            data = {
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

                    var inline_properties = objects[i].inline_properties,
                        row = $(sublist.elems[i]).parents('tr'),
                        p = 0;

                    for (p in inline_properties) {
                        var td = row.find('.property-' + p);
                        td.html(inline_properties[p]);
                    }
                }
            }
        });

    });

    $('.js-list-import', that.element).on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var widget_type = $this.data('widget-type');
        console.debug(widget_type);
        //console.debug(this.title());

        that.widget_dialog({
            title: 'Importer une liste',
            widget_type: widget_type,
            widget_options: {
                obj_type: that.obj_type,
                obj_id: 0
            }
        });
    });
};

Charcoal.Admin.Widget_Table.prototype.sublist = function ()
{
    //var that = this;

    var selected = $('.select-row:checked'),
        ret = {
            elems: [],
            obj_ids: []
        };

    selected.each(function (i, el) {
        ret.obj_ids.push($(el).parents('tr').data('id'));
        ret.elems.push(el);
    });

    return ret;
};

/**
* As it says, it ADDs a filter to the already existing list
* @param object
* @return this chainable
* @see set_filters
*/
Charcoal.Admin.Widget_Table.prototype.add_filter = function (filter)
{
    var filters = this.get_filters();

    // Null by default
    // When you add a filter, you want it to be
    // in an object
    if (filters === null) {
        filters = {};
    }

    filters = $.extend(filters, filter);
    this.set_filters(filters);

    return this;
};

/**
* This will overwrite existing filters
*/
Charcoal.Admin.Widget_Table.prototype.set_filters = function (filters)
{
    this.filters = filters;
};

/**
* Getter
* @return {Object | null} filters
*/
Charcoal.Admin.Widget_Table.prototype.get_filters = function ()
{
    return this.filters;
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function ()
{
    return {
        obj_type:   this.obj_type,
        collection_config: {
            properties: this.properties,
            properties_options: this.properties_options,
            filters:    this.filters,
            orders:     this.orders,
            pagination: this.pagination
        },
        collection_ident: this.collection_ident
    };
};

/**
*
*/
Charcoal.Admin.Widget_Table.prototype.reload = function (cb)
{
    var callback = function (response)
    {
        if (typeof cb === 'function') {
            cb(response);
        }
    };

    // Call supra class
    Charcoal.Admin.Widget.prototype.reload.call(this, callback);

    return this;

};

/**
* Load a widget (via ajax) into a dialog
*
* ## Options
* - `title`
* - `widget_type`
* - `widget_options`
*/
Charcoal.Admin.Widget_Table.prototype.widget_dialog = function (opts)
{
    //return new Charcoal.Admin.Widget(opts).dialog(opts);
    var title = opts.title || '',
        type = opts.type || BootstrapDialog.TYPE_PRIMARY,
        widget_type = opts.widget_type,
        widget_options = opts.widget_options || {};

    if (!widget_type) {
        return;
    }

    BootstrapDialog.show({
            title: title,
            type: type,
            nl2br: false,
            message: function (dialog) {
                console.debug(dialog);
                var url = Charcoal.Admin.admin_url() + 'widget/load',
                    data = {
                        widget_type: widget_type,
                        widget_options: widget_options
                    },
                    $message = $('<div>Loading...</div>');

                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data
                }).done(function (response) {
                    console.debug(response);
                    if (response.success) {
                        dialog.setMessage(response.widget_html);
                    } else {
                        dialog.setType(BootstrapDialog.TYPE_DANGER);
                        dialog.setMessage('Error');
                    }
                });
                return $message;
            }

        });
};

/**
* Table_Row object
*/
Charcoal.Admin.Widget_Table.Table_Row = function (container, row)
{
    this.widget_table = container;
    this.element = row;

    this.obj_id = this.element.getAttribute('data-id');
    this.obj_type = this.widget_table.obj_type;
    this.load_url = Charcoal.Admin.admin_url() + 'widget/load';
    this.inline_url = Charcoal.Admin.admin_url() + 'widget/table/inline';
    this.delete_url = Charcoal.Admin.admin_url() + 'object/delete';

    this.bind_events();
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.bind_events = function ()
{
    var that = this;

    $('.js-obj-quick-edit', that.element).on('click', function (e) {
        e.preventDefault();
        that.quick_edit();
    });

    $('.js-obj-inline-edit', that.element).on('click', function (e) {
        e.preventDefault();
        that.inline_edit();
    });

    $('.js-obj-delete', that.element).on('click', function (e) {
        e.preventDefault();
        that.delete_object();
    });
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.quick_edit = function ()
{
    var data = {
        widget_type: 'charcoal/admin/widget/objectForm',
        widget_options: {
            obj_type: this.obj_type,
            obj_id: this.obj_id
        }
    };

    $.post(this.load_url, data, function (response) {
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
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.inline_edit = function ()
{
    var that = this,
        data = {
        obj_type: that.obj_type,
        obj_id: that.obj_id
    };

    $.post(that.inline_url, data, function (response) {
        if (response.success) {

            var inline_properties = response.inline_properties,
                p;

            for (p in inline_properties) {
                var td = $(that.element).find('.property-' + p);
                td.html(inline_properties[p]);
            }
        }
    });
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.delete_object = function ()
{
    var that = this,
        data = {
            obj_type: that.obj_type,
            obj_id: that.obj_id
        };

    if (window.confirm('Are you sure you want to delete this object?')) {

        $.post(that.delete_url, data, function (response) {
            if (response.success) {
                $(that.element).remove();
                //that.widget_table.reload();
            } else {
                window.alert('Delete failed.');
            }
        });
    }
};

;Charcoal.Admin.Widget_Wysiwyg = function ()
{
    $('.js-wysiwyg').summernote({
        height: 300
    });
};
