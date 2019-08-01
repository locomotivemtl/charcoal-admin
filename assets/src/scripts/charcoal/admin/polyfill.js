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
    })();
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
    })();
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
            if (this == null) {
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
        value: function assign (target/*, varArgs*/) {
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
