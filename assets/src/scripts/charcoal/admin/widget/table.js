/**
 * Table widget used for listing collections of objects
 * charcoal/admin/widget/table
 *
 * Require:
 * - jQuery
 * - Boostrap3-Dialog
 *
 * @param  {Object}  opts Options for widget
 */

Charcoal.Admin.Widget_Table = function ()
{
    // Widget_Table properties
    this.obj_type       = null;
    this.widget_id      = null;
    this.table_selector = null;
    this.filters        = {};
    this.orders         = {};
    this.pagination     = {
        page: 1,
        num_per_page: 50
    };
    this.list_actions = {};
    this.object_actions = {};

    this.template = this.properties = this.properties_options = undefined;
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
 * Necessary for a widget.
 */
Charcoal.Admin.Widget_Table.prototype.init = function ()
{
    this.set_properties().bind_events();
};

Charcoal.Admin.Widget_Table.prototype.set_properties = function ()
{
    var opts = this.opts();

    this.obj_type           = opts.data.obj_type           || this.obj_type;
    this.widget_id          = opts.id                      || this.widget_id;
    this.table_selector     = '#' + this.widget_id;
    this.template           = opts.data.template           || this.template;
    this.properties         = opts.data.properties         || this.properties;
    this.properties_options = opts.data.properties_options || this.properties_options;
    this.filters            = opts.data.filters            || this.filters;
    this.orders             = opts.data.orders             || this.orders;
    this.pagination         = opts.data.pagination         || this.pagination;
    this.list_actions       = opts.data.list_actions       || this.list_actions;
    this.object_actions     = opts.data.object_actions     || this.object_actions;

    // @todo remove the hardcoded shit
    this.collection_ident = opts.data.collection_ident || 'default';

    return this;
};

Charcoal.Admin.Widget_Table.prototype.bind_events = function ()
{
    var that = this;

    var $sortable_table = $('tbody.js-sortable', that.table_selector);
    if ($sortable_table.length > 0) {
        new window.Sortable.default($sortable_table.get(), {
            delay: 150,
            draggable: '.js-table-row',
            handle: '.js-sortable-handle',
            mirror: {
                constrainDimensions: true,
            }
        }).on('mirror:create', function (event) {
            var originalCells = event.originalSource.querySelectorAll(':scope > td');
            var mirrorCells = event.source.querySelectorAll(':scope > td');
            originalCells.forEach(function (cell, index) {
                mirrorCells[index].style.width = cell.offsetWidth + 'px';
            });
        }).on('sortable:stop', function (event) {
            if (event.oldIndex !== event.newIndex) {
                var rows = Array.from(event.newContainer.querySelectorAll(':scope > tr')).map(function (row) {
                    if (row.classList.contains('draggable-mirror') || row.classList.contains('draggable--original')) {
                        return '';
                    } else {
                        return row.getAttribute('data-id');
                    }
                }).filter(function (row) {
                    return row !== '';
                });

                $.ajax({
                    method: 'POST',
                    url: Charcoal.Admin.admin_url() + 'object/reorder',
                    data: {
                        obj_type: that.obj_type,
                        obj_orders: rows,
                        starting_order: 1
                    },
                    dataType: 'json'
                }).done(function (response) {
                    console.debug(response);
                    if (response.feedbacks) {
                        Charcoal.Admin.feedback(response.feedbacks).dispatch();
                    }
                });
            }
        });
    }
    
    $('.js-jump-page-form', that.table_selector).on('submit', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = parseInt($this.find('input').val());

        if (page_num) {
            that.pagination.page = page_num;
            that.reload();
        }
    });

    $('.js-page-switch', that.table_selector).on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');
        that.pagination.page = page_num;
        that.reload();
    });
};

/**
 * As it says, it ADDs a filter to the already existing list
 * @param object
 * @return this chainable
 * @see set_filters
 */
Charcoal.Admin.Widget_Table.prototype.add_filter = function (filter)
{
    var filters = this.get_filters();

    // Null by default
    // When you add a filter, you want it to be
    // in an object
    if (filters === null) {
        filters = {};
    }

    filters = $.extend(filters, filter);
    this.set_filters(filters);

    return this;
};

/**
 * This will overwrite existing filters
 */
Charcoal.Admin.Widget_Table.prototype.set_filters = function (filters)
{
    this.filters = filters;
};

/**
 * Getter
 * @return {Object | null} filters
 */
Charcoal.Admin.Widget_Table.prototype.get_filters = function ()
{
    return this.filters;
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function ()
{
    return {
        obj_type:          this.obj_type,
        template:          this.template,
        collection_ident:  this.collection_ident,
        collection_config: {
            properties:         this.properties,
            properties_options: this.properties_options,
            filters:            this.filters,
            orders:             this.orders,
            pagination:         this.pagination,
            list_actions:       this.list_actions,
            object_actions:     this.object_actions
        }
    };
};

/**
 *
 */
Charcoal.Admin.Widget_Table.prototype.reload = function (callback)
{
    // Call supra class
    Charcoal.Admin.Widget.prototype.reload.call(this, callback);

    return this;
};
