/* eslint-disable dot-notation */

/**
 * Enable each element in the set of matched elements.
 */
$.fn.enable = function () {
    this.each(function () {
        $(this).removeAttr('disabled').prop('disabled', false);
    });

    return this;
};

/**
 * Disable each element in the set of matched elements.
 */
$.fn.disable = function () {
    this.each(function () {
        $(this).attr('disabled', true).prop('disabled', true);
    });

    return this;
};

/**
 * Return TRUE if a jQuery selection exists.
 *
 * @link      https://advancedcustomfields.com/
 * @copyright Elliot Condon
 * @return    {boolean}
 */
$.fn.exists = function () {
    return (this.length > 0);
};

/**
 * Return the fallback if a jQuery selection does not exists.
 *
 * @link      https://github.com/byrichardpowell/jquery-or
 * @copyright Richard Powell
 * @param     {mixed} selector  - A sselector expression.
 * @param     {mixed} [context] -A DOM Element, Document, or jQuery to use as context.
 * @return    {jQuery}
 */
$.fn.or = function () {
    return this.length ? this : $.apply($, arguments);
};

/**
 * Return a cached script.
 *
 * @link   https://api.jquery.com/jQuery.getScript/#caching-requests
 * @param  {String} url     - A string containing the URL to which the request is sent.
 * @param  {Object} options - A set of key/value pairs that configure the Ajax request.
 * @return {jqXHR}
 */
jQuery.getCachedScript = function (url, options) {
    // Allow user to set any option except for dataType, cache, and url
    options = $.extend(options || {}, {
        dataType: 'script',
        cache: true,
        url: url
    });

    // Use $.ajax() since it is more flexible than $.getScript
    // Return the jqXHR object so we can chain callbacks
    return jQuery.ajax(options);
};

if (!RegExp.escape) {
    /**
     * Quote regular expression characters.
     *
     * @param  {String} str - The input string.
     * @return {String} Returns the quoted (escaped) string.
     */
    RegExp.escape = function (str) {
        return str.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&');
    };
}

/*
 * Polyfill for IE9, IE10 and IE11
 */
if (!window.CustomEvent || typeof window.CustomEvent !== 'function') {
    window.CustomEvent = (function () {
        function _Class(event, params) {
            params = params || { bubbles: false, cancelable: false, detail: undefined };
            var evt = document.createEvent('CustomEvent');
            evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
            return evt;
        }

        _Class.prototype = window.Event.prototype;

        return _Class;
    }());
}

if (!window.Promise) {
    /**
     * Polyfill for Promise with jQuery Deferreds
     *
     * @link https://makandracards.com/makandra/46682
     */
    window.Promise = (function () {
        function _Class(executor) {
            this['catch'] = this['catch'].bind(this);
            this.then     = this.then.bind(this);
            this.deferred = $.Deferred();
            executor(this.deferred.resolve, this.deferred.reject);
        }

        _Class.prototype.then = function (onFulfilled, onRejected) {
            return this.deferred.then(onFulfilled, onRejected);
        };

        _Class.prototype['catch'] = function (onRejected) {
            return this.then(void 0, onRejected);
        };

        _Class.prototype['finally'] = function (onFinally) {
            return this.deferred.always(onFinally);
        };

        _Class.all = function (promises) {
            return $.when.apply($, [ this.resolve() ].concat(promises.slice()));
        };

        _Class.allSettled = function (promises) {
            var wrappedPromises = promises.map(function (p) {
                _Class.resolve(p).then(
                    function (val) {
                        return {
                            state: 'fulfilled',
                            value: val
                        };
                    },
                    function (err) {
                        return {
                            state: 'rejected',
                            reason: err
                        };
                    }
                );
            });

            return this.all(wrappedPromises);
        };

        _Class.race = function (promises) {
            var fulfilled, i, len, promise, rejected, settle, settled, winner;

            settled = false;
            winner  = $.Deferred();
            settle  = function (settler, value) {
                if (!settled) {
                    settled = true;
                    winner[settler](value);
                }
                return void 0;
            };
            fulfilled = settle.apply(this, 'resolve');
            rejected  = settle.apply(this, 'reject');
            for (i = 0, len = promises.length; i < len; i++) {
                promise = promises[i];
                promise.then(fulfilled, rejected);
            }
            return winner.promise();
        };

        _Class.reject = function (value) {
            var deferred;
            deferred = $.Deferred();
            deferred.reject(value);
            return deferred.promise();
        };

        _Class.resolve = function (value) {
            var deferred;
            deferred = $.Deferred();
            deferred.resolve(value);
            return deferred.promise();
        };

        return _Class;
    }());
}

if (!Array.prototype.find) {
    /**
     * Retrieve the value of the first element in the array that satisfies the provided
     * testing function. Otherwise `undefined` is returned.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
     * @link https://tc39.github.io/ecma262/#sec-array.prototype.find
     *
     * @param  {arrayFind} callback - Function to execute on each value in the array, taking three arguments:
     * @return {*} A value in the array if an element passes the test; otherwise, `undefined`.
     */
    Object.defineProperty(Array.prototype, 'find', {
        value: function (predicate) {
            if (this === null || this === undefined) {
                throw new TypeError('"this" is null or not defined');
            }

            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            var arr     = Object(this);
            var length  = arr.length >>> 0;
            var thisArg = arguments[1];

            var i = 0;
            while (i < length) {
                var val = arr[i];
                if (predicate.call(thisArg, val, i, arr)) {
                    return val;
                }

                i++;
            }

            return undefined;
        }
    });
}

if (typeof Object.assign !== 'function') {
    // Must be writable: true, enumerable: false, configurable: true
    Object.defineProperty(Object, 'assign', {
        value: function (target/*, varArgs*/) {
            'use strict';
            if (target === null || target === undefined) {
                throw new TypeError('Cannot convert undefined or null to object');
            }

            var to = Object(target);

            for (var index = 1; index < arguments.length; index++) {
                var nextSource = arguments[index];

                if (nextSource !== null && nextSource !== undefined) {
                    for (var nextKey in nextSource) {
                        // Avoid bugs when hasOwnProperty is shadowed
                        if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                            to[nextKey] = nextSource[nextKey];
                        }
                    }
                }
            }
            return to;
        },
        writable: true,
        configurable: true
    });
}

if (!String.prototype.replacePairs) {
    /**
     * Replace all occurrences from a map of patterns and replacements.
     *
     * If replacement pairs contain a mix of substrings, regular expressions, and functions,
     * regular expressions are executed last.
     *
     * @link http://stackoverflow.com/a/5069776/140357
     *
     * @param  {Object} replacePairs - An array in the form `{ 'from': 'to', … }`.
     * @return {String} Returns the translated string.
     */
    Object.defineProperty(String.prototype, 'replaceMap', {
        value: function (replacements) {
            var regex = [];
            for (var pattern in replacements) {
                if (pattern instanceof RegExp) {
                    this.replace(pattern, replacements[pattern]);
                } else {
                    regex.push(RegExp.escape(pattern));
                }
            }

            if (regex.length === 0) {
                return this;
            }

            regex = new RegExp(regex.join('|'), 'g');
            return this.replace(regex, function (match) {
                var replacement = replacements[match];
                if (typeof replacement === 'function') {
                    /**
                     * Retrieve the offset of the matched substring `args[0]`
                     * and the whole string being examined `args[1]`.
                     */
                    var args = Array.prototype.slice.call(arguments, -2);

                    return replacement(match, args[0], args[1]);
                } else {
                    return replacement;
                }
            });
        }
    });
}

var Charcoal = Charcoal || {};

/**
 * Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
 * It gives access to private properties and public methods
 * @return  {object}  Charcoal.Admin
 */
