/**
 * Charcoal Feedback Manager
 *
 * Class that deals with all the feedbacks throughout the admin
 * Feedbacks uses the LEVEL concept which could be:
 * - `success`
 * - `warning`
 * - `error`
 *
 * It uses BootstrapDialog to display all of this.
 */

;(function ($, Admin, document, undefined) {
    'use strict';

    var lvls, defs, alts, arr = [], reset = function () {
        lvls = DEFAULTS.supported.slice();
        defs = $.extend({}, DEFAULTS.definitions);
        alts = $.extend({}, DEFAULTS.aliases);
    };

    var DEFAULTS = {
        supported: [ 'success', 'info', 'notice', 'warning', 'error' ],
        definitions: {
            success: {
                title: (Admin.lang('fr') ? 'Succès!' : 'Success!'),
                type:  BootstrapDialog.TYPE_SUCCESS
            },
            notice: {
                title: (Admin.lang('fr') ? 'Notice!' : 'Notice!'),
                type:  BootstrapDialog.TYPE_INFO,
                alias: [ 'info' ]
            },
            warning: {
                title: (Admin.lang('fr') ? 'Attention!' : 'Attention!'),
                type:  BootstrapDialog.TYPE_WARNING
            },
            error: {
                title: (Admin.lang('fr') ? 'Une erreur s\'est produite!' : 'An error occurred!'),
                type:  BootstrapDialog.TYPE_DANGER
            }
        },
        aliases: {
            info: 'notice'
        }
    };

    /**
     * Create a new feedback manager.
     *
     * @class
     */
    var Manager = function ()
    {
        this.reset();

        if (arguments.length) {
            this.push.apply(this, arguments);
        }

        return this;
    };

    Manager.prototype.validContext = function (context) {
        return ($.type(context) === 'string');
    };

    Manager.prototype.parseContext = function (context) {
        if ($.type(context) === 'undefined') {
            context = 'global';
        } else {
            var type = $.type(context);
            if (type !== 'string') {
                throw new TypeError('Storage key must be a string, received ' + type);
            }
        }

        if (context in this.storage) {
            return context;
        } else {
            throw new TypeError('Invalid key, received ' + context);
        }
    };

    /**
     * Resolve the aliases for the given level.
     *
     * @param  {string} level - A feedback level to resolve.
     * @return {this}
     */
    Manager.prototype.resolveAliases = function (level)
    {
        if ($.inArray(level, lvls) === -1) {
            throw new TypeError(
                'Unsupported feedback level, received "' + level +
                '". Must be one of: ' + lvls.join(', ')
            );
        }

        var key = level;
        level = defs[level];
        for (var alias, i = level.alias.length - 1; i >= 0; i--) {
            alias = level.alias[i];

            alts[alias] = key;
        }

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
     *
     * @param  {mixed}  [entries]
     * @param  {string} [context]
     * @return this
     */
    Manager.prototype.push = function (/* context, entries */)
    {
        var context = arguments[0];
        var entries = arguments;

        if (this.validContext(context)) {
            entries = arr.slice.call(arguments, 1);
        } else {
            context = 'global';
        }

        for (var entry, i = 0; i < entries.length; i++) {
            entry = entries[i];

            if ($.type(entry) === 'array') {
                this.push.apply(this, [ context ].concat(entry));
                continue;
            }

            if (($.type(entry) === 'object') && !(entry instanceof Entry)) {
                entry = Entry.createFromObject(entry);
            }

            if (entry instanceof Entry) {
                this.storage/*[context]*/.push(entry);
            }
        }

        return this;
    };

    /** @deprecated in favor of Manager.prototype.push() */
    Manager.prototype.add_data = Manager.prototype.push;

    /**
     * Get Messages
     *
     * @param  {string} [key] - The key to get the messages from.
     * @return {array}  Messages to show.
     */
    Manager.prototype.getMessages = function (/* key */) {
        /*
        key = this.parseContext(key);
        return this.storage[key];
        */
        return this.storage;
    };

    /**
     * Count Messages
     *
     * @param  {string}  [key] - The key to get the messages from.
     * @return {integer} The number of messages.
     */
    Manager.prototype.countMessages = function (/* key */) {
        /*
        key = this.parseContext(key);
        return this.storage[key].length;
        */
        return this.storage.length;
    };

    /**
     * Has Messages
     *
     * @param  {string}  [key] - The key to get the messages from.
     * @return {boolean} Whether messages have been set or not.
     */
    Manager.prototype.hasMessages = function (/* key */) {
        /*
        return this.countMessages(key) > 0;
        */
        return this.countMessages() > 0;
    };

    /**
     * Retrieve the list of supported feedback levels.
     *
     * @return {array}
     */
    Manager.prototype.availableLevels = function ()
    {
        return lvls;
    };

    /**
     * Retrieve the feedback level definitions.
     *
     * @return {object}
     */
    Manager.prototype.levels = function ()
    {
        return defs;
    };

    /**
     * Retrieve the feedback level definitions.
     *
     * @return {object}
     */
    Manager.prototype.level = function (key)
    {
        return defs[key] || null;
    };

    /**
     * Replace the level definitions set with the given parameters.
     *
     * @param  {object} [config] - New definitions.
     * @return {this}
     */
    Manager.prototype.setLevels = function (config)
    {
        var type = $.type(config);
        if (type !== 'object') {
            throw new TypeError('Level(s) must be an associative array, received ' + type);
        }

        for (var key in config) {
            if (!$.inArray(key, lvls)) {
                lvls.push(key);
            }

            if ('aliases' in config[key]) {
                config[key].alias = config[key].aliases;
                delete config[key].aliases;
            }

            defs[key] = $.extend({}, DEFAULTS.definitions[key] || {}, config[key]);

            if (config[key].alias) {
                this.resolveAliases(key);
            }
        }

        return this;
    };

    /**
     * Merge given parameters into the level definitions.
     *
     * @param  {object} [config] - New definitions.
     * @return {this}
     */
    Manager.prototype.mergeLevels = function (config)
    {
        var type = $.type(config);
        if (type !== 'object') {
            throw new TypeError('Level(s) must be an associative array, received ' + type);
        }

        for (var key in config) {
            if (!$.inArray(key, lvls)) {
                lvls.push(key);
            }

            if ('aliases' in config[key]) {
                config[key].alias = config[key].aliases;
                delete config[key].aliases;
            }

            defs[key] = $.extend({}, DEFAULTS.definitions[key] || {}, defs[key] || {}, config[key]);

            if (config[key].alias) {
                this.resolveAliases(key);
            }
        }

        return this;
    };

    /** @deprecated */
    Manager.prototype.add_context = function (context) {
        if (!context) {
            return this;
        }

        if (typeof context.name === 'undefined' || typeof context.title === 'undefined') {
            return this;
        }

        defs[ context.name ] = context;
        // for (var k in context) {
        //     if (typeof context[ k ].title === 'undefined') {
        //         // WRONG
        //         return this;
        //         break;
        //     }
        // }

        return this;
    };

    /** @deprecated */
    Manager.prototype.add_context_alias = function (alias, context) {
        if (!alias || !context || !defs[ context ]) {
            return this;
        }

        alts[ alias ] = context;

        return this;
    };

    /**
     * Actions in the dialog box
     */
    Manager.prototype.add_action = function (opts)
    {
        this.actions.push(opts);
    };

    /**
     * Dispatch the results of all feedback accumulated.
     *
     * @param  {string} [key] - The key to get the messages from.
     * @return this
     */
    Manager.prototype.dispatch = function (/* key */)
    {
        if (!this.hasMessages(/* key */)) {
            return this;
        }

        var key, entry, level, buttons;
        var entries = this.getMessages(/* key */);
        var grouped = {};
        for (var i = 0; i < entries.length; i++) {
            entry = entries[i];
            key   = entry.level();

            if (!(key in grouped)) {
                grouped[key] = [];
            }

            grouped[key].push(entry);
        }

        for (key in grouped) {
            level   = this.level(key);
            buttons = [];
            if (this.actions.length) {
                for (var action, k = 0; k < this.actions.length; k++) {
                    action = this.actions[k];
                    buttons.push({
                        label:  action.label,
                        action: action.callback
                    });
                }
            }

            BootstrapDialog.show({
                title:   level.title,
                message: '<p>' + grouped[key].join('</p><p>') + '</p>',
                type:    level.type,
                buttons: buttons
            });
        }

        this.empty();

        return this;
    };

    /** @deprecated in favor of Manager.prototype.dispatch() */
    Manager.prototype.call = Manager.prototype.dispatch;

    Manager.prototype.empty = function ()
    {
        reset();

        this.actions = [];
        this.storage = []/*{
            global: []
        }*/;
    };

    /** @deprecated in favor of Manager.prototype.empty() */
    Manager.prototype.reset = Manager.prototype.empty;

    /**
     * Single Feedback Message
     *
     * @param {String} [level]   - The feedback level.
     * @param {String} [message] - The feedback message.
     */
    var Entry = function (level, message) {
        // Initialize the feedback manager
        Admin.feedback();

        if (this.validLevel(level)) {
            this.setLevel(level);
        } else {
            throw new TypeError(
                'Feedback level required. Must be one of: ' + lvls.join(', ')
            );
        }

        if (this.validMessage(message)) {
            this.setMessage(message);
        }

        return this;
    };

    Entry.createFromObject = function (obj) {
        var level   = obj.level || null;
        var message = obj.message || obj.msg || null;

        if (!level && !message) {
            return null;
        }

        return new Entry(level, message);
    };

    Entry.prototype = {
        toString: function () {
            return this.message();
        },

        level: function () {
            return this._level || null;
        },

        setLevel: function (level) {
            var type = $.type(level);
            if (type !== 'string') {
                throw new TypeError('Feedback level must be a string, received ' + type);
            }

            if ($.inArray(level, lvls) === -1) {
                throw new TypeError(
                    'Unsupported feedback level, received "' + level +
                    '". Must be one of: ' + lvls.join(', ')
                );
            }

            if (level in alts) {
                level = alts[level];
            }

            this._level = level;

            return this;
        },

        validLevel: function (level) {
            return ($.type(level) === 'string' && $.inArray(level, lvls) > -1);
        },

        message: function () {
            return this._message || null;
        },

        setMessage: function (message) {
            var type = $.type(message);
            if (type !== 'string') {
                throw new TypeError('Feedback message must be a string, received ' + type);
            }

            this._message = message;

            return this;
        },

        validMessage: function (message) {
            return ($.type(message) === 'string');
        }
    };

    reset();

    /**
     * Public Interface
     */

    Admin.Feedback      = Manager;
    Admin.FeedbackEntry = Entry;

}(jQuery, Charcoal.Admin, document));
