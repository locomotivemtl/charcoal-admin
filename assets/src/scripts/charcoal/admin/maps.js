/* globals mapsL10n,URL,URLSearchParams */
/**
 * Charcoal Maps Controller
 *
 * Class that deals with loading the Google Maps JS API.
 */

;(function ($, Admin, document) {
    'use strict';

    var $document = $(document),
        API_CALLBACK = 'Charcoal.Admin.maps.onMapsApiLoaded',
        BASE_API_URL = 'https://maps.googleapis.com/maps/api/js',
        DATA_KEY = 'charcoal.google.maps',
        EVENT_KEY = '.' + DATA_KEY,
        Event = {
            READY:   'api:ready'  + EVENT_KEY,
            LOADING: 'api:load'   + EVENT_KEY,
            LOADED:  'api:loaded' + EVENT_KEY,
            FAILED:  'api:failed' + EVENT_KEY
        };

    /**
     * @var {boolean}         isLoaded   - Whether the Google Maps JS API is loaded.
     * @var {boolean}         isLoading  - Whether the Google Maps JS API is loading.
     * @var {jQuery.Deferred} readyList  - Callbacks to execute when the Google Maps JS API is ready.
     * @var {object}          controller - The public interface for loading Google Maps JS API.
     */
    var isLoaded,
        isLoading,
        apiUrl,
        readyList = $.Deferred(),
        controller = {};

    /**
     * Checks if the Google Maps JS API is available.
     *
     * @return {boolean}
     */
    controller.isMapsApiLoaded = function () {
        if (isLoading) {
            return false;
        }

        if (!isLoaded) {
            var isLoaded = ('google' in window && typeof google === 'object' && typeof google.maps === 'object');
            if (isLoaded) {
                isLoaded = true;
            }
        }

        return isLoaded;
    };

    /**
     * Asserts that the Google Maps JS API is available, throws an Error if not.
     *
     * @throws {Error} If the Google Maps JS API is unavailable.
     * @return {void}
     */
    controller.assertMapsApiLoaded = function () {
        if (!this.isMapsApiLoaded()) {
            throw new Error('Google Maps JS API is unavailable');
        }
    };

    /**
     * Retrieves the API key for the Google Maps JS API.
     *
     * @return {?string}
     */
    controller.getMapsApiKey = function () {
        return Admin.store.get('apis.google.maps.apiKey');
    };

    /**
     * Retrieves the URL for the Google Maps JS API.
     *
     * @return {URL}
     */
    controller.createMapsApiUrl = function () {
        var url, key, lang;

        var url = new URL(BASE_API_URL);

        url.searchParams.set('v', 3);

        if (key = this.getMapsApiKey()) {
            url.searchParams.set('key', key);
        }

        if (lang = Admin.lang()) {
            url.searchParams.set('language', lang);
        }

        return url;
    };

    /**
     * Replaces the URL for the Google Maps JS API.
     *
     * @param  {URL|string} url - The new URL.
     * @return {void}
     */
    controller.setMapsApiUrl = function (url) {
        if (typeof url === 'string') {
            url = new URL(url);
        }

        apiUrl = url;
    };

    /**
     * Retrieves the URL for the Google Maps JS API.
     *
     * @return {?URL}
     */
    controller.getMapsApiUrl = function () {
        if (!apiUrl) {
            apiUrl = this.createMapsApiUrl();
        }

        return apiUrl;
    };

    /**
     * Retrieves the URL query parameters object for the Google Maps JS API.
     *
     * @return {?URLSearchParam}
     */
    controller.getMapsApiUrlParams = function () {
        return this.getMapsApiUrl().searchParams;
    };

    /**
     * Specify a function to execute when the Google Maps JS API is fully loaded.
     *
     * A Promise-like object (or "thenable") that resolves when the external library is ready.
     *
     * This method will initiate the request for the library.
     *
     * @param  {function} handler - A function called when the library is ready.
     * @return {this}
     */
    controller.whenMapsApiReady = function (handler) {
        readyList.then(handler);

        var state = readyList.state();
        if (state === 'pending' && typeof isLoading !== 'boolean' && typeof isLoaded !== 'boolean') {
            if (this.isMapsApiLoaded()) {
                readyList.resolve(window.google.maps);
            } else {
                this.fetchMapsApi();
            }
        }

        return this;
    };

    /**
     * Fetches the Google Maps JS API.
     *
     * @todo Throw error instead of console warning.
     *
     * @return {jQuery.Deferred} - The Promise for the Google Maps JS API.
     */
    controller.fetchMapsApi = function () {
        if (this.isMapsApiLoaded() || isLoading) {
            return readyList;
        }

        isLoading = true;

        // console.log('Memo.GoogleMaps.Loading');
        $(document).trigger(Event.LOADING);

        var key = this.getMapsApiKey();
        if (!key) {
            console.warn('Missing Google Maps JS API Key');
            isLoaded  = false;
            isLoading = false;
            return readyList.reject();
        }

        var url = this.getMapsApiUrl();
        if (!url) {
            console.warn('Missing Google Maps JS API URL');
            isLoaded  = false;
            isLoading = false;
            return readyList.reject();
        }

        var callback;
        if (url.searchParams.has('callback')) {
            var callable = url.searchParams.get('callable');
            if (callable !== API_CALLBACK) {
                try {
                    callback = Admin.resolveValueFromDotNotation(callback);
                } catch (err) {
                    callback = null;
                }

                if (typeof callback !== 'function') {
                    console.warn(callable + ' is not a valid callback for Google Maps JS API');
                }
            }
        }

        url.searchParams.set('callback', API_CALLBACK);

        /**
         * Temporary closure called when the Google Maps JS API is ready.
         *
         * @callback controller~onMapsApiLoaded
         * @return   {void}
         */
        this.onMapsApiLoaded = function () {
            delete this.onMapsApiLoaded;

            isLoaded  = true;
            isLoading = false;

            readyList.resolve(google.maps);

            // console.log('Memo.GoogleMaps.Ready');
            var readyEvent = $.Event(Event.READY, {
                relatedTarget: google.maps
            });
            $(document).trigger(readyEvent);

            if (typeof callback === 'function') {
                try {
                    callback();
                } catch (err) {
                    console.warn('Error caught from ' + callable + ':', err);
                }
            }
        };

        var fail = function () {
            delete this.onMapsApiLoaded;

            // console.log('Memo.GoogleMaps.Failed');
            $(document).trigger(Event.FAILED);

            isLoaded  = false;
            isLoading = false;

            console.warn('Missing Google Maps JS API');
            readyList.reject();
        };

        var done = function () {
            // console.log('Memo.GoogleMaps.Loaded');
            $(document).trigger(Event.LOADED);
        };

        $.getScript(url, done.bind(this, fail.bind(this);

        return readyList;
    };

    /**
     * Public Interface
     */

    Admin.maps = controller;

}(jQuery, Charcoal.Admin, document));
