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
 * @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Array/find
 */
if (!Array.prototype.find) {
    Object.defineProperty(Array.prototype, 'find', {
        value: function (predicate) {
            'use strict';

            if (this == null) {
                throw new TypeError('Array.prototype.find called on null or undefined');
            }

            if (typeof predicate !== 'function') {
                throw new TypeError('predicate must be a function');
            }

            var list = Object(this);
            var length = list.length >>> 0;
            var thisArg = arguments[1];

            for (var i = 0; i !== length; i++) {
                if (predicate.call(thisArg, this[i], i, list)) {
                    return this[i];
                }
            }

            return undefined;
        }
    });
}
