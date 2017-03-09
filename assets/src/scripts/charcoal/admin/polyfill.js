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

if (!Array.prototype.find) {
    /**
     * Function to execute on each value in the array, taking three arguments:
     *
     * @callback arrayFind
     * @param  {*}      element - The current element being processed in the array.
     * @param  {Number} index   - The index of the current element being processed in the array.
     * @param  {Array}  array   - The array `find` was called upon.
     * @return {Boolean}
     */

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
