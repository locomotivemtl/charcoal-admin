var Charcoal = Charcoal || {};

/**
 * Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
 * It gives access to private properties and public methods
 * @return  {object}  Charcoal.Admin
 */
Charcoal.Admin = (function ()
{
    'use strict';

    var options, manager, feedback;

    options = {
        base_url:   null,
        admin_path: null,
    };

    /**
     * Object function that acts as a container for public methods
     */
    function Admin()
    {
    }

    /**
     * Simple cache store.
     *
     * @type {Object}
     */
    Admin.cachePool = {};

    /**
     * Current language.
     *
     * @type {string}
     */
    Admin.lang = 'en';

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
     * Returns the base_url of the project
     * @return  {string}  URL for admin section
     */
    Admin.base_url = function ()
    {
        return options.base_url;
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

    /**
     * Get the numeric value of a variable.
     *
     * @param   {string|number}  value - The value to parse.
     * @return  {string|number}  Returns a numeric value if one was detected otherwise a string.
     */
    Admin.parseNumber = function (value) {
        var re = /^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/;

        if (re.test(value)) {
            return Number(value);
        }

        return value;
    };

    /**
     * Load Script
     *
     * @param   {string}    src      - Full path to a script file.
     * @param   {function}  callback - Fires multiple times
     */
    Admin.loadScript = function (src, callback)
    {
        this.cache(src, function (defer) {
            $.ajax({
                url:      src,
                dataType: 'script',
                success:  defer.resolve,
                error:    defer.reject
            });
        }).then(callback);
    };

    /**
     * Retrieve or cache a value shared across all instances.
     *
     * @param   {string}    key      - The key for the cached value.
     * @param   {function}  value    - The value to store. If a function, fires once when promise is completed.
     * @param   {function}  callback - Fires multiple times.
     * @return  {mixed}     Returns the stored value.
     */
    Admin.cache = function (key, value, callback)
    {
        if (!this.cachePool[key]) {
            if (typeof value === 'function') {
                this.cachePool[key] = $.Deferred(function (defer) {
                    value(defer);
                }).promise();
            }
        }

        if (typeof this.cachePool[key] === 'function') {
            return this.cachePool[key].done(callback);
        }

        return this.cachePool[key];
    };

    /**
     * Resolves the context of parameters for the "complete" callback option.
     *
     * (`jqXHR.always(function( data|jqXHR, textStatus, jqXHR|errorThrown ) {})`).
     *
     * @param  {...} Successful or failed argument list.
     * @return {mixed[]} Standardized argument list.
     */
    Admin.parseJqXhrArgs = function ()
    {
        var args = {
            failed:      true,
            jqXHR:       null,
            textStatus:  '',
            errorThrown: '',
            response:    null
        };

        // If the third argument is a string, the request failed
        // and the value is an error message: errorThrown;
        // otherwise it's probably the XML HTTP Request Object.
        if (arguments[2] && $.type(arguments[2]) === 'string') {
            args.jqXHR       = arguments[0] || null;
            args.textStatus  = arguments[1] || null;
            args.errorThrown = arguments[2] || null;
            args.response    = arguments[3] || args.jqXHR.responseJSON || null;

            if ($.type(args.response) === 'object') {
                args.failed = !args.response.success;
            } else {
                args.failed = true;
            }
        } else {
            args.response    = arguments[0] || null;
            args.textStatus  = arguments[1] || null;
            args.jqXHR       = arguments[2] || null;
            args.errorThrown = null;

            if (args.response === null) {
                args.response = args.jqXHR.responseJSON;
            }

            if ($.type(args.response) === 'object') {
                args.failed = !args.response.success;
            } else {
                args.failed = false;
            }
        }

        return args;
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
        var super_class = Charcoal;

        switch (component_type)
        {
        case 'widgets' :
            super_class = Charcoal.Admin.Widget;
            break;

        case 'property_inputs' :
            super_class = Charcoal.Admin.Property;
            break;

        case 'templates' :
            super_class = Charcoal.Admin.Template;
            break;

    }

        for (var i = 0, len = this.components[component_type].length; i < len; i++) {

            var component_data = this.components[component_type][i];

            // If we are already dealing with a full on component
            if (component_data instanceof super_class) {
                if (typeof component_data.destroy === 'function') {
                    component_data.destroy();
                    component_data.init();
                }
                continue;
            }

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
    this.prepare_inputs();
    this.prepare_widgets();
    return true;
};
Charcoal.Admin.ComponentManager.prototype.prepare_inputs = function ()
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

Charcoal.Admin.ComponentManager.prototype.prepare_widgets = function ()
{
    // Get inputs
    var widgets = (typeof this.components.widgets !== 'undefined') ? this.components.widgets : [];

    if (!widgets.length) {
        // No inputs? Move on
        return true;
    }

    var length = widgets.length;
    var widget;

    // Loop for validation
    var k = 0;
    for (; k < length; k++) {
        widget = widgets[ k ];
        if (typeof widget.validate === 'function') {
            widget.validate();
        }
    }

    // We should add a check if the validation passed right here, before saving

    // Loop for save
    var i = 0;
    for (; i < length; i++) {
        widget = widgets[ i ];
        if (typeof widget.save === 'function') {
            widget.save();
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
        info: {
            title: 'Information!',
            type: BootstrapDialog.TYPE_INFO,
            alias: ['notice']
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

    this.context_aliases = {
        notice: 'info'
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
* A an alias to an existing context
* @return this
*/
Charcoal.Admin.Feedback.prototype.add_context_alias = function (alias, context) {
    if (!alias || !context || !this.context_definitions[ context ]) {
        return this;
    }

    this.context_aliases[ alias ] = context;

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
        if (typeof this.context_aliases[ level ] === 'string') {
            level = this.context_aliases[ level ];
        }

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
                    label:  action.label,
                    action: action.callback
                });
            }
        }

        BootstrapDialog.show({
            title:   this.context_definitions[ level ].title,
            message: '<p>' + ret[ level ].join('</p><p>') + '</p>',
            type:    this.context_definitions[ level ].type,
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
 * charcoal/admin/widget
 * This should be the base for all widgets
 * It is still possible to add a widget without passing
 * throught this class, but not suggested
 *
 * @see Component_Manager.render() for automatic call to widget constructor
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

Charcoal.Admin.Widget = function (opts) {
    this._element = undefined;
    this._id      = undefined;
    this._type    = undefined;
    this._opts    = undefined;

    if (!opts) {
        return this;
    }

    if (typeof opts.id === 'string') {
        this.set_element($('#' + opts.id));
        this.set_id(opts.id);
    }

    if (typeof opts.type === 'string') {
        this.set_type(opts.type);
        this.widget_type = opts.type;
    }

    this.set_opts(opts);

    return this;
};

/**
 * Set options
 * @param {Object} opts
 * @return this (chainable)
 */
Charcoal.Admin.Widget.prototype.set_opts = function (opts) {
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
Charcoal.Admin.Widget.prototype.opts = function (ident) {
    if (typeof ident === 'string') {
        if (typeof this._opts[ident] === 'undefined') {
            return false;
        }
        return this._opts[ident];
    }

    return this._opts;
};

/**
 * Default init
 * @return this (chainable)
 */
Charcoal.Admin.Widget.prototype.init = function () {
    // Default init. Nothing!
    return this;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_id = function (id) {
    this._id = id;
};

Charcoal.Admin.Widget.prototype.id = function () {
    return this._id;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_type = function (type) {
    //
    this._type = type;

    // Should we update anything? Change the container ID or replace it?
    // Maybe reinit the plugin?
};

Charcoal.Admin.Widget.prototype.type = function () {
    return this._type;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_element = function (elem) {
    this._element = elem;

    return this;
};

/**
 * Default behavior
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget.prototype.widget_options = function () {
    return this.opts();
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.element = function () {
    return this._element;
};

/**
 * Default widget options
 * Can be overwritten by widget
 * @return {Object}
 */
Charcoal.Admin.Widget.prototype.widget_options = function () {
    return this.opts();
};

/**
 * Default widget type
 * Can be overwritten by widget
 * @return {String}
 */
Charcoal.Admin.Widget.prototype.widget_type = function () {
    return this.type();
};

/**
 * Called upon save by the component manager
 *
 * @return {boolean} Default action is set to true.
 */
Charcoal.Admin.Widget.prototype.save = function () {
    return true;
};

/**
 * Animate the widget out on reload
 * Use callback to define what to do after the animation.
 *
 * @param  {Function} callback What to do after the anim_out?
 * @return {thisArg}           Chainable
 */
Charcoal.Admin.Widget.prototype.anim_out = function (callback) {
    if (typeof callback !== 'function') {
        callback = function () {
        };
    }
    this.element().fadeOut(400, callback);
    return this;
};

Charcoal.Admin.Widget.prototype.reload = function (cb) {
    var that = this;

    var url  = Charcoal.Admin.admin_url() + 'widget/load';
    var data = {
        widget_type: that.type(),
        widget_options: that.widget_options()
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    $.post(url, data, function (response) {
        if (typeof response.widget_id === 'string') {
            that.set_id(response.widget_id);
            that.anim_out(function () {
                that.element().replaceWith(response.widget_html);
                that.set_element($('#' + that.id()));

                // Pure dompe.
                that.element().hide().fadeIn();
                that.init();
            });
        }
        // Callback
        if (typeof cb === 'function') {
            cb(response);
        }
    }, 'json');

};

/**
 * Load the widget into a dialog
 */
Charcoal.Admin.Widget.prototype.dialog = function (dialog_opts, callback) {
    var title       = dialog_opts.title || '',
        type        = dialog_opts.type || BootstrapDialog.TYPE_DEFAULT,
        size        = dialog_opts.size || BootstrapDialog.SIZE_NORMAL,
        cssClass    = dialog_opts.cssClass || '',
        showHeader  = dialog_opts.showHeader || true,
        showFooter  = dialog_opts.showFooter || true,
        userOptions = dialog_opts.dialog_options || {};

    delete dialog_opts.title;
    delete dialog_opts.type;
    delete dialog_opts.size;
    delete dialog_opts.cssClass;
    delete dialog_opts.dialog_options;

    var defaultOptions = {
        title: title,
        type: type,
        size: size,
        cssClass: cssClass,
        nl2br: false,
        showHeader: showHeader,
        showFooter: showFooter,
        onshown: function () {
            Charcoal.Admin.manager().render();
        }
    };

    var dialogOptions = $.extend({}, defaultOptions, userOptions);

    dialogOptions.message = function (dialog) {
        var xhr,
            url      = Charcoal.Admin.admin_url() + 'widget/load',
            data     = dialog_opts,
            $message = $('<div>Loading...</div>');

        if (!showHeader) {
            dialog.getModalHeader().addClass('hidden');
        }

        if (!showFooter) {
            dialog.getModalFooter().addClass('hidden');
        }

        dialog.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dialog },
            function (event) {
                event.data.dialog.close();
            }
        );

        xhr = $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json'
        });

        xhr.then(function (response, textStatus, jqXHR) {
            if (!response || !response.success) {
                if (response.feedbacks) {
                    return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
                } else {
                    return $.Deferred().reject(jqXHR, textStatus, 'An unknown error occurred.');
                }
            }

            return $.Deferred().resolve(response, textStatus, jqXHR);
        })
            .done(function (response/*, textStatus, jqXHR*/) {
                dialog.setMessage(response.widget_html);

                if (typeof callback === 'function') {
                    callback(response);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                dialog.setType(BootstrapDialog.TYPE_DANGER);
                dialog.setMessage('<div class="alert alert-danger" role="alert">An unknown error occurred.</div>');

                var errorHtml = '';

                if ($.type(errorThrown) === 'string') {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
                        errorThrown = jqXHR.responseJSON.feedbacks;
                    }
                }

                if ($.isArray(errorThrown)) {
                    $.each(errorThrown, function (i, error) {
                        if (error.message) {
                            if (error.level === 'error') {
                                error.level = 'danger';
                            }
                            errorHtml += '<div class="alert alert-' + error.level + '" role="alert">' +
                                error.message +
                                '</div>';
                        }
                    });
                } else if ($.type(errorThrown) === 'string') {
                    errorHtml = '<div class="alert alert-danger" role="alert">' + errorThrown + '</div>';
                }

                if (errorHtml) {
                    dialog.setMessage(errorHtml);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            });

        return $message;
    };

    BootstrapDialog.show(dialogOptions);
};

Charcoal.Admin.Widget.prototype.confirm = function (dialog_opts, confirmed_callback, cancel_callback) {
    var defaults = {
        title: 'Voulez-vous vraiment effectuer cette action?',
        confirm_label: 'Oui',
        cancel_label: 'Non'
    };

    var opts = $.extend(defaults, dialog_opts);

    BootstrapDialog.show({
        title: opts.title,
        buttons: [{
            label: opts.cancel_label,
            action: function (dialog) {
                if (typeof cancel_callback === 'function') {
                    cancel_callback();
                }
                dialog.close();
            }
        }, {
            label: opts.confirm_label,
            action: function (dialog) {
                if (typeof confirmed_callback === 'function') {
                    confirmed_callback();
                }
                dialog.close();
            }
        }]
    });
};
;/**
* Attachment widget
* You can associate a perticular object to another
* using this widget.
*
* @see widget.js (Charcoal.Admin.Widget
*/
Charcoal.Admin.Widget_Attachment = function ()
{
    this.glyphs = {
        embed:      'glyphicon-blackboard',
        video:      'glyphicon-film',
        image:      'glyphicon-picture',
        file:       'glyphicon-file',
        link:       'glyphicon-link',
        text:       'glyphicon-font',
        gallery:    'glyphicon-duplicate',
        container:  'glyphicon-list',
        accordion:  'glyphicon-list'
    };

    this.dirty = false;
    return this;
};

Charcoal.Admin.Widget_Attachment.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Attachment.prototype.constructor = Charcoal.Admin.Widget_Attachment;
Charcoal.Admin.Widget_Attachment.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
 * Called upon creation
 * Use as constructor
 * Access available configurations with `this.opts()`
 * Encapsulate all events within the current widget
 * element: `this.element()`.
 *
 *
 * @see Component_Manager.render()
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.init = function ()
{
    console.log(this.element());
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-attachment-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel js-attachment-placeholder',
        start:       function (event, ui) {
            var $heading     = ui.item.children('.panel-heading'),
                $collapsible = $heading.find('[data-toggle="collapse"]');

            if (!$collapsible.hasClass('collapsed')) {
                ui.item.children('.panel-collapse').collapse('hide');
            }
        }
    }).disableSelection();

    this.listeners();
    return this;
};

/**
 * Check if the widget has something a dirty state that needs to be saved.
 * @return Boolean     Widget dirty of not.
 */
Charcoal.Admin.Widget_Attachment.prototype.is_dirty = function ()
{
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Attachment_Widget Chainable.
 */
Charcoal.Admin.Widget_Attachment.prototype.set_dirty_state = function (bool)
{
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.listeners = function ()
{
    // Scope
    var that = this,
        $container = this.element().find('.js-attachment-sortable .js-grid-container');

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.attachments', '.js-attachments-collapse', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.children('.panel-heading.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $attachments.children('.panel-collapse.in').collapse('hide');
        })
        .on('click.charcoal.attachments', '.js-attachments-expand', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.children('.panel-heading.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $attachments.children('.panel-collapse:not(.in)').collapse('show');
        })
        .on('click.charcoal.attachments', '.js-add-attachment', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var title = $(this).data('title') || 'Edit';
            that.create_attachment(type, title, 0, function (response) {
                if (response.success) {
                    response.obj.id = response.obj_id;
                    that.add(response.obj);
                    that.join(function () {
                        that.reload();
                    });
                }
            });
        })
        .on('click.charcoal.attachments', '.js-attachment-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit' :
                    var type = _this.data('type');
                    var id = _this.data('id');
                    if (!type || !id) {
                        break;
                    }
                    var title = _this.data('title') || 'Édition';
                    that.create_attachment(type, title, id, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title: 'Voulez-vous vraiment supprimer cet item?'
                        },
                        function () {
                            that.remove_join(_this.data('id'), function () {
                                that.reload();
                            });
                        }
                    );
                    break;

                case 'add-object':
                    var container_type   = _this.data('type'),
                        container_group  = _this.data('group'),
                        container_id     = _this.data('id'),
                        attachment_title = _this.data('title'),
                        attachment_type  = _this.data('attachment');

                    that.create_attachment(attachment_type, attachment_title, 0, function (response) {
                        if (response.success) {
                            that.add_object_to_container(
                                {
                                    id:   response.obj_id,
                                    type: response.obj.type
                                },
                                {
                                    id:    container_id,
                                    type:  container_type,
                                    group: container_group
                                }
                            );
                        }
                    });

                    break;
            }
        });
};

/**
 * Select an attachment from the list
 *
 * @param  {jQuery Object} elem Clicked element
 * @return {thisArg}            (Chainable)
 */
Charcoal.Admin.Widget_Attachment.prototype.select_attachment = function (elem)
{
    if (!elem.data('id') || !elem.data('type')) {
        // Invalid
        return this;
    }
};

Charcoal.Admin.Widget_Attachment.prototype.create_attachment = function (type, title, id, cb)
{
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    var data = {
        title:          title,
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quickForm',
        widget_options: {
            obj_type:   type,
            obj_id:     id
        }
    };
    this.dialog(data, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id:   response.widget_id,
                type: 'charcoal/admin/widget/quick-form',
                data: {
                    obj_type: type
                },
                obj_id: id,
                save_callback: function (response) {
                    cb(response);
                    BootstrapDialog.closeAll();
                }
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * Add an attachment to an existing container.
 *
 * @param {object} attachment - The attachment to add to the container.
 * @param {object} container  - The container attachment.
 */
Charcoal.Admin.Widget_Attachment.prototype.add_object_to_container = function (attachment, container, grouping)
{
    var that = this,
        data = {
            obj_type:    container.type,
            obj_id:      container.id,
            attachments: [
                {
                    attachment_id:   attachment.id,
                    attachment_type: attachment.type,
                    position: 0
                }
            ],
            group: grouping || container.group || ''
        };

    $.post('add-join', data, function () {
        that.reload();
    }, 'json');
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.add = function (obj)
{
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);

    var template = this.element().find('.js-attachment-template').clone();
    template.find('.js-attachment').data('id', obj.id).data('type', obj.type);
    this.element().find('.js-attachment-sortable').find('.js-grid-container').append(template);

    return this;

};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.save = function ()
{
    if (this.is_dirty()) {
        return false;
    }

    // Create join from current list.
    this.join();
};

Charcoal.Admin.Widget_Attachment.prototype.join = function (cb)
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type:    opts.data.obj_type,
        obj_id:      opts.data.obj_id,
        attachments: [],
        group:       opts.data.group
    };

    this.element().find('.js-attachment-container').find('.js-attachment').each(function (i)
    {
        var $this = $(this);
        var id    = $this.data('id');
        var type  = $this.data('type');

        data.attachments.push({
            attachment_id:   id,
            attachment_type: type, // Further use.
            position:        i
        });
    });

    $.post('join', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * [remove_join description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.remove_join = function (id, cb)
{
    if (!id) {
        // How could this possibly be!
        return false;
    }

    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type:      opts.data.obj_type,
        obj_id:        opts.data.obj_id,
        attachment_id: id,
        group:         opts.data.group
    };

    $.post('remove-join', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * Widget options as output by the widget itself.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.widget_options = function ()
{
    return this.opts('widget_options');
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
    this.widget_id         = null;
    this.obj_type          = null;
    this.obj_id            = null;
    this.form_selector     = null;
    this.form_working      = false;
    this.suppress_feedback = false;
    this.is_new_object     = false;
    this.xhr               = null;

    Charcoal.Admin.lang = $('[data-lang]:not(.hidden)').data('lang');

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts)
{
    this.widget_id     = opts.id || this.widget_id;
    this.obj_type      = opts.data.obj_type || this.obj_type;
    this.obj_id        = Charcoal.Admin.parseNumber(opts.data.obj_id || this.obj_id);
    this.form_selector = opts.data.form_selector || this.form_selector;
    this.isTab         = opts.data.tab;

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

    // Reset button
    $('.js-reset-form').on('click', function (e) {
        e.preventDefault();
        $(that.form_selector)[0].reset();
    });

    // Language switcher
    $('.js-lang-switch button').on('click', function (e) {
        e.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    /*if (that.isTab) {
        $(that.form_selector).on('click', '.js-group-tabs', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $(that.form_selector).find('.js-group-tab').addClass('hidden');
            $(that.form_selector).find('.js-group-tab.' + href).removeClass('hidden');
            $(this).parent().addClass('active').siblings('.active').removeClass('active');
        });
    }*/

};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @param  Element form - The submitted form.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.submit_form = function (form)
{
    if (this.form_working) {
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    var $trigger, $form, form_data;

    $form    = $(form);
    $trigger = $form.find('[type="submit"]');

    if ($trigger.prop('disabled')) {
        return false;
    }

    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    form_data = new FormData(form);

    this.disable_form($form, $trigger);

    // Use this loop if ever cascading checkbox inputs end up not
    // working properly in checkbox.mustache
    // $form.find('input[type="checkbox"]').each(function () {
    //     var $input = $(this);
    //     var inputName = $input.attr('name');

    //     // Prevents affecting switch type radio inputs
    //     if (typeof inputName !== 'undefined') {b
    //         if (!form_data.has(inputName)) {
    //             form_data.set(inputName, '');
    //         }
    //     }
    // });

    this.xhr = $.ajax({
        type:        'POST',            // ($form.prop('method') || 'POST')
        url:         this.request_url(),  // ($form.data('action') || this.request_url())
        data:        form_data,
        dataType:    'json',
        processData: false,
        contentType: false,
    });

    this.xhr
        .then($.proxy(this.request_done, this, $form, $trigger))
        .done($.proxy(this.request_success, this, $form, $trigger))
        .fail($.proxy(this.request_failed, this, $form, $trigger))
        .always($.proxy(this.request_complete, this, $form, $trigger));
};

Charcoal.Admin.Widget_Form.prototype.request_done = function ($form, $trigger, response, textStatus, jqXHR)
{
    if (!response || !response.success) {
        if (response.feedbacks) {
            return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
        } else {
            return $.Deferred().reject(jqXHR, textStatus, 'An unknown error occurred.');
        }
    }

    return $.Deferred().resolve(response, textStatus, jqXHR);
};

Charcoal.Admin.Widget_Form.prototype.request_success = function ($form, $trigger, response/* textStatus, jqXHR */)
{
    if (response.feedbacks) {
        Charcoal.Admin.feedback().add_data(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label:    'Continuer',
            callback: function () {
                window.location.href =
                    Charcoal.Admin.admin_url() +
                    response.next_url;
            }
        });
    }

    if (this.is_new_object) {
        this.suppress_feedback = true;

        if (response.next_url) {
            window.location.href =
                Charcoal.Admin.admin_url() +
                response.next_url;
        } else {
            window.location.href =
                Charcoal.Admin.admin_url() +
                'object/edit?obj_type=' + this.obj_type +
                '&obj_id=' + response.obj_id;
        }
    }
};

Charcoal.Admin.Widget_Form.prototype.request_failed = function ($form, $trigger, jqXHR, textStatus, errorThrown)
{
    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
        Charcoal.Admin.feedback().add_data(jqXHR.responseJSON.feedbacks);
    } else {
        var message = (this.is_new_object ? 'The object could not be saved: ' : 'The object could not be updated: ');
        var error   = errorThrown || 'Unknown Error';

        Charcoal.Admin.feedback().add_data([{
            level: message + error,
            msg:   'error'
        }]);
    }
};

Charcoal.Admin.Widget_Form.prototype.request_complete = function ($form, $trigger/*, .... */)
{
    if (!this.suppress_feedback) {
        Charcoal.Admin.feedback().call();
        this.enable_form($form, $trigger);
    }

    this.form_working = this.is_new_object = this.suppress_feedback = false;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.disable_form = function ($form, $trigger)
{
    if ($form) {
        $form.prop('disabled', true);
    }

    if ($trigger) {
        $trigger.prop('disabled', true)
            .children('.glyphicon').removeClass('hidden')
            .next('.btn-label').addClass('sr-only');
    }

    return this;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.enable_form = function ($form, $trigger)
{
    if ($form) {
        $form.prop('disabled', false);
    }

    if ($trigger) {
        $trigger.prop('disabled', false)
            .children('.glyphicon').addClass('hidden')
            .next('.btn-label').removeClass('sr-only');
    }

    return this;
};

/**
 * @return string The requested URL for processing the form.
 */
Charcoal.Admin.Widget_Form.prototype.request_url = function ()
{
    if (this.is_new_object) {
        return Charcoal.Admin.admin_url() + 'object/save';
    } else {
        return Charcoal.Admin.admin_url() + 'object/update';
    }
};

/**
 * Handle the "delete" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.delete_object = function (/* form */)
{
    var that = this;
    //console.debug(form);
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
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    //console.debug(response);
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

/**
* Switch languages for all l10n elements in the form
*/
Charcoal.Admin.Widget_Form.prototype.switch_language = function (lang)
{
    Charcoal.Admin.lang = lang;
    $('[data-lang][data-lang!=' + lang + ']').addClass('hidden');
    $('[data-lang][data-lang=' + lang + ']').removeClass('hidden');

    $('[data-lang-switch][data-lang-switch!=' + lang + ']')
        .removeClass('btn-info')
        .addClass('btn-default');

    $('[data-lang-switch][data-lang-switch=' + lang + ']')
        .removeClass('btn-default')
        .addClass('btn-info');
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
* Pivot widget
* You can associate a specific object to another
* using this widget.
*
* @see widget.js (Charcoal.Admin.Widget)
*/
Charcoal.Admin.Widget_Pivot = function ()
{
    this.dirty = false;
    return this;
};

Charcoal.Admin.Widget_Pivot.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Pivot.prototype.constructor = Charcoal.Admin.Widget_Pivot;
Charcoal.Admin.Widget_Pivot.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
 * Called upon creation
 * Use as constructor
 * Access available configurations with `this.opts()`
 * Encapsulate all events within the current widget
 * element: `this.element()`.
 *
 *
 * @see Component_Manager.render()
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Pivot.prototype.init = function ()
{
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-pivot-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel js-pivot-placeholder',
        start:       function (event, ui) {
            var $heading     = ui.item.children('.panel-heading'),
                $collapsible = $heading.find('[data-toggle="collapse"]');

            if (!$collapsible.hasClass('collapsed')) {
                ui.item.children('.panel-collapse').collapse('hide');
            }
        }
    }).disableSelection();

    this.listeners();
    return this;
};

/**
 * Check if the widget has something a dirty state that needs to be saved.
 * @return Boolean     Widget dirty of not.
 */
Charcoal.Admin.Widget_Pivot.prototype.is_dirty = function ()
{
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Pivot_Widget Chainable.
 */
Charcoal.Admin.Widget_Pivot.prototype.set_dirty_state = function (bool)
{
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Pivot.prototype.listeners = function ()
{
    // Scope
    var that = this;

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.pivots', '.js-add-pivot', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var title = $(this).data('title') || 'Edit';
            that.create_pivot_dialog(type, title, 0, function (response) {
                if (response.success) {
                    response.obj.id = response.obj_id;

                    that.add(response.obj);
                    that.create_pivot(function () {
                        that.reload();
                    });
                }
            });
        })
        .on('click.charcoal.pivots', '.js-pivot-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title: 'Are you certain that you want to remove this item?'
                        },
                        function () {
                            that.remove_pivot(_this.data('id'), function () {
                                that.reload();
                            });
                        }
                    );
                    break;
            }
        });
};

Charcoal.Admin.Widget_Pivot.prototype.create_pivot_dialog = function (type, title, id, cb)
{
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    var data = {
        title:          title,
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quickForm',
        widget_options: {
            obj_type: type,
            obj_id:   id
        }
    };
    this.dialog(data, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id:   response.widget_id,
                type: 'charcoal/admin/widget/quick-form',
                data: {
                    obj_type: type
                },
                obj_id: id,
                save_callback: function (response) {
                    cb(response);
                    BootstrapDialog.closeAll();
                }
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.add = function (obj)
{
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);
    var $template = this.element().find('.js-pivot-template').clone();
    $template.find('.js-pivot').data('id', obj.id).data('type', obj.type);
    this.element().find('.js-pivot-sortable').find('.js-grid-container').append($template);
    return this;

};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.save = function ()
{
    if (this.is_dirty()) {
        return false;
    }

    // Create create_pivot from current list.
    this.create_pivot();
};

Charcoal.Admin.Widget_Pivot.prototype.create_pivot = function (cb)
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type: opts.data.obj_type,
        obj_id: opts.data.obj_id,
        target_object_type: opts.data.target_object_type,
        pivots: []
    };

    this.element().find('.js-pivot-container').find('.js-pivot').each(function (i)
    {
        var $this = $(this);
        var id = $this.data('id');

        data.pivots.push({
            target_object_id: id,
            position: i
        });
    });

    $.post('pivot/create', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * [remove_pivot description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.remove_pivot = function (id, cb)
{
    if (!id) {
        return false;
    }

    // Scope
    var that = this;
    var data = {
        pivot_id: id
    };

    $.post('pivot/remove', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * Widget options as output by the widget itself.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.widget_options = function ()
{
    return this.opts('widget_options');
};
;/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts) {
    this.widget_type       = 'charcoal/admin/widget/quick-form';
    this.save_callback     = opts.save_callback || '';
    this.cancel_callback   = opts.cancel_callback || '';
    this.form_working      = false;
    this.suppress_feedback = false;
    this.is_new_object     = false;
    this.xhr               = null;
    this.obj_id            = Charcoal.Admin.parseNumber(opts.obj_id) || 0;

    return this;
};
Charcoal.Admin.Widget_Quick_Form.prototype             = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Quick_Form.prototype.constructor = Charcoal.Admin.Widget_Quick_Form;
Charcoal.Admin.Widget_Quick_Form.prototype.parent      = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Quick_Form.prototype.init = function () {
    this.bind_events();
};

Charcoal.Admin.Widget_Quick_Form.prototype.bind_events = function () {
    var that = this;
    $(document).on('submit', '#' + this.id(), function (e) {
        e.preventDefault();
        that.submit_form(this);
    });
    $('#' + this.id()).on(
        'click.charcoal.bs.dialog',
        '[data-dismiss="dialog"]',
        function (event) {
            if ($.isFunction(that.cancel_callback)) {
                that.cancel_callback(event);
            }
        }
    );
};

Charcoal.Admin.Widget_Quick_Form.prototype.submit_form = function (form) {
    if (this.form_working) {
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    var $trigger, $form, form_data;

    $form    = $(form);
    $trigger = $form.find('[type="submit"]');

    if ($trigger.prop('disabled')) {
        return false;
    }

    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    form_data = new FormData(form);

    this.disable_form($form, $trigger);

    this.xhr = $.ajax({
        type: 'POST',
        url: this.request_url(),
        data: form_data,
        dataType: 'json',
        processData: false,
        contentType: false,
    });

    this.xhr
        .then($.proxy(this.request_done, this, $form, $trigger))
        .done($.proxy(this.request_success, this, $form, $trigger))
        .fail($.proxy(this.request_failed, this, $form, $trigger))
        .always($.proxy(this.request_complete, this, $form, $trigger));
};

Charcoal.Admin.Widget_Quick_Form.prototype.disable_form = Charcoal.Admin.Widget_Form.prototype.disable_form;

Charcoal.Admin.Widget_Quick_Form.prototype.enable_form = Charcoal.Admin.Widget_Form.prototype.enable_form;

Charcoal.Admin.Widget_Quick_Form.prototype.request_url = Charcoal.Admin.Widget_Form.prototype.request_url;

Charcoal.Admin.Widget_Quick_Form.prototype.request_done = Charcoal.Admin.Widget_Form.prototype.request_done;

Charcoal.Admin.Widget_Quick_Form.prototype.request_failed = Charcoal.Admin.Widget_Form.prototype.request_failed;

Charcoal.Admin.Widget_Quick_Form.prototype.request_complete = Charcoal.Admin.Widget_Form.prototype.request_complete;

Charcoal.Admin.Widget_Quick_Form.prototype.request_success = function ($form, $trigger, response/* ... */) {
    if (response.feedbacks) {
        Charcoal.Admin.feedback().add_data(response.feedbacks);
    }

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

    if (typeof this.save_callback === 'function') {
        this.save_callback(response);
    }
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

    if (typeof widget.pagination !== 'undefined') {
        widget.pagination.page = 1;
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
    this.filters = {};
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
                widget_type: 'charcoal/admin/widget/objectForm',
                widget_options: {
                    obj_type: that.obj_type,
                    obj_id: 0
                }
            };

        $.post(url, data, function (response) {
            var dlg = BootstrapDialog.show({
                    title:   'Quick Create',
                    message: '…',
                    nl2br:   false
                });

            dlg.getModalBody().on(
                'click.charcoal.bs.dialog',
                '[data-dismiss="dialog"]',
                { dialog: dlg },
                function (event) {
                    event.data.dialog.close();
                }
            );

            if (response.success) {
                dlg.setMessage(response.widget_html);
            } else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        }, 'json');

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
        }, 'json');

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

    $('tbody.js-sortable').sortable({
        cursor: 'ns-resize',
        delay: 150,
        distance: 5,
        opacity: 0.75,
        containment: 'parent',
        placeholder: 'ui-tablesort-placeholder',
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
            });
            return ui;
        },
        change: function (e, ui) {
            // Update UI with position
            console.debug(e, ui);
        },
        update: function (e, ui) {
            console.debug(e, ui);
            var rows = $(this).sortable('toArray', {
                attribute: 'data-id'
            });

            var data = {
                obj_type: that.obj_type,
                obj_orders: rows,
                starting_order: 1
            };
            var url = Charcoal.Admin.admin_url() + 'object/reorder';
            $.ajax({
                method: 'POST',
                url: url,
                data: data,
                dataType: 'json'
            }).done(function (response) {
                console.debug(response);
            });
        }
    }).disableSelection();

    $('.js-page-switch').on('click', function (e) {
        e.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');
        that.pagination.page = page_num;
        that.reload();
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
    var title          = opts.title || '',
        type           = opts.type || BootstrapDialog.TYPE_PRIMARY,
        size           = opts.size || BootstrapDialog.SIZE_NORMAL,
        widget_type    = opts.widget_type,
        widget_options = opts.widget_options || {};

    if (!widget_type) {
        return;
    }

    BootstrapDialog.show({
        title:   title,
        type:    type,
        size:    size,
        nl2br:   false,
        message: function (dialog) {
            var url  = Charcoal.Admin.admin_url() + 'widget/load',
                data = {
                    widget_type: widget_type,
                    widget_options: widget_options
                },
                $message = $('<div>Loading…</div>');

            dialog.getModalBody().on(
                'click.charcoal.bs.dialog',
                '[data-dismiss="dialog"]',
                { dialog: dialog },
                function (event) {
                    event.data.dialog.close();
                }
            );

            $.ajax({
                method:   'POST',
                url:      url,
                data:     data,
                dataType: 'json'
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
        widget_type:    'charcoal/admin/widget/objectForm',
        widget_options: {
            obj_type:   this.obj_type,
            obj_id:     this.obj_id
        }
    };

    $.post(this.load_url, data, function (response) {
        var dlg = BootstrapDialog.show({
            title:   'Quick Edit',
            message: '…',
            nl2br:   false
        });

        dlg.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dlg },
            function (event) {
                event.data.dialog.close();
            }
        );

        if (response.success) {
            dlg.setMessage(response.widget_html);
        } else {
            dlg.setType(BootstrapDialog.TYPE_DANGER);
            dlg.setMessage('Error');
        }
    }, 'json');
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
    }, 'json');
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.delete_object = function ()
{
    var that = this;

    BootstrapDialog.confirm({
        title: 'Confirmer la suppression',
        type: BootstrapDialog.TYPE_DANGER,
        message:'Êtes-vous sûr de vouloir supprimer cet objet? Cette action est irréversible.',
        btnOKLabel: 'Supprimer',
        btnCancelLabel: 'Annuler',
        callback: function (result) {
            if (result) {
                var url = that.delete_url;
                var data = {
                    obj_type: that.obj_type,
                    obj_id: that.obj_id
                };
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    //console.debug(response);
                    if (response.success) {
                        $(that.element).remove();
                    } else {
                        window.alert('Erreur. Impossible de supprimer cet objet.');
                    }
                });
            }
        }
    });

};

;Charcoal.Admin.Widget_Wysiwyg = function ()
{
    $('.js-wysiwyg').summernote({
        height: 300
    });
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

/**
 * Error handling
 * @param  {Mixed} data  Could be a simple message, an array, wtv.
 * @return {thisArg}     Chainable.
 */
Charcoal.Admin.Property.prototype.error = function (data)
{
    window.console.error(data);
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
;/**
* Color picker
*
* Require
* - jquery-minicolors
*/

Charcoal.Admin.Property_Input_Colorpicker = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/colorpicker';

    // Property_Input_Colorpicker properties
    this.input_id = null;
    this.colorpicker_options = null;

    this.set_properties(opts);
    this.create_colorpicker();
};

Charcoal.Admin.Property_Input_Colorpicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Colorpicker.prototype.constructor = Charcoal.Admin.Property_Input_Colorpicker;
Charcoal.Admin.Property_Input_Colorpicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Colorpicker.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.colorpicker_options = opts.data.colorpicker_options || this.colorpicker_options;

    var default_opts = {
        format: 'hex',
        letterCase: 'uppercase',
        opacity: false,
        theme: 'bootstrap'
    };

    this.colorpicker_options = $.extend({}, default_opts, this.colorpicker_options);

    return this;
};

Charcoal.Admin.Property_Input_Colorpicker.prototype.create_colorpicker = function ()
{
    $('#' + this.input_id).minicolors(this.colorpicker_options);
};
;/**
* DateTime picker that manages datetime properties
* charcoal/admin/property/input/datetimepicker
*
* Require:
* - eonasdan-bootstrap-datetimepicker
*
* @param  {Object}  opts  Options for input property
*/

Charcoal.Admin.Property_Input_DateTimePicker = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/datetimepicker';

    // Property_Input_DateTimePicker properties
    this.input_id = null;
    this.datetimepicker_selector = null;
    this.datetimepicker_options = null;

    this.set_properties(opts).create_datetimepicker();
};
Charcoal.Admin.Property_Input_DateTimePicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_DateTimePicker.prototype.constructor = Charcoal.Admin.Property_Input_DateTimePicker;
Charcoal.Admin.Property_Input_DateTimePicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_DateTimePicker.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.datetimepicker_selector = opts.data.datetimepicker_selector || this.datetimepicker_selector;
    this.datetimepicker_options = opts.data.datetimepicker_options || this.datetimepicker_options;

    var default_opts = {

    };

    this.datetimepicker_options = $.extend({}, default_opts, this.datetimepicker_options);

    return this;
};

Charcoal.Admin.Property_Input_DateTimePicker.prototype.create_datetimepicker = function ()
{
    $(this.datetimepicker_selector).datetimepicker(this.datetimepicker_options);
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

Charcoal.Admin.Property_Input_DualSelect = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/dualselect';

    // Property_Input_DualSelect properties
    this.input_id = null;
    this.dualinput_options = {};
    this._dualselect = null;

    this.set_properties(opts).init();
};
Charcoal.Admin.Property_Input_DualSelect.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_DualSelect.prototype.constructor = Charcoal.Admin.Property_Input_DualSelect;
Charcoal.Admin.Property_Input_DualSelect.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.init = function ()
{
    this.create_dualinput();
};

Charcoal.Admin.Property_Input_DualSelect.prototype.set_properties = function (opts)
{
    this.input_id = opts.id || this.input_id;
    this.dualinput_options = opts.dualinput_options || opts.data.dualinput_options || this.dualinput_options;

    var id = '#' + this.input_id;

    var default_options = {
        keepRenderingSort: false
    };

    if (opts.data.dualinput_options.searchable) {
        this.dualinput_options.search = {
            left: id + '_searchLeft',
            right: id + '_searchRight'
        };
    }

    this.dualinput_options = $.extend({}, default_options, this.dualinput_options);
    return this;
};

Charcoal.Admin.Property_Input_DualSelect.prototype.create_dualinput = function ()
{
    $('#' + this.input_id).multiselect(this.dualinput_options);
};

/**
 * Sets the dualselect into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} dualselect The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.set_dualselect = function (dualselect)
{
    this._dualselect = dualselect;
    return this;
};

/**
 * Returns the dualselect object
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.dualselect = function ()
{
    return this._dualselect;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.destroy = function ()
{
    var dualselect = this.dualselect();

    if (dualselect) {
        dualselect.remove();
    }
};
;/**
 * Upload File Property Control
 */

Charcoal.Admin.Property_Input_File = function (opts)
{
    this.EVENT_NAMESPACE = '.charcoal.property.file';
    this.input_type = 'charcoal/admin/property/input/file';

    this.opts = opts;
    this.data = opts.data;
    this.dialog = null;

    // Required
    this.set_input_id(this.opts.id);

    // Run the plugin or whatever is necessary
    this.init();

    return this;
};

Charcoal.Admin.Property_Input_File.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_File.prototype.constructor = Charcoal.Admin.Property_Input_File;
Charcoal.Admin.Property_Input_File.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_File.prototype.init = function ()
{
    // console.log('Init', this.input_type);

    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input   = $('#' + this.input_id);
    this.$file    = this.$input.find('input[type="file"]');
    this.$hidden  = this.$input.find('input[type="hidden"]');
    this.$preview = this.$input.find('.js-preview');

    if (!window.elFinderCallback) {
        window.elFinderCallback = {};
    }

    this.set_listeners();
};

Charcoal.Admin.Property_Input_File.prototype.set_listeners = function ()
{
    // console.log('Events', this.input_type);

    if (typeof this.$input === 'undefined') {
        return this;
    }

    this.$input
        .on('click' + this.EVENT_NAMESPACE, '.js-remove-file', this.remove_file.bind(this))
        .on('click' + this.EVENT_NAMESPACE, '.js-elfinder', this.load_elfinder.bind(this));

    this.$file.on('change' + this.EVENT_NAMESPACE, this.change_file.bind(this));

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);
};

Charcoal.Admin.Property_Input_File.prototype.remove_file = function (event)
{
    // console.log('Remove File');

    event.preventDefault();

    this.$hidden.val('');
    this.$input.find('.form-control-static').empty();
    this.$input.find('.hide-if-no-file').addClass('hidden');
};

Charcoal.Admin.Property_Input_File.prototype.change_file = function (event)
{
    // console.log('Change File');

    var target, file, src;

    target = event.dataTransfer || event.target;
    file   = target && target.files && target.files[0];
    src    = URL.createObjectURL(file);

    this.$input.find('.hide-if-no-file').removeClass('hidden');
    this.$input.find('.form-control-static').html(file);
    this.$preview.empty();
};

Charcoal.Admin.Property_Input_File.prototype.load_elfinder = function (event)
{
    // console.log('Load elFinder');

    event.preventDefault();

    this.dialog = BootstrapDialog.show({
        title:      this.data.dialog_title || '',
        size:       BootstrapDialog.SIZE_WIDE,
        cssClass:  '-elfinder',
        message:   $(
            '<iframe name="' + this.input_id + '-elfinder" width="100%" height="400px" frameborder="0" ' +
            'src="' + this.data.elfinder_url + '"></iframe>'
        )
    });
};

Charcoal.Admin.Property_Input_File.prototype.elfinder_callback = function (file/*, elf */)
{
    // console.group('elFinder Callback (File)');
    // console.log('elFinder', elf);
    // console.log('Selected File', file);

    if (this.dialog) {
        this.dialog.close();
    }

    if (file && file.path) {
        this.$input.find('.hide-if-no-file').removeClass('hidden');
        this.$input.find('.form-control-static').html(file.name);
        this.$hidden.val(decodeURI(file.url).replace(Charcoal.Admin.base_url(), ''));
        this.$preview.empty();
    }
    // console.groupEnd();
};

/**
 * SETTERS
 */
/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_id = function (input_id)
{
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_name = function (input_name)
{
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_val = function (input_val)
{
    this.input_val = input_val;
    return this;
};
;/**
 * Upload Image Property Control
 */

Charcoal.Admin.Property_Input_Image = function (opts)
{
    this.EVENT_NAMESPACE = '.charcoal.property.image';
    this.input_type = 'charcoal/admin/property/input/image';

    this.opts = opts;
    this.data = opts.data;

    // Required
    this.set_input_id(this.opts.id);

    // Run the plugin or whatever is necessary
    this.init();

    return this;
};

Charcoal.Admin.Property_Input_Image.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Image.prototype.constructor = Charcoal.Admin.Property_Input_Image;
Charcoal.Admin.Property_Input_Image.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Image.prototype.remove_file = function (event)
{
    // console.log('Remove Image');

    event.preventDefault();

    this.$hidden.val('');
    this.$preview.empty();
    this.$input.find('.form-control-static').empty();
    this.$input.find('.hide-if-no-file').addClass('hidden');
};

Charcoal.Admin.Property_Input_Image.prototype.change_file = function (event)
{
    // console.log('Change Image');

    var img, target, file, src;

    img = new File();

    target = event.dataTransfer || event.target;
    file   = target && target.files && target.files[0];
    src    = URL.createObjectURL(file);

    img.src = src;

    this.$input.find('.hide-if-no-file').removeClass('hidden');
    this.$input.find('.form-control-static').html(file);
    this.$preview.empty().append(img);
};

Charcoal.Admin.Property_Input_Image.prototype.elfinder_callback = function (file/*, elf */)
{
    // console.group('elFinder Callback (Image)');
    // console.log('elFinder', elf);
    // console.log('Selected File', file);

    if (this.dialog) {
        this.dialog.close();
    }

    if (file && file.path) {
        var $img = $('<img src="' + file.url + '" style="max-width: 100%">');

        this.$input.find('.hide-if-no-file').removeClass('hidden');
        this.$input.find('.form-control-static').html(file.name);
        this.$hidden.val(decodeURI(file.url).replace(Charcoal.Admin.base_url(), ''));
        this.$preview.empty().append($img);
    }
    // console.groupEnd();
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
            '&language=fr&callback=_tmp_google_onload_function&key=' + this.data.data.api_key,
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

    this.element().on('click', '.js-reset', function (e) {
        e.preventDefault();
        that.controller().reset();
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
 * TextExt implementation for Tags inputs
 * charcoal/admin/property/input/text-ext/tags
 *
 * Require:
 * - jQuery
 * - text-ext
 *
 * @param  {Object}  opts Options for input property
 */

Charcoal.Admin.Property_Input_Selectize_Tags = function (opts) {
    this.input_type = 'charcoal/admin/property/input/selectize/tags';

    // Property_Input_Selectize_Tags properties
    this.input_id     = null;
    this.type         = null;
    this.title        = null;
    this.selectize_options = {};
    this._tags        = null;

    this.set_properties(opts).init();
};
Charcoal.Admin.Property_Input_Selectize_Tags.prototype             = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Selectize_Tags.prototype.constructor = Charcoal.Admin.Property_Input_Selectize_Tags;
Charcoal.Admin.Property_Input_Selectize_Tags.prototype.parent      = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init = function () {
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    this.init_selectize();
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.set_properties = function (opts) {
    this.input_id     = opts.id || this.input_id;
    this.type         = opts.obj_type || this.type;
    this.title        = opts.title || this.title;
    this.selectize_options = opts.selectize_options || opts.data.selectize_options || this.selectize_options;

    // var selectedItems = this.tags_initialized();

    var default_opts = {
        plugins: [
            // 'restore_on_backspace',
            'remove_button',
            'drag_drop',
            'item_color'
        ],
        delimiter: ',',
        persist: false,
        preload: true,
        openOnFocus: true,
        create: this.create_tag.bind(this),
        createFilter: function (input) {
            for (var item in this.options) {
                if (this.options[item].text === input) {
                    return false;
                }
            }
            return true;
        },
        load: this.load_tags.bind(this),
        onInitialize: function () {
            var self = this;
            self.sifter.iterator(this.items, function (value) {
                var option = self.options[value];
                var $item = self.getItem(value);
                $item.css('background-color', option.color/*[options.colorField]*/);
            });
        }
    };

    this.selectize_options = $.extend({}, default_opts, this.selectize_options);
    // this.selectize_options.selector = '#' + this.input_id;

    return this;

};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.create_tag = function (input, callback) {

    var type  = this.type;
    var id    = this.id;
    var title = this.title;

    var data = {
        title: title,
        size: BootstrapDialog.SIZE_WIDE,
        cssClass: '-quick-form',
        dialog_options: {
            onhide: function () {
                callback({
                    return: false
                });
            }
        },
        widget_type: 'charcoal/admin/widget/quickForm',
        widget_options: {
            obj_type: type,
            obj_id: id,
            form_data: {
                name: input
            }
        }
    };
    this.dialog(data, function (response) {

        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id: response.widget_id,
                type: 'charcoal/admin/widget/quick-form',
                data: {
                    obj_type: type
                },
                obj_id: id,
                save_callback: function (response) {
                    callback({
                        value: response.obj.id,
                        text: response.obj.name[Charcoal.Admin.lang],
                        color: response.obj.color || '#4D84F1'
                    });
                    BootstrapDialog.closeAll();
                }
            });
        }

    });

};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.load_tags = function (query, callback) {

    var self = this;

    if (!query.length) {
        return callback();
    }
    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: self.type
        },
        type: 'GET',
        error: function () {
            callback();
        },
        success: function (res) {
            var items = [];
            for (var item in res.collection) {
                item = res.collection[item];
                items.push({
                    value: item.id,
                    text: item.name[Charcoal.Admin.lang],
                    color: item.color
                });
            }
            callback(items);
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_selectize = function () {
    var selectize = $('#' + this.input_id).selectize(this.selectize_options);
    console.log(selectize);
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
* Switch looking input that manages boolean properties
* charcoal/admin/property/input/switch
*
* Require:
* - jQuery
* - bootstrapSwitch
*
* @param  {Object}  opts Options for input property
*/

Charcoal.Admin.Property_Input_Text = function (opts)
{
    this.input_type = 'charcoal/admin/property/input/text';
    this.opts = opts;
    this.data = opts.data;

    // Required
    this.set_input_id(this.opts.id);

    // Dispatches the data
    this.set_data(this.data);

    // Run the plugin or whatever is necessary
    this.init();
    return this;
};
Charcoal.Admin.Property_Input_Text.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Text.prototype.constructor = Charcoal.Admin.Property_Input_Text;
Charcoal.Admin.Property_Input_Text.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Set multiple values required
 * @param {Object} data Data passed from the template
 */
Charcoal.Admin.Property_Input_Text.prototype.set_data = function (data)
{
    // Input desc
    this.set_input_name(data.input_name);
    this.set_input_val(data.input_val);

    // Input definition
    this.set_readonly(data.readonly);
    this.set_required(data.required);
    this.set_min_length(data.min_length);
    this.set_max_length(data.max_length);
    this.set_size(data.size);

    // Multiple
    this.set_multiple(data.multiple);
    this.set_multiple_separator(data.multiple_separator);
    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.init = function ()
{
    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input = $('#' + this.input_id);

    if (this.multiple) {
        this.init_multiple();
    }
};

/**
 * When multiple
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.init_multiple = function ()
{
    // New input
    this.chars_new = [13];
    // Check to delete current input
    this.chars_remove = [8, 46];
    // Navigate.
    this.char_next = [40];
    this.char_prev = [38];

    // Add to container.
    // input.wrap('<div></div>');
    this.$container = this.$input.parent('div');

    // OG input keyboard events.
    this.bind_keyboard_events(this.$input);

    // Initial split.
    this.split_val(this.$input);

    return this;
};
/**
 * Split the value with separator
 * If the input is specified, splits relative to the input
 * @param  {String} val  Value
 * @param  {[type]} input [description]
 * @return {thisArg}      Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.split_val = function (input)
{
    var separator = this.multiple_separator;
    input = input || this.$input;
    var val = input.val();

    var split = val.split(separator);
    var i = 0;
    var total = split.length;
    if (total === 1) {
        // Nothing to split.
        return false;
    }

    for (; i < total; i++) {
        if (i === 0) {
            input.val(split[i]);
        } else {
            input = this.insert_item(input, split[i]);
        }
    }

    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.bind_keyboard_events = function (input)
{
    // Scope
    var that = this;

    var chars_new = this.chars_new;
    var chars_remove = this.chars_remove;
    var char_next = this.char_next;
    var char_prev = this.char_prev;

    // Bind the keyboard events
    input.on('keydown', function (e) {

        var keyCode = e.keyCode;
        if (chars_new.indexOf(keyCode) > -1) {
            e.preventDefault();
            that.insert_item($(this));
        }

        if (chars_remove.indexOf(keyCode) > -1) {
            // Delete keys (8 is backspage, 46 is "del")
            if ($(this).val() === '') {
                e.preventDefault();
                that.remove_item($(this));
            }
        }

        if (char_prev.indexOf(keyCode) > -1) {
            e.preventDefault();
            // Up arrow key (Navigate to previous item if it exists)
            $(this).prev('input').focus();
        }
        if (char_next.indexOf(keyCode) > -1) {
            e.preventDefault();
            // Down arrow key
            $(this).next('input').focus();
        }
    });

    input.on('keyup', function () {
        that.split_val($(this));
    });
};

/**
 * Insert a clone relative to an element
 * @param  {jQueryObject} elem      Input element
 * @param  {String|undefined} val   Should we have a value already in that input.
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.insert_item = function (elem, val)
{
    var clone = this.input_clone(val);
    clone.insertAfter(elem);
    this.bind_keyboard_events(clone);
    clone.focus();
    return clone;
};

/**
 * Add an item (append)
 * @param {String|undefined} val    If the input already as a value
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.add_item = function (val)
{
    var clone = this.input_clone(val);
    this.$container.append(clone);
    this.bind_keyboard_events(clone);
    clone.focus();

    return clone;
};
/**
 * Remove specific item
 * Sets focus to the prev item (or next if previous doesn'T exist)
 * Won't remove the LAST input standing.
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item = function (item)
{
    var prev = item.prev('input');
    var next = item.next('input');

    if (!prev.length && !next.length) {
        // Don't remove the last one
        return false;
    }

    if (prev.length) {
        prev.focus();
    } else if (next.length) {
        next.focus();
    }

    this.remove_item_listeners(item);
    item.remove();

    return this;
};
/**
 * Remove listeners from an item
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item_listeners = function (item)
{
    item.off('keydown');
    item.off('keyup');

    return this;
};

/**
 * Create a clone of the OG input
 * @param  {String} val Optional parameter - Value of the input.
 * @return {jQueryObject}     The actual "clone", which isn't really a clone.
 */
Charcoal.Admin.Property_Input_Text.prototype.input_clone = function (val)
{
    var input = this.$input;
    var classes = input.attr('class');
    var min_length = this.min_length;
    var max_length = this.max_length;
    // var size = this.size;
    var required = this.required;
    var readonly = this.readonly;
    var input_name = this.input_name;

    var clone = $('<input type="text" />');

    if (classes) {
        clone.attr('class', classes);
    }
    if (min_length) {
        clone.attr('minlength', min_length);
    }
    if (max_length) {
        clone.attr('maxlength', max_length);
    }
    if (required) {
        clone.attr('required', 'required');
    }
    if (readonly) {
        clone.attr('read_only', 'read_only');
    }
    if (val) {
        clone.val(val);
    }
    clone.attr('name', input_name);

    return clone;
};

/**
 * SETTERS
 */
/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_id = function (input_id)
{
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_name = function (input_name)
{
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_val = function (input_val)
{
    this.input_val = input_val;
    return this;
};

/**
 * Is the input in readOnly mode?
 * @param {Boolean|undefined} readonly Defines if input is in readonly mode or not
 */
Charcoal.Admin.Property_Input_Text.prototype.set_readonly = function (readonly)
{
    if (!readonly) {
        readonly = false;
    }
    this.readonly = readonly;
    return this;
};

/**
 * Is the input required?
 * @param {Boolean|undefined} required Defines if input is required
 */
Charcoal.Admin.Property_Input_Text.prototype.set_required = function (required)
{
    if (!required) {
        required = false;
    }
    this.required = required;
    return this;
};

/**
 * The input min length
 * @param {Integer} min_length Min length of the input.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_min_length = function (min_length)
{
    if (!min_length) {
        min_length = 0;
    }
    this.min_length = min_length;
    return this;
};

/**
 * The input max length
 * @param {Integer} max_length Max length of the input.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_max_length = function (max_length)
{
    if (!max_length) {
        max_length = 0;
    }
    this.max_length = max_length;
    return this;
};

/**
 * Size of the input
 * @param {Integer} size Not sure about this one.
 */
Charcoal.Admin.Property_Input_Text.prototype.set_size = function (size)
{
    if (!size) {
        size = 0;
    }
    this.size = size;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple = function (multiple)
{
    if (!multiple) {
        multiple = false;
    }
    this.multiple = multiple;
    return this;
};
/**
 * Multiple separator
 * @param {String} separator Multiple separator || undefined.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_separator = function (separator)
{
    if (!separator) {
        // Default
        separator = ',';
    }
    this.multiple_separator = separator;
    return this;
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
    this.data = opts.data;

    this.editor_options = null;
    this._editor = null;

    if (!window.elFinderCallback) {
        window.elFinderCallback = {};
    }

    this.set_properties(opts);
    this.init();
};
Charcoal.Admin.Property_Input_Tinymce.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Tinymce.prototype.constructor = Charcoal.Admin.Property_Input_Tinymce;
Charcoal.Admin.Property_Input_Tinymce.prototype.parent = Charcoal.Admin.Property.prototype;

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.init = function ()
{
    this.create_tinymce();
};

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.base_url = function ()
{
    return Charcoal.Admin.base_url() + 'assets/admin/scripts/vendors/tinymce';
};

Charcoal.Admin.Property_Input_Tinymce.prototype.set_properties = function (opts)
{
    this.input_id = opts.input_id || this.input_id;
    this.editor_options = opts.editor_options || opts.data.editor_options || this.editor_options;

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);

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
            'charcoal',
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
        entity_encoding:'raw',

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
        document_base_url: Charcoal.Admin.base_url(),
        relative_urls: true,
        remove_script_host: false,

        // Plugins options
        autoresize_min_height: '150px',
        autoresize_max_height: '400px',
        //code_dialog_width: '400px',
        //code_dialog_height: '400px',
        contextmenu: 'link image inserttable | cell row column deletetable',

        file_picker_callback: $.proxy(this.elfinder_browser, null, this),
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
        root_lang_attr: $('#' + this.input_id).closest('[data-lang]').data('lang'),
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
    // Scope
    var that = this;

    if (typeof window.tinyMCE !== 'object') {
        var url = this.base_url() + '/tinymce.min.js';
        Charcoal.Admin.loadScript(url, this.create_tinymce.bind(this));

        return this;
    }

    window.tinyMCE.dom.Event.domLoaded = true;
    window.tinyMCE.baseURI = new window.tinyMCE.util.URI(this.base_url());
    window.tinyMCE.baseURL = this.base_url();
    window.tinyMCE.suffix  = '.min';

    // This would allow us to have custom features to each tinyMCEs instances
    //
    if (!window.tinyMCE.PluginManager.get(this.input_id)) {
        // Means we need to instanciate the self plugin now.
        window.tinyMCE.PluginManager.add(this.input_id, function (editor) {
            that.set_editor(editor);
        });

        if ($.type(this.editor_options.plugins) !== 'array') {
            this.editor_options.plugins = [];
        }

        this.editor_options.plugins.push(this.input_id);
    }

    window.tinyMCE.init(this.editor_options);
};

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_callback = function (file, elf)
{
    // pass selected file data to TinyMCE
    parent.tinyMCE.activeEditor.windowManager.getParams().oninsert(file, elf);
    parent.tinyMCE.activeEditor.windowManager.close();
};

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_browser = function (control, callback, value, meta)
{
    var editor = this;

    window.tinyMCE.activeEditor.windowManager.open({
        file:      control.data.elfinder_url + '&' + $.param(meta),
        title:     control.data.dialog_title || '',
        width:     900,
        height:    450,
        resizable: 'yes'
    }, {
        oninsert: function (file, elf) {
            var url, regex, alias, selected;

            // URL normalization
            url = file.url;
            regex = /\/[^/]+?\/\.\.\//;
            while (url.match(regex)) {
                url = url.replace(regex, '/');
            }

            selected = editor.selection.getContent();

            if (selected.length === 0 && editor.selection.getNode().nodeName === 'A') {
                selected = editor.selection.getNode().textContent;
            }

            // Generate a nice file info
            alias = file.name + ' (' + elf.formatSize(file.size) + ')';

            // Provide file and text for the link dialog
            if (meta.filetype === 'file') {
                callback(url, { text: (selected || alias), title: alias });
            }

            // Provide image and alt text for the image dialog
            if (meta.filetype === 'image') {
                callback(url, { alt: alias });
            }

            // Provide alternative source and posted for the media dialog
            if (meta.filetype === 'media') {
                callback(url);
            }
        }
    });

    return false;
};

/**
 * Sets the editor into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} editor The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.set_editor = function (editor)
{
    this._editor = editor;
    return this;
};

/**
 * Returns the editor object
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.editor = function ()
{
    return this._editor;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.destroy = function ()
{
    var editor = this.editor();

    if (editor) {
        editor.remove();
    }
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
                    title:   'Login error',
                    message: 'Authentication failed. Please try again.',
                    type:    BootstrapDialog.TYPE_DANGER
                });
            }
        }, 'json').fail(function () {
            //window.alert('Error');
            BootstrapDialog.show({
                title:   'Login error',
                message: 'Authentication failed. Please try again.',
                type:    BootstrapDialog.TYPE_DANGER
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
