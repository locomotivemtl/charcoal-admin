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

;(function ($, Admin) {
    'use strict';

    /**
     * @typedef {Level} string
     */

    /**
     * @typedef {DisplayMode} ?string
     */

    /**
     * @type {Level[]}       lvls
     * @type {DisplayMode[]} modes
     */
    var lvls, modes, defs, alts, reset = function () {
        lvls  = DEFAULTS.supported.slice();
        modes = DEFAULTS.displayModes.slice();
        defs  = $.extend({}, DEFAULTS.definitions);
        alts  = $.extend({}, DEFAULTS.aliases);
    };

    var DEFAULTS = {
        displayModes: [ 'dialog', 'toast' ],
        supported: [ 'success', 'info', 'notice', 'warning', 'error', 'danger' ],
        definitions: {
            success: {
                title:   commonL10n.success,
                display: 'toast',
                type:    BootstrapDialog.TYPE_SUCCESS
            },
            notice: {
                title:   commonL10n.notice,
                display: 'toast',
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
        },
        delay: 3200
    };

    /**
     * Create a new feedback manager.
     *
     * @class
     */
    var Manager = function () {
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
    Manager.prototype.resolveAliases = function (level) {
        this.assertValidLevel(level);

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
    Manager.prototype.push = function () {
        var context = arguments[0];
        var entries = arguments;

        if (this.validContext(context)) {
            entries = Array.prototype.slice.call(arguments, 1);
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
     * @return {Entry[]}  Messages to show.
     */
    Manager.prototype.getMessages = function () {
        return this.storage;
    };

    /**
     * Count Messages
     *
     * @return {number} The number of messages.
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

    Manager.prototype.validMessage = function (message) {
        return ($.type(message) === 'string');
    };

    /**
     * Get all messages grouped by level
     *
     * @deprecated in faovur of {@see this#getMessagesMaoByLevel()}
     *
     * @return {object<Level, Entry[]>}
     */
    Manager.prototype.getMessagesMap = function () {
        return this.getMessagesMapByLevel();
    };

    /**
     * Get all messages grouped by level
     *
     * @return {object<Level, Entry[]>}
     */
    Manager.prototype.getMessagesMapByLevel = function () {
        if (!this.hasMessages()) {
            return {};
        }

        return this.groupMessagesByLevel(this.getMessages());
    };

    /**
     * Group messages by level
     *
     * @example
     * {
     *     '<level>': [ <messages> ]
     * }
     *
     * @param  {Entry[]} entries
     * @return {object<Level, Entry[]>}
     */
    Manager.prototype.groupMessagesByLevel = function (entries) {
        if (!entries.length) {
            return {};
        }

        var key, entry;
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
     * Group messages by display mode
     *
     * @example
     * {
     *     '<displayMode>': [ <messages> ]
     * }
     *
     * @param  {Entry[]} entries
     * @return {object<DisplayMode, Entry[]>}
     */
    Manager.prototype.groupMessagesByDisplay = function (entries) {
        if (!entries.length) {
            return {};
        }

        var mode, entry;
        var grouped = {};
        for (var i = 0; i < entries.length; i++) {
            entry = entries[i];
            mode  = entry.display() || this.level(entry.level()).display;

            if (!(mode in grouped)) {
                grouped[mode] = [];
            }

            grouped[mode].push(entry);
        }

        return grouped;
    };

    /**
     * Group messages by display mode and level
     *
     * @example
     * {
     *     '<displayMode>': {
     *         '<level>': [ <messages> ]
     *     }
     * }
     *
     * @param  {Entry[]} entries
     * @return {object<DisplayMode, object<Level, Entry[]>>}
     */
    Manager.prototype.groupMessagesByDisplayAndLevel = function (entries) {
        if (!entries.length) {
            return {};
        }

        var mode, level, entry;
        var grouped = {};
        for (var i = 0; i < entries.length; i++) {
            entry = entries[i];
            level = entry.level();
            mode  = entry.display() || this.level(level).display;

            if (!(mode in grouped)) {
                grouped[mode] = {};
            }

            if (!(level in grouped[mode])) {
                grouped[mode][level] = [];
            }

            grouped[mode][level].push(entry);
        }

        return grouped;
    };

    /**
     * Retrieve the list of supported feedback levels.
     *
     * @return {array}
     */
    Manager.prototype.availableLevels = function () {
        return lvls;
    };

    /**
     * Retrieve the feedback level definitions.
     *
     * @return {object}
     */
    Manager.prototype.levels = function () {
        return defs;
    };

    /**
     * Retrieve the feedback level definitions.
     *
     * @return {object}
     */
    Manager.prototype.level = function (key) {
        return defs[key] || null;
    };

    /**
     * Replace the level definitions set with the given parameters.
     *
     * @param  {object} [config] - New definitions.
     * @return {this}
     */
    Manager.prototype.setLevels = function (config) {
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
    Manager.prototype.mergeLevels = function (config) {
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
     * Determines if level is valid.
     */
    Manager.prototype.assertValidLevel = function (level) {
        if (!this.isValidLevel(level)) {
            throw new TypeError(
                'Unsupported feedback level, received "' + level +
                '". Must be one of: ' + lvls.join(', ')
            );
        }
    };

    /**
     * Determines if level is valid.
     */
    Manager.prototype.isValidLevel = function (level) {
        return ($.type(level) === 'string' && $.inArray(level, lvls) > -1);
    };

    /**
     * Get display mode override
     */
    Manager.prototype.getDisplay = function () {
        return this.display;
    };

    /**
     * Set display mode override
     */
    Manager.prototype.setDisplay = function (mode) {
        this.assertValidDisplay(mode);

        this.display = mode;
        return this;
    };

    /**
     * Determines if display mode is valid.
     */
    Manager.prototype.assertValidDisplay = function (mode) {
        if (!this.isValidDisplay(mode)) {
            throw new TypeError(
                'Unsupported display mode, received "' + mode +
                '". Must be one of: null, ' + modes.join(', ')
            );
        }
    };

    /**
     * Determines if display mode is valid.
     */
    Manager.prototype.isValidDisplay = function (mode) {
        return (mode !== null && $.inArray(mode, modes) > -1);
    };

    /**
     * Actions in the dialog box
     */
    Manager.prototype.addAction = function (opts) {
        this.actions.push(opts);

        return this;
    };

    /**
     * Alias of {@see Manager.prototype.addAction}
     */
    Manager.prototype.add_action = function (opts) {
        return this.addAction(opts);
    };

    /**
     * Dispatch the results of all feedback accumulated.
     *
     * @return this
     */
    Manager.prototype.dispatch = function () {
        if (!this.hasMessages()) {
            return this;
        }

        var display,
            displayOverride = this.getDisplay(),
            buttons,
            config,
            dialogQueue = 0,
            group,
            groupingDisplay,
            groupingLevel,
            groupsByDisplay = this.groupMessagesByDisplayAndLevel(this.getMessages()),
            groupsByLevel,
            hasMixedDisplayModes = (Object.keys(groupsByDisplay) > 1),
            level,
            queueId = Charcoal.Admin.uid();

        for (groupingDisplay in groupsByDisplay) {
            groupsByLevel = groupsByDisplay[groupingDisplay];

            for (groupingLevel in groupsByLevel) {
                group = groupsByLevel[groupingLevel];

                level   = this.level(groupingLevel);
                buttons = [];
                if (this.actions.length) {
                    for (var action, k = 0; k < this.actions.length; k++) {
                        action = this.actions[k];
                        action = $.extend(action, {
                            label:  action.label,
                            action: action.callback
                        });
                        buttons.push(action);
                    }
                }

                config = {
                    queueId: queueId,
                    title:   level.title,
                    message: '<p class="mb-0">' + group.join('</p><p class="mb-0 mt-3">') + '</p>',
                    level:   groupingLevel,
                    type:    level.type,
                    buttons: buttons
                };

                display = groupingDisplay;

                if (displayOverride) {
                    switch (displayOverride) {
                        case 'dialog':
                        case 'toast':
                            display = displayOverride;
                            break;
                    }
                } else if (hasMixedDisplayModes) {
                    switch (display) {
                        case 'toast':
                            config.delay = 0;
                            break;

                        case 'dialog':
                            /* falls through */
                        default:
                            // Make Feedback Toasts wait until Feedback Dialogs are closed.
                            dialogQueue++;

                            config.onhidden = function () {
                                $.each(BootstrapDialog.dialogs, function (id, dialog) {
                                    if (dialog.options.queueId && dialog.options.queueId === queueId) {
                                        dialogQueue--;
                                    }
                                });

                                if (dialogQueue <= 0) {
                                    var i = 0;
                                    $.each(Notification.notifications, function (id, notification) {
                                        if (notification.config.queueId && notification.config.queueId === queueId) {
                                            notification.close(DEFAULTS.delay + (i++ * 1000));
                                        }
                                    });
                                }
                            };
                            break;
                    }
                }

                switch (display) {
                    case 'toast':
                        config.dismissible = (buttons.length === 0);
                        config.delay = (hasMixedDisplayModes ? 0 : DEFAULTS.delay);
                        (new Notification(config)).show();
                        break;

                    case 'dialog':
                        /* falls through */
                    default:
                        BootstrapDialog.show(config);
                        break;
                }
            }
        }

        this.empty();

        return this;
    };

    /**
     * Reset feedback storages.
     */
    Manager.prototype.empty = function () {
        reset();

        this.display = null;
        this.actions = [];
        this.storage = [];
    };

    /**
     * Single Feedback Message
     *
     * @param {String} [level]   - The feedback level.
     * @param {String} [message] - The feedback message.
     * @param {String} [display] - The feedback display style.
     */
    var Entry = function (level, message, display) {
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

        if (this.validDisplay(display)) {
            this.setDisplay(display);
        }

        return this;
    };

    Entry.createFromObject = function (obj) {
        var level   = obj.level || null;
        var display = obj.display || null;
        var message = obj.message || null;

        if (!level && !message) {
            return null;
        }

        return new Entry(level, message, display);
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

            Manager.prototype.assertValidLevel(level);

            if (level in alts) {
                level = alts[level];
            }

            this._level = level;

            return this;
        },

        validLevel: function (level) {
            return Manager.prototype.isValidLevel(level);
        },

        display: function () {
            return this._display || null;
        },

        setDisplay: function (mode) {
            var vartype = $.type(mode);
            if (vartype !== 'string' && vartype !== 'null') {
                throw new TypeError('Feedback display mode must be a string or null, received ' + vartype);
            }

            Manager.prototype.assertValidDisplay(mode);

            this._display = mode;

            return this;
        },

        validDisplay: function (mode) {
            return Manager.prototype.isValidDisplay(mode);
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
            return Manager.prototype.validMessage(message);
        }
    };

    /**
     * Notification Component (extends Entry)
     */
    var Notification = function (config) {
        var vartype = $.type(config);
        if (vartype !== 'object') {
            throw new TypeError('Notification config must be an associative array, received ' + vartype);
        }

        this.shown  = false;
        this.config = $.extend({}, {
            id:    Charcoal.Admin.uid(),
            delay: DEFAULTS.delay
        }, config);

        this.$elem = $('<article class="c-notifications_item alert fade show" role="alert"></article>');
        this.$elem.prop('id', this.config.id);
        this.$elem.addClass('alert-' + this.config.type.replace('type-', ''));

        if (this.config.dismissible) {
            this.$elem.addClass('alert-dismissible');
            var $button = $('<button type="button" class="close" data-dismiss="alert" aria-label="' + commonL10n.close + '"></button>');
            $button.append('<span aria-hidden="true">&times;</span>');
            this.$elem.append($button);
        }

        if (this.config.message) {
            var $content = $('<div class="alert-body"></div>');
            $content.html('').append(this.config.message);
            this.$elem.append($content);
        }

        return this;
    };

    Notification.prototype = {
        getId: function () {
            return this.config.id;
        },
        show: function () {
            if (this.shown) {
                return;
            }

            this.shown = true;

            this.$elem.appendTo('.c-notifications').addClass('show');

            this.$elem.on('closed.bs.alert', { notification: this }, function (event) {
                var notification = event.data.notification;
                notification.$elem.off('.charcoal.feedback');
                notification.cancelDelayedClose();
            });

            if (typeof this.config.delay === 'number' && this.config.delay > 0) {
                this.$elem.on('mouseover.charcoal.feedback', { notification: this }, function (event) {
                    var notification = event.data.notification;
                    notification.cancelDelayedClose();
                });

                this.$elem.on('mouseout.charcoal.feedback', { notification: this }, function (event) {
                    var notification = event.data.notification;
                    notification.close();
                });

                this.close();
            }
        },
        close: function (delay) {
            this.cancelDelayedClose();

            if (delay == null) {
                delay = this.config.delay;
            }

            if (typeof delay === 'number' && delay > 0) {
                this.closeTimer = window.setTimeout(
                    $.proxy(function () {
                        this.$elem.alert('close');
                    }, this),
                    this.config.delay
                );
            } else {
                this.$elem.alert('close');
            }
        },
        cancelDelayedClose: function () {
            if (this.closeTimer) {
                window.clearTimeout(this.closeTimer);
            }
        }
    };

    /**
     * Show / Close all created notifications all at once.
     */
    Notification.notifications = {};
    Notification.showAll = function () {
        $.each(Notification.notifications, function (id, notification) {
            notification.show();
        });
    };
    Notification.closeAll = function (delay) {
        $.each(Notification.notifications, function (id, notification) {
            notification.close(delay);
        });
    };

    /**
     * Get notification instance by given ID.
     *
     * @return {?Notification}
     */
    Notification.getNotification = function (id) {
        var notification = null;
        if (typeof Notification.notifications[id] !== 'undefined') {
            notification = Notification.notifications[id];
        }

        return notification;
    };

    /**
     * Set a notification.
     *
     * @return {Notification}
     */
    Notification.setNotification = function (notification) {
        Notification.notifications[notification.getId()] = notification;

        return notification;
    };

    /**
     * Alias of {@see Notification.setNotification}
     *
     * @param  {Notification} notification
     * @return {Notification}
     */
    Notification.addNotification = function (notification) {
        return Notification.setNotification(notification);
    };

    reset();

    /**
     * Public Interface
     */

    Admin.Feedback      = Manager;
    Admin.FeedbackEntry = Entry;

}(jQuery, Charcoal.Admin, document));
