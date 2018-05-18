/* globals commonL10n */
/**
 * Charcoal Feedback Manager
 *
 * Class that deals with all the feedbacks throughout the admin
 * Feedbacks uses the LEVEL concept which could be:
 * - `success`
 * - `warning`
 * - `error`
 */

;(function ($, Admin, document, undefined) {
    'use strict';

    var lvls, defs, alts, arr = [], reset = function () {
        lvls = DEFAULTS.supported.slice();
        defs = $.extend({}, DEFAULTS.definitions);
        alts = $.extend({}, DEFAULTS.aliases);
    };

    var DEFAULTS = {
        supported: [ 'success', 'info', 'notice', 'warning', 'error', 'danger' ],
        definitions: {
            success: {
                title:   commonL10n.success,
                display: 'notification',
                type:    BootstrapDialog.TYPE_SUCCESS
            },
            notice: {
                title:   commonL10n.notice,
                display: 'notification',
                type:    BootstrapDialog.TYPE_INFO,
                alias:   [ 'info' ]
            },
            warning: {
                title:   commonL10n.warning,
                display: 'dialog',
                type:    BootstrapDialog.TYPE_WARNING
            },
            error: {
                title:   commonL10n.errorOccurred,
                display: 'dialog',
                type:    BootstrapDialog.TYPE_DANGER,
                alias:   [ 'danger' ]
            }
        },
        aliases: {
            info: 'notice',
            danger: 'error'
        }
    };

    /**
     * Create a new feedback manager.
     *
     * @class
     */
    var Manager = function ()
    {
        this.empty();

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
     *   { 'level': 'success', 'message': 'Good job!' },
     *   { 'level': 'success', 'message': 'Good job!' }
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
     * @return this
     */
    Manager.prototype.push = function ()
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
                this.storage.push(entry);
            }
        }

        return this;
    };

    /**
     * Get Messages
     *
     * @return {array}  Messages to show.
     */
    Manager.prototype.getMessages = function () {
        return this.storage;
    };

    /**
     * Count Messages
     *
     * @return {integer} The number of messages.
     */
    Manager.prototype.countMessages = function () {
        return this.storage.length;
    };

    /**
     * Has Messages
     *
     * @return {boolean} Whether messages have been set or not.
     */
    Manager.prototype.hasMessages = function () {
        return this.countMessages() > 0;
    };

    /**
     * Get all messages grouped by level
     *
     * @example
     * {
     *     '<level>': [ <messages> ]
     * }
     *
     * @return {object} Messages to show.
     */
    Manager.prototype.getMessagesMap = function () {
        if (!this.hasMessages()) {
            return {};
        }

        var key, entry;
        var entries = this.getMessages();
        var grouped = {};
        for (var i = 0; i < entries.length; i++) {
            entry = entries[i];
            key   = entry.level();

            if (!(key in grouped)) {
                grouped[key] = [];
            }

            grouped[key].push(entry);
        }

        return grouped;
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
     * @return this
     */
    Manager.prototype.dispatch = function ()
    {
        if (!this.hasMessages()) {
            return this;
        }

        var key, level, buttons;
        var grouped = this.getMessagesMap();

        for (key in grouped) {
            level       = this.level(key);
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

            var config = {
                title:   level.title,
                message: '<p class="mb-0">' + grouped[key].join('</p><p class="mb-0 mt-3">') + '</p>',
                level:   key,
                type:    level.type,
                buttons: buttons
            }

            switch (level.display) {
                case 'notification':
                    config.dismissible = buttons.length === 0;
                    new Notification(config);
                    break;

                case 'dialog':
                    /* falls through */
                default:
                    BootstrapDialog.show(config);
                    break;
            }
        }

        this.empty();

        return this;
    };

    /**
     * Reset feedback storages.
     */
    Manager.prototype.empty = function ()
    {
        reset();

        this.actions = [];
        this.storage = [];
    };

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
        var message = obj.message || null;

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
            var vartype = $.type(level);
            if (vartype !== 'string') {
                throw new TypeError('Feedback level must be a string, received ' + vartype);
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

    /**
     * Notification Component (extends Entry)
     */
    var Notification = function (config) {
        var vartype = $.type(config);
        if (vartype !== 'object') {
            throw new TypeError('Notification config must be an associative array, received ' + type);
        }

        if (this.validLevel(config.level)) {
            this.setLevel(config.level);
        } else {
            throw new TypeError(
                'Feedback level required. Must be one of: ' + lvls.join(', ')
            );
        }

        if (this.validMessage(config.message)) {
            this.setMessage(config.message);
        }

        this.config = $.extend({}, {
            id:    BootstrapDialog.newGuid(),
            delay: 3200
        }, config);

        this.$elem = $('<article class="c-notifications_item alert fade show" role="alert"></article>');
        this.$elem.prop('id', this.config.id);
        this.$elem.addClass('alert-' + this.config.level);

        if (this.config.dismissible) {
            this.$elem.addClass('alert-dismissible');
            var $button = $('<button type="button" class="close" data-dismiss="alert" aria-label="'+commonL10n.close+'"><span aria-hidden="true">&times;</span></button>');
            this.$elem.append($button);
        }

        if (this.config.message) {
            var $content = $('<div class="alert-body"></div>');
            $content.html('').append(this.config.message);
            this.$elem.append($content);
        }

        this.$elem.appendTo('.c-notifications').addClass('show');

        this.$elem.on('mouseover.charcoal.feedback', { notification: this }, function (event) {
            window.clearTimeout(event.data.notification.closeTimer);
        });

        this.$elem.on('mouseout.charcoal.feedback', { notification: this }, function (event) {
            var notification = event.data.notification;
            notification.closeTimer = window.setTimeout(function() {
                notification.$elem.alert('close');
            }, notification.config.delay);
        });

        this.$elem.on('closed.bs.alert', { notification: this }, function (event) {
            event.data.notification.$elem.off('.charcoal.feedback');
            window.clearTimeout(event.data.notification.closeTimer);
        });

        this.closeTimer = window.setTimeout(
            $.proxy(
                function () {
                    this.$elem.alert('close');
                },
                this
            ),
            this.config.delay
        );

        return this;
    };

    Notification.prototype = Object.create(Entry.prototype);
    Notification.prototype.constructor = Notification;
    Notification.prototype.parent = Entry.prototype;

    // Notification.prototype = {};

    reset();

    /**
     * Public Interface
     */

    Admin.Feedback      = Manager;
    Admin.FeedbackEntry = Entry;

}(jQuery, Charcoal.Admin, document));
