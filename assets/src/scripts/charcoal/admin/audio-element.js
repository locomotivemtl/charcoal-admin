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
