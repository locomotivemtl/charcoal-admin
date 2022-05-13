/* globals cacheL10n */
/**
 * Charcoal Cache Manager
 *
 * Class that deals with all the server-side cache pool.
 *
 * It uses BootstrapDialog to display feedback.
 */

/**
 * @typedef {string} CacheItemPoolName
 */

/**
 * @typedef {string} CacheItemGroupName
 */

/**
 * @typedef {string} CacheItemKey
 */

/**
 * List of item group names.
 *
 * @typedef {CacheItemGroupName[]} CacheItemGroupPoolList
 */

/**
 * List of item keys.
 *
 * @typedef {CacheItemKey[]} CacheItemPoolList
 */

/**
 * Map of cache pool names (key) and item group names (value).
 *
 * @typedef {object<CacheItemPoolName, (CacheItemGroupName|CacheItemGroupName[])>} CacheItemGroupPoolMap
 */

/**
 * Map of cache pool names (key) and item keys (value).
 *
 * @typedef {object<CacheItemPoolName, (CacheItemKey|CacheItemKey[])>} CacheItemPoolMap
 */

/**
 * List of item group names or map of cache pool names and item group names.
 *
 * @typedef {CacheItemGroupPoolList|CacheItemGroupPoolMap} CacheItemGroupPoolListOrMap
 */

/**
 * List of item keys or map of cache pool names and item keys.
 *
 * @typedef {CacheItemPoolList|CacheItemPoolMap} CacheItemPoolListOrMap
 */

/**
 * Base cache event options.
 *
 * Object expects at least one of the predefined properties.
 *
 * If {@see cacheItemPool} or {@see cacheItemPools} are omitted,
 * the default cache is will be used.
 *
 * @typedef {object} CacheManagerState
 *
 * @property {Element}                     [relatedTarget] -
 *     A reference to the Element that triggered the cache action.
 * @property {string}                      [cacheAction] -
 *     The cache action: {@see Action.CLEAR} or {@see Action.PURGE}.
 * @property {CacheItemPoolName}           [cacheItemPool]
 * @property {CacheItemPoolName[]}         [cacheItemPools]
 * @property {CacheItemGroupName}          [cacheItemKeyGroup]
 * @property {CacheItemGroupPoolListOrMap} [cacheItemKeyGroups]
 * @property {CacheItemKey}                [cacheItemKey]
 * @property {CacheItemPoolListOrMap}      [cacheItemKeys]
 */

/**
 * Base cache event options.
 *
 * Object expects at least one of the predefined properties.
 *
 * If {@see cacheItemPool} or {@see cacheItemPools} are omitted,
 * the default cache is will be used.
 *
 * @typedef {object} CacheEventOptions
 *
 * @property {Element}                     [relatedTarget] -
 *     A reference to the Element that triggered the event.
 * @property {CacheItemPoolName}           [cacheManager] -
 *     A reference to the cache manager.
 * @property {CacheItemPoolName}           [cacheItemPool]
 * @property {CacheItemPoolName[]}         [cacheItemPools]
 * @property {CacheItemGroupName}          [cacheItemKeyGroup]
 * @property {CacheItemGroupPoolListOrMap} [cacheItemKeyGroups]
 * @property {CacheItemKey}                [cacheItemKey]
 * @property {CacheItemPoolListOrMap}      [cacheItemKeys]
 */

/**
 * Flush event options.
 *
 * Object expects at least one of the predefined properties.
 *
 * @typedef {CacheEventOptions} FlushEventOptions
 *
 * @property {string} [cacheAction] -
 *     The cache action: {@see Action.CLEAR} or {@see Action.PURGE}.
 */

/**
 * HTTP request interface for flushing cache items.
 *
 * Object expects at least one of the predefined properties.
 *
 * @typedef {object} HttpFlushCacheRequest
 *
 * @property {CacheItemPoolName}           [cache_item_pool] -
 *     If omitted, the default cache is targeted.
 * @property {CacheItemPoolName[]}         [cache_item_pools] -
 *     If omitted, the default cache is targeted.
 * @property {CacheItemGroupName}          [cache_item_key_group]
 * @property {CacheItemGroupPoolListOrMap} [cache_item_key_groups]
 * @property {CacheItemKey}                [cache_item_key]
 * @property {CacheItemPoolListOrMap}      [cache_item_keys]
 */

