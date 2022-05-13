/**
 * Base Cache Widget
 */

;(function ($, Admin) {
    'use strict';

    var $document = $(document);

    var Widget = function (opts) {
        this.CACHE_ITEM_KEY_GROUP_POOL = 'pool';
        this.EVENT_NAMESPACE = '.charcoal.widget.cache';
        this.SELECTOR = {
            DATA_ACTION_CLEAR: '[data-clear="cache"]',
            DATA_ACTION_PURGE: '[data-purge="cache"]'
        };

        Charcoal.Admin.Widget.call(this, opts);

        this.data = opts.data;

        this.on_flushed = this.on_flushed.bind(this);
    };

    Widget.prototype            = Object.create(Charcoal.Admin.Widget.prototype);
    Widget.prototype.contructor = Widget;
    Widget.prototype.parent     = Charcoal.Admin.Widget.prototype;

    Widget.prototype.init = function () {
        $document.on('flushed.charcoal.cache', this.on_flushed);
    };

    Widget.prototype.destroy = function () {
        $document.off('flushed.charcoal.cache', this.on_flushed);
    };

    Widget.prototype.widget_options = function () {
        return this.data;
    };

    /**
     * Handles cache "flushed" event.
     *
     * @see {Charcoal.Admin.Cache}
     *
     * @param {Event} event - A cache event.
     */
    Widget.prototype.on_flushed = function (event) {
        if (this.is_event_related(event)) {
            this.reload();
            return;
        }
    };

    /**
     * Determines whether the specified event is event related
     * to this caching widget.
     *
     * @param {Event}       event                   - A cache event.
     * @param {?string}     event.cacheItemKeyGroup - A cache item key group.
     * @param {?(string[])} event.cacheItemKeys     - A list of cache item keys.
     */
    Widget.prototype.is_event_related = function (event) {
        if (
            event.cacheItemKeyGroup &&
            (
                (
                    this.CACHE_ITEM_KEY_GROUP_POOL === event.cacheItemKeyGroup
                ) || (
                    this.data.cache_item_key_group &&
                    this.data.cache_item_key_group === event.cacheItemKeyGroup
                )
            )
        ) {
            return true;
        }

        if (
            event.cacheItemKeys &&
            this.data.cache_item_keys &&
            this.data.cache_item_keys === event.cacheItemKeys
        ) {
            return true;
        }

        return false;
    };

    Admin.Widget_Cache = Widget;

}(jQuery, Charcoal.Admin));
