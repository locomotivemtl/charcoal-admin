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