;(function ($, Admin, document) {
    'use strict';

    var $document = $(document),
        DATA_KEY = 'charcoal.cache',
        EVENT_KEY = '.' + DATA_KEY,
        Event = {
            CLICK:   'click'   + EVENT_KEY,
            DELETE:  'delete'  + EVENT_KEY,
            DELETED: 'deleted' + EVENT_KEY,
            FLUSH:   'flush'   + EVENT_KEY,
            FLUSHED: 'flushed' + EVENT_KEY,
            PURGE:   'purge'   + EVENT_KEY,
            PURGED:  'purged'  + EVENT_KEY
        },
        Selector = {
            DATA_ITEM_GROUP:   '[data-cache-item-group]',
            DATA_ITEM_KEYS:    '[data-cache-item-keys]',
            DATA_ITEM_KEY:     '[data-cache-item-key]',
            DATA_ACTION_CLEAR: '[data-clear="cache"]',
            DATA_ACTION_PURGE: '[data-purge="cache"]'
        },
        Action = {
            CLEAR: 'clear',
            PURGE: 'purge'
        },
        lastXhr               = null,
        lastCacheAction       = null,
        lastCacheItemKeyGroup = null,
        lastCacheItemKeys     = null,
        lastTarget            = null,
        lastState             = {},
        fromEvent             = false,
        isBusy                = false;

    /**
     * Create a new cache manager.
     *
     * @class
     */
    var Manager = function () {
        $(this.init.bind(this));

        this.clearCacheRequestURL = Admin.admin_url('system/cache/clear');
        this.purgeCacheRequestURL = Admin.admin_url('system/cache/purge');

        this.onClear = this.onClear.bind(this);
        this.onPurge = this.onPurge.bind(this);

        return this;
    };

    /**
     * Initialize the cache manager.
     *
     * @listens jQuery#ready
     */
    Manager.prototype.init = function () {
        $document
            .off(Event.CLICK)
            .on(Event.CLICK, Selector.DATA_ACTION_CLEAR, this.onClear)
            .on(Event.CLICK, Selector.DATA_ACTION_PURGE, this.onPurge);
    };

    /**
     * Determine if the cache manager is busy.
     *
     * @deprecated In favor of {@see Manager#isBusy}.
     *
     * @return {boolean}
     */
    Manager.prototype.isFlushing = function () {
        return this.isBusy();
    };

    /**
     * Determine if the cache manager is busy.
     *
     * @return {boolean}
     */
    Manager.prototype.isBusy = function () {
        return isBusy;
    };

    /**
     * Retrieve the last flush action called.
     *
     * @return {String|null}
     */
    Manager.prototype.lastCacheAction = function () {
        return lastCacheAction;
    };

    /**
     * Retrieve the last cache item key group to be cleared.
     *
     * @return {String|null}
     */
    Manager.prototype.lastCacheItemKeyGroup = function () {
        return lastCacheItemKeyGroup;
    };

    /**
     * Retrieve the last cache item keys to be cleared.
     *
     * @return {String[]|null}
     */
    Manager.prototype.lastCacheItemKeys = function () {
        return lastCacheItemKeys;
    };

    /**
     * Retrieve the last target to trigger the flush.
     *
     * @return {Element|null}
     */
    Manager.prototype.lastTarget = function () {
        return lastTarget;
    };

    /**
     * Retrieve the last XHR object.
     *
     * @return {Thenable|null}
     */
    Manager.prototype.lastXhr = function () {
        return lastXhr;
    };

    /**
     * Parses the cache item pools.
     *
     * @param  {CacheItemPoolName[]} pools
     * @return {?(CacheItemPoolName[])}
     */
    Manager.prototype.parseCacheItemPools = function (pools) {
        if (typeof pools === 'string') {
            pools = pools.split(' ');
        }

        if (Array.isArray(pools) && pools.length) {
            return pools.map(this.parseCacheItemPool.bind(this)).filter();
        }

        return null;
    };

    /**
     * Parses the cache item pool.
     *
     * @param  {CacheItemPoolName} pool
     * @return {?CacheItemPoolName}
     */
    Manager.prototype.parseCacheItemPool = function (pool) {
        return pool;
    };

    /**
     * Parse the cache item key group.
     *
     * @param  {String|String[]} group - The cache item key group.
     * @return {String[]|Null}
     */
    Manager.prototype.parseCacheItemGroup = function (group) {
        if (typeof group === 'string' && group.length) {
            return group;
        }

        return null;
    };

    /**
     * Parse the cache item keys.
     *
     * @param  {String[]} keys - The cache item keys.
     * @return {String[]|Null}
     */
    Manager.prototype.parseCacheItemKeys = function (keys) {
        if (typeof keys === 'string') {
            keys = keys.split(' ');
        }

        if (Array.isArray(keys) && keys.length) {
            return keys.map(this.parseCacheItemKey.bind(this)).filter();
        }

        return null;
    };

    /**
     * Parse the cache item key.
     *
     * @param  {String} key - The cache item key.
     * @return {String|Null}
     */
    Manager.prototype.parseCacheItemKey = function (key) {
        return key;
    };

    /**
     * Resolve the cache item key group from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveCacheItemGroupFromElement = function ($trigger) {
        return $trigger.data('cacheItemGroup') || null;
    };

    /**
     * Resolve the cache item keys from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|String[]|Null}
     */
    Manager.prototype.resolveCacheItemKeysFromElement = function ($trigger) {
        return $trigger.data('cacheItemKeys') || null;
    };

    /**
     * Resolve the cache item key from the event target.
     *
     * @param  {Element} $trigger - The jQuery element.
     * @return {String|Null}
     */
    Manager.prototype.resolveCacheItemKeyFromElement = function ($trigger) {
        return $trigger.data('cacheItemKey') || null;
    };

    /**
     * Event: Clear the cache.
     *
     * @this   {CacheManager}
     * @event  document#click
     * @param  {Event} event - The event handler.
     */
    Manager.prototype.onClear = function (event) {
        event.preventDefault();

        var $trigger, group, keys;

        fromEvent  = true;
        lastTarget = event.currentTarget;
        $trigger   = $(event.currentTarget);

        group = this.parseCacheItemGroup(
            event.cacheItemKeyGroup ||
            this.resolveCacheItemGroupFromElement($trigger)
        );

        keys = this.parseCacheItemKeys(
            event.cacheItemKeys ||
            event.cacheItemKey ||
            this.resolveCacheItemKeysFromElement($trigger) ||
            this.resolveCacheItemKeyFromElement($trigger)
        );

        if (group || keys) {
            this.clear(group, keys);
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
    Manager.prototype.onPurge = function (event) {
        event.preventDefault();

        var $trigger, group, keys;

        fromEvent  = true;
        lastTarget = event.currentTarget;
        $trigger   = $(event.currentTarget);

        group = this.parseCacheItemGroup(
            event.cacheItemKeyGroup ||
            this.resolveCacheItemGroupFromElement($trigger)
        );

        keys = this.parseCacheItemKeys(
            event.cacheItemKeys ||
            event.cacheItemKey ||
            this.resolveCacheItemKeysFromElement($trigger) ||
            this.resolveCacheItemKeyFromElement($trigger)
        );

        if (group || keys) {
            this.purge(group, keys);
        }

        fromEvent = false;
    };

    /**
     * Purges items in the cache pool.
     *
     * @param {CacheItemPoolName} pool
     */
    Manager.prototype.purgePool = function (pool) {
        // TODO
    };

    /**
     * Purges items in the cache pools.
     *
     * @param {CacheItemPoolName[]} pools
     */
    Manager.prototype.purgePools = function (pools) {
        // TODO
    };

    /**
     * Deletes all items in the cache pool.
     *
     * @param {CacheItemPoolName} pool
     */
    Manager.prototype.clearPool = function (pool) {
        // TODO
    };

    /**
     * Deletes all items in the cache pools.
     *
     * @param {CacheItemPoolName[]} pools
     */
    Manager.prototype.clearPools = function (pools) {
        // TODO
    };

    /**
     * Deletes all items in the cache item group from the cache pool.
     *
     * @param {CacheItemGroupName} group
     * @param {?CacheItemPoolName} [pool]
     */
    Manager.prototype.clearItemGroup = function (group, pool) {
        // TODO
    };

    /**
     * Deletes all items in the cache item groups from the cache pool.
     *
     * @param {CacheItemGroupPoolListOrMap} groups
     * @param {?CacheItemPoolName}          [pool]
     */
    Manager.prototype.clearItemGroups = function (groups, pool) {
        // TODO
    };

    /**
     * Delete item from the cache pool.
     *
     * @param {CacheItemKey}       item
     * @param {?CacheItemPoolName} [pool]
     */
    Manager.prototype.deleteItem = function (item, pool) {
        // TODO
    };

    /**
     * Deletes multiple items from the cache pool.
     *
     * @param {CacheItemPoolListOrMap} items
     * @param {?CacheItemPoolName}     [pool]
     */
    Manager.prototype.deleteItems = function (items, pool) {
        // TODO
    };

    /**
     * Empty the entire cache pool of all items.
     *
     * @param {String}          [cacheItemGroup] - The item key group to delete.
     * @param {String|String[]} [cacheItemKeys]  - The cache key(s) to delete.
     */
    Manager.prototype.clear = function (cacheItemGroup, cacheItemKeys) {
        var cacheRequestData = {
            cache_item_key_group: cacheItemGroup,
            cache_item_keys:      cacheItemKeys
        };

        this._make_request(Action.CLEAR, cacheRequestData);
    };

    /**
     * Sends a request to purge the given cache item pool(s)
     * of stale or expired items.
     *
     * @param  {CacheItemPoolName|CacheItemPoolName[]} [pools] -
     *     One or more cache item pools.
     *     If omitted, the default cache item pool is purged.
     * @return {?jqXHR}
     */
    Manager.prototype.purge = function (pools) {
        pools = this.parseCacheItemPools(pools);

        if (isBusy === true) {
            return null;
        }

        isBusy    = true;
        lastState = {
            relatedTarget: (fromEvent ? lastTarget : null),
            cacheAction:   Action.PURGE
        };

        var flushEvent,
            flushEventData,
            purgeEvent,
            purgeEventData;

        /** @type {HttpFlushCacheRequest} */
        var postData = {};

        /** @type {CacheEventOptions} */
        var purgeEventData = {
            relatedTarget: lastState.target,
            cacheManager:  this,
        };

        /** @type {FlushEventOptions} */
        var flushEventData = {
            relatedTarget: lastState.target,
            cacheManager:  this,
            cacheAction:   lastState.cacheAction,
        };

        if (pools) {
            postData.cache_item_pools     = pools;
            purgeEventData.cacheItemPools = pools;
            flushEventData.cacheItemPools = pools;
            lastState.cacheItemPools      = pools;
        }

        /*
        var flushEvent, purgeEvent, eventData, postData, settings;

        lastCacheAction       = cacheAction;
        lastCacheItemKeyGroup = cacheItemGroup;
        lastCacheItemKeys     = cacheItemKeys;
        lastXhr               = null;

        if (fromEvent === false) {
            lastTarget = null;
        }

        postData = {};

        eventData = {
            relatedTarget: lastTarget,
            cacheManager:  this,
            cacheAction:   cacheAction,
        };

        if (cacheItemGroup) {
            postData.cache_item_key_group = cacheItemGroup;
            eventData.cacheItemKeyGroup   = cacheItemGroup;
        }

        if (cacheItemKeys) {
            postData.cache_item_keys = cacheItemKeys;
            eventData.cacheItemKeys  = cacheItemKeys;
        }

        flushEvent = $.Event(Event.FLUSH, eventData);

        $document.trigger(flushEvent);

        if (flushEvent.isDefaultPrevented()) {
            return;
        }
        */

        /** @type {jQuery#postInit} */
        var postSettings = {
            url:      this.purgeCacheRequestURL,
            data:     postData,
            dataType: 'json',
            context:  this
        };

        lastXhr = $.post(postSettings)
            .then(Charcoal.Admin.resolveJqXhrFalsePositive)
            .done(handleFulfilledResponse)
            .fail(handleRejectedResponse)
            .always(handleFinalResponse);

        return lastXhr;
    };

    /**
     * @deprecated In favour of {@see Manager#_make_request}
     *
     * @param {String}          cacheAction      - Whether to empty all items or purge stale or expired items.
     * @param {String}          [cacheItemGroup] - The item key group to delete.
     * @param {String|String[]} [cacheItemKeys]  - The cache key(s) to delete.
     */
    Manager.prototype.flush = function (cacheAction, cacheItemGroup, cacheItemKeys) {
        var cacheRequestData = {
            cache_item_key_group: cacheItemGroup,
            cache_item_keys:      cacheItemKeys
        };

        this._make_request(cacheAction, cacheRequestData);
    };

    /**
     * Sends cache flush request.
     *
     * @param {String}          cacheAction      - Whether to empty all items or purge stale or expired items.
     * @param {String}          [cacheItemGroup] - The item key group to delete.
     * @param {String|String[]} [cacheItemKeys]  - The cache key(s) to delete.
     */
    Manager.prototype._make_request = function (cacheAction, cacheItemGroup, cacheItemKeys) {
        if (isBusy === true) {
            return;
        }

        if (cacheAction !== Action.CLEAR && cacheAction !== Action.PURGE) {
            cacheAction = Action.PURGE;
        }

        var flushEvent, eventData, settings, postData;

        isBusy                = true;
        lastCacheAction       = cacheAction;
        lastCacheItemKeyGroup = cacheItemGroup;
        lastCacheItemKeys     = cacheItemKeys;
        lastXhr               = null;

        if (fromEvent === false) {
            lastTarget = null;
        }

        postData = {};

        eventData = {
            relatedTarget: lastTarget,
            cacheManager:  this,
            cacheAction:   cacheAction,
        };

        if (cacheItemGroup) {
            postData.cache_item_key_group = cacheItemGroup;
            eventData.cacheItemKeyGroup   = cacheItemGroup;
        }

        if (cacheItemKeys) {
            postData.cache_item_keys = cacheItemKeys;
            eventData.cacheItemKeys  = cacheItemKeys;
        }

        flushEvent = $.Event(Event.FLUSH, eventData);

        $document.trigger(flushEvent);

        if (flushEvent.isDefaultPrevented()) {
            return;
        }

        settings = {
            url:      this.clearCacheRequestURL,
            data:     postData,
            dataType: 'json',
            context:  this
        };

        lastXhr = $.post(settings)
            .then(Charcoal.Admin.resolveJqXhrFalsePositive)
            .done(handleFulfilledResponse)
            .fail(handleRejectedResponse)
            .always(handleFinalResponse);
    };

    /**
     * Private Utilities
     */

    /**
     * @this   {CacheManager}
     * @param  {Object}   response   The HTTP Response object.
     * @param  {String}   textStatus The XHR status category.
     * @param  {Thenable} jqXHR      The promisable XHR object.
     */
    var handleFulfilledResponse = function (response/* textStatus, jqXHR */) {
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
    var handleRejectedResponse = function (jqXHR, textStatus, errorThrown) {
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
    var handleFinalResponse = function () {
        isBusy = false;

        var eventData = {
            relatedTarget: lastTarget,
            cacheManager:  this,
            cacheAction:   lastCacheAction,
        };

        if (lastCacheItemKeyGroup) {
            eventData.cacheItemKeyGroup = lastCacheItemKeyGroup;
        }

        if (lastCacheItemKeys) {
            eventData.cacheItemKeys = lastCacheItemKeys;
        }

        var flushedEvent = $.Event(Event.FLUSHED, eventData);

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
