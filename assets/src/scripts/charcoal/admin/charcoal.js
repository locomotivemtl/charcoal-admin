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
                    : [ { msg: response.message } ];
            } else {
                response.feedbacks = Array.isArray(error)
                    ? error
                    : [ { msg: error } ];
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
     * Note: Expects the data type to be 'json'.
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
                            : [ { msg: response.message } ];
                    } else {
                        response.feedbacks = Array.isArray(error)
                            ? error
                            : [ { msg: error } ];
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
