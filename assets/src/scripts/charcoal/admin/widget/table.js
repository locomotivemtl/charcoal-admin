/* globals commonL10n,tableWidgetL10n,widgetL10n */
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
    this.table_rows     = [];
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
    this.set_properties().create_rows().bind_events();
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

Charcoal.Admin.Widget_Table.prototype.create_rows = function ()
{
    var rows = $('.js-table-row');

    for (var i = 0, len = rows.length; i < len; i++) {
        var element = rows[i],
            row = new Charcoal.Admin.Widget_Table.Table_Row(this,element);
        this.table_rows.push(row);
    }

    return this;
};

Charcoal.Admin.Widget_Table.prototype.bind_events = function ()
{
    var that = this;

    // The "quick create" event button loads the objectform widget
    // $('.js-list-quick-create', that.table_selector).on('click', function (e) {
    //     e.preventDefault();
    //     var url = Charcoal.Admin.admin_url() + 'widget/load',
    //         data = {
    //             widget_type: 'charcoal/admin/widget/objectForm',
    //             widget_options: {
    //                 obj_type: that.obj_type,
    //                 obj_id: 0
    //             }
    //         };

    //     $.post(url, data, function (response) {
    //         var dlg = BootstrapDialog.show({
    //                 title:   tableWidgetL10n.quickCreate,
    //                 message: '…',
    //                 nl2br:   false
    //             });

    //         dlg.getModalBody().on(
    //             'click.charcoal.bs.dialog',
    //             '[data-dismiss="dialog"]',
    //             { dialog: dlg },
    //             function (event) {
    //                 event.data.dialog.close();
    //             }
    //         );

    //         if (response.success) {
    //             dlg.setMessage(response.widget_html);
    //         } else {
    //             dlg.setType(BootstrapDialog.TYPE_DANGER);
    //             dlg.setMessage(commonL10n.errorOccurred);
    //         }
    //     }, 'json');

    // });

    // $('.js-sublist-inline-edit', that.table_selector).on('click', function (e) {
    //     e.preventDefault();

    //     var sublist = that.sublist(),
    //         url = Charcoal.Admin.admin_url() + 'widget/table/inlinemulti',
    //         data = {
    //             obj_type: that.obj_type,
    //             obj_ids: sublist.obj_ids
    //         };

    //     $.post(url, data, function (response) {
    //         if (response.success) {
    //             var objects = response.objects;
    //             for (var i = 0;i <= objects.length -1;i++) {

    //                 var formControls = objects[i].properties,
    //                     row = $(sublist.elems[i]).parents('tr'),
    //                     p = 0;

    //                 for (p in formControls) {
    //                     var td = row.find('.property-' + p);
    //                     td.html(formControls[p]);
    //                 }
    //             }
    //         }
    //     }, 'json');

    // });

    // $('.js-list-import', that.element).on('click', function (e) {
    //     e.preventDefault();

    //     var $this = $(this);
    //     var widget_type = $this.data('widget-type');

    //     that.widget_dialog({
    //         title: tableWidgetL10n.importList,
    //         widget_type: widget_type,
    //         widget_options: {
    //             obj_type: that.obj_type,
    //             obj_id: 0
    //         }
    //     });
    // });

    var $sortable_table = $('tbody.js-sortable', that.table_selector);
    if ($sortable_table.length > 0) {
        var sortableTable = new Sortable.default($sortable_table.get(), {
            delay: 150,
            draggable: '.js-table-row',
            handle: '.js-sortable-handle'
        });
        sortableTable.on('mirror:create', function(event) {
            // console.log(event.cancel());
            console.log('mirror creation');
        });
        sortableTable.on('sortable:start', function() {
            console.log('sortable:start');
        });
        sortableTable.on('sortable:sort',  function() {
            console.log('sortable:sort');
        });
        sortableTable.on('sortable:sorted', function() {
            console.log('sortable:sorted');
        });
        sortableTable.on('sortable:stop', function() {
            console.log('sortable:stop');
        });
    }

    // $('tbody.js-sortable', that.table_selector).sortable({
    //     cursor: 'ns-resize',
    //     delay: 150,
    //     distance: 5,
    //     opacity: 0.75,
    //     containment: 'parent',
    //     placeholder: 'ui-tablesort-placeholder',
    //     helper: function (e, ui) {
    //         ui.children().each(function () {
    //             $(this).width($(this).width());
    //         });
    //         return ui;
    //     },
    //     update: function () {
    //         var rows = $(this).sortable('toArray', {
    //             attribute: 'data-id'
    //         });

    //         var data = {
    //             obj_type: that.obj_type,
    //             obj_orders: rows,
    //             starting_order: 1
    //         };
    //         var url = Charcoal.Admin.admin_url() + 'object/reorder';
    //         $.ajax({
    //             method: 'POST',
    //             url: url,
    //             data: data,
    //             dataType: 'json'
    //         }).done(function (response) {
    //             console.debug(response);
    //         });
    //     }
    // }).disableSelection();

    $('.js-page-switch', that.table_selector).on('click', function (event) {
        event.preventDefault();

        var $this = $(this);
        var page_num = $this.data('page-num');
        that.pagination.page = page_num;
        that.reload();
    });

};

