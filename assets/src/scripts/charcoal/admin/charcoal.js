var Charcoal = Charcoal || {};

/**
 * Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
 * It gives access to private properties and public methods
 * @return  {object}  Charcoal.Admin
 */
Charcoal.Admin = (function () {
    'use strict';

    var options, manager, feedback, debug,
        currentLang = document.documentElement.lang,
        defaultLang = 'en';

    options = {
        base_url: null,
        admin_path: null,
    };

    /**
     * Object function that acts as a container for public methods
     */
    function Admin() {}

    /**
     * Simple cache store.
     *
     * @type {Object}
     */
    Admin.cachePool = {};

    /**
     * Application Debug Mode.
     *
     * @param  {boolean} [mode]
     * @return {boolean}
     */
    Admin.debug = function (mode) {
        if (typeof mode === 'boolean') {
            debug = mode;
        } else {
            throw new TypeError('Must be a boolean, received ' + (typeof mode));
        }

        return debug || false;
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
     * Generates the admin URL used by forms and other objects
     * @return  {string}  URL for admin section
     */
    Admin.admin_url = function () {
        return options.base_url + options.admin_path + '/';
    };

    /**
     * Returns the base_url of the project
     * @return  {string}  URL for admin section
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
     * Convert an object namespace string into a usable object name
     * @param   {string}  name  String that respects the namespace structure : charcoal/admin/property/input/switch
     * @return  {string}  name  String that respects the object name structure : Property_Input_Switch
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
    Admin.loadScript = function (src, callback) {
        this.cache(src, function (defer) {
            $.ajax({
                url: src,
                dataType: 'script',
                success: defer.resolve,
                error: defer.reject
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
    Admin.cache = function (key, value, callback) {
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

    return Admin;

}());
