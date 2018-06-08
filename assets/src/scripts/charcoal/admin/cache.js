/* globals cacheL10n */
/**
 * Charcoal Cache Manager
 *
 * Class that deals with all the server-side cache pool.
 *
 * It uses BootstrapDialog to display feedback.
 */

;(function ($, Admin, document, undefined) {
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
    var Manager = function ()
    {
        $(this.init.bind(this));

        return this;
    };

    /**
     * Initialize the cache manager.
     *
     * @fires document#ready
     */
    Manager.prototype.init = function ()
    {
        $document.off(Event.CLICK)
                 .on(Event.CLICK, Selector.DATA_CLEAR, this.onClear.bind(this))
                 .on(Event.CLICK, Selector.DATA_PURGE, this.onPurge.bind(this));
    };

    /**
     * Determine if the cache is in the process of flushing.
     *
     * @return {Boolean} TRUE if the cache is clearing data otherwise FALSE.
     */
    Manager.prototype.isFlushing = function ()
    {
        return isFlushing;
    };

    /**
     * Retrieve the last flush action called.
     *
     * @return {String|null}
     */
    Manager.prototype.lastAction = function ()
    {
        return lastAction;
    };

    /**
     * Retrieve the last cache type to be cleared.
     *
     * @return {String|null}
     */
    Manager.prototype.lastCacheType = function ()
    {
        return lastCache;
    };

    /**
     * Retrieve the last target to trigger the flush.
     *
     * @return {Element|null}
     */
    Manager.prototype.lastTarget = function ()
    {
        return lastTarget;
    };

    /**
     * Retrieve the last XHR object.
     *
     * @return {Thenable|null}
     */
    Manager.prototype.lastXhr = function ()
    {
        return lastXhr;
    };

    /**
     * Resolve the cache type from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveType = function ($trigger)
    {
        return $trigger.data('cacheType') || null;
    };

    /**
     * Resolve the cache item key from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveKey = function ($trigger)
    {
        return $trigger.data('cacheKey') || null;
    };

    /**
     * Event: Clear the cache.
     *
     * @this   {CacheManager}
     * @event  document#click
     * @param  {Event} event - The event handler.
     */
    Manager.prototype.onClear = function (event)
    {
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
    Manager.prototype.onPurge = function (event)
    {
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
    Manager.prototype.clear = function (cacheType, cacheKey)
    {
        this.flush(Action.CLEAR, cacheType, cacheKey);
    };

    /**
     * Purge the cache pool of stale or expired items.
     *
     * @param {String} cacheType   - The cache type to flush.
     * @param {String} cacheKey    - The cache key to delete.
     */
    Manager.prototype.purge = function (cacheType, cacheKey)
    {
        this.flush(Action.PURGE, cacheType, cacheKey);
    };

    /**
     * Flush the cache for given category.
     *
     * @param {String} cacheAction - Whether to empty all items or purge stale or expired items.
     * @param {String} cacheType   - The cache type to flush.
     * @param {String} cacheKey    - The cache key to delete.
     */
    Manager.prototype.flush = function (cacheAction, cacheType, cacheKey)
    {
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
