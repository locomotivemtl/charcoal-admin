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
            PURGE:  'purge'  + EVENT_KEY,
            PURGED: 'purged' + EVENT_KEY,
            CLICK:  'click'  + EVENT_KEY
        },
        Selector = {
            DATA_CACHE: '[data-cache-type]',
            DATA_PURGE: '[data-purge="cache"]'
        },
        lastXhr    = null,
        lastCache  = null,
        lastTarget = null,
        fromEvent  = false,
        isPurging  = false;

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
        $document.off(Event.CLICK).on(Event.CLICK, Selector.DATA_PURGE, this.onPurge.bind(this));
    };

    /**
     * Determine if the cache is in the process of purging.
     *
     * @return {Boolean} TRUE if the cache is clearing data otherwise FALSE.
     */
    Manager.prototype.isPurging = function ()
    {
        return isPurging;
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
     * Retrieve the last target to trigger the purge.
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
     * Event: Purge the cache.
     *
     * @this   {CacheManager}
     * @event  document#click
     * @param  {Event} event - The event handler.
     */
    Manager.prototype.onPurge = function (event)
    {
        event.preventDefault();

        fromEvent  = true;
        lastTarget = event.currentTarget;

        var type = event.cacheType || $(event.currentTarget).data('cacheType') || null;
        if (type) {
            this.purge(type);
        }

        fromEvent = false;
    };

    /**
     * Purge the cache for given category.
     *
     * @param {String} cacheType - The cache type to clean out.
     */
    Manager.prototype.purge = function (cacheType)
    {
        if (isPurging === true) {
            return;
        }

        isPurging = true;
        lastCache = cacheType;
        lastXhr   = null;

        if (fromEvent === false) {
            lastTarget = null;
        }

        var purgeEvent = $.Event(Event.PURGE, {
            cacheManager:  this,
            cacheType:     lastCache,
            relatedTarget: lastTarget
        });

        $document.trigger(purgeEvent);

        if (purgeEvent.isDefaultPrevented()) {
            return;
        }

        lastXhr = $.post({
                    url: Admin.admin_url() + 'system/clear-cache',
                    data: {
                        cache_type: cacheType
                    },
                    dataType: 'json',
                    context: this
                })
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
        isPurging = false;

        var purgedEvent = $.Event(Event.PURGE, {
            cacheManager:  this,
            cacheType:     lastCache,
            relatedTarget: lastTarget
        });

        $document.trigger(purgedEvent);
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