Charcoal.Admin.Widget_Table.prototype.sublist = function ()
{
    var selected = $('.select-row:checked'),
        ret = {
            elems: [],
            obj_ids: []
        };

    selected.each(function (i, el) {
        ret.obj_ids.push($(el).parents('tr').data('id'));
        ret.elems.push(el);
    });

    return ret;
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

/**
 * Load a widget (via ajax) into a dialog
 *
 * ## Options
 * - `title`
 * - `widget_type`
 * - `widget_options`
 */
Charcoal.Admin.Widget_Table.prototype.widget_dialog = function (opts)
{
    //return new Charcoal.Admin.Widget(opts).dialog(opts);
    var title          = opts.title || '',
        type           = opts.type || BootstrapDialog.TYPE_PRIMARY,
        size           = opts.size || BootstrapDialog.SIZE_NORMAL,
        widget_type    = opts.widget_type,
        widget_options = opts.widget_options || {};

    if (!widget_type) {
        return;
    }

    BootstrapDialog.show({
        title:   title,
        type:    type,
        size:    size,
        nl2br:   false,
        message: function (dialog) {
            var url  = Charcoal.Admin.admin_url() + 'widget/load',
                data = {
                    widget_type: widget_type,
                    widget_options: widget_options
                },
                $message = $('<div>' + widgetL10n.loading + '</div>');

            dialog.getModalBody().on(
                'click.charcoal.bs.dialog',
                '[data-dismiss="dialog"]',
                { dialog: dialog },
                function (event) {
                    event.data.dialog.close();
                }
            );

            $.ajax({
                method:   'POST',
                url:      url,
                data:     data,
                dataType: 'json'
            }).done(function (response) {
                if (response.success) {
                    dialog.setMessage(response.widget_html);
                } else {
                    dialog.setType(BootstrapDialog.TYPE_DANGER);
                    dialog.setMessage(commonL10n.errorOccurred);
                }
            });

            return $message;
        }
    });
};

/**
 * Table_Row object
 */
Charcoal.Admin.Widget_Table.Table_Row = function (container, row)
{
    this.widget_table = container;
    this.element = row;

    this.obj_id     = this.element.getAttribute('data-id');
    this.obj_type   = this.widget_table.obj_type;
    this.load_url   = Charcoal.Admin.admin_url() + 'widget/load';
    this.inline_url = Charcoal.Admin.admin_url() + 'widget/table/inline';
    this.delete_url = Charcoal.Admin.admin_url() + 'object/delete';

    this.bind_events();
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.bind_events = function ()
{
    var that = this;

    $('.js-obj-quick-edit', that.element).on('click', function (e) {
        e.preventDefault();
        that.quick_edit();
    });

    $('.js-obj-inline-edit', that.element).on('click', function (e) {
        e.preventDefault();
        that.inline_edit();
    });

    $('.js-obj-delete', that.element).on('click', function (e) {
        e.preventDefault();
        that.delete_object();
    });
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.quick_edit = function ()
{
    var data = {
        widget_type:    'charcoal/admin/widget/objectForm',
        widget_options: {
            obj_type:   this.obj_type,
            obj_id:     this.obj_id
        }
    };

    $.post(this.load_url, data, function (response) {
        var dlg = BootstrapDialog.show({
            title:   tableWidgetL10n.quickEdit,
            message: '…',
            nl2br:   false
        });

        dlg.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dlg },
            function (event) {
                event.data.dialog.close();
            }
        );

        if (response.success) {
            dlg.setMessage(response.widget_html);
        } else {
            dlg.setType(BootstrapDialog.TYPE_DANGER);
            dlg.setMessage(commonL10n.errorOccurred);
        }
    }, 'json');
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.inline_edit = function ()
{
    var that = this,
        data = {
        obj_type: that.obj_type,
        obj_id: that.obj_id
    };

    $.post(that.inline_url, data, function (response) {
        if (response.success) {

            var formControls = response.properties,
                p;

            for (p in formControls) {
                var td = $(that.element).find('.property-' + p);
                td.html(formControls[p]);
            }
        }
    }, 'json');
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.delete_object = function ()
{
    var that = this;

    BootstrapDialog.confirm({
        title:      tableWidgetL10n.confirmDeletion,
        type:       BootstrapDialog.TYPE_DANGER,
        message:    $('<p>' + commonL10n.confirmAction + '</p><p>' + commonL10n.cantUndo + '</p>'),
        btnOKLabel: commonL10n.delete,
        callback: function (result) {
            if (result) {
                var url = that.delete_url;
                var data = {
                    obj_type: that.obj_type,
                    obj_id: that.obj_id
                };

                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    if (response.success) {
                        $(that.element).remove();
                    } else {
                        window.alert(tableWidgetL10n.deleteFailed);
                    }
                });
            }
        }
    });
};