Charcoal.Admin = (function () {
    'use strict';

    var options, manager, action, feedback, recaptcha, cache, store,
        currentLocale = document.documentElement.getAttribute('locale'),
        currentLang   = document.documentElement.lang,
        defaultLang   = 'en';

    var IDX = 36, HEX = '';
    while (IDX--) {
        HEX += IDX.toString(36);
    }

    /**
     * Centralized Store
     *
     * @type {Object}
     */
    store = {};

    /**
     * Application Options
     *
     * @type {Object}
     */
    options = {
        debug:         false,
        base_url:      null,
        admin_url:     null,
        admin_path:    null,
    };

    /**
     * Object function that acts as a container for public methods
     */
    function Admin() {}

    /**
     * Application Debug Mode.
     *
     * @param  {boolean} [mode]
     * @return {boolean}
     */
    Admin.debug = function (mode) {
        if (arguments.length) {
            if (typeof mode === 'boolean') {
                options.debug = mode;
            } else {
                throw new TypeError('Must be a boolean, received ' + (typeof mode));
            }
        }

        return options.debug || false;
    };

    /**
     * @alias  Admin.debug
     * @param  {boolean} [mode]
     * @return {boolean}
     */
    Admin.devMode = function (mode) {
        return Admin.debug(mode);
    };

    /**
     * Retrieve the current locale.
     *
     * @return {string|null}
     */
    Admin.locale = function () {
        return currentLocale;
    };

    /**
     * Retrieve the current language or determine
     * if the given language is the default one.
     *
     * @param  {string} [lang] - A language code.
     * @return {string|boolean}
     */
    Admin.lang = function (lang) {
        if (typeof lang === 'string') {
            return currentLang === lang;
        }

        return currentLang || defaultLang;
    };

    /**
     * Retrieve the default language or determine
     * if the given language is the default one.
     *
     * @param  {string} [lang] - A language code.
     * @return {string|boolean}
     */
    Admin.defaultLang = function (lang) {
        if (typeof lang === 'string') {
            return defaultLang === lang;
        }

        return defaultLang;
    };

    /**
     * Set the current language.
     *
     * @param  {string|null} lang - A language code.
     * @return {string}
     */
    Admin.setLang = function (lang) {
        if (lang === null) {
            currentLang = document.documentElement.lang || defaultLang;
        } else if (typeof lang === 'string') {
            currentLang = lang || document.documentElement.lang || defaultLang;
        } else {
            throw new TypeError('Must be a language code, received ' + (typeof mode));
        }

        return currentLang;
    };

    /**
     * @alias  Admin.setLang
     * @param  {string|null} lang - A language code.
     * @return {string}
     */
    Admin.set_lang = Admin.setLang;

    /**
     * Set data that can be used by public methods
     * @param  {object}  data  Object containing data that needs to be set
     */
    Admin.set_data = function (data) {
        options = $.extend(true, options, data);
    };

    /**
     * Generates the admin URL used by forms and other objects.
     *
     * @param  {string|null} [path] - A target path of the admin.
     * @return {string} - The admin URL.
     */
    Admin.admin_url = function (path) {
        return options.admin_url + (typeof path === 'string' ? path : '');
    };

    /**
     * Returns the base_url of the project.
     *
     * @return {string} - The base URL.
     */
    Admin.base_url = function () {
        return options.base_url;
    };

    /**
     * Provides access to the component manager.
     *
     * @return {ComponentManager}
     */
    Admin.manager = function () {
        if (typeof(manager) === 'undefined') {
            manager = new Charcoal.Admin.ComponentManager();
        }

        return manager;
    };

    /**
     * Provides access to the component manager.
     *
     * @return {ComponentManager}
     */
    Admin.action = function () {
        if (typeof(action) === 'undefined') {
            action = new Charcoal.Admin.ActionManager();
        }

        return action;
    };

    /**
     * Convert the query string into a query object.
     *
     * @return {object} Key/Value pair of query parameters.
     */
    Admin.queryParams = function () {
        var pairs = location.search.slice(1).split('&');

        var result = {};
        pairs.forEach(function (pair) {
            pair = pair.split('=');
            if (pair[1]) {
                result[pair[0]] = decodeURIComponent(pair[1] || '');
            }
        });

        return JSON.parse(JSON.stringify(result));
    };

    /**
     * Provides access to the feedback manager.
     *
     * @param  {array|object} [entries] Optional entries to push on the manager.
     * @return {Feedback}
     */
    Admin.feedback = function (/* entries */) {
        if (typeof feedback === 'undefined') {
            feedback = new Charcoal.Admin.Feedback();
        }

        if (arguments.length) {
            feedback.push.apply(feedback, arguments);
        }

        return feedback;
    };

    /**
     * Provides access to the reCAPTCHA handler.
     *
     * @return {Captcha}
     */
    Admin.recaptcha = function () {
        if (typeof recaptcha === 'undefined') {
            recaptcha = new Charcoal.Admin.ReCaptcha();
        }

        return recaptcha;
    };

    /**
     * Convert an object namespace string into a usable object name.
     *
     * @param  {string} name - String that respects the namespace structure : charcoal/admin/property/input/switch
     * @return {string} - String that respects the object name structure : Property_Input_Switch
     */
    Admin.get_object_name = function (name) {
        // Getting rid of Charcoal.Admin namespacing
        var string_array = name.split('/');
        string_array     = string_array.splice(2, string_array.length);

        // Uppercasing
        string_array.forEach(function (element, index, array) {

            // Camel case when splitted by '-'
            // Joined back with '_'
            var substr_array = element.split('-');
            if (substr_array.length > 1) {
                substr_array.forEach(function (e, i) {
                    substr_array[i] = e.charAt(0).toUpperCase() + e.slice(1);
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
     * @param  {string|number} value - The value to parse.
     * @return {string|number} - Returns a numeric value if one was detected otherwise a string.
     */
    Admin.parseNumber = function (value) {
        var re = /^(\-|\+)?([0-9]+(\.[0-9]+)?|Infinity)$/;

        if (re.test(value)) {
            return Number(value);
        }

        return value;
    };

    /**
     * Load JavaScript
     *
     * @param  {string}    src      - Full path to a script file.
     * @param  {function}  callback - Fires multiple times.
     * @return {void}
     */
    Admin.loadScript = function (src, callback) {
        this.store(src, function (defer) {
            $.ajax({
                url: src,
                dataType: 'script',
                success: defer.resolve,
                error: defer.reject
            });
        }).then(callback);
    };

    /**
     * Retrieve or store a value shared across the application.
     *
     * @param  {string}   key      - The key for the stored value.
     * @param  {function} value    - The value to store.
     *     If a function, fires once when promise is completed.
     * @param  {function} callback - Fires multiple times.
     * @return {mixed}    Returns the stored value.
     */
    Admin.store = function (key, value, callback) {
        if (!store[key]) {
            if (typeof value === 'function') {
                store[key] = $.Deferred(function (defer) {
                    value(defer);
                }).promise();
            }
        }

        if (typeof store[key] === 'function') {
            return store[key].done(callback);
        }

        return store[key];
    };

    /**
     * Provides access to the cache manager.
     *
     * @param  {string} [type] Optional cache type to purge.
     * @return {Cache}
     */
    Admin.cache = function (/* type */) {
        if (typeof cache === 'undefined') {
            cache = new Charcoal.Admin.Cache();
        }

        if (arguments.length) {
            cache.purge.apply(cache, arguments);
        }

        return cache;
    };

    /**
     * Creates a new random identifer of fixed length.
     *
     * A tiny (200B) and fast utility to randomize unique IDs of fixed length.
     * This is a port of the @lukeed/uid JS library.
     *
     * @link https://github.com/lukeed/uid Repository
     * @link https://www.npmjs.com/package/uid NPM Package
     *
     * @param {number} [len=11] - Then length of the output string.
     *     Your risk of collisions decreases with longer strings.
     * @return {string}
     */
    Admin.uid = function (len) {
        var str = '', num = len || 11;
        while (num--) {
            str += HEX[Math.random() * 36 | 0];
        }
        return str;
    }

    /**
     * Resolves the context of parameters for the "complete" callback option.
     *
     * (`jqXHR.always(function( data|jqXHR, textStatus, jqXHR|errorThrown ) {})`).
     *
     * @param  {...} Successful or failed argument list.
     * @return {mixed[]} Standardized argument list.
     */
    Admin.parseJqXhrArgs = function () {
        var args = {
            failed: true,
            jqXHR: null,
            textStatus: '',
            errorThrown: '',
            response: null
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

    /**
     * Resolves the structure of the XHR response dataset.
     *
     * @link   https://github.com/locomotivemtl/memo-boilerplate/blob/v0.4.0/assets/src/scripts/objects/api.js#L112-L130
     *
     * @param  {jqXHR}  jqxhr  - The jqXHR promise.
     * @param  {string} status - A string describing the status.
     * @param  {mixed}  error  - The error message.
     * @return {object} - The resolved XHR response structure.
     */
    Admin.parseJqXhrResponse = function (jqxhr, status, error) {
        var response = { success: false, feedbacks: [] };

        if (jqxhr.responseJSON) {
            $.extend(response, jqxhr.responseJSON);
        }

        if (response.feedbacks.length === 0) {
            if (response.message) {
                response.feedbacks = Array.isArray(response.message)
                    ? response.message
                    : [ { level: 'error', message: response.message } ];
            } else {
                response.feedbacks = Array.isArray(error)
                    ? error
                    : [ { level: 'error', message: error } ];
            }
        }

        return response;
    };

    /**
     * Resolves false positives from successful requests.
     *
     * @param  {mixed}  response - The response body, formatted according to the dataType parameter or the dataFilter callback function, if specified.
     * @param  {string} status   - A string describing the status.
     * @param  {jqXHR}  jqxhr    - The jqXHR promise.
     * @return {jqXHR} - The resolved jqXHR promise.
     */
    Admin.resolveJqXhrFalsePositive = function (response, status, jqxhr) {
        if (!response || !response.success || response.error) {
            if (response.message) {
                return $.Deferred().reject(jqxhr, 'error', response.message);
            } else if (response.feedbacks) {
                return $.Deferred().reject(jqxhr, 'error', response.feedbacks);
            } else {
                return $.Deferred().reject(jqxhr, 'error', '');
            }
        }

        return $.Deferred().resolve(response, status, jqxhr);
    };

    /**
     * Resolves the given promise.
     *
     * Note: This decorator expects the data type to be 'json'.
     *
     * This function will handle all nitty gritty of processing a promise,
     * resolving false positives (such as a successful HTTP response
     * containing an unsuccessful body), running the appropriate callback
     * without affecting its context, and providing a standard response format.
     *
     * @param  {jqXHR}        jqxhr      - A jqXHR object.
     * @param  {simpleDone}   [success]  - A function to be called if the request succeeds.
     * @param  {simpleFail}   [failure]  - A function to be called if the request fails.
     * @param  {simpleAlways} [complete] - A function to be called when the request finishes.
     * @return {jqXHR} - The given jqXHR object.
     */
    Admin.resolveSimpleJsonXhr = function (jqxhr, success, failure, complete) {
        jqxhr = jqxhr.then(this.resolveJqXhrFalsePositive);

        if (typeof success === 'function') {
            jqxhr.done(function (response, status, jqxhr) {
                response = $.extend({ success: true, feedbacks: [] }, response);

                if (response.feedbacks.length === 0 && response.message) {
                    response.feedbacks = Array.isArray(response.message)
                        ? response.message
                        : [ { level: 'notice', message: response.message } ];
                }

                /**
                 * Fires when the request succeeds.
                 *
                 * @callback simpleDone
                 * @this     jqXHR
                 * @param    {object} response - The response body.
                 * @return   {void}
                 */
                success.call(jqxhr, response);
            });
        }

        if (typeof failure === 'function') {
            jqxhr.fail(function (jqxhr, status, error) {
                var response = { success: false, feedbacks: [] };

                if (jqxhr.responseJSON) {
                    $.extend(response, jqxhr.responseJSON);
                }

                if (response.feedbacks.length === 0) {
                    if (response.message) {
                        response.feedbacks = Array.isArray(response.message)
                            ? response.message
                            : [ { level: 'error', message: response.message } ];
                    } else {
                        response.feedbacks = Array.isArray(error)
                            ? error
                            : [ { level: 'error', message: error } ];
                    }
                }

                response = $.extend({ success: false, feedbacks: [] }, response);

                /**
                 * Fires when the request fails.
                 *
                 * @callback simpleFail
                 * @this     jqXHR
                 * @param    {object} response - The response body.
                 * @return   {void}
                 */
                failure.call(jqxhr, response);
            });
        }

        if (typeof complete === 'function') {
            jqxhr.always(function () {
                /**
                 * Fires when the request finishes.
                 *
                 * @callback simpleAlways
                 * @this     jqXHR
                 * @return   {void}
                 */
                complete.call(jqxhr);
            });
        }

        return jqxhr;
    };

    return Admin;

}());

/* globals cacheL10n */
/**
 * Charcoal Cache Manager
 *
 * Class that deals with all the server-side cache pool.
 *
 * It uses BootstrapDialog to display feedback.
 */

;(function ($, Admin, document) {
    'use strict';

    var $document = $(document),
        DATA_KEY = 'charcoal.cache',
        EVENT_KEY = '.' + DATA_KEY,
        Event = {
            FLUSH:   'flush'   + EVENT_KEY,
            FLUSHED: 'flushed' + EVENT_KEY,
            CLICK:   'click'   + EVENT_KEY
        },
        Selector = {
            DATA_ITEM:  '[data-cache-key]',
            DATA_CACHE: '[data-cache-type]',
            DATA_CLEAR: '[data-clear="cache"]',
            DATA_PURGE: '[data-purge="cache"]'
        },
        Action = {
            CLEAR: 'clear',
            PURGE: 'purge'
        },
        lastXhr    = null,
        lastAction = null,
        lastCache  = null,
        lastTarget = null,
        fromEvent  = false,
        isFlushing = false;

    /**
     * Create a new cache manager.
     *
     * @class
     */
    var Manager = function () {
        $(this.init.bind(this));

        return this;
    };

    /**
     * Initialize the cache manager.
     *
     * @fires document#ready
     */
    Manager.prototype.init = function () {
        $document
            .off(Event.CLICK)
            .on(Event.CLICK, Selector.DATA_CLEAR, this.onClear.bind(this))
            .on(Event.CLICK, Selector.DATA_PURGE, this.onPurge.bind(this));
    };

    /**
     * Determine if the cache is in the process of flushing.
     *
     * @return {Boolean} TRUE if the cache is clearing data otherwise FALSE.
     */
    Manager.prototype.isFlushing = function () {
        return isFlushing;
    };

    /**
     * Retrieve the last flush action called.
     *
     * @return {String|null}
     */
    Manager.prototype.lastAction = function () {
        return lastAction;
    };

    /**
     * Retrieve the last cache type to be cleared.
     *
     * @return {String|null}
     */
    Manager.prototype.lastCacheType = function () {
        return lastCache;
    };

    /**
     * Retrieve the last target to trigger the flush.
     *
     * @return {Element|null}
     */
    Manager.prototype.lastTarget = function () {
        return lastTarget;
    };

    /**
     * Retrieve the last XHR object.
     *
     * @return {Thenable|null}
     */
    Manager.prototype.lastXhr = function () {
        return lastXhr;
    };

    /**
     * Resolve the cache type from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveType = function ($trigger) {
        return $trigger.data('cacheType') || null;
    };

    /**
     * Resolve the cache item key from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveKey = function ($trigger) {
        return $trigger.data('cacheKey') || null;
    };

    /**
     * Event: Clear the cache.
     *
     * @this   {CacheManager}
     * @event  document#click
     * @param  {Event} event - The event handler.
     */
    Manager.prototype.onClear = function (event) {
        event.preventDefault();

        var $trigger, type, key;

        fromEvent  = true;
        lastTarget = event.currentTarget;
        $trigger   = $(event.currentTarget);

        type = event.cacheType || this.resolveType($trigger);
        key  = event.cacheKey  || this.resolveKey($trigger);

        if (type) {
            this.clear(type, key);
        }

        fromEvent = false;
    };

    /**
     * Event: Purge the cache.
     *
     * @this   {CacheManager}
     * @event  document#click
     * @param  {Event} event - The event handler.
     */
    Manager.prototype.onPurge = function (event) {
        event.preventDefault();

        var $trigger, type, key;

        fromEvent  = true;
        lastTarget = event.currentTarget;
        $trigger   = $(event.currentTarget);

        type = event.cacheType || this.resolveType($trigger);
        key  = event.cacheKey  || this.resolveKey($trigger);

        if (type) {
            this.purge(type, key);
        }

        fromEvent = false;
    };

    /**
     * Empty the entire cache pool of all items.
     *
     * @param {String} cacheType   - The cache type to flush.
     * @param {String} cacheKey    - The cache key to delete.
     */
    Manager.prototype.clear = function (cacheType, cacheKey) {
        this.flush(Action.CLEAR, cacheType, cacheKey);
    };

    /**
     * Purge the cache pool of stale or expired items.
     *
     * @param {String} cacheType   - The cache type to flush.
     * @param {String} cacheKey    - The cache key to delete.
     */
    Manager.prototype.purge = function (cacheType, cacheKey) {
        this.flush(Action.PURGE, cacheType, cacheKey);
    };

    /**
     * Flush the cache for given category.
     *
     * @param {String} cacheAction - Whether to empty all items or purge stale or expired items.
     * @param {String} cacheType   - The cache type to flush.
     * @param {String} cacheKey    - The cache key to delete.
     */
    Manager.prototype.flush = function (cacheAction, cacheType, cacheKey) {
        if (isFlushing === true) {
            return;
        }

        if (cacheAction !== Action.CLEAR && cacheAction !== Action.PURGE) {
            cacheAction = Action.PURGE;
        }

        var flushEvent, settings, data;

        isFlushing = true;
        lastAction = cacheAction;
        lastCache  = cacheType;
        lastXhr    = null;

        if (fromEvent === false) {
            lastTarget = null;
        }

        flushEvent = $.Event(Event.FLUSH, {
            cacheManager:  this,
            cacheAction:   cacheAction,
            cacheType:     lastCache,
            relatedTarget: lastTarget
        });

        $document.trigger(flushEvent);

        if (flushEvent.isDefaultPrevented()) {
            return;
        }

        data = {};

        if (cacheType) {
            data.cache_type = cacheType;
        }

        if (cacheKey) {
            data.cache_key = cacheKey;
        }

        settings = {
            url:      Admin.admin_url() + 'system/cache/' + cacheAction,
            data:     data,
            dataType: 'json',
            context:  this
        };

        lastXhr = $.post(settings)
            .then(juggle)
            .done(done)
            .fail(fail)
            .always(finalize);
    };

    /**
     * Private Utilities
     */

    /**
     * @this   {CacheManager}
     * @param  {Object}   response   The HTTP Response object.
     * @param  {String}   textStatus The HTTP status message.
     * @param  {Thenable} jqXHR      The promisable XHR object.
     * @return {Thenable} Returns the fixed XHR object.
     */
    var juggle = function (response, textStatus, jqXHR) {
        if (!response || !response.success) {
            var feedback = {};
            if (response.feedbacks && $.isArray(response.feedbacks)) {
                feedback = parseFeedback(response).message;
            }

            return $.Deferred().rejectWith(this, [ jqXHR, textStatus, feedback ]);
        }

        return $.Deferred().resolveWith(this, [ response, textStatus, jqXHR ]);
    };

    /**
     * @this   {CacheManager}
     * @param  {Object}   response   The HTTP Response object.
     * @param  {String}   textStatus The XHR status category.
     * @param  {Thenable} jqXHR      The promisable XHR object.
     */
    var done = function (response/* textStatus, jqXHR */) {
        window.console.debug(response);

        var feedback = parseFeedback(response).message;

        BootstrapDialog.show({
            title:   cacheL10n.title,
            message: feedback || cacheL10n.cleared,
            type:    BootstrapDialog.TYPE_SUCCESS
        });
    };

    /**
     * @this   {CacheManager}
     * @param  {Thenable} jqXHR       The promisable XHR object.
     * @param  {String}   textStatus  The XHR status category.
     * @param  {String}   errorThrown The HTTP status message.
     */
    var fail = function (jqXHR, textStatus, errorThrown) {
        var response = jqXHR.responseJSON,
            feedback = parseFeedback(response).message;

        if (Admin.debug() === false) {
            errorThrown = cacheL10n.failed;
        }

        BootstrapDialog.show({
            title:   cacheL10n.title,
            message: feedback || errorThrown || cacheL10n.failed,
            type:    BootstrapDialog.TYPE_DANGER
        });
    };

    /**
     * @this {CacheManager}
     */
    var finalize = function () {
        isFlushing = false;

        var flushedEvent = $.Event(Event.FLUSH, {
            cacheManager:  this,
            cacheAction:   lastAction,
            cacheType:     lastCache,
            relatedTarget: lastTarget
        });

        $document.trigger(flushedEvent);
    };

    /**
     * Extract the first feedback entry.
     *
     * @param  {Object} response - The HTTP Response object.
     * @return {Object} Returns a feedback entry object.
     */
    var parseFeedback = function (response) {
        var feedback;
        if (response && response.feedbacks && response.feedbacks.length) {
            feedback = response.feedbacks.shift();
        }

        return feedback || {};
    };

    /**
     * Public Interface
     */

    Admin.Cache = Manager;

    /** Initialize the manager */
    Admin.cache();

}(jQuery, Charcoal.Admin, document));

/**
 * Charcoal Component Manager
 *
 * Implements its own deferred "ready list" based on `jQuery.fn.ready`.
 */

;(function ($, document, undefined) {
    'use strict';

    // Stored for quick usage
    var $document = $(document);

    // The deferred used when the Components and the DOM are ready
    var readyList = $.Deferred();

    // A counter to track how many items to wait for before the ready event fires.
    var readyWait = 1;

    /**
     * Creates a new component manager.
     *
     * @class
     */
    var Manager = function () {
        // Are the Components and the DOM ready to be used? Set to true once it occurs.
        this.isReady = false;

        // The collection of registered components
        this.components = {};

        var that = this;

        $(document).ready(function () {
            that.render();
        });
    };

    Manager.prototype.add_property_input = function (opts) {
        this.add_component('property_inputs', opts);
    };

    Manager.prototype.add_widget = function (opts) {
        this.add_component('widgets', opts);
    };

    Manager.prototype.add_template = function (opts) {
        this.add_component('templates', opts);
    };

    Manager.prototype.add_component = function (component_type, opts) {
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
     * Retrieve Components
     */

    Manager.prototype.get_property_input = function (id) {
        return this.get_component('property_inputs', id);
    };

    Manager.prototype.get_widget = function (id) {
        return this.get_component('widgets', id);
    };

    Manager.prototype.get_template = function (id) {
        return this.get_component('templates', id);
    };

    /**
     * Get component from Type and ID
     *
     * @param component_type (widgets, inputs, properties)
     * @param component_id
     * @returns {*}
     */
    Manager.prototype.get_component = function (component_type, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_type in this.components) {
            return this.components[component_type].find(function (component) {
                return component._id === component_id;
            });
        }

        return undefined;
    };

    /**
     * Remove component from the manager
     *
     * @param component_type (widgets, inputs, properties)
     * @param component_id
     * @returns {undefined}
     */
    Manager.prototype.remove_component = function (component_type, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_type in this.components) {
            this.components[component_type] = this.components[component_type].filter(function (component) {
                return component._id !== component_id;
            });
        }

        return undefined;
    };

    /**
     * Specify a function to execute when the components are rendered.
     *
     * The `.ready()` method is also constrained by the DOM's readiness.
     *
     * @param  {Function} fn - A function to execute after the DOM is ready.
     * @return {this}
     */
    Manager.prototype.ready = function (fn) {
        readyList.promise().done(fn);

        return this;
    };

    Manager.prototype.render = function () {
        var renderEvent = $.Event('render.charcoal.components', {
            relatedTarget: this
        });

        $document.trigger(renderEvent);

        if (renderEvent.isDefaultPrevented()) {
            return;
        }

        for (var component_type in this.components) {
            var super_class = Charcoal;

            switch (component_type) {
                case 'widgets':
                    super_class = Charcoal.Admin.Widget;
                    break;

                case 'property_inputs':
                    super_class = Charcoal.Admin.Property;
                    break;

                case 'templates':
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
                        case 'widgets':
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

        // Handle it asynchronously to allow scripts the opportunity to delay ready
        if (this.isReady) {
            return this;
        }

        // Remember that the DOM is ready
        this.isReady = true;

        // If a normal DOM Ready event fired, decrement, and wait if need be
        if (--readyWait > 0) {
            return;
        }

        // If there are functions bound, to execute
        readyList.resolveWith(this);

        var renderedEvent = $.Event('rendered.charcoal.components', {
            relatedTarget: this
        });

        $document.trigger(renderedEvent);

        return this;
    };

    /**
     * This is called by the widget.form on form submit
     * Called save because it's calling the save method on the properties' input
     * @see admin/widget/form.js submit_form()
     * @return boolean Success (in case of validation)
     */
    Manager.prototype.prepare_submit = function () {
        this.prepare_inputs();
        this.prepare_widgets();
        return true;
    };

    Manager.prototype.prepare_inputs = function () {
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

    Manager.prototype.prepare_widgets = function () {
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

    Charcoal.Admin.ComponentManager = Manager;

}(jQuery, document));

/* globals commonL10n */
/**
 * Charcoal Action
 *
 * Handles bindings for actionable buttons.
 */

;(function ($, document) {
    'use strict';

    // Stored for quick usage
    var $document = $(document);

    /**
     * Creates a new action manager.
     *
     * @class
     */
    var Manager = function () {
        // Submit the form via ajax
        $($document).on('click', '.js-action-button', function (event) {
            this.handle_action(event);
        }.bind(this));
    };

    Manager.prototype.handle_action = function (event) {
        event.preventDefault();

        var url = $(event.target).attr('href');

        this.xhr = $.ajax({
            type:        'GET',
            url:         url,
            processData: false,
            contentType: false,
        });

        this.xhr
            .then($.proxy(this.request_done, this))
            .done($.proxy(this.request_success, this))
            .fail($.proxy(this.request_failed, this))
            .always($.proxy(this.request_complete, this));
    };

    Manager.prototype.request_done = function (response, textStatus, jqXHR) {
        if (!response || !response.success) {
            if (response.feedbacks) {
                return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
            }
            // else {
            // return $.Deferred().reject(jqXHR, textStatus, commonL10n.errorOccurred);
            // }
        }

        return $.Deferred().resolve(response, textStatus, jqXHR);
    };

    Manager.prototype.request_success = function (response/* textStatus, jqXHR */) {
        if (response.feedbacks) {
            Charcoal.Admin.feedback(response.feedbacks);
        }
    };

    Manager.prototype.request_failed = function (jqXHR, textStatus, errorThrown) {
        if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
            Charcoal.Admin.feedback(jqXHR.responseJSON.feedbacks);
        } else {
            var error   = errorThrown || commonL10n.errorOccurred;
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': 'There was an error. Sorry for the inconvenience.',
                    '[[ errorThrown ]]': error
                })
            } ]);
        }
    };

    Manager.prototype.request_complete = function (/*, .... */) {
        Charcoal.Admin.feedback().dispatch();
    };

    Charcoal.Admin.ActionManager = Manager;

    new Charcoal.Admin.ActionManager();

}(jQuery, document));

;(function (window) {
    'use strict';

    /**
     * The `SimpleAudioElement()` interface provides special properties and methods
     * for manipulating a `HTMLAudioElement` instance.
     *
     * The constructor will create a new `HTMLAudioElement` instance if one is not provided.
     * Any event handlers provided will be added to the audio element.
     *
     * @param  {Object}           options              - An options object that specifies characteristics about the player.
     * @param  {HTMLAudioElement} [options.element]    - The HTML `<audio>` element to attach the player to.
     * @param  {Object}           [options.properties] - A map of properties where the key is the property name.
     * @param  {Object}           [options.listeners]  - A map of event listeners where the key is the event name.
     * @return {this}
     */
    var SimpleAudioElement = function (options) {
        var element, key, listeners, _options;

        _options = typeof options;
        switch (true) {
            case (options instanceof HTMLAudioElement):
                options = {
                    element: options
                };
                break;

            case (options instanceof URL):
            case (_options === 'string'):
                options = {
                    src: options
                };
                break;

            case (_options === 'function'):
                options = {
                    factory: options
                };
                break;

            case (_options === 'object'):
                break;

            default:
                options = {};
                break;
        }

        if (options.element instanceof HTMLAudioElement) {
            element = options.element;
        } else {
            element = new window.Audio();
        }

        if (options.properties) {
            for (key in options.properties) {
                element[key] = options.properties[key];
            }
        }

        if (options.listeners) {
            for (key in options.listeners) {
                if ('on' + key in HTMLAudioElement.prototype) {
                    listeners = options.listeners[key];

                    if (!Array.isArray(listeners)) {
                        listeners = [ listeners ];
                    }

                    listeners.forEach((function (element, type, listener) {
                        element.addEventListener(type, listener);
                    }).bind(null, element, key));
                }
            }
        }

        if (options.src) {
            element.src = options.src;
        }

        this.getElement = function () {
            return element;
        };

        this.isPlaying = function () {
            return !element.paused && !element.ended && element.currentTime > 0;
        };

        this.reset = function () {
            element.src = '';
        };

        this.src = function (url) {
            element.src = url;
        };

        this.stop = function () {
            element.pause();
            element.currentTime = 0;
        };

        this.canPlayType = function (/* mediaType */) {
            var canPlay = element.canPlayType.apply(element, arguments);

            // handle "no" edge case with super legacy browsers...
            // https://groups.google.com/forum/#!topic/google-web-toolkit-contributors/a8Uy0bXq1Ho
            return canPlay.replace(/no/, '');
        };

        this.load = function () {
            return element.load.apply(element, arguments);
        };

        this.pause = function () {
            return element.pause.apply(element, arguments);
        };

        this.play = function () {
            return element.play.apply(element, arguments);
        };

        return this;
    };

    window.SimpleAudioElement = SimpleAudioElement;

}(window));

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

    var lvls, modes, defs, alts, arr = [], reset = function () {
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
        }
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
    Manager.prototype.push = function () {
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
     * Get display mode override
     */
    Manager.prototype.getDisplay = function () {
        return this.display;
    };

    /**
     * Set display mode override
     */
    Manager.prototype.setDisplay = function (mode) {
        if ($.inArray(mode, modes) === -1 && mode !== null) {
            throw new TypeError(
                'Unsupported display mode, received "' + mode +
                '". Must be one of: null, ' + modes.join(', ')
            );
        }

        this.display = mode;
        return this;
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

        var key, level, buttons;
        var grouped = this.getMessagesMap();

        for (key in grouped) {
            level   = this.level(key);
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

            var config = {
                title:   level.title,
                message: '<p class="mb-0">' + grouped[key].join('</p><p class="mb-0 mt-3">') + '</p>',
                level:   key,
                type:    level.type,
                buttons: buttons
            };

            var override = this.getDisplay(),
                display  = null;

            switch (override) {
                case 'dialog':
                case 'toast':
                    display = override;
                    break;
                default:
                    display = level.display;
                    break;
            }

            switch (display) {
                case 'toast':
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
            throw new TypeError('Notification config must be an associative array, received ' + vartype);
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

        this.$elem.appendTo('.c-notifications').addClass('show');

        this.$elem.on('closed.bs.alert', { notification: this }, function (event) {
            var notification = event.data.notification;
            notification.$elem.off('.charcoal.feedback');
            if (notification.closeTimer) {
                window.clearTimeout(notification.closeTimer);
            }
        });

        if (typeof this.config.delay === 'number' && this.config.delay > 0) {
            this.$elem.on('mouseover.charcoal.feedback', { notification: this }, function (event) {
                var notification = event.data.notification;
                if (notification.closeTimer) {
                    window.clearTimeout(notification.closeTimer);
                }
            });

            this.$elem.on('mouseout.charcoal.feedback', { notification: this }, function (event) {
                var notification = event.data.notification;
                notification.closeTimer = window.setTimeout(function () {
                    notification.$elem.alert('close');
                }, notification.config.delay);
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
        }

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

/**
 * Charcoal reCAPTCHA Handler
 */

;(function ($, Admin, window) {
    'use strict';

    /**
     * Creates a new reCAPTCHA handler.
     *
     * @class
     * @return {this}
     */
    var Captcha = function () {
        return this;
    };

    /**
     * Public Interface
     */

    /**
     * Retrieve the Google reCAPTCHA API instance.
     *
     * @return {grecaptcha|null} - The Google reCAPTCHA object or NULL.
     */
    Captcha.prototype.getApi = function () {
        return window.grecaptcha || null;
    };

    /**
     * Determine if the Google reCAPTCHA API is available.
     *
     * @return {boolean}
     */
    Captcha.prototype.hasApi = function () {
        return (typeof window.grecaptcha !== 'undefined');
    };

    /**
     * Determine if a Google reCAPTCHA widget exists.
     *
     * @param  {HTMLFormElement|jQuery} context    - The HTML element containing the reCAPTCHA widget.
     * @param  {string}                 [selector] - The CSS selector of the reCAPTCHA widget to locate.
     * @return {boolean} - Returns TRUE if the Google reCAPTCHA API is avialable
     *     and if the widget exists.
     */
    Captcha.prototype.hasWidget = function (context, selector) {
        // Bail early
        if (this.hasApi() === false) {
            return false;
        }

        selector = selector || '.g-recaptcha';

        var $context = $(context);

        return ($context.is(selector) || $context.find(selector).exists());
    };

    /**
     * Determine if a Google reCAPTCHA widget exists and is invisible.
     *
     * @param  {HTMLFormElement|jQuery} context    - The HTML element containing the reCAPTCHA widget.
     * @param  {string}                 [selector] - The CSS selector of the reCAPTCHA widget to locate.
     * @return {boolean} - Returns TRUE if the Google reCAPTCHA API is avialable
     *     and if the widget exists and is invisible.
     */
    Captcha.prototype.hasInvisibleWidget = function (context, selector) {
        // Bail early
        if (this.hasApi() === false) {
            return false;
        }

        selector = selector || '.g-recaptcha';

        var $context = $(context),
            $widget  = $context.is(selector) ? $context : $context.find(selector);

        return ($widget.exists() && $widget.data('size') === 'invisible');
    };

    Admin.ReCaptcha = Captcha;

}(jQuery, Charcoal.Admin, window));

/**
 * Abstract Component
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Component}
 */
Charcoal.Admin.Component = function (opts) {
    /* jshint ignore:start */
    this._element;
    this._id;
    this._type;
    this._opts;
    /* jshint ignore:end */

    if (opts) {
        if (opts.element) {
            this.set_element(opts.element);
        } else if (typeof opts.id === 'string') {
            this.set_element('#' + opts.id);
            this.set_id(opts.id);
        }

        if (typeof opts.type === 'string') {
            this.set_type(opts.type);
        }

        this.set_opts(opts);
    }

    return this;
};

/**
 * @param  {Element} element - The jQuery/DOM element related to the component instance.
 * @throws {TypeError} If the element argument is not a valid jQuery element.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_element = function (element) {
    if (!(element instanceof jQuery)) {
        element = $(element);
    }

    if (element.length !== 1) {
        throw new TypeError('Component Element must be a DOM Element');
    }

    this.set_id(element.attr('id'));
    this._element = element;
    return this;
};

/**
 * @return {?jQuery} The related jQuery element.
 */
Charcoal.Admin.Component.prototype.element = function () {
    return this._element;
};

/**
 * @param  {String} id - The component instance ID.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_id = function (id) {
    this._id = id;
    return this;
};

/**
 * @return {?String} The component instance ID.
 */
Charcoal.Admin.Component.prototype.id = function () {
    return this._id;
};

/**
 * @param  {String} type - The component type or subtype.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_type = function (type) {
    this._type = type;
    return this;
};

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Component.prototype.type = function () {
    return this._type;
};

/**
 * @param  {Object} opts - The component instance options.
 * @throws {TypeError} If the options argument is invalid.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.set_opts = function (opts) {
    if (typeof opts === 'object') {
        this._opts = opts;
    } else {
        throw new TypeError('Component Options must be an object');
    }
    return this;
};

/**
 * @param  {String} key - The data key.
 * @param  {*}      val - The data value.
 * @throws {TypeError} If the data key argument is invalid.
 * @return {this}
 */
Charcoal.Admin.Component.prototype.add_opts = function (key, val) {
    if (typeof key === 'string') {
        this._opts[key] = val;
    } else {
        throw new TypeError('Component Options Key must be a string');
    }
    return this;
};

/**
 * @param  {String} [key] - The optional data key.
 * @return {Object|?*}
 *     If `key` is provided, the key's value is returned or NULL.
 *     If `key` is not provided, the component instance options is returned.
 */
Charcoal.Admin.Component.prototype.opts = function (key) {
    if (typeof key === 'string') {
        if (typeof this._opts[key] === 'undefined') {
            return null;
        }
        return this._opts[key];
    }

    return this._opts;
};

/**
 * @return {this}
 */
Charcoal.Admin.Component.prototype.init = function () {
    // Do nothing
    return this;
};

/**
 * @return {void}
 */
Charcoal.Admin.Component.prototype.destroy = function () {
    // Do nothing
};

;(function ($, Admin) {
    'use strict';

    /**
     * Parse values as expression objects.
     *
     * @param  {*} exprs - Zero or more expression objects.
     * @return {Object|null}
     */
    function parse_exprs(exprs) {
        if (Array.isArray(exprs)) {
            return Object.assign({}, exprs);
        } else if ($.isPlainObject(exprs)) {
            return exprs;
        }

        return null;
    };

    /**
     * This provides methods used for handling collection source filters.
     *
     * @mixin
     */
    Admin.Mixin_Model_Filters = {
        filters: {},

        parse_filters: parse_exprs,

        /**
         * Parse value as filter object.
         *
         * @param  {*} filter - A filter object.
         * @return {Object|null}
         */
        parse_filter: function (filter) {
            if (Array.isArray(filter)) {
                return { filters: filter };
            } else if ($.isPlainObject(filter)) {
                return filter;
            }

            return null;
        },

        /**
         * Add a filter.
         *
         * @param  {Object|Array} filter - A filter object.
         * @return {this}
         */
        add_filter: function (filter) {
            filter = this.parse_filter(filter);
            if (filter !== null) {
                if (!this.has_filters()) {
                    this.filters = {};
                }

                var key = Charcoal.Admin.uid();
                this.filters[key] = filter;
            }

            return this;
        },

        /**
         * Add filters.
         *
         * @param  {Object|Object[]} filters - Zero or more filter objects.
         * @return {this}
         */
        add_filters: function (filters) {
            filters = this.parse_filters(filters);
            if (filters !== null) {
                if (this.has_filters()) {
                    this.filters = $.extend({}, this.filters, filters);
                } else {
                    this.filters = filters;
                }
            }

            return this;
        },

        /**
         * Remove an existing filter.
         *
         * @param  {string} name - A filter name.
         * @return {this}
         */
        remove_filter: function (name) {
            if (this.has_filters()) {
                delete this.filters[name];
            }

            return this;
        },

        /**
         * Replace an existing filter.
         *
         * @param  {string}            name   - A filter name.
         * @param  {Object|false|null} filter - A filter object or FALSE or NULL to remove.
         * @return {this}
         */
        set_filter: function (name, filter) {
            if (filter === null || filter === false) {
                this.remove_filter(name);
                return this;
            }

            filter = this.parse_filter(filter);
            if (filter !== null) {
                if (!this.has_filters()) {
                    this.filters = {};
                }

                this.filters[name] = filter;
            }

            return this;
        },

        /**
         * Replace existing filters.
         *
         * @param  {Object|Object[]} filters - Zero or more filter objectss.
         * @return {void}
         */
        set_filters: function (filters) {
            this.filters = this.parse_filters(filters);
        },

        /**
         * Determines if a named filter is defined.
         *
         * @return {boolean}
         */
        has_filter: function (key) {
            return this.filters && (key in this.filters);
        },

        /**
         * Determines if any filters are defined.
         *
         * @return {boolean}
         */
        has_filters: function () {
            return !$.isEmptyObject(this.filters);
        },

        /**
         * Retrieve all filters.
         *
         * @return {Object|null}
         */
        get_filters: function () {
            return this.filters;
        }
    };

    /**
     * This provides methods used for handling collection source orders.
     *
     * @mixin
     */
    Admin.Mixin_Model_Orders = {
        orders: {},

        parse_orders: parse_exprs,

        /**
         * Parse value as order object.
         *
         * @param  {*} order - A order object.
         * @return {Object|null}
         */
        parse_order: function (order) {
            if ($.isPlainObject(order)) {
                return order;
            }

            return null;
        },

        /**
         * Add a order.
         *
         * @param  {Object|Array} order - A order object.
         * @return {this}
         */
        add_order: function (order) {
            order = this.parse_order(order);
            if (order !== null) {
                if (!this.has_orders()) {
                    this.orders = {};
                }

                var key = Charcoal.Admin.uid();
                this.orders[key] = order;
            }

            return this;
        },

        /**
         * Add orders.
         *
         * @param  {Object|Object[]} orders - Zero or more order objects.
         * @return {this}
         */
        add_orders: function (orders) {
            orders = this.parse_orders(orders);
            if (orders !== null) {
                if (this.has_orders()) {
                    this.orders = $.extend({}, this.orders, orders);
                } else {
                    this.orders = orders;
                }
            }

            return this;
        },

        /**
         * Remove an existing order.
         *
         * @param  {string} name - A order name.
         * @return {this}
         */
        remove_order: function (name) {
            if (this.has_orders()) {
                delete this.orders[name];
            }

            return this;
        },

        /**
         * Replace an existing order.
         *
         * @param  {string}            name  - A order name.
         * @param  {Object|false|null} order - A order object or FALSE or NULL to remove.
         * @return {this}
         */
        set_order: function (name, order) {
            if (order === null || order === false) {
                this.remove_order(name);
                return this;
            }

            order = this.parse_order(order);
            if (order !== null) {
                if (!this.has_orders()) {
                    this.orders = {};
                }

                this.orders[name] = order;
            }

            return this;
        },

        /**
         * Replace existing orders.
         *
         * @param  {Object|Object[]} orders - Zero or more order objectss.
         * @return {void}
         */
        set_orders: function (orders) {
            this.orders = this.parse_orders(orders);
        },

        /**
         * Determines if a named order is defined.
         *
         * @return {boolean}
         */
        has_order: function (key) {
            return this.orders && (key in this.orders);
        },

        /**
         * Determines if any orders are defined.
         *
         * @return {boolean}
         */
        has_orders: function () {
            return !$.isEmptyObject(this.orders);
        },

        /**
         * Retrieve all orders.
         *
         * @return {Object|null}
         */
        get_orders: function () {
            return this.orders;
        }
    };

}(jQuery, Charcoal.Admin));

;(function (Admin) {
    'use strict';

    /**
     * This provides methods used for handling collection search.
     *
     * @mixin
     */
    Admin.Mixin_Model_Search = {
        search_query: null,

        /**
         * Set the user search query
         *
         * @param  {string|null} query
         * @return {void}
         */
        set_search_query: function (query) {
            this.search_query = query;
        },

        /**
         * Get the user search query
         *
         * @return {string|null}
         */
        get_search_query: function () {
            return this.search_query;
        }
    };

}(Charcoal.Admin));

/* globals widgetL10n */

/**
 * Base Widget (charcoal/admin/widget)
 *
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
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Widget}
 */
Charcoal.Admin.Widget = function (opts) {
    Charcoal.Admin.Component.call(this, opts);

    /* jshint ignore:start */
    this._widget_id;
    this._widget_type;
    /* jshint ignore:end */
    this._suppress_feedback = false;

    if (opts.widget_id) {
        this._widget_id = opts.widget_id;
    }

    if (opts.widget_type) {
        this._widget_type = opts.widget_type;
    }

    if ('suppress_feedback' in opts) {
        this._suppress_feedback = opts.suppress_feedback;
    }

    return this;
};

Charcoal.Admin.Widget.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Widget.prototype.constructor = Charcoal.Admin.Widget;
Charcoal.Admin.Widget.prototype.parent = Charcoal.Admin.Component.prototype;

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Widget.prototype.widget_id = function () {
    return this._widget_id || this.id();
};

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Widget.prototype.widget_type = function () {
    return this._widget_type || this.type();
};

/**
 * @return {Object} The component instance options.
 */
Charcoal.Admin.Widget.prototype.widget_options = function () {
    return this.opts();
};

Charcoal.Admin.Widget.prototype.suppress_feedback = function (flag) {
    if (arguments.length) {
        if (typeof flag === 'boolean') {
            this._suppress_feedback = flag;
        } else {
            throw new TypeError('Must be a boolean, received ' + (typeof flag));
        }
    }

    return this._suppress_feedback || false;
};

/**
 * Called upon save by the component manager
 *
 * @return {Boolean} Default action is set to true.
 */
Charcoal.Admin.Widget.prototype.save = function () {
    return true;
};

/**
 * Animate the widget out on reload
 * Use callback to define what to do after the animation.
 *
 * @param  {Function} [callback] - What to do after the anim_out?
 * @return {this}
 */
Charcoal.Admin.Widget.prototype.anim_out = function (callback) {
    if (typeof callback !== 'function') {
        callback = null;
    }
    this.element().fadeOut(400, callback);
    return this;
};

/**
 * @param  {Function} [callback]  - What to do after the reload?
 * @param  {*}        [with_data] - Additional data to passthrough.
 * @return {this}
 */
Charcoal.Admin.Widget.prototype.reload = function (callback, with_data) {
    var that = this;

    var url  = Charcoal.Admin.admin_url() + 'widget/load' + window.location.search;
    var data = {
        widget_type:    that.widget_type(),
        widget_options: that.widget_options(),
        with_data:      with_data
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    if (this.reloadXHR) {
        this.reloadXHR.abort();
    }

    this.element().addClass('is-loading');

    this.reloadXHR = $.ajax({
        type:        'POST',
        url:         url,
        data:        JSON.stringify(data),
        dataType:    'json',
        contentType: 'application/json'
    });

    var success, failure, complete;

    success = function (response) {
        if (typeof response.widget_id !== 'string') {
            response.feedbacks.push({
                level: 'error',
                message: widgetL10n.loadingFailed
            });

            failure.call(this, response);
            return;
        }

        var wid = response.widget_id;
        that.set_id(wid);
        that.add_opts('id', wid);
        that.add_opts('widget_id', wid);

        if (with_data) {
            that.add_opts('data', response.widget_data);
        }

        that.widget_id = wid;
        that.anim_out(function () {
            that.element().replaceWith(response.widget_html);
            that.set_element($('#' + that.id()));

            // Pure dompe.
            that.element().removeClass('is-loading');
            that.element().hide().fadeIn();
            that.init();
            // Callback
            if (typeof callback === 'function') {
                callback.call(that, response);
            }
        });
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: widgetL10n.loadingFailed
            } ]);
        }
    };

    complete = function () {
        if (!that.suppress_feedback()) {
            Charcoal.Admin.feedback().dispatch();
        }
    };

    Charcoal.Admin.resolveSimpleJsonXhr(
        this.reloadXHR,
        success,
        failure,
        complete
    );

    return this;
};

/**
 * Load the widget into a dialog
 *
 * @param  {Object}   [dialog_opts] - Dialog settings.
 * @param  {Function} [callback]    - What to do after the dialog?
 * @return {BootstrapDialog}
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
        showFooter: showFooter
    };

    var dialogOptions = $.extend({}, defaultOptions, userOptions);
    var alertTemplate = '<div class="alert alert-{type}" role="alert">{text}</div>';

    dialogOptions.onshown = function (dialog) {
        var xhr,
            url      = Charcoal.Admin.admin_url() + 'widget/load',
            data     = dialog_opts;

        xhr = $.ajax({
            method:   'POST',
            url:      url,
            data:     data,
            dataType: 'json'
        });

        xhr.then(function (response, textStatus, jqXHR) {
            if (!response || !response.success) {
                if (response.feedbacks) {
                    return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
                } else {
                    return $.Deferred().reject(jqXHR, textStatus, widgetL10n.loadingFailed);
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
                dialog.setMessage(widgetL10n.loadingFailed);

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
                            errorHtml += alertTemplate.replaceMap({
                                '{type}': error.level,
                                '{text}': error.message
                            });
                        }
                    });
                } else if ($.type(errorThrown) === 'string') {
                    errorHtml = alertTemplate.replaceMap({
                        '{type}': 'danger',
                        '{text}': errorThrown
                    });
                }

                if (errorHtml) {
                    dialog.setMessage(errorHtml);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            });

        Charcoal.Admin.manager().render();
    };

    dialogOptions.message = function (dialog) {
        var $message = $(
            alertTemplate.replaceMap({
                '{type}': 'warning',
                '{text}': widgetL10n.loading
            })
        );

        if (!showHeader) {
            dialog.getModalHeader().addClass('d-none');
        }

        if (!showFooter) {
            dialog.getModalFooter().addClass('d-none');
        }

        dialog.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dialog },
            function (event) {
                event.data.dialog.close();
            }
        );

        return $message;
    };

    return new BootstrapDialog.show(dialogOptions);
};

/**
 * Load the widget into a dialog
 *
 * @param  {Object}   [dialog_opts]        - Dialog settings.
 * @param  {Function} [confirmed_callback] - What to do after the dialog is confirmed?
 * @param  {Function} [cancel_callback]    - What to do after the dialog is canceled?
 * @return {void}
 */
Charcoal.Admin.Widget.prototype.confirm = function (dialog_opts, confirmed_callback, cancel_callback) {
    var defaults = {
        type:     BootstrapDialog.TYPE_DANGER,
        callback: function (result) {
            if (result) {
                if (typeof confirmed_callback === 'function') {
                    confirmed_callback();
                }
            } else {
                if (typeof cancel_callback === 'function') {
                    cancel_callback();
                }
            }
        }
    };

    var opts = $.extend(defaults, dialog_opts);

    BootstrapDialog.confirm(opts);
};

/* globals commonL10n,attachmentWidgetL10n */

/**
 * Keep track of XHR by group
 * @type {{}}
 */
var globalXHR = {};

/**
 * Attachment widget
 * You can associate a perticular object to another
 * using this widget.
 *
 * @see widget.js (Charcoal.Admin.Widget
 */
Charcoal.Admin.Widget_Attachment = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

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

    var that = this;
    $(document).on('switch_language.charcoal', function () {
        var opts = that.opts();
        // Set widget lang to current Charcoal Admin Lang
        opts.widget_options.lang = Charcoal.Admin.lang();
        that.set_opts(opts);
        that.reload();
    });

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
Charcoal.Admin.Widget_Attachment.prototype.init = function () {
    var $container = this.element().find('.js-attachment-sortable > .js-grid-container');
    if ($container.length) {
        this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
            $container.sortable('refreshPositions');
        });

        $container.sortable({
            handle:      '[draggable="true"]',
            placeholder: 'card c-attachments_row -placeholder',
            start:       function (event, ui) {
                var $heading     = ui.item.children('.card-header'),
                    $collapsible = $heading.find('[data-toggle="collapse"]');

                if (!$collapsible.hasClass('collapsed')) {
                    ui.item.children('.collapse').collapse('hide');
                }
            }
        }).disableSelection();
    }

    this.listeners();
    return this;
};

/**
 * Check if the widget has something a dirty state that needs to be saved.
 * @return Boolean     Widget dirty of not.
 */
Charcoal.Admin.Widget_Attachment.prototype.is_dirty = function () {
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Attachment_Widget Chainable.
 */
Charcoal.Admin.Widget_Attachment.prototype.set_dirty_state = function (bool) {
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.listeners = function () {
    // Scope
    var that = this,
        $container = this.element().find('.c-attachments_container > .js-grid-container');

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.attachments', '.js-attachments-collapse', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $attachments.find('.collapse.show').collapse('hide');
        })
        .on('click.charcoal.attachments', '.js-attachments-expand', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $attachments.find('.collapse:not(.show)').collapse('show');
        })
        .on('click.charcoal.attachments', '.js-add-attachment', function (e) {
            e.preventDefault();

            var _this = $(this);

            var type = _this.data('type');
            if (!type) {
                return false;
            }

            var id = _this.data('id');
            if (id) {
                that.add({
                    id:   id,
                    type: type
                });
                that.join(function () {
                    that.reload();
                });
            } else {
                var attachment_struct = {
                    title:     _this.data('title') || attachmentWidgetL10n.editObject,
                    formIdent: _this.data('form-ident'),
                    skipForm:  _this.data('skip-form')
                };

                that.create_attachment(type, 0, null, attachment_struct, function (response) {
                    if (response.success) {
                        response.obj.id = response.obj_id;
                        that.add(response.obj);
                        that.join(function () {
                            that.reload();
                        });
                    }
                });
            }
        })
        .on('click.charcoal.attachments', '.js-attachment-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit':
                    var type = _this.data('type'),
                        id = _this.data('id');

                    if (!type || !id) {
                        break;
                    }

                    var attachment_struct = {
                        title:     _this.data('title') || attachmentWidgetL10n.editObject,
                        formIdent: _this.data('form-ident')
                    };

                    that.create_attachment(type, id, null, attachment_struct, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm({
                        title:      attachmentWidgetL10n.confirmRemoval,
                        message:    commonL10n.confirmAction,
                        btnOKLabel: commonL10n.removeObject,
                        callback:   function (result) {
                            if (result) {
                                that.remove_join(_this.data('id'), function () {
                                    that.reload();
                                });
                            }
                        }
                    });
                    break;

                case 'add-object':
                    var attachment_title = _this.data('title'),
                        attachment_type  = _this.data('attachment'),
                        container_type   = _this.data('type'),
                        container_id     = _this.data('id'),
                        container_group  = _this.data('group'),
                        form_ident       = _this.data('form-ident'),
                        skip_form        = _this.data('skip-form'),
                        container_struct = {
                            id:       container_id,
                            type:     container_type,
                            group:    container_group
                        };
                    attachment_struct = {
                        title:     attachment_title,
                        formIdent: form_ident,
                        skipForm:  skip_form
                    };

                    that.create_attachment(
                        attachment_type,
                        0,
                        container_struct,
                        attachment_struct,
                        function (response) {
                            if (response.success) {
                                that.add_object_to_container(
                                    {
                                        id:   response.obj_id,
                                        type: response.obj.type
                                    },
                                    container_struct
                                );
                            }
                        }
                    );

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
Charcoal.Admin.Widget_Attachment.prototype.select_attachment = function (elem) {
    if (!elem.data('id') || !elem.data('type')) {
        // Invalid
        return this;
    }
};

Charcoal.Admin.Widget_Attachment.prototype.create_attachment = function (type, id, parent, customOpts, callback) {
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    if (!customOpts) {
        customOpts = {};
    }

    // Scope
    var that = this;

    if (!parent) {
        var opts = that.opts();
        parent   = {
            obj_type: opts.data.obj_type,
            obj_id:   opts.data.obj_id,
            group:    opts.data.group
        };
    }

    // Skip quick form
    if (customOpts.skipForm) {
        this.xhr = $.ajax({
            type: 'POST',
            url: 'object/save',
            data: {
                obj_type:  type,
                obj_id:    id,
                pivot:     parent
            }
        });

        this.xhr.done(function (response) {
            if (response.feedbacks) {
                Charcoal.Admin.feedback(response.feedbacks).dispatch();
            }
            callback(response);
        });

        Charcoal.Admin.manager().render();
        return;
    }

    var defaultOpts = {
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quick-form',
        widget_options: {
            obj_type:  type,
            obj_id:    id,
            form_ident: customOpts.formIdent || null,
            form_data: {
                pivot: parent
            }
        }
    };

    var immutableOpts = {};
    var dialogOpts = $.extend({}, defaultOpts, customOpts, immutableOpts);

    var dialog = this.dialog(dialogOpts, function (response) {
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
                    callback(response);
                    dialog.close();
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
Charcoal.Admin.Widget_Attachment.prototype.add_object_to_container = function (attachment, container, grouping) {
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
Charcoal.Admin.Widget_Attachment.prototype.add = function (obj) {
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);

    var template = this.element().find('.js-attachment-template').clone();
    template.find('.js-attachment').data('id', obj.id).data('type', obj.type);
    this.element().find('.c-attachments_container > .js-grid-container').append(template);

    return this;

};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.save = function () {
    if (this.is_dirty()) {
        return false;
    }

    // Create join from current list.
    this.join();
};

Charcoal.Admin.Widget_Attachment.prototype.join = function (cb) {
    if (!$('#' + this.element().attr('id')).length) {
        return ;
    }
    // Scope
    var that = this;

    var opts = that.opts();

    var data = {
        obj_type:    opts.data.obj_type,
        obj_id:      opts.data.obj_id,
        attachments: [],
        group:       opts.data.group
    };

    this.element().find('.c-attachments_container').find('.js-attachment').each(function (i) {
        var $this = $(this);
        var id    = $this.data('id');
        var type  = $this.data('type');

        data.attachments.push({
            attachment_id:   id,
            attachment_type: type, // Further use.
            position:        i
        });
    });

    if (typeof globalXHR[opts.data.group] !== 'undefined') {
        globalXHR[opts.data.group].abort();
    }

    globalXHR[opts.data.group] = $.post('join', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
        delete globalXHR[opts.data.group];
    }, 'json');
};

/**
 * [remove_join description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.remove_join = function (id, cb) {
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
Charcoal.Admin.Widget_Attachment.prototype.widget_options = function () {
    return this.opts('widget_options');
};

/* globals moment */
/**
 * Table widget used for listing collections of objects
 * charcoal/admin/widget/table
 *
 * Require:
 * - jQuery
 * - Boostrap3-Dialog
 * - Moment.js
 *
 * @mixes Charcoal.Admin.Mixin_Model_Search
 * @mixes Charcoal.Admin.Mixin_Model_Filters
 * @mixes Charcoal.Admin.Mixin_Model_Orders
 *
 * @param  {Object}  opts Options for widget
 */

Charcoal.Admin.Widget_Card_Collection = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Card_Collection properties
    this.obj_type       = null;
    this.widget_id      = null;
    this.table_selector = null;
    this.pagination     = {
        page: 1,
        num_per_page: 50
    };
    this.list_actions = {};
    this.object_actions = {};

    this.template = this.properties = this.properties_options = undefined;

    this.sortable         = false;
    this.sortable_handler = null;
};

Charcoal.Admin.Widget_Card_Collection.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Card_Collection.prototype.constructor = Charcoal.Admin.Widget_Card_Collection;
Charcoal.Admin.Widget_Card_Collection.prototype.parent = Charcoal.Admin.Widget.prototype;

Object.assign(Charcoal.Admin.Widget_Card_Collection.prototype, Charcoal.Admin.Mixin_Model_Search);
Object.assign(Charcoal.Admin.Widget_Card_Collection.prototype, Charcoal.Admin.Mixin_Model_Filters);
Object.assign(Charcoal.Admin.Widget_Card_Collection.prototype, Charcoal.Admin.Mixin_Model_Orders);

/**
 * Necessary for a widget.
 */
Charcoal.Admin.Widget_Card_Collection.prototype.init = function () {
    this.set_properties().bind_events();
};

Charcoal.Admin.Widget_Card_Collection.prototype.set_properties = function () {
    var opts = this.opts();

    this.obj_type           = opts.data.obj_type           || this.obj_type;
    this.widget_id          = opts.id                      || this.widget_id;
    this.table_selector     = '#' + this.widget_id;
    this.sortable           = opts.data.sortable           || this.sortable;
    this.template           = opts.data.template           || this.template;
    this.card_template      = opts.data.card_template      || this.card_template;
    this.num_columns        = opts.data.num_columns        || this.num_columns;
    this.collection_ident   = opts.data.collection_ident   || 'default'; // @todo remove the hardcoded shit

    if (('properties' in opts.data) && Array.isArray(opts.data.properties)) {
        this.properties = opts.data.properties;
    }

    if (('properties_options' in opts.data) && $.isPlainObject(opts.data.properties_options)) {
        this.properties_options = opts.data.properties_options;
    }

    if ('filters' in opts.data) {
        this.set_filters(opts.data.filters);
    }

    if ('orders' in opts.data) {
        this.set_orders(opts.data.orders);
    }

    if (('pagination' in opts.data) && $.isPlainObject(opts.data.pagination)) {
        this.pagination = opts.data.pagination;
    }

    if ('list_actions' in opts.data) {
        if (Array.isArray(opts.data.list_actions)) {
            this.list_actions = Object.assign({}, opts.data.list_actions);
        } else if ($.isPlainObject(opts.data.list_actions)) {
            this.list_actions = opts.data.list_actions;
        }
    }

    if ('object_actions' in opts.data) {
        if (Array.isArray(opts.data.object_actions)) {
            this.object_actions = Object.assign({}, opts.data.object_actions);
        } else if ($.isPlainObject(opts.data.object_actions)) {
            this.object_actions = opts.data.object_actions;
        }
    }

    switch (opts.lang) {
        case 'fr':
            moment.locale('fr-ca');
            break;
        case 'en':
            moment.locale('en-ca');
            break;
        default:
            moment.locale(opts.lang);
            break;
    }

    $('.js-last-time', this.table_selector).each(function () {
        $(this).html(moment.unix($(this).attr('data-time')).fromNow());
    });

    return this;
};

/**
 * @see Charcoal.Admin.Widget_Table.prototype.bind_events()
 *     Similar method.
 */
Charcoal.Admin.Widget_Card_Collection.prototype.bind_events = function () {
    if (this.sortable_handler !== null) {
        this.sortable_handler.destroy();
    }

    var that = this;

    var $sortable_table = $('.js-sortable', that.table_selector);
    if ($sortable_table.length > 0) {
        this.sortable_handler = new window.Sortable.default($sortable_table.get(), {
            draggable: '.js-sortable-item',
            handle: '.js-sortable-handle',
            mirror: {
                constrainDimensions: true,
            }
        }).on('mirror:create', function (event) {
            var originalCells = event.originalSource.querySelectorAll(':scope .js-sortable-item');
            var mirrorCells = event.source.querySelectorAll(':scope .js-sortable-item');
            originalCells.forEach(function (cell, index) {
                mirrorCells[index].style.width = cell.offsetWidth + 'px';
            });
        }).on('sortable:stop', function (event) {
            if (event.oldIndex !== event.newIndex) {
                var rows = Array.from(event.newContainer.querySelectorAll(':scope > .js-sortable-item')).map(function (row) {
                    if (row.classList.contains('draggable-mirror') || row.classList.contains('draggable--original')) {
                        return '';
                    } else {
                        return row.getAttribute('data-id');
                    }
                }).filter(function (row) {
                    return row !== '';
                });

                $.ajax({
                    method: 'POST',
                    url: Charcoal.Admin.admin_url() + 'object/reorder',
                    data: {
                        obj_type: that.obj_type,
                        obj_orders: rows,
                        starting_order: 1
                    },
                    dataType: 'json'
                }).done(function (response) {
                    console.debug(response);
                    if (response.feedbacks) {
                        Charcoal.Admin.feedback(response.feedbacks).dispatch();
                    }
                });
            }
        });
    }

    $('.js-jump-page-form', that.table_selector).on('submit', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = parseInt($this.find('input').val());

        if (page_num) {
            that.pagination.page = page_num;
            that.reload(null, true);
        }
    });

    $('.js-page-switch', that.table_selector).on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');

        console.log(page_num);

        that.pagination.page = page_num;
        that.reload(null, true);
    });
};

Charcoal.Admin.Widget_Card_Collection.prototype.widget_options = function () {
    return {
        obj_type:          this.obj_type,
        template:          this.template,
        collection_ident:  this.collection_ident,
        card_template:     this.card_template,
        num_columns:       this.num_columns,
        collection_config: {
            properties:         this.properties,
            properties_options: this.properties_options,
            search_query:       this.get_search_query(),
            filters:            this.get_filters(),
            orders:             this.get_orders(),
            pagination:         this.pagination,
            list_actions:       this.list_actions,
            object_actions:     this.object_actions
        }
    };
};

/* eslint-disable consistent-this */
/* globals commonL10n,formWidgetL10n,URLSearchParams */
/**
 * Form widget that manages data sending
 * charcoal/admin/widget/form
 *
 * Require:
 * - jQuery
 * - Boostrap3-Dialog
 *
 * @param  {Object}  opts Options for widget
 */

Charcoal.Admin.Widget_Form = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Form properties
    this.widget_id         = null;
    this.obj_type          = null;
    this.obj_id            = null;
    this.save_action       = 'object/save';
    this.update_action     = 'object/update';
    this.form_selector     = null;
    this.form_working      = false;
    this.submitted_via     = null;
    this.is_new_object     = false;
    this.xhr               = null;
    this.useDefaultAction  = false;
    this.confirmed         = false;

    this.update_tab_ident();

    var lang = $('[data-lang]:not(.d-none)').data('lang');
    if (lang) {
        Charcoal.Admin.setLang(lang);
    }

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype             = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent      = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts) {
    this.widget_id        = opts.id || this.widget_id;
    this.obj_type         = opts.data.obj_type || this.obj_type;
    this.obj_id           = Charcoal.Admin.parseNumber(opts.data.obj_id || this.obj_id);
    this.form_selector    = opts.data.form_selector || this.form_selector;
    this.isTab            = opts.data.tab;
    this.group_conditions = opts.data.group_conditions;
    this.$form            = $(this.form_selector);
    this.allow_reload     = opts.data.allow_reload;
    this.useDefaultAction = opts.data.use_default_action;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.init = function () {
};

Charcoal.Admin.Widget_Form.prototype.widget_options = function () {
    var options = this.parent.widget_options.call(this);

    return $.extend({}, options, this.opts('data'));
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function () {
    var that = this;

    var $sidebar = $('.js-sidebar-widget', this.form_selector);

    // Submit the form via ajax
    $(that.form_selector)
        .on('submit.charcoal.form', function (event) {
            event.preventDefault();
            that.submit_form(this);
        })
        .find(':submit')
        .on('click.charcoal.form', function () {
            that.submitted_via = this;
        });

    // Any delete button should trigger the delete-object method.
    $('.js-obj-delete', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.delete_object(this);
    });

    // Reset button
    $('.js-reset-form', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        $(that.form_selector)[0].reset();
    });

    // Revision button
    $('.js-obj-revision', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.view_revision(this);
    });

    // Back-to-list button
    $('.js-obj-list', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.back_to_list(this);
    });

    // Language switcher
    $('.js-lang-switch button', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    window.onpopstate = function () {
        that.update_tab_ident();
    };

    this.parse_group_conditions();

    // crappy push state
    if (that.isTab) {
        $(this.form_selector).on('shown.bs.tab', '.js-group-tabs', function (event) {
            var $tab   = $(event.target); // active tab
            var params = [];

            var urlParams = Charcoal.Admin.queryParams();

            // Skip push state for same state.
            if (urlParams.tab_ident !== undefined &&
                $tab.data('tab-ident') === urlParams.tab_ident
            ) {
                return;
            }

            urlParams.tab_ident = $tab.data('tab-ident');

            for (var param in urlParams) {
                params.push(param + '=' + urlParams[param]);
            }

            history.pushState('', '', window.location.pathname + '?' + params.join('&'));
        });
    }

    /*if (that.isTab) {
         $(that.form_selector).on('click', '.js-group-tabs', function (event) {
             event.preventDefault();
             var href = $(this).attr('href');
             $(that.form_selector).find('.js-group-tab').addClass('d-none');
             $(that.form_selector).find('.js-group-tab.' + href).removeClass('d-none');
             $(this).parent().addClass('active').siblings('.active').removeClass('active');
         });
     }*/

};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.parse_group_conditions = function () {
    var that = this;

    $.each(this.group_conditions, function (target, conditions) {
        var isValid = that.validate_group_conditions(target);
        if (!isValid) {
            that.toggle_conditional_group(target, isValid, false);
        }

        $.each(conditions, function (index, condition) {
            $(that.form_selector).on('change.charcoal.form', '#' + condition.input_id, {
                condition_target: target
            }, function (event) {
                var isValid = that.validate_group_conditions(event.data.condition_target);
                that.toggle_conditional_group(event.data.condition_target, isValid);
            });
        });
    });
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.validate_group_conditions = function (target) {
    var conditions = this.group_conditions[target];
    var that       = this;
    var valid      = true;

    $.each(conditions, function (index, condition) {
        var $input    = that.$form.find('#' + condition.input_id);
        var input_val = that.get_input_value($input);

        switch (JSON.stringify(condition.operator)) {
            case '"!=="':
            case '"!="':
            case '"!"':
            case '"not"':
                if (input_val === condition.value) {
                    valid = false;
                    return;
                }
                break;
            default:
            case '"==="':
            case '"=="':
            case '"="':
            case '"is"':
                if (input_val !== condition.value) {
                    valid = false;
                    return;
                }
                break;
        }

    });

    return valid;
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.toggle_conditional_group = function (group, flag, animate) {
    var $group  = this.$form.find('#' + group);
    var $inputs = $group.find('select, input, textarea');
    animate     = animate !== undefined ? animate : true;

    var complete = function () {
        $inputs.each(function () {
            $(this).attr('disabled', !flag);
        });
    };

    if (flag) {
        if (animate) {
            $group.slideDown({
                easing: 'easeInOutQuad',
                start:  complete
            });
        } else {
            $group.show(0, complete);
        }
    } else {
        if (animate) {
            $group.slideUp({
                easing:   'easeInOutQuad',
                complete: complete
            });
        } else {
            $group.hide(0, complete);
        }
    }
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.get_input_value = function ($input) {
    // skip if disabled
    if ($input.attr('disabled') === 'disabled') {
        return null;
    }

    var val;

    var $inputType = $input.attr('type');
    switch ($inputType) {
        case 'select':
            val = $input.find(':selected').val();
            break;
        case 'checkbox':
            val = $input.is(':checked');
            break;
        default:
            val = $input.val();
            break;
    }

    return val;
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.update_tab_ident = function () {
    var urlParams = Charcoal.Admin.queryParams();

    if ('tab_ident' in urlParams) {
        $('.js-group-tabs[data-tab-ident="' + urlParams.tab_ident + '"]').tab('show');
    }
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @param  Element form - The submitted form.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.submit_form = function (form) {
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

    if (this.submitted_via && this.submitted_via.name) {
        form_data.append(this.submitted_via.name, this.submitted_via.value || true);
    }

    if (this.confirmed) {
        form_data.append('confirmed', true);
    }

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

Charcoal.Admin.Widget_Form.prototype.request_done = function ($form, $trigger, response, textStatus, jqXHR) {
    if (!response || !response.success) {
        if (response.feedbacks) {
            return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
        } else {
            return $.Deferred().reject(jqXHR, textStatus, commonL10n.errorOccurred);
        }
    }

    return $.Deferred().resolve(response, textStatus, jqXHR);
};

Charcoal.Admin.Widget_Form.prototype.request_success = function ($form, $trigger, response/* textStatus, jqXHR */) {
    this.confirmed = false;

    if (response.feedbacks) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.need_confirmation) {
        Charcoal.Admin.feedback()
            .add_action({
                label:    commonL10n.cancel,
                cssClass: 'btn-danger',
                callback: function () {
                    BootstrapDialog.closeAll();
                }
            })
            .add_action({
                label:    commonL10n.continue,
                callback: function () {
                    //TODO THIS IS NOT IDEAL ... In the future,
                    // receiving an instance of BootstrapDialog would be better,
                    // unfortunately, this is not the case. Good day sir.
                    BootstrapDialog.closeAll();

                    this.confirmed = true;
                    this.submit_form($form[0]);
                }.bind(this)
            });
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label:    commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }

    if (!this.useDefaultAction && this.is_new_object) {
        this.suppress_feedback(true);

        if (response.next_url) {
            window.location.href = Charcoal.Admin.admin_url() + response.next_url;
        } else {
            var params = new URLSearchParams(window.location.search);

            window.location.href =
                Charcoal.Admin.admin_url() +
                'object/edit?' +
                (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
                (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
                'obj_type=' + this.obj_type +
                '&obj_id=' + response.obj_id;
        }
    } else {
        if (this.allow_reload) {
            var manager = Charcoal.Admin.manager();
            var widgets = manager.components.widgets;

            $.each(widgets, function (i, widget) {
                widget.reload();
            }.bind(this));
        }
    }
};

Charcoal.Admin.Widget_Form.prototype.request_failed = function ($form, $trigger, jqXHR, textStatus, errorThrown) {
    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
        Charcoal.Admin.feedback(jqXHR.responseJSON.feedbacks);
    } else {
        var message = (this.is_new_object ? formWidgetL10n.createFailed : formWidgetL10n.updateFailed);
        var error   = errorThrown || commonL10n.errorOccurred;

        Charcoal.Admin.feedback([ {
            level:   'error',
            message: commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': message,
                '[[ errorThrown ]]':  error
            })
        } ]);
    }
};

Charcoal.Admin.Widget_Form.prototype.request_complete = function ($form, $trigger/*, .... */) {
    if (!this.suppress_feedback()) {
        Charcoal.Admin.feedback().dispatch();
        this.enable_form($form, $trigger);
    }

    this.submitted_via = null;

    this.suppress_feedback(false);

    this.form_working = this.is_new_object = false;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.disable_form = function ($form, $trigger) {
    if ($form) {
        $form.prop('disabled', true);
    }

    if ($trigger) {
        $trigger.prop('disabled', true);
    }

    if (this.submitted_via) {
        this.disable_button(this.submitted_via);
    }

    return this;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.enable_form = function ($form, $trigger) {
    if ($form) {
        $form.prop('disabled', false);
    }

    if ($trigger) {
        $trigger.prop('disabled', false);
    }

    if (this.submitted_via) {
        this.enable_button(this.submitted_via);
    }

    return this;
};

/**
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.disable_button = function ($trigger) {
    if (!($trigger instanceof jQuery)) {
        $trigger = $($trigger);
    }

    $trigger.prop('disabled', true)
        .children('.fa').removeClass('d-none')
        .next('.btn-label').addClass('sr-only');

    return this;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.enable_button = function ($trigger) {
    if (!($trigger instanceof jQuery)) {
        $trigger = $($trigger);
    }

    $trigger.prop('disabled', false)
        .children('.fa').addClass('d-none')
        .next('.btn-label').removeClass('sr-only');

    return this;
};

/**
 * @return string The requested URL for processing the form.
 */
Charcoal.Admin.Widget_Form.prototype.request_url = function () {
    if (this.useDefaultAction) {
        return this.$form.attr('action');
    } else if (this.is_new_object) {
        return Charcoal.Admin.admin_url() + this.save_action;
    } else {
        return Charcoal.Admin.admin_url() + this.update_action;
    }
};

/**
 * Handle the "revision" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.view_revision = function (/* form */) {
    var type = this.obj_type,
        id   = this.obj_id;

    var defaultOpts = {
        size:           BootstrapDialog.SIZE_WIDE,
        title:          formWidgetL10n.revisions,
        widget_type:    'charcoal/admin/widget/object-revisions',
        widget_options: {
            obj_type:  type,
            obj_id:    id
        }
    };

    var dialogOpts = $.extend({}, defaultOpts);

    this.dialog(dialogOpts, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id:   response.widget_id,
                type: 'charcoal/admin/widget/object-revisions',
                obj_type: type,
                obj_id: id
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * Hande the "back to list" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.back_to_list = function () {
    var params     = new URLSearchParams(window.location.search);
    window.location.href = 'object/collection?' +
        (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
        (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
        'obj_type=' + this.obj_type;
};

/**
 * Handle the "delete" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.delete_object = function (/* form */) {
    var that       = this;
    var params     = new URLSearchParams(window.location.search);
    var successUrl = Charcoal.Admin.admin_url() +
        'object/collection?' +
        (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
        (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
        'obj_type=' + this.obj_type;

    if (!that.obj_type || !that.obj_id) {
        var error = {
            level: 'warning',
            message: commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': formWidgetL10n.deleteFailed,
                '[[ errorThrown ]]': commonL10n.invalidObject
            })
        };
        Charcoal.Admin.feedback([ error ]).dispatch();
        return;
    }

    BootstrapDialog.confirm({
        title:      formWidgetL10n.confirmDeletion,
        type:       BootstrapDialog.TYPE_DANGER,
        message:    $('<p>' + commonL10n.confirmAction + '</p><p class="mb-0">' + commonL10n.cantUndo + '</p>'),
        btnOKLabel: commonL10n.delete,
        callback:   function (result) {
            if (result) {
                var url  = Charcoal.Admin.admin_url() + 'object/delete';
                var data = {
                    obj_type: that.obj_type,
                    obj_id:   that.obj_id
                };
                var xhr = $.ajax({
                    method:   'POST',
                    url:      url,
                    data:     data,
                    dataType: 'json'
                });

                Charcoal.Admin.resolveSimpleJsonXhr(
                    xhr,
                    // Success
                    function () {
                        window.location.href = successUrl;
                    },
                    // Failure
                    function (response) {
                        if (response.feedbacks.length) {
                            Charcoal.Admin.feedback(response.feedbacks);
                        } else {
                            Charcoal.Admin.feedback([ {
                                level:   'error',
                                message: formWidgetL10n.deleteFailed
                            } ]);
                        }
                    },
                    // Complete
                    function () {
                        if (!that.suppress_feedback()) {
                            Charcoal.Admin.feedback().dispatch();
                        }
                    }
                );
            }
        }
    });
};

/**
 * reload callback
 */
Charcoal.Admin.Widget_Form.prototype.reload = function (callback) {
    // Call supra class
    Charcoal.Admin.Widget.prototype.reload.call(this, function (that, response) {
        // Callback
        if (typeof callback === 'function') {
            callback.call(that, response);
        }
        // Re render.
        // This is not good.
        Charcoal.Admin.manager().render();
    }, true);

    $(document).off('charcoal.form');

    return this;
};

/**
 * Switch languages for all l10n elements in the form
 */
Charcoal.Admin.Widget_Form.prototype.switch_language = function (lang) {
    var currentLang = Charcoal.Admin.lang();
    if (currentLang !== lang) {
        Charcoal.Admin.setLang(lang);
        $('[data-lang][data-lang!=' + lang + ']').addClass('d-none');
        $('[data-lang][data-lang=' + lang + ']').removeClass('d-none');

        $('[data-lang-switch][data-lang-switch!=' + lang + ']')
            .removeClass('btn-primary')
            .addClass('btn-outline-primary');

        $('[data-lang-switch][data-lang-switch=' + lang + ']')
            .removeClass('btn-outline-primary')
            .addClass('btn-primary');

        $(document).triggerHandler({
            type: 'switch_language.charcoal'
        });
    }
};

/* globals echarts, widgetL10n */
/**
 * Graph widget used to display graphical charts
 * charcoal/admin/widget/graph
 *
 * Require:
 * - jQuery
 * - echarts {@link https://ecomfe.github.io/echarts-doc/public/en/api.html#echarts}
 *
 * @param  {Object}  opts Options for widget
 */

var Graph = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);
};

Graph.prototype            = Object.create(Charcoal.Admin.Widget.prototype);
Graph.prototype.contructor = Graph;
Graph.prototype.parent     = Charcoal.Admin.Widget.prototype;

Graph.prototype.init = function () {
    // Elements
    this.$widget = this.element();

    var chart = echarts.init(this.$widget.find('.js-graph-container').get(0));

    chart.showLoading({
        text: widgetL10n.loading,
    });
    chart.hideLoading();

    chart.setOption(this.echartsOptions());

    $(window).on('resize', function () {
        chart.resize();
    });
};

Graph.prototype.echartsOptions = function () {
    var defaultOpts = {
        color:   this._opts.data.colors,
        tooltip: {
            trigger: 'item'
        },
        toolbox: {
            show: true
        }
    };

    return $.extend(true, defaultOpts, this._opts.data.options);
};

Charcoal.Admin.Widget_Graph = Graph;

/**
 * Map sidebar
 *
 * According lat, lon or address must be specified
 * Styles might be defined as well.
 *
 * @param  {Object}  opts Options for widget
 */

Charcoal.Admin.Widget_Map = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this._controller = undefined;

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
Charcoal.Admin.Widget_Map.prototype.init = function () {
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

Charcoal.Admin.Widget_Map.prototype.activate_map = function () {
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
        places: {
            first: {
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

    this.controller().set_styles([
        {
            featureType: 'poi',
            elementType: 'all',
            stylers: [
                { visibility: 'off' }
            ]
        }
    ]);

    this.controller().remove_focus();
    this.controller().init();

};

Charcoal.Admin.Widget_Map.prototype.controller = function () {
    return this._controller;
};

Charcoal.Admin.Widget_Map.prototype.coords = function () {
    return this.opts('coords');
};

/* globals commonL10n, objectRevisionsWidgetL10n */
/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Object_Revisions = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this.extra_form_data = opts.extra_form_data || {};

    this.xhr = null;
    this.obj_id = Charcoal.Admin.parseNumber(opts.obj_id) || 0;
    this.obj_type = opts.obj_type;

    return this;
};
Charcoal.Admin.Widget_Object_Revisions.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Object_Revisions.prototype.constructor = Charcoal.Admin.Widget_Object_Revisions;
Charcoal.Admin.Widget_Object_Revisions.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Object_Revisions.prototype.init = function () {
    this.bind_events();
};

Charcoal.Admin.Widget_Object_Revisions.prototype.bind_events = function () {
    var that = this;

    $('#' + this.id()).on('click.object.revisions', '.js-obj-revert', this.revert.bind(this));

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

Charcoal.Admin.Widget_Object_Revisions.prototype.revert = function (event) {
    event.preventDefault();

    var url = Charcoal.Admin.admin_url() + 'object/revert-revision';
    var data = {
        obj_type: this.obj_type,
        obj_id: this.obj_id,
        rev_num: $(event.currentTarget).attr('data-rev-num')
    };

    BootstrapDialog.show({
        title: objectRevisionsWidgetL10n.title,
        message: objectRevisionsWidgetL10n.message,
        buttons: [ {
            id: 'ok-btn',
            label: objectRevisionsWidgetL10n.restore,
            action: function (dialog) {
                dialog.close();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            Charcoal.Admin.feedback().push([
                                {
                                    level: 'error',
                                    message: objectRevisionsWidgetL10n.restoreError
                                }
                            ]);
                            Charcoal.Admin.feedback().dispatch();
                        }
                    },
                    error: function () {
                        Charcoal.Admin.feedback().push([
                            {
                                level: 'error',
                                message: objectRevisionsWidgetL10n.restoreError
                            }
                        ]);
                        Charcoal.Admin.feedback().dispatch();
                    }
                });
            }
        } ]
    });
};

Charcoal.Admin.Widget_Object_Revisions.prototype.disable_form = Charcoal.Admin.Widget_Form.prototype.disable_form;

Charcoal.Admin.Widget_Object_Revisions.prototype.enable_form = Charcoal.Admin.Widget_Form.prototype.enable_form;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_url = Charcoal.Admin.Widget_Form.prototype.request_url;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_done = Charcoal.Admin.Widget_Form.prototype.request_done;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_failed = Charcoal.Admin.Widget_Form.prototype.request_failed;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_complete = Charcoal.Admin.Widget_Form.prototype.request_complete;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_success = function ($form, $trigger, response/* ... */) {
    if (response.feedbacks && !this.suppress_feedback()) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label: commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }
};

/* globals commonL10n */
/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this.save_callback = opts.save_callback || '';
    this.cancel_callback = opts.cancel_callback || '';

    this.form_selector = opts.data.form_selector;
    this.$form         = $(this.form_selector);

    this.save_action   = opts.save_action || 'object/save';
    this.update_action = opts.update_action || 'object/update';
    this.extra_form_data = opts.extra_form_data || {};

    this.group_conditions = opts.data.group_conditions;
    this.form_working = false;
    this.is_new_object = false;
    this.xhr = null;
    this.obj_id = Charcoal.Admin.parseNumber(opts.obj_id) || 0;

    return this;
};
Charcoal.Admin.Widget_Quick_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Quick_Form.prototype.constructor = Charcoal.Admin.Widget_Quick_Form;
Charcoal.Admin.Widget_Quick_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

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

    this.parse_group_conditions();
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.parse_group_conditions = function () {
    var that = this;

    $.each(this.group_conditions, function (target, conditions) {
        var isValid = that.validate_group_conditions(target);
        if (!isValid) {
            that.toggle_conditional_group(target, isValid, false);
        }

        $.each(conditions, function (index, condition) {
            that.$form.on('change.charcoal.quick.form', '#' + condition.input_id, {
                condition_target: target
            }, function (event) {
                var isValid = that.validate_group_conditions(event.data.condition_target);
                that.toggle_conditional_group(event.data.condition_target, isValid);
            });
        });
    });
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.validate_group_conditions = function (target) {
    var conditions = this.group_conditions[target];
    var that       = this;
    var valid      = true;

    $.each(conditions, function (index, condition) {
        var $input    = that.$form.find('#' + condition.input_id);
        var input_val = that.get_input_value($input);

        switch (JSON.stringify(condition.operator)) {
            case '"!=="':
            case '"!="':
            case '"!"':
            case '"not"':
                if (input_val === condition.value) {
                    valid = false;
                    return;
                }
                break;
            default:
            case '"==="':
            case '"=="':
            case '"="':
            case '"is"':
                if (input_val !== condition.value) {
                    valid = false;
                    return;
                }
                break;
        }

    });

    return valid;
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.toggle_conditional_group = function (group, flag, animate) {
    var $group  = this.$form.find('#' + group);
    var $inputs = $group.find('select, input, textarea');
    animate     = animate || true;

    var complete = function () {
        $inputs.each(function () {
            $(this).attr('disabled', !flag);
        });
    };

    if (flag) {
        if (animate) {
            $group.slideDown({
                easing: 'easeInOutQuad',
                start:  complete
            });
        } else {
            $group.show(0, complete);
        }
    } else {
        if (animate) {
            $group.slideUp({
                easing:   'easeInOutQuad',
                complete: complete
            });
        } else {
            $group.hide(0, complete);
        }
    }
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.get_input_value = function ($input) {
    // skip if disabled
    if ($input.attr('disabled') === 'disabled') {
        return null;
    }

    var val;

    var $inputType = $input.attr('type');
    switch ($inputType) {
        case 'select':
            val = $input.find(':selected').val();
            break;
        case 'checkbox':
            val = $input.is(':checked');
            break;
        default:
            val = $input.val();
            break;
    }

    return val;
};

Charcoal.Admin.Widget_Quick_Form.prototype.submit_form = function (form) {
    if (this.form_working) {
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    var $trigger, $form, form_data;

    $form = $(form);
    $trigger = $form.find('[type="submit"]');

    if ($trigger.prop('disabled')) {
        return false;
    }

    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    form_data = new FormData(form);

    this.disable_form($form, $trigger);

    var extraFormData = this.extra_form_data;

    for (var data in extraFormData) {
        if (extraFormData.hasOwnProperty(data)){
            form_data.append(data, extraFormData[data]);
        }
    }

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
    if (response.feedbacks && !this.suppress_feedback()) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label: commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }

    this.enable_form($form, $trigger);
    this.form_working = false;

    if (typeof this.save_callback === 'function') {
        this.save_callback(response);
    }
};

Charcoal.Admin.Widget_Quick_Form.prototype.destroy = function () {
    this.$form.off('charcoal.quick.form');
};

/* globals commonL10n,relationWidgetL10n */
/**
 * Relation widget
 * You can associate a specific object to another
 * using this widget.
 *
 * @see widget.js (Charcoal.Admin.Widget)
 */
Charcoal.Admin.Widget_Relation = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this.dirty = false;
    return this;
};

Charcoal.Admin.Widget_Relation.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Relation.prototype.constructor = Charcoal.Admin.Widget_Relation;
Charcoal.Admin.Widget_Relation.prototype.parent = Charcoal.Admin.Widget.prototype;

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
Charcoal.Admin.Widget_Relation.prototype.init = function () {
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-relation-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel c-attachment_placeholder',
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
Charcoal.Admin.Widget_Relation.prototype.is_dirty = function () {
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Widget_Relation Chainable.
 */
Charcoal.Admin.Widget_Relation.prototype.set_dirty_state = function (bool) {
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Relation.prototype.listeners = function () {
    // Scope
    var that = this;

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.relation', '.js-add-relation', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var id = $(this).data('id');
            if (id) {
                that.add({
                    id: id,
                    type: type
                });
                that.create_relation(function () {
                    that.reload();
                });
            } else {
                var title = $(this).data('title') || relationWidgetL10n.editObject;
                that.create_relation_dialog({
                    title: title,
                    widget_options: {
                        form_data: {
                            target_object_type: type,
                            target_object_id: null
                        }
                    }
                }, function (response) {
                    if (response.success) {
                        response.obj.id = response.obj_id;
                        that.add(response.obj);
                        that.create_relation(function () {
                            that.reload();
                        });
                    }
                });
            }
        })
        .on('click.charcoal.relation', '.js-relation-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit':
                    var type = _this.data('type');
                    var id = _this.data('id');
                    if (!type || !id) {
                        break;
                    }
                    var title = _this.data('title') || relationWidgetL10n.editObject;
                    that.create_relation_dialog({
                        title: title,
                        widget_options: {
                            form_data: {
                                target_object_type: type,
                                target_object_id: null
                            }
                        }
                    }, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'unlink':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title:      relationWidgetL10n.confirmRemoval,
                            message:    commonL10n.confirmAction,
                            btnOKLabel: commonL10n.removeObject,
                            callback:   function (result) {
                                if (result) {
                                    that.remove_relation(_this.data('id'), function () {
                                        that.reload();
                                    });
                                }
                            }
                        }
                    );
                    break;
            }
        });
};

/**
 * Dialog that will be used to create a relation between two existing objects.
 *
 * @param  {Object} widgetOptions A set of options for the dialog creation.
 * @return {void}
 */
Charcoal.Admin.Widget_Relation.prototype.create_relation_dialog = function (widgetOptions, callback) {
    widgetOptions = widgetOptions || {};

    var sourceOptions = this.opts().data;
    var defaultOptions = {
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quick-form',
        widget_options: {
            obj_type:           'charcoal/relation/pivot',
            obj_id:             0,
            form_data: {
                group: sourceOptions.group,
                source_object_type: sourceOptions.obj_type,
                source_object_id: sourceOptions.obj_id,
                target_object_type: '',
                target_object_id:   0
            }
        }
    };

    var immutableOptions = {};
    var dialogOptions = $.extend(true, {}, defaultOptions, widgetOptions, immutableOptions);

    var dialog = this.dialog(dialogOptions, function (response) {
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
                    obj_type: dialogOptions.widget_options.type
                },
                obj_id: dialogOptions.widget_options.id,
                save_callback: function (response) {
                    callback(response);
                    dialog.close();
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
Charcoal.Admin.Widget_Relation.prototype.add = function (obj) {
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);
    var $template = this.element().find('.js-relation-template').clone();
    $template.find('.js-relation').attr({
        'data-id': obj.target_object_id,
        'data-type': obj.target_object_type
    });
    this.element().find('.js-relation-sortable').find('.js-grid-container').append($template);

    return this;
};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Relation.prototype.save = function () {
    if (this.is_dirty()) {
        return false;
    }

    // Create relations from current list.
    this.create_relation();
};

Charcoal.Admin.Widget_Relation.prototype.create_relation = function (cb) {
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type: opts.data.obj_type,
        obj_id: opts.data.obj_id,
        group: opts.data.group,
        pivots: []
    };

    this.element().find('.js-relation-container').find('.js-relation').each(function (i) {
        var $this = $(this);
        var id    = $this.attr('data-id');
        var type  = $this.attr('data-type');

        data.pivots.push({
            target_object_id:   id,
            target_object_type: type,
            position: i
        });
    });

    $.post('relation/link', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * [remove_relation description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Relation.prototype.remove_relation = function (id, cb) {
    if (!id) {
        return false;
    }

    // Scope
    var that = this;
    var data = {
        pivot_id: id
    };

    $.post('relation/unlink', data, function () {
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
Charcoal.Admin.Widget_Relation.prototype.widget_options = function () {
    return this.opts('widget_options');
};

/**
 * Search widget used for filtering a list
 * charcoal/admin/widget/search
 *
 * Require:
 * - jQuery
 *
 * @param  {Object}  opts Options for widget
 */
Charcoal.Admin.Widget_Search = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

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

    this.data   = opts.data;
    this.$input = null;

    this._search_filters = false;
    this._search_query   = false;

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
Charcoal.Admin.Widget_Search.prototype.set_remote_widget = function () {
    // Do something about this.
};

Charcoal.Admin.Widget_Search.prototype.init = function () {
    var that  = this,
        $form = this.element();

    this.$input = $form.find('[name="query"]');

    $form.on('submit.charcoal.search', function (event) {
        event.preventDefault();
        that.submit();
    });

    $form.on('reset.charcoal.search', function (event) {
        event.preventDefault();
        that.clear();
    });
};

/**
 * Submit the search filters as expected to all widgets.
 *
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.submit = function () {
    this.set_search_query(this.$input.val());

    Charcoal.Admin.manager().components.widgets.forEach(this.dispatch.bind(this));

    this.set_search_query(null);

    return this;
};

/**
 * Resets the searchable widgets.
 *
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.clear = function () {
    this._search_search  = false;
    this._search_filters = false;

    this.$input.val('');
    this.submit();
    return this;
};

/**
 * Parse a search query.
 *
 * @param  {string} query - The search query.
 * @return {string|null} A search query or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.parse_search_query = function (query) {
    if (typeof query !== 'string') {
        return null;
    }

    query = query.trim();

    if (query.length === 0) {
        return null;
    }

    return query;
};

/**
 * Parse a search query into query filters.
 *
 * @param  {string} query - The search query.
 * @return {array|null} A search request object or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.parse_search_filters = function (query) {
    var words, props, filters = [], sub_filters;

    query = this.parse_search_query(query);

    if (query) {
        words = query.split(/\s/);
        props = this.data.properties || [];
        $.each(words, function (i, word) {
            sub_filters = [];
            $.each(props, function (j, prop) {
                sub_filters.push({
                    property: prop,
                    operator: 'LIKE',
                    value:    ('%' + word + '%')
                });
            });

            filters.push({
                conjunction: 'OR',
                filters:     sub_filters
            });
        });
    }

    if (filters.length) {
        return filters;
    }

    return null;
};

/**
 * Set the search query.
 *
 * @param  {string} query - The search query.
 * @return {void}
 */
Charcoal.Admin.Widget_Search.prototype.set_search_query = function (query) {
    this._search_search  = this.parse_search_query(query);
    this._search_filters = false;
};

/**
 * Get the search query.
 *
 * @return {string|null} The search query or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.search_query = function () {
    if (this._search_search === false) {
        return null;
    }

    return this._search_search;
};

/**
 * Get the search filters.
 *
 * @return {array|null} The query filters object or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.search_filters = function () {
    if (this._search_filters === false) {
        this._search_filters = this.parse_search_filters(this._search_search);
    }

    return this._search_filters;
};

/**
 * Assign the search query or filters on any searchable widget and dispatch request.
 *
 * @param  {object} widget - The widget to search on.
 * @return void
 */
Charcoal.Admin.Widget_Search.prototype.dispatch = function (widget) {
    // Bail early if no widget or if widget is self
    if (!widget || widget === this) {
        return;
    }

    var is_searchable = (typeof widget.set_search_query === 'function');
    var is_filterable = (typeof widget.set_filter === 'function');

    if (!is_searchable && !is_filterable) {
        return this;
    }

    if (is_searchable) {
        var query = this.search_query();
        widget.set_search_query(query);
    }

    if (is_filterable) {
        var filters = this.search_filters();
        widget.set_filter('search', filters);
    }

    if (typeof widget.pagination !== 'undefined') {
        widget.pagination.page = 1;
    }

    widget.reload(null, true);
};

/**
 * Table widget used for listing collections of objects
 * charcoal/admin/widget/table
 *
 * Require:
 * - jQuery
 * - Boostrap3-Dialog
 *
 * @mixes Charcoal.Admin.Mixin_Model_Search
 * @mixes Charcoal.Admin.Mixin_Model_Filters
 * @mixes Charcoal.Admin.Mixin_Model_Orders
 *
 * @param {Object} opts - Options for widget
 */

Charcoal.Admin.Widget_Table = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Table properties
    this.obj_type       = null;
    this.widget_id      = null;
    this.table_selector = null;
    this.pagination     = {
        page: 1,
        num_per_page: 50
    };
    this.list_actions = {};
    this.object_actions = {};

    this.template = this.properties = this.properties_options = undefined;

    this.sortable         = false;
    this.sortable_handler = null;
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Search);
Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Filters);
Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Orders);

/**
 * Necessary for a widget.
 */
Charcoal.Admin.Widget_Table.prototype.init = function () {
    this.set_properties().bind_events();
};

Charcoal.Admin.Widget_Table.prototype.set_properties = function () {
    var opts = this.opts();

    this.obj_type           = opts.data.obj_type           || this.obj_type;
    this.widget_id          = opts.id                      || this.widget_id;
    this.table_selector     = '#' + this.widget_id;
    this.sortable           = opts.data.sortable           || this.sortable;
    this.template           = opts.data.template           || this.template;
    this.collection_ident   = opts.data.collection_ident   || 'default'; // @todo remove the hardcoded shit

    if (('properties' in opts.data) && Array.isArray(opts.data.properties)) {
        this.properties = opts.data.properties;
    }

    if (('properties_options' in opts.data) && $.isPlainObject(opts.data.properties_options)) {
        this.properties_options = opts.data.properties_options;
    }

    if ('filters' in opts.data) {
        this.set_filters(opts.data.filters);
    }

    if ('orders' in opts.data) {
        this.set_orders(opts.data.orders);
    }

    if (('pagination' in opts.data) && $.isPlainObject(opts.data.pagination)) {
        this.pagination = opts.data.pagination;
    }

    if ('list_actions' in opts.data) {
        if (Array.isArray(opts.data.list_actions)) {
            this.list_actions = Object.assign({}, opts.data.list_actions);
        } else if ($.isPlainObject(opts.data.list_actions)) {
            this.list_actions = opts.data.list_actions;
        }
    }

    if ('object_actions' in opts.data) {
        if (Array.isArray(opts.data.object_actions)) {
            this.object_actions = Object.assign({}, opts.data.object_actions);
        } else if ($.isPlainObject(opts.data.object_actions)) {
            this.object_actions = opts.data.object_actions;
        }
    }

    return this;
};

/**
 * @see Charcoal.Admin.Widget_Table.prototype.bind_events()
 *     Similar method.
 */
Charcoal.Admin.Widget_Table.prototype.bind_events = function () {
    if (this.sortable_handler !== null) {
        this.sortable_handler.destroy();
    }

    var that = this;

    var $sortable_table = $('tbody.js-sortable', that.table_selector);
    if ($sortable_table.length > 0) {
        this.sortable_handler = new window.Sortable.default($sortable_table.get(), {
            delay: 150,
            draggable: '.js-table-row',
            handle: '.js-sortable-handle',
            mirror: {
                constrainDimensions: true,
            }
        }).on('mirror:create', function (event) {
            var originalCells = event.originalSource.querySelectorAll(':scope > td');
            var mirrorCells = event.source.querySelectorAll(':scope > td');
            originalCells.forEach(function (cell, index) {
                mirrorCells[index].style.width = cell.offsetWidth + 'px';
            });
        }).on('sortable:stop', function (event) {
            if (event.oldIndex !== event.newIndex) {
                var rows = Array.from(event.newContainer.querySelectorAll(':scope > tr')).map(function (row) {
                    if (row.classList.contains('draggable-mirror') || row.classList.contains('draggable--original')) {
                        return '';
                    } else {
                        return row.getAttribute('data-id');
                    }
                }).filter(function (row) {
                    return row !== '';
                });

                $.ajax({
                    method: 'POST',
                    url: Charcoal.Admin.admin_url() + 'object/reorder',
                    data: {
                        obj_type: that.obj_type,
                        obj_orders: rows,
                        starting_order: 1
                    },
                    dataType: 'json'
                }).done(function (response) {
                    console.debug(response);
                    if (response.feedbacks) {
                        Charcoal.Admin.feedback(response.feedbacks).dispatch();
                    }
                });
            }
        });
    }

    $('.js-jump-page-form', that.table_selector).on('submit', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = parseInt($this.find('input').val());

        if (page_num) {
            that.pagination.page = page_num;
            that.reload(null, true);
        }
    });

    $('.js-page-switch', that.table_selector).on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');
        that.pagination.page = page_num;
        that.reload(null, true);
    });
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function () {
    return {
        obj_type:          this.obj_type,
        template:          this.template,
        sortable:          this.sortable,
        collection_ident:  this.collection_ident,
        collection_config: {
            properties:         this.properties,
            properties_options: this.properties_options,
            search_query:       this.get_search_query(),
            filters:            this.get_filters(),
            orders:             this.get_orders(),
            pagination:         this.pagination,
            list_actions:       this.list_actions,
            object_actions:     this.object_actions
        }
    };
};

/**
 * Base Property Input (charcoal/admin/property/input)
 *
 * Should mimic the PHP equivalent AbstractProperty
 * This will prevent multiple directions in property implementation
 * by giving multiple usefull methods such as ident, val, etc.
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Property}
 */
Charcoal.Admin.Property = function (opts) {
    Charcoal.Admin.Component.call(this, opts);

    /* jshint ignore:start */
    this._ident;
    this._val;
    this._input_type;
    /* jshint ignore:end */

    if (opts) {
        if (typeof opts.ident === 'string') {
            this.set_ident(opts.ident);
        }

        if (typeof opts.val !== 'undefined') {
            this.set_val(opts.val);
        }

        if (typeof opts.input_type !== 'undefined') {
            this.set_input_type(opts.input_type);
        }
    }

    return this;
};

Charcoal.Admin.Property.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Property.prototype.constructor = Charcoal.Admin.Property;
Charcoal.Admin.Property.prototype.parent = Charcoal.Admin.Component.prototype;

/**
 * @override Charcoal.Admin.Property.prototype.element
 *
 * @return {?jQuery} The related jQuery element.
 */
Charcoal.Admin.Property.prototype.element = function () {
    if (!this._element) {
        if (!this.id()) {
            return null;
        }
        this.set_element('#' + this.id());
    }

    return this._element;
};

/**
 * @param  {String} ident - The component instance identifier.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_ident = function (ident) {
    this._ident = ident;
    return this;
};

/**
 * @return {?String} The component instance identifier.
 */
Charcoal.Admin.Property.prototype.ident = function () {
    return this._ident;
};

/**
 * @param  {String} input_type - The component form control type.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_input_type = function (input_type) {
    this._input_type = input_type;
    return this;
};

/**
 * @return {?String} The component form control type.
 */
Charcoal.Admin.Property.prototype.input_type = function () {
    return this._input_type;
};

/**
 * @param  {*} val - The component instance value.
 * @return {this}
 */
Charcoal.Admin.Property.prototype.set_val = function (val) {
    this._val = val;
    return this;
};

/**
 * @return {?String} The component instance value.
 */
Charcoal.Admin.Property.prototype.val = function () {
    return this._val;
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
Charcoal.Admin.Property.prototype.validate = function () {
    // Validate the current
    return {};
};

/**
 * Default save action
 *
 * @return {this}
 */
Charcoal.Admin.Property.prototype.save = function () {
    // Default action = nothing
    return this;
};

/**
 * Error handling
 *
 * @param  {*} data - Could be a simple message, an array, wtv.
 * @return {void}
 */
Charcoal.Admin.Property.prototype.error = function (data) {
    window.console.error(data);
};

/**
 * Upload File Property Control
 */

Charcoal.Admin.Property_Input_File = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.file';
    Charcoal.Admin.Property.call(this, opts);
    this.input_type = 'charcoal/admin/property/input/file';

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_File.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_File.prototype.constructor = Charcoal.Admin.Property_Input_File;
Charcoal.Admin.Property_Input_File.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_File.prototype.init = function () {
    if (!this.input_id) {
        return;
    }

    this.$input  = $('#' + this.input_id);
    this.$file   = $('#' + this.data.file_input_id).or('input[type="file"]', this.$input);
    this.$hidden = $('#' + this.data.hidden_input_id).or('input[type="hidden"]', this.$input);

    this.$previewFile = this.$input.find('.js-preview-file');
    this.$previewText = this.$input.find('.js-preview-text');

    if (!window.elFinderCallback) {
        window.elFinderCallback = {};
    }

    this.set_listeners();
};

Charcoal.Admin.Property_Input_File.prototype.set_listeners = function () {
    if (typeof this.$input === 'undefined') {
        return;
    }

    this.$input
        .on('click' + this.EVENT_NAMESPACE, '.js-remove-file', this.remove_file.bind(this))
        .on('click' + this.EVENT_NAMESPACE, '.js-elfinder', this.load_elfinder.bind(this));

    this.$file.on('change' + this.EVENT_NAMESPACE, this.change_file.bind(this));

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);
};

Charcoal.Admin.Property_Input_File.prototype.remove_file = function (event) {
    event.preventDefault();

    this.$hidden.val('');
    this.$file.val('');

    this.$previewFile.empty();
    this.$previewText.empty();

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');
};

Charcoal.Admin.Property_Input_File.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewFile.empty();
    this.$previewText.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var file = event.target.files[0];

        console.log('[Property_Input_File.change_file]', file);

        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
    }
};

Charcoal.Admin.Property_Input_File.prototype.load_elfinder = function (event) {
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

Charcoal.Admin.Property_Input_File.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewFile.empty();
    this.$previewText.empty();

    if (file && file.url) {
        var path = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');

        console.log('[Property_Input_File.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
    }
};

/**
 * SETTERS
 */

/**
 * Set input id
 * @param {string} input_id ID of the input.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_id = function (input_id) {
    this.input_id = input_id;
    return this;
};

/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_name = function (input_name) {
    this.input_name = input_name;
    return this;
};

/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_File.prototype.set_input_val = function (input_val) {
    this.input_val = input_val;
    return this;
};

Charcoal.Admin.Property_Input_File.prototype.destroy = function () {
    this.$input.off(this.EVENT_NAMESPACE);
    this.$file.off(this.EVENT_NAMESPACE);
};

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

/**
 * Interface for saving audio messages
 * Property_Input_Audio JavaScript class
 * charcoal/admin/property/input/audio
 *
 * @method Property_Input_Audio_Widget
 * @param Object opts
 */
Charcoal.Admin.Property_Input_Audio_Widget = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.audio.widget';

    Charcoal.Admin.Property.call(this, opts);

    this.data    = opts.data;
    this.data.id = opts.id;

    this.init();
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Audio_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Audio_Widget;
Charcoal.Admin.Property_Input_Audio_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init = function () {
    var $el = this.element();

    // Properties for each audio type
    // Since all components can be destroyed, we need to make sure they're initialized with the widget.
    this.text_component    = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/textarea',
        property_class: null
    };
    this.capture_component = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/audio-recorder',
        property_class: Charcoal.Admin.Property_Input_Audio_Recorder
    };
    this.upload_component  = {
        enabled:        false,
        property:       null,
        property_type:  'charcoal/admin/property/input/audio',
        property_class: Charcoal.Admin.Property_Input_Audio
    };

    // Navigation
    this.active_pane = this.data.active_pane || 'text';

    this.$input_text   = $('#' + this.data.text_input_id).or('.js-text-voice-message', $el);
    this.$input_file   = $('#' + this.data.upload_input_id).or('.js-file-input', $el);
    this.$input_hidden = $('#' + this.data.hidden_input_id).or('.js-file-input-hidden', $el);

    if (this.$input_hidden.length === 0) {
        console.error('Missing hidden input to store audio');
    }

    this.bind_events();

    if (this.active_pane) {
        // This ensures the current pane is initialized even if it's already showing.
        // It fixes an issue with AdminManager::render()
        this.init_pane($('#' + this.data.id + '_' + this.active_pane + '_tab'));
        $('#' + this.data.id + '_' + this.active_pane + '_tab').tab('show');
    }
};

/**
 * Create tabular navigation
 */
Charcoal.Admin.Property_Input_Audio_Widget.prototype.bind_events = function () {
    var that = this;

    this.element().on('shown.bs.tab', '[data-toggle="tab"]', function (event) {
        that.init_pane(event.target, false);
    });

    return this;
};

/**
 * Show the selected tab.
 *
 * @param  {String|jQuery} pane - The pane to show.
 * @return {this}
 */
Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_pane = function (pane) {
    if (typeof pane !== 'string') {
        pane = $(pane).attr('data-pane');
    }

    if (pane) {
        var fn;

        this.active_pane = pane;

        fn = 'init_' + pane;
        if (typeof(this[fn]) === 'function') {
            this[fn]();
        }
    }

    return this;
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_text = function () {
    var component = this.text_component;

    if (component.enabled) {
        return;
    }

    if (!component.property) {
        component.enabled = true;

        if (!component.property_class) {
            return;
        }

        if (!this.data.text_input_id) {
            console.error('[Property_Input_Audio_Widget]', 'Missing text-to-speech input');
            return;
        }

        component.property = new component.property_class({
            id:   this.data.text_input_id,
            type: component.property_type
        });
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_upload = function () {
    var component = this.upload_component;

    if (component.enabled) {
        return;
    }

    if (!component.property) {
        component.enabled = true;

        if (!component.property_class) {
            return;
        }

        if (!(this.data.upload_input_id && this.data.hidden_input_id)) {
            console.error('[Property_Input_Audio_Widget]', 'Missing file or hidden input');
            return;
        }

        component.property = new component.property_class({
            id:   this.data.upload_input_id,
            type: component.property_type,
            data: {
                hidden_input_id: this.data.hidden_input_id,
                input_name:      this.data.input_name,
                dialog_title:    this.data.dialog_title,
                elfinder_url:    this.data.elfinder_url
            }
        });
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.init_capture = function () {
    var component = this.capture_component;

    if (component.enabled) {
        return;
    }

    if (component.property) {
        if (component.property_class.is_audio_supported()) {
            console.info('[Property_Input_Audio_Widget]', 'New request for user permission to use media input');
            component.property.get_user_media(true);
        }
    } else {
        if (!component.property_class) {
            return;
        }

        if (!component.property_class.is_recorder_available() && !this.data.recorder_plugin_url) {
            console.error('[Property_Input_Audio_Widget]', 'Missing recorder library');
            return;
        }

        if (!this.data.hidden_input_id) {
            console.error('[Property_Input_Audio_Widget]', 'Missing hidden input');
            return;
        }

        var readyCallback, endedCallback, promptCallback;

        readyCallback  = (function () {
            component.enabled = true;
        }).bind(this);

        endedCallback  = (function () {
            component.enabled = false;
        }).bind(this);

        promptCallback = (function (event) {
            event.preventDefault();

            if (this.active_pane === 'capture') {
                this.init_capture();
            }
        }).bind(this);

        component.property = new component.property_class({
            id:   this.data.capture_input_id,
            type: component.property_type,
            data: {
                recorder_plugin_url: this.data.recorder_plugin_url,
                hidden_input_id:     this.data.hidden_input_id,
                on_stream_ready:     readyCallback,
                on_stream_ended:     endedCallback,
                on_stream_error:     endedCallback
            }
        });

        this.element().on('click.' + this.EVENT_NAMESPACE, '[data-pane="capture"]', promptCallback);
    }
};

Charcoal.Admin.Property_Input_Audio_Widget.prototype.destroy = function () {
    this.element().off(this.EVENT_NAMESPACE);

    if (this.text_component.property) {
        this.text_component.property.destroy();
    }

    if (this.upload_component.property) {
        this.upload_component.property.destroy();
    }

    if (this.capture_component.property) {
        this.capture_component.property.destroy();
    }
};

/* globals audioPropertyL10n */
/**
 * Upload Audio Property Control
 */

Charcoal.Admin.Property_Input_Audio = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.audio';
    this.input_type = 'charcoal/admin/property/input/audio';
    Charcoal.Admin.Property.call(this, opts);

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_Audio.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Audio.prototype.constructor = Charcoal.Admin.Property_Input_Audio;
Charcoal.Admin.Property_Input_Audio.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Audio.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var reader, file;

        file   = event.target.files[0];
        reader = new FileReader();

        reader.addEventListener('loadend', (function () {
            var audio = new Audio();

            console.log('[Property_Input_Audio.change_file]', file);

            audio.innerHTML = audioPropertyL10n.unsupportedElement;
            audio.controls  = true;
            audio.title     = file.name;
            audio.src       = reader.result;
            audio.load();

            this.$input.find('.hide-if-no-file').removeClass('d-none');
            this.$input.find('.show-if-no-file').addClass('d-none');

            this.$previewFile.append(audio);
            this.$previewText.html(file.name);
        }).bind(this), false);

        reader.readAsDataURL(file);
    }
};

Charcoal.Admin.Property_Input_Audio.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (file && file.url) {
        var path, $audio;

        path    = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');
        $audio = $('<audio controls src="' + file.url + '" class="js-file-audio">' + audioPropertyL10n.unsupportedElement + '</audio>');

        console.log('[Property_Input_Audio.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
        this.$previewFile.append($audio);
    }
};

/**
 * Color picker
 *
 * Require
 * - jquery-minicolors
 */

Charcoal.Admin.Property_Input_ColorPicker = function (opts) {
    this.input_type = 'charcoal/admin/property/input/colorpicker';
    Charcoal.Admin.Property.call(this, opts);

    this.input_id = null;

    this.colorpicker_selector = null;
    this.colorpicker_options  = null;

    this.set_properties(opts).create_colorpicker();
};

Charcoal.Admin.Property_Input_ColorPicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_ColorPicker.prototype.constructor = Charcoal.Admin.Property_Input_ColorPicker;
Charcoal.Admin.Property_Input_ColorPicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_ColorPicker.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.colorpicker_selector = opts.data.colorpicker_selector || this.colorpicker_selector;
    this.colorpicker_options  = opts.data.colorpicker_options  || this.colorpicker_options;

    var default_opts = {};

    this.colorpicker_options = $.extend({}, default_opts, this.colorpicker_options);

    return this;
};

Charcoal.Admin.Property_Input_ColorPicker.prototype.create_colorpicker = function () {
    $(this.colorpicker_selector).minicolors(this.colorpicker_options);

    return this;
};

/**
 * DateTime picker that manages datetime properties
 * charcoal/admin/property/input/datetimepicker
 *
 * Require:
 * - eonasdan-bootstrap-datetimepicker
 *
 * @param  {Object}  opts  Options for input property
 */

Charcoal.Admin.Property_Input_DateTimePicker = function (opts) {
    this.input_type = 'charcoal/admin/property/input/datetimepicker';
    Charcoal.Admin.Property.call(this, opts);

    // Property_Input_DateTimePicker properties
    this.input_id = null;

    this.datetimepicker_selector = null;
    this.datetimepicker_options  = null;

    this.set_properties(opts).create_datetimepicker();
};
Charcoal.Admin.Property_Input_DateTimePicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_DateTimePicker.prototype.constructor = Charcoal.Admin.Property_Input_DateTimePicker;
Charcoal.Admin.Property_Input_DateTimePicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_DateTimePicker.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.datetimepicker_selector = opts.data.datetimepicker_selector || this.datetimepicker_selector;
    this.datetimepicker_options  = opts.data.datetimepicker_options  || this.datetimepicker_options;

    var default_opts = {};

    this.datetimepicker_options = $.extend({}, default_opts, this.datetimepicker_options);

    return this;
};

Charcoal.Admin.Property_Input_DateTimePicker.prototype.create_datetimepicker = function () {
    $(this.datetimepicker_selector).datetimepicker(this.datetimepicker_options);

    return this;
};

/**
 * TinyMCE implementation for WYSIWYG inputs
 * charcoal/admin/property/input/tinymce
 *
 * Require:
 * - jQuery
 * - tinyMCE
 *
 * @param  {Object}  opts Options for input property
 */

Charcoal.Admin.Property_Input_DualSelect = function (opts) {
    this.input_type = 'charcoal/admin/property/input/dualselect';
    Charcoal.Admin.Property.call(this, opts);

    // Property_Input_DualSelect properties
    this.input_id = null;

    this.dualselect_selector = null;
    this.dualselect_options  = {};

    // The instance of Multiselect
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
Charcoal.Admin.Property_Input_DualSelect.prototype.init = function () {
    this.create_dualselect();
};

Charcoal.Admin.Property_Input_DualSelect.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.dualselect_selector = opts.dualselect_selector || opts.data.dualselect_selector || this.dualselect_selector;
    this.dualselect_options  = opts.dualselect_options  || opts.data.dualselect_options  || this.dualselect_options;

    var default_options = {
        keepRenderingSort: false
    };

    if (opts.data.dualselect_options.searchable) {
        this.dualselect_options.search = {
            left:  this.dualselect_selector + '_searchLeft',
            right: this.dualselect_selector + '_searchRight'
        };
    }

    this.dualselect_options = $.extend({}, default_options, this.dualselect_options);

    return this;
};

Charcoal.Admin.Property_Input_DualSelect.prototype.create_dualselect = function () {
    $(this.dualselect_selector).multiselect(this.dualselect_options);

    return this;
};

/**
 * Sets the dualselect into the current object
 * Might be usefull.
 * @param {TinyMCE Editor} dualselect The tinymce object.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.set_dualselect = function (dualselect) {
    this._dualselect = dualselect;
    return this;
};

/**
 * Returns the dualselect object
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.dualselect = function () {
    return this._dualselect;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} dualselect The tinymce object.
 */
Charcoal.Admin.Property_Input_DualSelect.prototype.destroy = function () {
    var dualselect = this.dualselect();

    if (dualselect) {
        dualselect.remove();
    }
};

/***
 * `charcoal/admin/property/input/geometry-widget`
 * Property_Input_Geometry_Widget Javascript class
 *
 */
Charcoal.Admin.Property_Input_Geometry_Widget = function (data) {
    // Input type
    data.input_type = 'charcoal/admin/property/input/geometry-widget';

    Charcoal.Admin.Property.call(this, data);

    // Scope
    var that = this;

    // Controller
    this._controller = undefined;
    // Create uniq ident for every entities on the map
    this._object_inc = 0;
    this._startGeometry = false;

    this._map_options = data.data.map_options;
    // Never send multiple true to BB gmap
    this._map_options.multiple = false;

    var EVENT_NAMESPACE = 'geolocation';
    var EVENT = {
        GOOGLE_MAP_LOADED: 'google-map-loaded.' + EVENT_NAMESPACE
    };

    if (typeof google === 'undefined') {
        if (window._geolocation_tmp_google !== true) {
            window._geolocation_tmp_google = true;
            $.getScript(
                'https://maps.googleapis.com/maps/api/js?sensor=false' +
                '&callback=_geolocation_tmp_google_onload_function&key=' + this._map_options.api_key,
                function () {}
            );

            // If google is undefined,
            window._geolocation_tmp_google_onload_function = function () {
                document.dispatchEvent(new Event(EVENT.GOOGLE_MAP_LOADED));
            };
        }

        document.addEventListener(EVENT.GOOGLE_MAP_LOADED, function () {
            that.init();
        }, { once: true })
    }

};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Geometry_Widget;
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.init = function () {
    if (typeof window._tmp_google_onload_function !== 'undefined') {
        delete window._tmp_google_onload_function;
    }
    if (typeof BB === 'undefined' || typeof google === 'undefined') {
        // We don't have what we need
        console.error('Plugins not loaded');
        return false;
    }

    var _data = this.opts();

    // Shouldn't happen at that point
    if (typeof _data.id === 'undefined') {
        console.error('Missing ID');
    }

    var default_styles = this.default_styles();
    var map_options = this.default_map_options();

    map_options = $.extend(true, map_options, this._map_options);

    // Get current map state from DB
    // This is located in the hidden input
    var current_value = this.element().find('input[type=hidden]').val();

    if (current_value) {
        // Parse the value
        var places = {
            object1: {
                ident:		'object1',
                paths:		this.reverse_translate_coords(current_value),
                editable:  true,
                draggable: true,
                type:      map_options.geometry_type,
                styles:    default_styles
            }
        };

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

    this.$map_maker = this.element().find('.js-map-maker');

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

    this.controller().set_styles([
        {
            featureType: 'poi',
            elementType: 'all',
            stylers: [
                { visibility: 'off' }
            ]
        }
    ]);

    this.controller().remove_focus();

    // link related properties to current widget
    this.link_related_property();

    // Scope
    var that = this;

    var key = 'object';

    var type = map_options.geometry_type;
    that.hide_marker_toolbar();

    var object_id = key + that.object_index();

    while (that.controller().get_place(object_id)) {
        object_id = key + that.object_index();
    }

    this.element().on('click', function () {
        var raw = that.controller().export();
        if (raw && Object.keys(raw.places).length !== 0) {
            return false;
        }

        if (!that._startGeometry) {
            that._startGeometry = true;

            switch (type) {
                case 'marker':
                case 'line':
                case 'polygon':
                    that.controller().create_new(type, object_id);
                    break;
            }
        }
    });

    this.element().on('click', '.js-reset', function (e) {
        that._startGeometry = false;
        e.preventDefault();
        that.controller().reset();
    });
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.link_related_property = function () {
    var related_property = this.opts().data.related_property;
    if (!related_property) {
        return false;
    }

    for (var obj in related_property) {
        switch (related_property[obj].obj_type) {
            case 'charcoal/admin/object/geometry-blueprint':
                this.related_object_geometry(obj);
                break;
        }
    }
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.related_object_geometry = function (obj) {
    // retrieve obj_type
    var type = this.opts().data.related_property[obj].obj_type;
    if (!type) {
        return false;
    }

    var geometry_objects = [];
    var geometry_objects_request_done = false;
    var that = this;

    // retrieve data
    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: type
        },
        type: 'GET',
        error: function () {},
        success: function (res) {
            geometry_objects_request_done = true;
            geometry_objects = res.collection;
        }
    });

    // on select
    this.element().parents('fieldset').on('change', '[name="' + obj + '"]', function (event) {
        if (!geometry_objects_request_done) {
            return false;
        }
        that.controller().reset();

        for (var index in geometry_objects) {
            if (geometry_objects[index].id !== $(event.currentTarget).val()) {
                continue;
            }

            var geometry = geometry_objects[index].geometry;

            var default_styles = that.default_styles();
            var map_options = that.default_map_options();

            map_options = $.extend(true, map_options, that._map_options);

            var object1  = {
                paths:     that.reverse_translate_coords(geometry),
                editable:  true,
                draggable: true,
                type:      map_options.geometry_type,
                styles:    default_styles
            };

            that.controller().add_place('object1', object1);
            that.controller().fit_bounds();
        }
    });
};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.controller = function () {
    return this._controller;
};

/**
 * This is to prevent any ident duplication
 * Return {Int} Object index
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.object_index = function () {
    return ++this._object_inc;
};

/**
 * This is to retrieve the defaults map styles
 * Return {Object}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.default_styles = function () {
    return {
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
};

/**
 * This is to retrieve the default map options
 * Return {Object}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.default_map_options = function () {
    return {
        default_styles: this.default_styles(),
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
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.display_marker_toolbar = function () {
    this.$map_maker.addClass('is-header-open');
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.hide_marker_toolbar = function () {
    this.$map_maker.removeClass('is-header-open');
};

/**
 * @var array coords
 * @return string coords (fits for sql geoshit)
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.translate_coords = function (coords) {
    var i = 0;
    var total = coords.length;
    var ret = [];
    for (; i < total; i++) {
        ret.push(coords[ i ].join(' '));
    }
    if (total) {
        // Duplicate first point!
        ret.push(coords[ 0 ].join(' '));
    }
    return ret.join(',');
};

/**
 * @var array coords
 * @return reverse string coords (fits for sql geoshit)
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.reverse_translate_coords = function (coords) {
    var first_level = coords.split(',');
    var i = 0;
    var total = first_level.length;
    for (; i < total; i++) {
        first_level[ i ] = first_level[ i ].split(' ');
    }
    // We do NOT duplicate
    first_level.pop();
    return first_level;
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
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.save = function () {
    // Get raw map datas
    if (!this.controller()) {
        return this;
    }
    var raw = this.controller().export();

    // We might wanna save ONLY the places values
    var places = (typeof raw.places === 'object') ? raw.places : {};

    // transform map data to geometry data for "geoshit" ¯\_(ツ)_/¯
    var coords = (Object.keys(places).length) ? this.translate_coords(places[Object.keys(places)[0]].paths) : '';

    // Affect to the current property's input
    // I see no reason to have more than one input hidden here.
    // Split with classes or data if needed
    this.element().find('input[type=hidden]').val(JSON.stringify(coords));

    return this;
};

/**
 * Upload Image Property Control
 */

Charcoal.Admin.Property_Input_Image = function (opts) {
    Charcoal.Admin.Property.call(this, opts);
    this.EVENT_NAMESPACE = '.charcoal.property.image';
    this.input_type = 'charcoal/admin/property/input/image';

    this.data   = opts.data;
    this.dialog = null;

    this.set_input_id(opts.id).init();
};

Charcoal.Admin.Property_Input_Image.prototype = Object.create(Charcoal.Admin.Property_Input_File.prototype);
Charcoal.Admin.Property_Input_Image.prototype.constructor = Charcoal.Admin.Property_Input_Image;
Charcoal.Admin.Property_Input_Image.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Image.prototype.change_file = function (event) {
    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (event.target && event.target.files && event.target.files[0])  {
        var reader, file;

        file   = event.target.files[0];
        reader = new FileReader();

        reader.addEventListener('loadend', (function () {
            var image = new Image();

            console.log('[Property_Input_Image.change_file]', file);

            image.style = 'max-width: 100%';
            image.title = file.name;
            image.src   = reader.result;
            image.load();

            this.$input.find('.hide-if-no-file').removeClass('d-none');
            this.$input.find('.show-if-no-file').addClass('d-none');

            this.$previewFile.append(image);
            this.$previewText.html(file.name);
        }).bind(this), false);

        reader.readAsDataURL(file);
    }
};

Charcoal.Admin.Property_Input_Image.prototype.elfinder_callback = function (file/*, elf */) {
    if (this.dialog) {
        this.dialog.close();
    }

    this.$input.find('.hide-if-no-file').addClass('d-none');
    this.$input.find('.show-if-no-file').removeClass('d-none');

    this.$previewText.empty();
    this.$previewFile.empty();

    if (file && file.url) {
        var path, $image;

        path    = decodeURI(file.url).replace(Charcoal.Admin.base_url(), '');
        $image = $('<img src="' + file.url + '" style="max-width: 100%">');

        console.log('[Property_Input_Image.elfinder_callback]', file);

        this.$hidden.val(path);
        this.$input.find('.hide-if-no-file').removeClass('d-none');
        this.$input.find('.show-if-no-file').addClass('d-none');
        this.$previewText.html(file.name);
        this.$previewFile.append($image);
    }
};

/***
 * `charcoal/admin/property/input/map-widget`
 * Property_Input_Map_Widget Javascript class
 *
 */
Charcoal.Admin.Property_Input_Map_Widget = function (data) {
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
            'https://maps.googleapis.com/maps/api/js?sensor=false' +
            '&callback=_tmp_google_onload_function&key=' + data.data.api_key,
            function () {}
        );
    } else {
        that.init();
    }
};

Charcoal.Admin.Property_Input_Map_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Map_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Map_Widget;
Charcoal.Admin.Property_Input_Map_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Map_Widget.prototype.init = function () {
    if (typeof window._tmp_google_onload_function !== 'undefined') {
        delete window._tmp_google_onload_function;
    }
    if (typeof BB === 'undefined' || typeof google === 'undefined') {
        // We don't have what we need
        console.error('Plugins not loaded');
        return false;
    }

    var _data = this.opts();

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

    this.$map_maker = this.element().find('.js-map-maker');

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

    this.controller().set_styles([
        {
            featureType: 'poi',
            elementType: 'all',
            stylers: [
                { visibility: 'off' }
            ]
        }
    ]);

    this.controller().remove_focus();

    // Scope
    var that = this;

    var key = 'object';

    this.element().on('change', '[name="' + this.opts('controls_name') + '"]', function (event) {
        var type = $(event.currentTarget).val();
        switch (type) {
            case 'display_marker_toolbar':
                that.display_marker_toolbar();

                break;
            case 'add_line':
            case 'add_polygon':
                that.hide_marker_toolbar();

                var object_id = key + that.object_index();

                while (that.controller().get_place(object_id)) {
                    object_id = key + that.object_index();
                }

                that.controller().create_new(type.replace('add_', ''), object_id);

                break;
        }
    });

    this.element().on('click', '.js-add-marker', function (e) {
        e.preventDefault();

        // Find uniq item ident
        var object_id = key + that.object_index();
        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        // Start creation of a new object
        that.controller().create_new('marker', object_id);
    });

    this.element().on('click', '.js-add_place_by_address', function (e) {
        e.preventDefault();

        var value = that.element().find('.js-address').text();
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
};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.controller = function () {
    return this._controller;
};

/**
 * This is to prevent any ident duplication
 * Return {Int} Object index
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.object_index = function () {
    return ++this._object_inc;
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.display_marker_toolbar = function () {
    this.$map_maker.addClass('is-header-open');
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.hide_marker_toolbar = function () {
    this.$map_maker.removeClass('is-header-open');
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
Charcoal.Admin.Property_Input_Map_Widget.prototype.save = function () {
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

/**
 * Range Input
 */

Charcoal.Admin.Property_Input_Range = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.property.range';

    Charcoal.Admin.Property.call(this, opts);

    this.input_type = 'charcoal/admin/property/input/range';

    this.data    = opts.data;
    this.data.id = opts.id;

    this.$output = null;
    this.$input  = null;

    this.init();
};

Charcoal.Admin.Property_Input_Range.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Range.prototype.constructor = Charcoal.Admin.Property_Input_Range;
Charcoal.Admin.Property_Input_Range.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Range.prototype.init = function () {
    if (this.data.show_range_value !== true) {
        return;
    }

    if (typeof this.data.range_value_location !== 'string') {
        return;
    }

    var input_id, location, event_name;

    input_id = this.id();

    location = this.data.range_value_location;
    switch (location) {
        case 'prefix':
            this.$output = $('#' + input_id + '_prefix_text');
            break;

        case 'suffix':
            this.$output = $('#' + input_id + '_suffix_text');
            break;

        default:
            if (location[0] === '#' || location[0] === '.') {
                this.$output = $(location);
            } else {
                this.$output = $('#' + input_id + '_' + location);
            }
            break;
    }

    this.$output.addClass('js-show-range-value');

    this.$input = $('#' + input_id);

    if (!this.$input.exists() || !this.$output.exists()) {
        return;
    }

    this.on_change(this.$input, this.$output);

    event_name = ('oninput' in this.$input[0]) ? 'input' : 'change';

    this.$input.on(event_name + this.EVENT_NAMESPACE, this.on_change.bind(this, this.$input, this.$output));
};

/**
 * Display the range value on change.
 *
 * @listens input
 *
 * @param  {Element[]|jQuery} $input  - The field's input range element.
 * @param  {Element[]|jQuery} $output - The field's output element.
 * @param  {Event}            event   - The change event.
 * @return {void}
 */
Charcoal.Admin.Property_Input_Range.prototype.on_change = function ($input, $output/*, event*/) {
    $output.text($output.text().replace(/[\d\.]+/, $input.val()));
};

Charcoal.Admin.Property_Input_Range.prototype.destroy = function () {
    this.element().off(this.EVENT_NAMESPACE);

    if (this.$input) {
        this.$input.off(this.EVENT_NAMESPACE);
    }
};

/**
 * Select Picker
 *
 * Require
 * - silviomoreto/bootstrap-select
 */

Charcoal.Admin.Property_Input_SelectPicker = function (opts) {
    Charcoal.Admin.Property.call(this, opts);
    this.input_type = 'charcoal/admin/property/input/select';

    // Property_Input_SelectPicker properties
    this.input_id = null;

    this.select_selector = null;
    this.select_options  = null;

    this.set_properties(opts).create_select();
};

Charcoal.Admin.Property_Input_SelectPicker.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_SelectPicker.prototype.constructor = Charcoal.Admin.Property_Input_SelectPicker;
Charcoal.Admin.Property_Input_SelectPicker.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_SelectPicker.prototype.set_properties = function (opts) {
    this.input_id = opts.id || this.input_id;

    this.select_selector = opts.data.select_selector || this.select_selector;
    this.select_options  = opts.data.select_options  || this.select_options;

    var default_opts = {};

    this.select_options = $.extend({}, default_opts, this.select_options);

    return this;
};

Charcoal.Admin.Property_Input_SelectPicker.prototype.create_select = function () {
    $(this.select_selector).selectpicker(this.select_options);

    return this;
};

/* eslint-disable consistent-this */
/* global ClipboardJS */
/**
 * Selectize Picker
 * Search.
 *
 * Require
 * - selectize.js
 */

;(function () {

    var Selectize = function (opts) {
        Charcoal.Admin.Property.call(this, opts);
        this.input_type = 'charcoal/admin/property/input/selectize';

        // Property_Input_Selectize properties
        this.input_id = null;
        this.obj_type = null;
        this.remote_source = null;
        this.copy_items = false;
        this.title = null;
        this.translations = null;

        // Pattern refers to the form property that matches the text inputted through selectize.
        this.pattern = null;
        this.multiple = false;
        this.separator = ',';

        this.selectize = null;
        this.selectize_selector = null;
        this.form_ident = null;
        this.selectize_options = {};
        this.choice_obj_map = {};
        this.selectize_property_ident = null;
        this.selectize_property = null;
        this.selectize_obj_type = null;
        this.selectize_templates = {};

        this.clipboard = null;
        this.allow_update = null;

        this.set_properties(opts).init();

        this.selectize_init();

    };
    Selectize.prototype = Object.create(Charcoal.Admin.Property.prototype);
    Selectize.constructor = Charcoal.Admin.Property_Input_Selectize;
    Selectize.parent = Charcoal.Admin.Property.prototype;

    Selectize.prototype.init = function () {};

    // Used of selectize_init in order
    // to avoid the re-execution of init() function on Charcoal.Admin.manager().render();
    Selectize.prototype.selectize_init = function () {
        this.init_selectize();
        this.init_clipboard();
        this.init_allow_update();
        this.init_allow_create();

        var self = this;

        this.selectize.on('update_item', function (e) {
            self.create_item(null, e.callback, {
                id: e.value,
                step: 0
            });
        });
    };

    Selectize.prototype.set_properties = function (opts) {

        this.input_id = opts.id || this.input_id;
        this.obj_type = opts.data.obj_type || this.obj_type;
        this.remote_source = opts.data.remote_source || this.remote_source;

        // Enables the copy button
        this.copy_items = opts.data.copy_items || this.copy_items;
        this.allow_update = opts.data.allow_update || this.allow_update;
        this.allow_create = opts.data.allow_create || this.allow_create;
        this.title = opts.data.title || this.title;
        this.translations = opts.data.translations || this.translations;
        this.pattern = opts.data.pattern || this.pattern;
        this.multiple = opts.data.multiple || this.multiple;
        this.separator = opts.data.multiple_separator || this.multiple_separator || ',';
        this.form_ident = opts.data.form_ident || this.form_ident;

        this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
        this.selectize_options = opts.data.selectize_options || this.selectize_options;
        this.choice_obj_map = opts.data.choice_obj_map || this.choice_obj_map;
        this.selectize_property_ident = opts.data.selectize_property_ident || this.selectize_property_ident;
        this.selectize_property = opts.data.selectize_property || this.selectize_property;
        this.selectize_obj_type = opts.data.selectize_obj_type || this.selectize_obj_type;
        this.selectize_templates = opts.data.selectize_templates || this.selectize_templates;

        this.$input = $(this.selectize_selector || '#' + this.input_id);

        var plugins;
        if (this.multiple) {
            plugins = {
                // 'restore_on_backspace',
                drag_drop: {},
                charcoal_item: {}
            };

        } else {
            plugins = {
                charcoal_item: {}
            };
        }

        var objType = this.obj_type;
        var remoteSource = this.remote_source;
        var default_opts = {
            plugins: plugins,
            formData: {},
            delimiter: this.separator,
            persist: true,
            preload: 'focus',
            openOnFocus: true,
            labelField: 'label',
            searchField: [ 'value', 'label' ],
            dropdownParent: this.$input.closest('.form-field'),
            render: {},
            onItemRemove: function (value) {
                this.refreshOption(value);
            },
            createFilter: function (input) {
                for (var item in this.options) {
                    item = this.options[item];
                    if (item.label === input) {
                        return false;
                    }
                }
                return true;
            },
            onInitialize: function () {
                var self = this;
                self.sifter.iterator(this.items, function (value) {
                    self.refreshItem(value, self.getItem(value));
                });
                self.sifter.iterator(this.options, function (data) {
                    self.refreshOption(data.value);
                });
            }
        };

        if (this.selectize_templates.item) {
            default_opts.render.item = function (item, escape) {
                if (item.item_render) {
                    return '<div class="item">' + item.item_render + '</div>';
                }
                return '<div class="item">' + escape(item[default_opts.labelField]) + '</div>';
            };
        }

        if (this.selectize_templates.option) {
            default_opts.render.option = function (option, escape) {
                if (option.option_render) {
                    return '<div class="option">' + option.option_render + '</div>';
                }
                return '<div class="option">' + escape(option[default_opts.labelField]) + '</div>';
            };
        }

        if (remoteSource) {
            default_opts.create = function (input) {
                return {
                    value: input,
                    label: input
                };
            };
            default_opts.load = this.load_from_remote.bind(this);
        } else if (objType) {
            default_opts.create = this.create_item.bind(this);
            default_opts.load = this.load_items.bind(this);
        } else {
            default_opts.plugins.create_on_enter = {};
            default_opts.create = function (input) {
                return {
                    value: input,
                    label: input
                };
            };
        }

        if (this.selectize_options.splitOn) {
            var splitOn = this.selectize_options.splitOn;
            if ($.type(splitOn) === 'array') {
                for (var i = splitOn.length - 1; i >= 0; i--) {
                    switch (splitOn[i]) {
                        case 'comma':
                            splitOn[i] = '\\s*,\\s*';
                            break;

                        case 'tab':
                            splitOn[i] = '\\t+';
                            break;

                        default:
                            splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    }
                }

                splitOn = splitOn.join('|');
            }

            this.selectize_options.splitOn = new RegExp(splitOn);
        }

        this.selectize_options = $.extend(true, {}, default_opts, this.selectize_options);

        return this;
    };

    Selectize.prototype.create_item = function (input, callback, opts) {
        var form_data = {};
        opts = opts || {};
        var self = this;
        var type = this.obj_type;
        var title = this.title;
        var translations = this.translations;
        var settings = this.selectize_options;
        var step = opts.step || 0;
        var form_ident = this.form_ident;
        var submit_label = null;
        var id = opts.id || null;
        var selectize_property = this.selectize_property;
        var selectize_property_ident = this.selectize_property_ident;
        var selectize_obj_type = this.selectize_obj_type;

        // Get the form ident
        if (form_ident && typeof form_ident === 'object') {
            if (!id && form_ident.create) {
                // The object must be created using 2 pop-up
                form_ident = form_ident.create;
                title += ' - ' + translations.statusTemplate.replaceMap({
                    '[[ current ]]': 1,
                    '[[ total ]]': 2
                });
                step = 1;
                submit_label = 'Next';
            } else if (id && form_ident.update) {
                form_ident = form_ident.update;

                if (step === 2) {
                    title += ' - ' + translations.statusTemplate.replaceMap({
                        '[[ current ]]': 2,
                        '[[ total ]]': 2
                    });
                    submit_label = 'Finish';
                }
            } else {
                form_ident = null;
            }
        }

        if ($.isEmptyObject(settings.formData)) {
            if (input) {
                form_data[this.choice_obj_map.label] = input;
            }
            form_data.form_ident = form_ident;
            form_data.submit_label = submit_label;
        } else if (input) {
            form_data = $.extend({}, settings.formData);
            $.each(form_data, function (key, value) {
                if (value === ':input') {
                    form_data[key] = input;
                }
            });
        }

        var data = {
            title: title,
            size: BootstrapDialog.SIZE_WIDE,
            cssClass: '-quick-form',
            dialog_options: {
                onhide: function () {
                    if (self.widget_id !== undefined) {
                        var widget = Charcoal.Admin.manager().get_widget(self.widget_id);
                        if (typeof widget.destroy === 'function') {
                            widget.destroy();
                        }
                        Charcoal.Admin.manager().remove_component('widgets', self.widget_id);
                    }

                    callback({
                        return: false
                    });
                }
            },
            widget_type: 'charcoal/admin/widget/quick-form',
            with_data: true,
            widget_options: {
                obj_type: type,
                obj_id: id,
                form_data: form_data
            }
        };

        if (step > 0) {
            data.type = BootstrapDialog.TYPE_PRIMARY;
        }

        var dialog = this.dialog(data, function (response) {
            if (response.success) {
                // Call the quickForm widget js.
                // Really not a good place to do that.
                if (!response.widget_id) {
                    return false;
                }

                self.widget_id = response.widget_id;

                Charcoal.Admin.manager().add_widget({
                    id: response.widget_id,
                    type: 'charcoal/admin/widget/quick-form',
                    data: response.widget_data,
                    obj_id: id,
                    extra_form_data: {
                        selectize_obj_type: selectize_obj_type,
                        selectize_prop_ident: selectize_property_ident,
                        selectize_property: selectize_property
                    },
                    save_action: 'selectize/save',
                    update_action: 'selectize/update',

                    suppress_feedback: (step === 1),
                    save_callback: function (response) {

                        var callbackOptions = {
                            class: 'new'
                        };

                        var selectizeResponse = response.selectize[0];

                        if (selectizeResponse) {
                            $.extend(true, callbackOptions, selectizeResponse);
                        }

                        callback(callbackOptions);

                        dialog.close();
                        if (step === 1) {
                            self.create_item(input, callback, {
                                id: selectizeResponse.value,
                                step: 2
                            });
                        }
                    }
                });

                // Re render.
                // This is not good.
                Charcoal.Admin.manager().render();

            }
        });
    };

    Selectize.prototype.load_from_remote = function (query, callback) {
        if (!query.length) {
            return callback();
        }
        var that = this;

        var selectize_obj_type = this.selectize_obj_type;
        var selectize_property_ident = this.selectize_property_ident;
        var selectize_property = this.selectize_property;

        var form_data = {
            selectize_obj_type: selectize_obj_type,
            selectize_prop_ident: selectize_property_ident,
            selectize_property: selectize_property
        };

        $.ajax({
            url: this.remote_source + encodeURIComponent(query),
            type: 'GET',
            data: form_data,
            error: function () {
                callback();
            },
            success: function (response) {
                if (response.optgroups !== null) {
                    $.each(response.optgroups, function (index, optgroup) {
                        that.selectize.registerOptionGroup(optgroup);
                    });
                }

                if (response.options !== null) {
                    callback(response.options);
                } else {
                    callback(response);
                }
            }
        });
    };

    Selectize.prototype.load_items = function (query, callback) {
        if (!query.length && this.selectize_options.preload === false) {
            return callback();
        }

        var type = this.obj_type;
        var selectize_property_ident = this.selectize_property_ident;
        var selectize_obj_type = this.selectize_obj_type;
        var selectize_property = this.selectize_property;

        var form_data = {
            obj_type: type,
            selectize_obj_type: selectize_obj_type,
            selectize_prop_ident: selectize_property_ident,
            selectize_property: selectize_property
        };

        var url = Charcoal.Admin.admin_url() + 'selectize/load';

        if (query) {
            url += '/' + encodeURIComponent(query);
        }

        $.ajax({
            url: url,
            data: form_data,
            type: 'GET',
            error: function () {
                callback();
            },
            success: function (response) {
                var items = [];

                var selectizeResponse = response.selectize;

                for (var item in selectizeResponse) {
                    if (selectizeResponse.hasOwnProperty(item)) {
                        item = selectizeResponse[item];

                        items.push(item);
                    }
                }
                callback(items);
            }
        });
    };

    Selectize.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

    Selectize.prototype.init_selectize = function () {
        var $select = this.$input.selectize(this.selectize_options);

        this.selectize = $select[0].selectize;
    };

    Selectize.prototype.init_allow_create = function () {
        if (!this.allow_create) {
            return;
        }

        var selectize = this.selectize;
        var $createButton = $(this.selectize_selector + '_create');

        var self = this;

        $createButton.on('click', function () {
            self.create_item(null, function (item) {
                // Create the item.
                if (item && item.value) {
                    selectize.addOption(item);
                    selectize.addItem(item.value);
                }
            });
        });
    };

    Selectize.prototype.init_allow_update = function () {
        switch (this.selectize.settings.mode) {
            case 'single' :
                this.allow_update_single();
                break;
            case 'multiple' :
                this.allow_update_multiple();
                break;
        }
    };

    Selectize.prototype.allow_update_single = function () {
        if (!this.allow_update) {
            return;
        }

        var selectize = this.selectize;
        var $updateButton = $(this.selectize_selector + '_update');
        var self = this;

        $updateButton.on('click', function () {
            var selectedItem = selectize.items;
            if (selectedItem) {
                self.create_item(null, function (item) {
                    // Update the item.
                    if (item && item.value) {
                        selectize.updateOption(item.value, item);
                        selectize.refreshOption(item.value);
                        selectize.refreshItem(item.value, selectize.getItem(item.value));
                    }
                }, {
                    id: selectedItem[0],
                    step: 0
                });
            }
        });
    };

    Selectize.prototype.allow_update_multiple = function () {
        if (!this.allow_update) {
            return;
        }

        var selectize = this.selectize;
        var $updateButton = $(this.selectize_selector + '_update');
        var id = null;
        var self = this;

        // Start by disabling update button.
        $updateButton[0].disabled = true;

        $updateButton.on('click', function () {
            if (id) {
                self.create_item(null, function (item) {
                    // Update the item.
                    if (item && item.value) {
                        selectize.updateOption(id, item);
                        selectize.refreshOption(id);
                        selectize.refreshItem(id, selectize.getItem(item));
                    }
                }, {
                    id: id,
                    step: 0
                });
            }
        });

        selectize.on('blur', function () {
            setTimeout(function () {
                $updateButton[0].disabled = true;
            }, 500);
        });

        selectize.$control.on('mousedown', '*:not(input)', function (e) {
            id = $(e.target).eq(0).data('value');

            if (selectize.$control.find('.active:not(input)')) {
                $updateButton[0].disabled = false;
            }
        });
    };

    Selectize.prototype.init_clipboard = function () {
        if (!this.copy_items) {
            return;
        }

        var selectize = this.selectize;

        this.clipboard = new ClipboardJS(this.selectize_selector + '_copy', {
            text: function () {
                return selectize.$input.val();
            }
        });
    };

    Charcoal.Admin.Property_Input_Selectize = Selectize;

}(jQuery, document));

/* global Selectize */
Selectize.define('btn_remove', function (options) {
    options = $.extend({
        label: '<span class="fa fa-trash-o"></span>',
        title: 'Remove',
        className: 'selectize-button-remove',
        append: true,
    }, options);

    this.require('buttons');

    var multiUpdate = function (thisRef, options) {
        var self = thisRef;
        self.addButton(thisRef, options, function (e) {
            e.preventDefault();

            if (self.isLocked) {
                return;
            }

            var $item = $(e.currentTarget).parent();
            self.setActiveItem($item);
            if (self.deleteSelection()) {
                self.setCaret(self.items.length);
            }
        });
    };

    if (this.settings.mode !== 'single') {
        multiUpdate(this, options);
    }
});

/* global Selectize */
Selectize.define('btn_update', function (options) {
    options = $.extend({
        label: '<span class="fa fa-pencil"></span>',
        title: 'Update',
        className: 'selectize-button-update',
        append: true,
    }, options);

    this.require('buttons');

    var multiUpdate = function (thisRef, options) {
        var self = thisRef;
        self.addButton(thisRef, options, function (e) {
            e.preventDefault();

            if (self.isLocked) {
                return;
            }

            var $item = $(e.currentTarget).parent();
            self.setActiveItem($item);

            self.trigger('update_item',{
                item: $item,
                value: $item.eq(0).data('value'),
                callback: function (item) {
                    if (item && item.value) {
                        self.updateOption(item.value, item);
                        self.refreshOption(item.value, item);
                        self.refreshItem(item.value, self.getItem(item.value));
                    }
                }
            });
        });
    };

    if (this.settings.mode !== 'single') {
        multiUpdate(this, options);
    }
});

/* global Selectize */
Selectize.define('buttons', function () {
    /**
     * Escapes a string for use within HTML.
     *
     * @param {string} str
     * @returns {string}
     */
    var escape_html = function (str) {
        return (str + '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    this.buttonOffset = 40;
    this.currentButtonOffset = 0;

    this.addButton = function (thisRef, options, callback) {
        var self = thisRef;
        var html = '<button type="button" ' +
            'class="selectize-button ' + options.className + '" ' +
            'tabindex="-1" ' +
            'title="' + escape_html(options.title) + '" ' +
            'style="right:' + self.currentButtonOffset + 'px">' +
            options.label + '</a>';

        self.currentButtonOffset += self.buttonOffset;

        /**
         * Appends an element as a child (with raw HTML).
         *
         * @param {string} html_container
         * @param {string} html_element
         * @return {string}
         */
        var append = function (html_container, html_element) {
            var $item = $(html_container);

            if ($item.hasClass('item')) {
                $item.append($(html_element));

                return $item[0];
            }

            return html_container;
        };

        var adjustContainerPadding = function (html_container, offset) {
            var $item = $(html_container);

            if ($item.hasClass('item')) {
                $item.css('padding-right', (offset + 8) + 'px');

                return $item[0];
            }

            return html_container;
        };

        thisRef.setup = (function () {
            var original = self.setup;
            return function () {
                // override the item rendering method to add the button to each
                if (options.append) {
                    var render_item = self.settings.render.item;

                    self.settings.render.item = function () {
                        return append(
                            adjustContainerPadding(
                                render_item.apply(thisRef, arguments),
                                self.currentButtonOffset
                            ),
                            html
                        );
                    };
                }

                original.apply(thisRef, arguments);

                // Prevent drag and drop while pressing button
                thisRef.$control.on('mousedown', '.' + options.className, function (e) {
                    e.preventDefault();
                    var sortable = self.$control.data('ui-sortable');

                    if (sortable) {
                        self.$control.sortable('disable');

                        $(document).on('mouseup.sortable', function () {
                            $(document).off('mouseup.sortable');
                            self.$control.sortable('enable');
                        });
                    }
                });

                // add event listener to button
                thisRef.$control.on('click', '.' + options.className, function (e) {
                    if (typeof callback === 'function') {
                        callback(e);
                    }
                });
            };
        }());
    };
});

/* eslint-disable consistent-this */
/* global Selectize */
/**
 * Plugin: "create_on_enter" for selectize.js
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Jordi Hereu Mayo <jhereumayo@gmail.com>
 */
Selectize.define('create_on_blur', function () {
    if (this.settings.mode !== 'multi') {
        return;
    }
    var self = this;
    this.onBlur = (function () {
        var original = self.onBlur;
        return function () {
            if (this.$control_input.val().trim() !== '') {
                self.createItem(this.$control_input.val());
            }
            return original.apply(this, arguments);
        };
    }());
});

/* eslint-disable consistent-this */
/* global Selectize */
/**
 * Plugin: "create_on_enter" for selectize.js
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * @author Jordi Hereu Mayo <jhereumayo@gmail.com>
 */
Selectize.define('create_on_enter', function () {
    if (this.settings.mode !== 'multi') {
        return;
    }
    var self = this;
    this.onKeyUp = (function () {
        var original = self.onKeyUp;
        return function (e) {
            if (e.keyCode === 13 && this.$control_input.val().trim() !== '') {
                self.createItem(this.$control_input.val());
            }
            return original.apply(this, arguments);
        };
    }());
});

/* eslint-disable consistent-this */
/* global Selectize */
Selectize.define('charcoal_item', function (options) {
    options = $.extend({
        classField: 'class',
        colorField: 'color',
    }, options);

    var self = this;
    var original = null;

    this.refreshItem = function (value, $item) {
        var option = self.options[value];

        if (option.hasOwnProperty(options.colorField)) {
            if (option[options.colorField]) {
                $item.addClass('has-color');
                $item.css('border-left-color', option[options.colorField]);
            }
            // $item.css('background-color', option[options.colorField]);
        }

        if (option.hasOwnProperty(options.classField)) {
            $item.addClass(option[options.classField]);
        }

        if (original) {
            return original.apply(this, arguments);
        }
    };

    this.refreshOption = function (value) {
        var option = self.options[value];
        self.refreshOptions(false);

        // Get all options including disabled ones
        var $option = self.getElementWithValue(value, self.$dropdown_content.find('.option'));

        if (option.hasOwnProperty(options.colorField)) {
            if (option[options.colorField]) {
                $option.addClass('has-color');
                $option.css('border-left-color', option[options.colorField]);
            }
        }

        if (original) {
            return original.apply(this, arguments);
        }
    };

    this.settings.onOptionAdd = (function () {
        original = null;

        // check if onItemAdd exists as it is an optional callback function
        if (self.settings.hasOwnProperty('onOptionAdd')) {
            original = self.settings.onOptionAdd;
        }

        return self.refreshOption;
    }());

    this.settings.onItemAdd = (function (/*value, $item*/) {
        original = null;

        // check if onItemAdd exists as it is an optional callback function
        if (self.settings.hasOwnProperty('onItemAdd')) {
            original = self.settings.onItemAdd;
        }

        return self.refreshItem;
    }());

});

/* eslint-disable consistent-this */
/**
 * Selectize Picker
 * List version.
 *
 * Require
 * - selectize.js
 */

;(function () {
    var List = function (opts) {
        this.input_type = 'charcoal/admin/property/input/selectize/list';

        // Property_Input_Selectize properties
        this.input_id = null;
        this.obj_type = null;
        this.copy_items = false;
        this.title = null;
        this.translations = null;

        // Pattern refers to the form property that matches the text inputted through selectize.
        this.pattern = null;
        this.multiple = false;
        this.separator = ',';

        this.selectize = null;
        this.selectize_selector = null;
        this.form_ident = null;
        this.selectize_options = {};

        this.clipboard = null;
        this.allow_update = false;

        this.set_properties(opts).init();
    };
    List.prototype = Object.create(Charcoal.Admin.Property_Input_Selectize.prototype);
    List.constructor = Charcoal.Admin.Property_Input_Selectize;
    List.parent = Charcoal.Admin.Property_Input_Selectize.prototype;

    List.prototype.set_properties = function (opts) {
        this.input_id = opts.id || this.input_id;
        this.obj_type = opts.data.obj_type || this.obj_type;

        // Enables the copy button
        this.copy_items = opts.data.copy_items || this.copy_items;
        this.allow_update = opts.data.allow_update || this.allow_update;
        this.title = opts.data.title || this.title;
        this.translations = opts.data.translations || this.translations;
        this.pattern = opts.data.pattern || this.pattern;
        this.multiple = opts.data.multiple || this.multiple;
        this.separator = opts.data.multiple_separator || this.multiple_separator || ',';
        this.form_ident = opts.data.form_ident || this.form_ident;

        this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;

        this.selectize_options = opts.data.selectize_options || this.selectize_options;

        this.$input = $(this.selectize_selector || '#' + this.input_id);

        var plugins;
        if (this.multiple) {
            plugins = {
                // 'restore_on_backspace',
                drag_drop: {},
                charcoal_item: {}
            };

        } else {
            plugins = {
                charcoal_item: {}
            };
        }

        var objType = this.obj_type;
        var default_opts = {
            plugins: plugins,
            formData: {},
            delimiter: this.separator,
            persist: true,
            preload: 'focus',
            openOnFocus: true,
            searchField: [ 'value', 'label' ],
            dropdownParent: this.$input.closest('.form-field'),

            createFilter: function (input) {
                for (var item in this.options) {
                    item = this.options[item];
                    if (item.label === input) {
                        return false;
                    }
                }
                return true;
            },
            onInitialize: function () {
                var self = this;
                self.sifter.iterator(this.items, function (value) {
                    var option = self.options[value];
                    var $item = self.getItem(value);

                    if (option.color) {
                        $item.css('background-color', option.color/*[options.colorField]*/);
                    }
                });
            }
        };

        if (objType) {
            default_opts.create = this.create_item.bind(this);
            default_opts.load = this.load_items.bind(this);
        } else {
            default_opts.plugins.create_on_enter = {};
            default_opts.create = function (input) {
                return {
                    value: input,
                    label: input
                };
            };
        }

        if (this.selectize_options.splitOn) {
            var splitOn = this.selectize_options.splitOn;
            if ($.type(splitOn) === 'array') {
                for (var i = splitOn.length - 1; i >= 0; i--) {
                    switch (splitOn[i]) {
                        case 'comma':
                            splitOn[i] = '\\s*,\\s*';
                            break;

                        case 'tab':
                            splitOn[i] = '\\t+';
                            break;

                        default:
                            splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                    }
                }

                splitOn = splitOn.join('|');
            }

            this.selectize_options.splitOn = new RegExp(splitOn);
        }

        this.selectize_options = $.extend(true,{}, default_opts, this.selectize_options);

        return this;
    };

    Charcoal.Admin.Property_Input_Selectize_List = List;

}(jQuery, document));

/* eslint-disable consistent-this */
/* global ClipboardJS */
/**
 * Selectize Picker
 *
 * Require
 * - selectize.js
 */

Charcoal.Admin.Property_Input_Selectize_Tags = function (opts) {
    this.input_type = 'charcoal/admin/property/input/selectize/tags';

    // Property_Input_Selectize_Tags properties
    this.input_id   = null;
    this.obj_type   = null;
    this.copy_items = false;
    this.title      = null;
    this.multiple   = false;
    this.separator  = ',';
    this._tags      = null;

    this.selectize          = null;
    this.selectize_selector = null;
    this.selectize_options  = {};

    this.clipboard = null;

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
    this.init_clipboard();
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.set_properties = function (opts) {
    this.input_id   = opts.id || this.input_id;
    this.obj_type   = opts.data.obj_type || this.obj_type;
    this.copy_items = opts.data.copy_items || this.copy_items;
    this.title      = opts.data.title || this.title;

    this.multiple  = opts.data.multiple || this.multiple;
    this.separator = opts.data.multiple_separator || this.multiple_separator || ',';

    this.selectize_selector = opts.data.selectize_selector || this.selectize_selector;
    this.selectize_options  = opts.data.selectize_options || this.selectize_options;

    this.$input = $(this.selectize_selector || '#' + this.input_id);

    // var selectedItems = this.tags_initialized();
    var plugins;
    if (this.multiple) {
        plugins = [
            // 'restore_on_backspace',
            'remove_button',
            'drag_drop',
            'charcoal_item'
        ];
    } else {
        plugins = [
            'charcoal_item'
        ];
    }

    var objType      = this.obj_type;
    var default_opts = {
        plugins: plugins,
        formData: {},
        delimiter: this.separator,
        persist: false,
        preload: true,
        openOnFocus: true,
        dropdownParent: this.$input.closest('.form-field'),
        createFilter: function (input) {
            for (var item in this.options) {
                item = this.options[item];
                if (item.text === input) {
                    return false;
                }
            }

            return true;
        },
        onInitialize: function () {
            var self = this;
            self.sifter.iterator(this.items, function (value) {
                var option = self.options[value];
                var $item  = self.getItem(value);

                if (option.color) {
                    $item.css('background-color', option.color/*[options.colorField]*/);
                }
            });
        }
    };

    if (objType) {
        default_opts.create = this.create_tag.bind(this);
        default_opts.load   = this.load_tags.bind(this);
    } else {
        default_opts.plugins.push('create_on_enter');
        default_opts.create = function (input) {
            return {
                value: input,
                text: input
            };
        };
    }

    this.selectize_options = $.extend({}, default_opts, this.selectize_options);

    if (this.selectize_options.splitOn) {
        var splitOn = this.selectize_options.splitOn;
        if ($.type(splitOn) === 'array') {
            for (var i = splitOn.length - 1; i >= 0; i--) {
                switch (splitOn[i]) {
                    case 'comma':
                        splitOn[i] = '\\s*,\\s*';
                        break;

                    case 'tab':
                        splitOn[i] = '\\t+';
                        break;

                    default:
                        splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                }
            }

            splitOn = splitOn.join('|');
        }

        this.selectize_options.splitOn = new RegExp(splitOn);
    }

    return this;
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.create_tag = function (input, callback) {
    var type      = this.obj_type;
    var id        = this.id;
    var title     = this.title;
    var settings  = this.selectize_options;
    var form_data = {};

    if ($.isEmptyObject(settings.formData)) {
        form_data = {
            name: input
        };
    } else {
        form_data = $.extend({}, settings.formData);
        $.each(form_data, function (key, value) {
            if (value === ':input') {
                form_data[key] = input;
            }
        });
    }

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
        widget_type: 'charcoal/admin/widget/quick-form',
        widget_options: {
            obj_type: type,
            obj_id: id,
            form_data: form_data
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
                    var label = response.obj.id;
                    if ('name' in response.obj && response.obj.name) {
                        label = response.obj.name[Charcoal.Admin.lang()] || response.obj.name;
                    }

                    callback({
                        value: response.obj.id,
                        text:  label,
                        color: response.obj.color,
                        class: 'new'
                    });
                    BootstrapDialog.closeAll();
                }
            });
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.load_tags = function (query, callback) {
    var type = this.obj_type;

    if (!query.length) {
        return callback();
    }

    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: type
        },
        type: 'GET',
        error: function () {
            callback();
        },
        success: function (res) {
            var items = [];
            for (var item in res.collection) {
                item = res.collection[item];
                var label = item.id;
                if ('name' in item && item.name) {
                    label = item.name[Charcoal.Admin.lang()] || item.name;
                }

                items.push({
                    value: item.id,
                    text:  label,
                    color: item.color
                });
            }
            callback(items);
        }
    });
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.dialog = Charcoal.Admin.Widget.prototype.dialog;

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.onKeyDown = function (event) {
    var self   = this;
    var isTemp = false;
    var IS_MAC = /Mac/.test(navigator.userAgent);

    if (self.isLocked) {
        if (event.keyCode !== 9) {
            event.preventDefault();
        }
    }

    if ($.type(self.isCmdDown) === 'undefined') {
        isTemp         = true;
        self.isCmdDown = event[IS_MAC ? 'metaKey' : 'ctrlKey'];
    }

    if (self.isCmdDown && event.keyCode === 67) {
        if (isTemp) {
            self.isCmdDown = undefined;
        }

        if (self.$activeItems.length) {
            var values = [], i = 0, n = self.$activeItems.length;
            for (; i < n; i++) {
                values.push($(self.$activeItems[i]).attr('data-value'));
                /** @todo Select Active Values */
                document.execCommand('copy');
            }
        }

        return;
    }

    if ((self.isFull() || self.isInputHidden) && !(IS_MAC ? event.metaKey : event.ctrlKey)) {
        event.preventDefault();
        return;
    }
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_selectize = function () {
    var $select    = this.$input.selectize(this.selectize_options);
    this.selectize = $select[0].selectize;

    /*
     if (this.copy_items) {
     var that = this;
     this.selectize.$control.on('keydown', function () {
     return that.onKeyDown.apply(that.selectize, arguments);
     });
     }
     */
};

Charcoal.Admin.Property_Input_Selectize_Tags.prototype.init_clipboard = function () {
    if (!this.copy_items) {
        return;
    }

    var selectize  = this.selectize;
    this.clipboard = new ClipboardJS(this.selectize_selector + '_copy', {
        text: function (/*trigger*/) {
            /*
             if (selectize.$activeItems.length) {
             console.log(selectize.$activeItems);
             }
             */

            return selectize.$input.val();
        }
    });
};

/**
 * Basic text input that also manages multiple (split) values
 * charcoal/admin/property/input/text
 *
 * Require:
 * - jQuery
 *
 * @param  {Object}  opts Options for input property
 */

Charcoal.Admin.Property_Input_Text = function (opts) {
    Charcoal.Admin.Property.call(this, opts);

    this.input_type = 'charcoal/admin/property/input/text';

    this.data = opts.data;

    // Required
    this.set_input_id(opts.id);

    // Dispatches the data
    this.set_data(this.data);

    // Run the plugin or whatever is necessary
    this.multiple_initialized = false;
    this.initialisation = true;
    this.init();
    this.initialisation = false;

    return this;
};
Charcoal.Admin.Property_Input_Text.prototype             = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Text.prototype.constructor = Charcoal.Admin.Property_Input_Text;
Charcoal.Admin.Property_Input_Text.prototype.parent      = Charcoal.Admin.Property.prototype;

/**
 * Set multiple values required
 * @param {Object} data Data passed from the template
 */
Charcoal.Admin.Property_Input_Text.prototype.set_data = function (data) {
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

    var min = (data.multiple_options) ? data.multiple_options.min : 0;
    var max = (data.multiple_options) ? data.multiple_options.max : 0;

    this.set_multiple_min(min);
    this.set_multiple_max(max);

    var split = (data.multiple_options) ? data.multiple_options.split_on : null;

    this.set_split_on(split);
    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.init = function () {
    // Impossible!
    if (!this.input_id) {
        return this;
    }

    // OG element.
    this.$input = $('#' + this.input_id);
    if (this.multiple && !this.multiple_initialized) {
        this.init_multiple();
    }
};

/**
 * When multiple
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.init_multiple = function () {
    this.multiple_initialized = true;
    // New input
    this.chars_new    = [ 13 ];
    // Check to delete current input
    this.chars_remove = [ 8, 46 ];
    // Navigate.
    this.char_next    = [ 40 ];
    this.char_prev    = [ 38 ];

    this.currentValAmount = 1;

    // Add to container.
    // input.wrap('<div></div>');
    this.$container = this.$input.parent('div');

    // OG input keyboard events.
    this.bind_keyboard_events(this.$input);

    // Initial split.
    this.split_val(this.$input);

    if (this.multiple_min) {
        var additionalFields = this.multiple_min - this.currentValAmount;
        for (; additionalFields > 0; additionalFields--) {
            this.add_item();
        }
    }

    return this;
};
/**
 * Split the value with separator
 * If the input is specified, splits relative to the input
 * @param  {String} val  Value
 * @param  {[type]} input [description]
 * @return {DOMElement|false}
 */
Charcoal.Admin.Property_Input_Text.prototype.split_val = function (input) {
    var separator = this.split_on || this.multiple_separator;
    input         = input || this.$input;
    var val       = input.val();

    var split = val.split(separator);
    var i     = 0;
    var total = split.length;

    if (total === 1) {
        // Nothing to split.
        return false;
    }

    for (; i < total; i++) {
        if (i === 0) {
            input.val(split[i]);
        } else {
            if (this.initialisation || !this.multiple_max || this.currentValAmount < this.multiple_max) {
                input = this.insert_item(input, split[i]);
            } else {
                var next = input.next('input');
                if (next.length && !next.innerHTML) {
                    this.remove_item(next);
                    input = this.insert_item(input, split[i]);
                }
            }
        }
    }

    return input;
};

Charcoal.Admin.Property_Input_Text.prototype.bind_keyboard_events = function (input) {
    // Scope
    var that = this;

    var chars_new    = this.chars_new;
    var chars_remove = this.chars_remove;
    var char_next    = this.char_next;
    var char_prev    = this.char_prev;

    // Bind the keyboard events
    input.on('keydown', function (e) {

        var keyCode = e.keyCode;
        if (chars_new.indexOf(keyCode) > -1) {
            if (!that.multiple_max || that.currentValAmount < that.multiple_max) {
                e.preventDefault();
                var clone = that.insert_item($(this));
                clone.focus();
            }
        }

        if (chars_remove.indexOf(keyCode) > -1) {

            if (!that.multiple_min || that.currentValAmount > that.multiple_min) {
                // Delete keys (8 is backspage, 46 is "del")
                if ($(this).val() === '') {
                    e.preventDefault();
                    that.remove_item($(this));
                }
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
        var clone = that.split_val($(this));
        if (clone) {
            clone.focus();
        }
    });
};

/**
 * Insert a clone relative to an element
 * @param  {jQueryObject} elem      Input element
 * @param  {String|undefined} val   Should we have a value already in that input.
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.insert_item = function (elem, val) {
    var clone = this.input_clone(val);
    clone.insertAfter(elem);
    this.bind_keyboard_events(clone);

    this.currentValAmount++;

    return clone;
};

/**
 * Add an item (append)
 * @param {String|undefined} val    If the input already as a value
 * @return {jQueryObject}           Clone object
 */
Charcoal.Admin.Property_Input_Text.prototype.add_item = function (val) {
    var clone = this.input_clone(val);
    this.$container.append(clone);
    this.bind_keyboard_events(clone);

    this.currentValAmount++;

    return clone;
};
/**
 * Remove specific item
 * Sets focus to the prev item (or next if previous doesn'T exist)
 * Won't remove the LAST input standing.
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item = function (item) {
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

    this.currentValAmount--;

    return this;
};
/**
 * Remove listeners from an item
 * @param  {jQueryObject} item      Input to be removed
 * @return {thisArg}                Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.remove_item_listeners = function (item) {
    item.off('keydown');
    item.off('keyup');

    return this;
};

/**
 * Create a clone of the OG input
 * @param  {String} val Optional parameter - Value of the input.
 * @return {jQueryObject}     The actual "clone", which isn't really a clone.
 */
Charcoal.Admin.Property_Input_Text.prototype.input_clone = function (val) {
    var input      = this.$input;
    var classes    = input.attr('class');
    var type       = input.attr('type');
    var min_length = this.min_length;
    var max_length = this.max_length;
    // var size = this.size;
    var required   = this.required;
    var readonly   = this.readonly;
    var input_name = this.input_name;

    var clone = $('<input />');

    if (type) {
        clone.attr('type', type);
    }
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
        clone.attr('readonly', 'readonly');
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
Charcoal.Admin.Property_Input_Text.prototype.set_input_id = function (input_id) {
    this.input_id = input_id;
    return this;
};
/**
 * Required
 * @param {String} input_name Name of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_name = function (input_name) {
    this.input_name = input_name;
    return this;
};
/**
 * Required
 * @param {String} input_val Value of the current input
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_input_val = function (input_val) {
    this.input_val = input_val;
    return this;
};

/**
 * Is the input in readOnly mode?
 * @param {Boolean|undefined} readonly Defines if input is in readonly mode or not
 */
Charcoal.Admin.Property_Input_Text.prototype.set_readonly = function (readonly) {
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
Charcoal.Admin.Property_Input_Text.prototype.set_required = function (required) {
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
Charcoal.Admin.Property_Input_Text.prototype.set_min_length = function (min_length) {
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
Charcoal.Admin.Property_Input_Text.prototype.set_max_length = function (max_length) {
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
Charcoal.Admin.Property_Input_Text.prototype.set_size = function (size) {
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
Charcoal.Admin.Property_Input_Text.prototype.set_multiple = function (multiple) {
    if (!multiple) {
        multiple = false;
    }
    this.multiple = multiple;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple_min Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_min = function (multiple_min) {
    if (!multiple_min) {
        multiple_min = false;
    }
    this.multiple_min = multiple_min;
    return this;
};

/**
 * Multiple true or false?
 * Multiple input will replicate itself when multiple separator is typed in.
 * @param {Boolean} multiple_max Is the input multiple or what.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_max = function (multiple_max) {
    if (!multiple_max) {
        multiple_max = false;
    }
    this.multiple_max = multiple_max;
    return this;
};

/**
 * Multiple separator
 * @param {String} separator Multiple separator || undefined.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_multiple_separator = function (separator) {
    if (!separator) {
        // Default
        separator = ',';
    }
    this.multiple_separator = separator;
    return this;
};

/**
 * Split delimiter
 * @param {String} separator Multiple separator || undefined.
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Property_Input_Text.prototype.set_split_on = function (splitOn) {
    if (!splitOn) {
        splitOn = this.multiple_separator;
    } else {
        if ($.type(splitOn) === 'array') {
            for (var i = splitOn.length - 1; i >= 0; i--) {
                switch (splitOn[i]) {
                    case 'comma':
                        splitOn[i] = '\\s*,\\s*';
                        break;

                    case 'tab':
                        splitOn[i] = '\\t+';
                        break;

                    case 'newline':
                        splitOn[i] = '[\\n\\r]+';
                        break;

                    default:
                        splitOn[i] = splitOn[i].replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                }
            }

            splitOn = splitOn.join('|');
        }

        splitOn = new RegExp(splitOn);
    }

    this.split_on = splitOn;
    return this;
};

Charcoal.Admin.Property_Input_Text.prototype.destroy = function () {
}

/* eslint-disable consistent-this */
/**
 * TinyMCE implementation for WYSIWYG inputs
 * charcoal/admin/property/input/tinymce
 *
 * Require:
 * - jQuery
 * - tinyMCE
 *
 * @param  {Object}  opts Options for input property
 */

Charcoal.Admin.Property_Input_Tinymce = function (opts) {
    Charcoal.Admin.Property.call(this, opts);
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
Charcoal.Admin.Property_Input_Tinymce.prototype.init = function () {
    this.create_tinymce();
};

/**
 * Init plugin
 * @return {thisArg} Chainable.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.base_url = function () {
    return Charcoal.Admin.base_url() + 'assets/admin/scripts/vendors/tinymce';
};

Charcoal.Admin.Property_Input_Tinymce.prototype.set_properties = function (opts) {
    this.input_id = opts.input_id || this.input_id;
    this.editor_options = opts.editor_options || opts.data.editor_options || this.editor_options;

    window.elFinderCallback[this.input_id] = this.elfinder_callback.bind(this);

    var locale = Charcoal.Admin.locale().match(/([a-zA-Z]{2})(_|-)([a-zA-Z]{2})/)[0] || 'en';
    locale = locale.replace('-', '_');

    if (locale.match(/en_/)) {
        locale = 'en';
    }

    var default_opts = {
        language: locale,

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
            'placeholder',
            //'preview',
            //'print',
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
        entity_encoding: 'raw',

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
        visualblocks_default_state: false,
        automatic_uploads: true,
        images_upload_url: 'tinymce/upload/image'
    };

    if (('plugins' in default_opts) && ('plugins' in this.editor_options)) {
        if ($.type(this.editor_options.plugins) === 'string') {
            this.editor_options.plugins = this.editor_options.plugins.split(' ');
        }

        $.each(this.editor_options.plugins, function (i, pattern) {
            // If the first character is ! it should be omitted
            var exclusion = pattern.indexOf('!') === 0;
            var index;

            // If the pattern is an exclusion, remove the !
            if (exclusion) {
                pattern = pattern.slice(1);
            }

            if (exclusion) {
                // If an exclusion, remove matching plugins.
                while ((index = default_opts.plugins.indexOf(pattern)) > -1) {
                    delete default_opts.plugins[index];
                }
            } else {
                // Otherwise add matching plugins.
                if (default_opts.plugins.indexOf(pattern) === -1) {
                    default_opts.plugins.push(pattern);
                }
            }
        });
        delete this.editor_options.plugins;
    }

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

Charcoal.Admin.Property_Input_Tinymce.prototype.create_tinymce = function () {
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

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_callback = function (file, elf) {
    // pass selected file data to TinyMCE
    parent.tinyMCE.activeEditor.windowManager.getParams().oninsert(file, elf);
    parent.tinyMCE.activeEditor.windowManager.close();
};

Charcoal.Admin.Property_Input_Tinymce.prototype.elfinder_browser = function (control, callback, value, meta) {
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
Charcoal.Admin.Property_Input_Tinymce.prototype.set_editor = function (editor) {
    this._editor = editor;
    return this;
};

/**
 * Returns the editor object
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.editor = function () {
    return this._editor;
};

/**
 * Destroy what needs to be destroyed
 * @return {TinyMCE Editor} editor The tinymce object.
 */
Charcoal.Admin.Property_Input_Tinymce.prototype.destroy = function () {
    var editor = this.editor();

    if (editor) {
        editor.remove();
    }
};

/**
 * Base Template (charcoal/admin/template)
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Template}
 */
Charcoal.Admin.Template = function (opts) {
    Charcoal.Admin.Component.call(this, opts);
    return this;
};

Charcoal.Admin.Template.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Template.prototype.constructor = Charcoal.Admin.Template;
Charcoal.Admin.Template.prototype.parent = Charcoal.Admin.Component.prototype;

/* globals authL10n */
/**
 * charcoal/admin/template/login
 */

Charcoal.Admin.Template_Login = function (opts) {
    // Common Template properties
    this.template_type = 'charcoal/admin/template/login';

    this.init(opts);
};

Charcoal.Admin.Template_Login.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Login.prototype.constructor = Charcoal.Admin.Template_Login;
Charcoal.Admin.Template_Login.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Login.prototype.init = function (opts) {
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Login.prototype.bind_events = function () {
    var $form = $('#login-form');

    /**
     * @fires Charcoal.Admin.Template_Login.prototype.onSubmit~event:submit.charcoal.login
     */
    $form.on('submit.charcoal.login', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaLoginCallback = this.submitForm.bind(this, $form);
};

/**
 * @listens Charcoal.Admin.Template_Login~event:submit.charcoal.login
 * @this    {Charcoal.Admin.Template_Login}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Login.prototype.onSubmit = function (event) {
    event.preventDefault();

    var $form   = $(event.currentTarget),
        captcha = Charcoal.Admin.recaptcha();

    if (captcha.hasInvisibleWidget($form, '#g-recaptcha-challenge')) {
        captcha.getApi().execute();
    } else {
        this.submitForm.call(this, $form);
    }
};

/**
 * @this  {Charcoal.Admin.Template_Login}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Login.prototype.submitForm = function ($form) {
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    var urlParams = Charcoal.Admin.queryParams();

    if ('redirect_to' in urlParams) {
        data = data.concat('&next_url=' + encodeURIComponent(urlParams.redirect_to));
    }

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
        .done(function (response) {
            var nextUrl  = (response.next_url || Charcoal.Admin.admin_url()),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.authSuccess),
                redirect = function () {
                    window.location.href = nextUrl;
                };

            message += '<p>' + authL10n.postLoginRedirect + ' ' +
                        authL10n.postLoginFallback.replace('[[ url ]]', nextUrl) + '</p>';

            BootstrapDialog.show({
                title:    authL10n.loginTitle,
                message:  message,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: redirect
            });

            setTimeout(redirect, 300);
        }).fail(function (jqxhr, status, error) {
            var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.authFailed),
                captcha  = Charcoal.Admin.recaptcha(),
                callback = null;

            if (captcha.hasApi()) {
                callback = function () {
                    captcha.getApi().reset();
                };
            }

            BootstrapDialog.show({
                title:    authL10n.loginTitle,
                message:  message,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: callback
            });
        });
};

/**
 * Generate HTML from the given feedback.
 *
 * @param  {array}  entries  - Collection of feedback entries.
 * @return {string|null} - The merged feedback messages as HTML paragraphs.
 */
Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml = function (entries) {
    if (entries.feedbacks) {
        entries = entries.feedbacks;
    }

    if (Array.isArray(entries) === false || entries.length === 0) {
        return null;
    }

    if (entries.length === 0) {
        return null;
    }

    var key,
        out,
        manager = Charcoal.Admin.feedback(entries),
        grouped = manager.getMessagesMap();

    out  = '<p>';
    for (key in grouped) {
        out += grouped[key].join('</p><p>');
    }
    out += '</p>';

    manager.empty();

    if (out === '<p></p>') {
        return null;
    }

    return out;
};

Charcoal.Admin.Template_MenuHeader = function () {
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

        $this
            .toggleClass('is-open')
            .siblings('.js-accordion-content')
            .stop()
            .slideToggle();
    });
};

/* globals authL10n */
/**
 * charcoal/admin/template/account/lost-password
 *
 * Require:
 * - jQuery
 * - Boostrap3
 * - Boostrap3-Dialog
 *
 * @todo Implement feedback from server-side
 */

Charcoal.Admin.Template_Account_LostPassword = function (opts) {
    // Common Template properties
    this.template_type = 'charcoal/admin/template/account/lost-password';

    this.init(opts);
};

Charcoal.Admin.Template_Account_LostPassword.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Account_LostPassword.prototype.constructor = Charcoal.Admin.Template_Account_LostPassword;
Charcoal.Admin.Template_Account_LostPassword.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Account_LostPassword.prototype.init = function (opts) {
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Account_LostPassword.prototype.bind_events = function () {
    var $form = $('#lost-password-form');

    /**
     * @fires Charcoal.Admin.Template_Account_LostPassword.prototype.onSubmit~event:submit.charcoal.password
     */
    $form.on('submit.charcoal.password', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaResetPassCallback = this.submitForm.bind(this, $form);
};

/**
 * @listens Charcoal.Admin.Template_Account_LostPassword~event:submit.charcoal.password
 * @this    {Charcoal.Admin.Template_Account_LostPassword}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.onSubmit = Charcoal.Admin.Template_Login.prototype.onSubmit;

/**
 * Generate HTML from the given feedback.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.parseFeedbackAsHtml = Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml;

/**
 * @this  {Charcoal.Admin.Template_Account_LostPassword}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.submitForm = function ($form) {
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
        .done(function (response) {
            var message = that.parseFeedbackAsHtml(response) || authL10n.lostPassSuccess;

            BootstrapDialog.show({
                title:    authL10n.lostPassword,
                message:  message,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.href = response.next_url || Charcoal.Admin.admin_url('login?notice=resetpass');
                }
            });
        }).fail(function (jqxhr, status, error) {
            var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.lostPassFailed),
                captcha  = Charcoal.Admin.recaptcha(),
                callback = null;

            if (captcha.hasApi()) {
                callback = function () {
                    captcha.getApi().reset();
                };
            }

            BootstrapDialog.show({
                title:    authL10n.lostPassword,
                message:  message,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: callback
            });
        });
};

/* globals authL10n */
/**
 * charcoal/admin/template/account/reset-password
 *
 * Require:
 * - jQuery
 * - Boostrap3
 * - Boostrap3-Dialog
 *
 * @todo Implement feedback from server-side
 */

Charcoal.Admin.Template_Account_ResetPassword = function (opts) {
    // Common Template properties
    this.template_type = 'charcoal/admin/template/account/reset-password';

    this.init(opts);
};

Charcoal.Admin.Template_Account_ResetPassword.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Account_ResetPassword.prototype.constructor = Charcoal.Admin.Template_Account_ResetPassword;
Charcoal.Admin.Template_Account_ResetPassword.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Account_ResetPassword.prototype.init = function (opts) {
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Account_ResetPassword.prototype.bind_events = function () {
    var $form = $('#reset-password-form');

    /**
     * @fires Charcoal.Admin.Template_Account_ResetPassword.prototype.onSubmit~event:submit.charcoal.password
     */
    $form.on('submit.charcoal.password', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaChangePassCallback = this.submitForm.bind(this, $form);
};

/**
 * @listens Charcoal.Admin.Template_Account_ResetPassword~event:submit.charcoal.password
 * @this    {Charcoal.Admin.Template_Account_ResetPassword}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Account_ResetPassword.prototype.onSubmit = Charcoal.Admin.Template_Login.prototype.onSubmit;

/**
 * Generate HTML from the given feedback.
 */
Charcoal.Admin.Template_Account_ResetPassword.prototype.parseFeedbackAsHtml = Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml;

/**
 * @this  {Charcoal.Admin.Template_Account_ResetPassword}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Account_ResetPassword.prototype.submitForm = function ($form) {
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
        .done(function (response) {
            var message = that.parseFeedbackAsHtml(response) || authL10n.resetPassSuccess;

            BootstrapDialog.show({
                title:    authL10n.passwordReset,
                message:  message,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.href = response.next_url || Charcoal.Admin.admin_url('login?notice=newpass');
                }
            });
        }).fail(function (jqxhr, status, error) {
            var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.resetPassFailed),
                captcha = Charcoal.Admin.recaptcha(),
                callback = null;

            if (captcha.hasApi()) {
                callback = function () {
                    captcha.getApi().reset();
                };
            }

            BootstrapDialog.show({
                title:    authL10n.passwordReset,
                message:  message,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: callback
            });
        });
};
