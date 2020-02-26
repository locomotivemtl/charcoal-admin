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

    this.opts   = opts;
    this.$input = null;

    return this;
};

Charcoal.Admin.Widget_Search.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Search.prototype.constructor = Charcoal.Admin.Widget_Search;
Charcoal.Admin.Widget_Search.prototype.parent = Charcoal.Admin.Widget.prototype;

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

    $form.on('submit.charcoal.search', function (event) {
        event.preventDefault();
        that.submit();
    });

    $form.on('reset.charcoal.search', function (event) {
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
    var manager, widgets, request, total;

    manager = Charcoal.Admin.manager();
    widgets = manager.components.widgets;

    total = widgets.length;
    if (total > 0) {
        request = this.prepare_request(this.$input.val());
        console.log('Search.submit', request);

        for (var i = 0; i < total; i++) {
            this.dispatch(request, widgets[i]);
        }
    }

    return this;
};

/**
 * Resets the searchable widgets.
 *
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.clear = function () {
    this.$input.val('');
    this.submit();
    return this;
};

/**
 * Prepares a search request from a query.
 *
 * @param  {string} query - The search query.
 * @return {object|null} A search request object or NULL.
 */
Charcoal.Admin.Widget_Search.prototype.prepare_request = function (query) {
    var words, props, request = null, filters = [], sub_filters;

    query = query.trim();
    if (query) {
        words = query.split(/\s/);
        props = this.opts.data.properties || [];
        $.each(words, function (i, word) {
            sub_filters = [];
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
        request = {
            filters: filters
        };
    }

    return request;
};

/**
 * Dispatches the event to all widgets that can listen to it
 *
 * @param  {object} request - The search request.
 * @param  {object} widget  - The widget to search on.
 * @return this
 */
Charcoal.Admin.Widget_Search.prototype.dispatch = function (request, widget) {
    if (!widget) {
        return this;
    }

    if (typeof widget.set_filters !== 'function') {
        return this;
    }

    if (typeof widget.pagination !== 'undefined') {
        widget.pagination.page = 1;
    }

    var filters = [];
    if (request) {
        filters.push(request);
    }

    widget.set_filters(filters);

    widget.reload();

    return this;
};
