/**
 * Search widget used for filtering a list
 * charcoal/admin/widget/search
 *
 * Require:
 * - jQuery
 *
 * @param  {Object}  opts Options for widget
 */
Charcoal.Admin.Widget_Search = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.widget.search';

    Charcoal.Admin.Widget.call(this, opts);

    this._elem = undefined;

    if (!opts) {
        // No chance
        return false;
    }

    if (typeof opts.id === 'undefined') {
        return false;
    }

    this.set_element($('#' + opts.id));

    if (typeof opts.data !== 'object') {
        return false;
    }

    this.data   = opts.data;
    this.$input = null;

    this._search_filters = false;
    this._search_query   = false;

    return this;
};

Charcoal.Admin.Widget_Search.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Search.prototype.constructor = Charcoal.Admin.Widget_Search;
Charcoal.Admin.Widget_Search.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Search.prototype.widget_options = function () {
    return this.data;
};

/**
 * Whats the widget that should be refreshed?
 * A list, a table? Definition of a widget includes:
 * - Widget type
 */
Charcoal.Admin.Widget_Search.prototype.set_remote_widget = function () {
    // Do something about this.
};

Charcoal.Admin.Widget_Search.prototype.init = function () {
    var that  = this,
        $form = this.element();

    this.$input = $form.find('[name="query"]');

    $form.on('submit' + this.EVENT_NAMESPACE, function (event) {
        event.preventDefault();
        that.submit();
    });

    $form.on('reset' + this.EVENT_NAMESPACE, function (event) {
        event.preventDefault();
        that.clear();
    });
};

/**
 * Submit the search filters as expected to all widgets.
 *
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.submit = function () {
    this.set_search_query(this.$input.val());

    Charcoal.Admin.manager().get_widgets().forEach(this.dispatch.bind(this));

    this.set_search_query(null);

    return this;
};

/**
 * Resets the searchable widgets.
 *
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.clear = function () {
    this._search_search  = false;
    this._search_filters = false;

    this.$input.val('');
    this.submit();
    return this;
};

/**
 * Parse a search query.
 *
 * @param  {string} query - The search query.
 * @return {string|null} A search query or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.parse_search_query = function (query) {
    if (typeof query !== 'string') {
        return null;
    }

    query = query.trim();

    if (query.length === 0) {
        return null;
    }

    return query;
};

/**
 * Parse a search query into query filters.
 *
 * @param  {string} query - The search query.
 * @return {array|null} A search request object or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.parse_search_filters = function (query) {
    var words, props, filters = [], sub_filters;

    query = this.parse_search_query(query);

    if (query) {
        words = query.split(/\s/);
        props = this.data.properties || [];
        $.each(words, function (i, word) {
            sub_filters = [];
            word = word.replace(/'/g,'\\\'');
            $.each(props, function (j, prop) {
                sub_filters.push({
                    property: prop,
                    operator: 'LIKE',
                    value:    ('%' + word + '%')
                });
            });

            filters.push({
                conjunction: 'OR',
                filters:     sub_filters
            });
        });
    }

    if (filters.length) {
        return filters;
    }

    return null;
};

/**
 * Set the search query.
 *
 * @param  {string} query - The search query.
 * @return {void}
 */
Charcoal.Admin.Widget_Search.prototype.set_search_query = function (query) {
    this._search_search  = this.parse_search_query(query);
    this._search_filters = false;
};

/**
 * Get the search query.
 *
 * @return {string|null} The search query or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.search_query = function () {
    if (this._search_search === false) {
        return null;
    }

    return this._search_search;
};

/**
 * Get the search filters.
 *
 * @return {array|null} The query filters object or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.search_filters = function () {
    if (this._search_filters === false) {
        this._search_filters = this.parse_search_filters(this._search_search);
    }

    return this._search_filters;
};

/**
 * Assign the search query or filters on any searchable widget and dispatch request.
 *
 * @param  {object} widget - The widget to search on.
 * @return void
 */
Charcoal.Admin.Widget_Search.prototype.dispatch = function (widget) {
    // Bail early if no widget or if widget is self
    if (!widget || widget === this) {
        return;
    }

    var is_searchable = (typeof widget.set_search_query === 'function');
    var is_filterable = (typeof widget.set_filter === 'function');

    if (!is_searchable && !is_filterable) {
        return this;
    }

    if (is_searchable) {
        var query = this.search_query();
        widget.set_search_query(query);
    }

    if (is_filterable) {
        var filters = this.search_filters();
        widget.set_filter('search', filters);
    }

    if (typeof widget.pagination !== 'undefined') {
        widget.pagination.page = 1;
    }

    widget.reload(null, true);
};

/**
 * @return {void}
 */
Charcoal.Admin.Widget_Search.prototype.destroy = function () {
    var $form = this.element();

    $form.off(this.EVENT_NAMESPACE);
};
