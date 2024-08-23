/**
 * Table widget used for listing collections of objects
 * charcoal/admin/widget/table
 *
 * Require:
 * - jQuery
 * - Boostrap3-Dialog
 *
 * @mixes Charcoal.Admin.Mixin_Model_Search
 * @mixes Charcoal.Admin.Mixin_Model_Filters
 * @mixes Charcoal.Admin.Mixin_Model_Orders
 *
 * @param {Object} opts - Options for widget
 */

Charcoal.Admin.Widget_Table = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Table properties
    this.obj_type       = null;
    this.widget_id      = null;
    this.table_selector = null;
    this.pagination     = {
        page: 1,
        num_per_page: 50
    };
    this.list_actions = {};
    this.object_actions = {};

    this.items = 0;
    this.pages = 0;

    this.template = this.properties = this.properties_options = undefined;

    this.sortable         = false;
    this.sortable_handler = null;
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Search);
Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Filters);
Object.assign(Charcoal.Admin.Widget_Table.prototype, Charcoal.Admin.Mixin_Model_Orders);

/**
 * Necessary for a widget.
 */
Charcoal.Admin.Widget_Table.prototype.init = function () {
    this.set_properties().bind_events();
};

Charcoal.Admin.Widget_Table.prototype.set_properties = function () {
    var opts = this.opts();

    this.obj_type           = opts.data.obj_type           || this.obj_type;
    this.widget_id          = opts.id                      || this.widget_id;
    this.table_selector     = '#' + this.widget_id;
    this.sortable           = opts.data.sortable           || this.sortable;
    this.template           = opts.data.template           || this.template;
    this.collection_ident   = opts.data.collection_ident   || 'default'; // @todo remove the hardcoded shit
    this.show_table_header  = (typeof opts.data.show_table_header !== 'undefined') ? opts.data.show_table_header : true;

    if (('properties' in opts.data) && Array.isArray(opts.data.properties)) {
        this.properties = opts.data.properties;
    }

    if (('properties_options' in opts.data) && $.isPlainObject(opts.data.properties_options)) {
        this.properties_options = opts.data.properties_options;
    }

    if ('filters' in opts.data) {
        this.set_filters(opts.data.filters);
    }

    if ('orders' in opts.data) {
        this.set_orders(opts.data.orders);
    }

    if (('pagination' in opts.data) && $.isPlainObject(opts.data.pagination)) {
        this.pagination = opts.data.pagination;
    }

    if ('list_actions' in opts.data) {
        if (Array.isArray(opts.data.list_actions)) {
            this.list_actions = Object.assign({}, opts.data.list_actions);
        } else if ($.isPlainObject(opts.data.list_actions)) {
            this.list_actions = opts.data.list_actions;
        }
    }

    if ('object_actions' in opts.data) {
        if (Array.isArray(opts.data.object_actions)) {
            this.object_actions = Object.assign({}, opts.data.object_actions);
        } else if ($.isPlainObject(opts.data.object_actions)) {
            this.object_actions = opts.data.object_actions;
        }
    }

    return this;
};

/**
 * @see Charcoal.Admin.Widget_Table.prototype.bind_events()
 *     Similar method.
 */
Charcoal.Admin.Widget_Table.prototype.bind_events = function () {
    if (this.sortable_handler !== null) {
        this.sortable_handler.destroy();
    }

    var that = this;

    var $sortable_table = $('tbody.js-sortable', that.table_selector);
    if ($sortable_table.length > 0) {
        this.sortable_handler = new window.Sortable.default($sortable_table.get(), {
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
            that.reload(null, true);
        }
    });

    $('.js-page-switch', that.table_selector).on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');
        that.pagination.page = page_num;
        that.reload(null, true);
    });
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function () {
    return {
        obj_type:          this.obj_type,
        template:          this.template,
        sortable:          this.sortable,
        collection_ident:  this.collection_ident,
        show_table_header: this.show_table_header,
        collection_config: {
            properties:         this.properties,
            properties_options: this.properties_options,
            search_query:       this.get_search_query(),
            filters:            this.get_filters(),
            orders:             this.get_orders(),
            pagination:         this.pagination,
            list_actions:       this.list_actions,
            object_actions:     this.object_actions
        }
    };
};
