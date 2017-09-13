/**
 * Search widget used for filtering a list
 * charcoal/admin/widget/search
 *
 * Require:
 * - jQuery
 *
 * @param  {Object}  opts Options for widget
 */
Charcoal.Admin.Widget_Search = function (opts)
{
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

    this.opts = opts;

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
Charcoal.Admin.Widget_Search.prototype.set_remote_widget = function ()
{
    // Do something about this.
};

Charcoal.Admin.Widget_Search.prototype.init = function ()
{
    var $elem = this.element();

    var that = this;

    // Submit
    $elem.on('click', '.js-search', function (e) {
        e.preventDefault();
        that.submit();
    });

    // Undo
    $elem.on('click', '.js-undo', function (e) {
        e.preventDefault();
        that.undo();
    });
};

/**
 * Submit the search filters as expected to all widgets
 * @return this (chainable);
 */
Charcoal.Admin.Widget_Search.prototype.submit = function ()
{
    var manager = Charcoal.Admin.manager();
    var widgets = manager.components.widgets;

    var i = 0;
    var total = widgets.length;
    for (; i < total; i++) {
        this.dispatch(widgets[i]);
    }

    return this;
};

/**
 * Resets the search filters
 * @return this (chainable);
 */
Charcoal.Admin.Widget_Search.prototype.undo = function ()
{
    this.element().find('input').val('');
    this.submit();
    return this;
};

/**
 * Dispatches the event to all widgets that can listen to it
 * @return this (chainable)
 */
Charcoal.Admin.Widget_Search.prototype.dispatch = function (widget)
{
    if (!widget) {
        return this;
    }

    if (typeof widget.add_filter !== 'function') {
        return this;
    }

    if (typeof widget.pagination !== 'undefined') {
        widget.pagination.page = 1;
    }

    var $input, words, props, filters = [];

    $input = this.element().find('input');
    words  = $input.val().split(/\s/);
    props  = this.opts.data.list || [];

    $.each(words, function (i, word) {
        $.each(props, function (j, prop) {
            filters.push({
                val:      '%' + word + '%',
                property: prop,
                operator: 'LIKE',
                operand:  'OR'
            });
        });
    });

    widget.set_filters(filters);

    // widget.add_search(val, props);

    widget.reload();

    return this;
};
