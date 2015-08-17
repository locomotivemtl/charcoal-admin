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

Charcoal.Admin.Widget_Table = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/table';

    // Widget_Table properties
    this.obj_type = null;
    this.widget_id = null;
    this.table_selector = null;
    this.properties = null;
    this.properties_options = null;
    this.filters = null;
    this.orders = null;
    this.pagination = {
        page: 1,
        num_per_page: 50
    };
    this.table_rows = [];

    this.set_properties(opts).create_rows().bind_events();
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Table.prototype.set_properties = function (opts)
{
    this.obj_type = opts.data.obj_type || this.obj_type;
    this.widget_id = opts.id || this.widget_id;
    this.table_selector = '#' + this.widget_id;
    this.properties = opts.data.properties || this.properties;
    this.properties_options = opts.data.properties_options || this.properties_options;
    this.filters = opts.data.filters || this.filters;
    this.orders = opts.data.orders || this.orders;
    this.pagination = opts.data.pagination || this.pagination;

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

    $('.js-list-quick-create',that.table_selector).on('click', function (e) {
        e.preventDefault();
        var url = Charcoal.Admin.admin_url() + 'action/json/widget/load',
            data = {
                widget_type: 'charcoal/admin/widget/objectForm',
                widget_options: {
                    obj_type: that.obj_type,
                    obj_id: 0
                }
            };
        $.post(url, data, function (response) {
            var dlg = BootstrapDialog.show({
                    title: 'Quick Create',
                    message: '...',
                    nl2br: false
                });
            if (response.success) {
                dlg.setMessage(response.widget_html);
            } else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        });

    });

    $('.js-sublist-inline-edit').on('click', function (e) {
        e.preventDefault();

        var sublist = that.sublist(),
            url = Charcoal.Admin.admin_url() + 'action/json/widget/table/inlinemulti',
            data = {
                obj_type: that.obj_type,
                obj_ids: sublist.obj_ids
            };

        $.post(url, data, function (response) {
            //console.debug(response);
            if (response.success) {
                var objects = response.objects;
                //console.debug(objects);
                //console.debug(objects.length);
                for (var i = 0;i <= objects.length -1;i++) {
                    //console.debug(i);
                    window.console.debug(objects[i]);

                    var inline_properties = objects[i].inline_properties,
                        row = $(sublist.elems[i]).parents('tr'),
                        p = 0;

                    for (p in inline_properties) {
                        var td = row.find('.property-' + p);
                        td.html(inline_properties[p]);
                    }
                }
            }
        });

    });
};

Charcoal.Admin.Widget_Table.prototype.sublist = function ()
{
    //var that = this;

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

Charcoal.Admin.Widget_Table.prototype.widget_options = function ()
{
    return {
        obj_type:   this.obj_type,
        properties: this.properties,
        properties_options: this.properties_options,
        filters:    this.filters,
        orders:     this.orders,
        pagination: this.pagination
    };
};

Charcoal.Admin.Widget_Table.prototype.reload = function ()
{
    var that = this,
        url = Charcoal.Admin.admin_url() + 'action/json/widget/load',
        data = {
            widget_type:    that.widget_type,
            widget_options: that.widget_options()
        };

    $.post(url, data, function (response) {
        //console.debug(that.elem_id);
        if (response.success && response.widget_html) {
            //console.debug(response.widget_html);
            $('#' + that.widget_id).replaceWith(response.widget_html);
            that.widget_id = response.widget_id;
            // Rebind events
            that.bind_events();
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

    this.obj_id = this.element.getAttribute('data-id');
    this.obj_type = this.widget_table.obj_type;
    this.load_url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
    this.inline_url = Charcoal.Admin.admin_url() + 'action/json/widget/table/inline';
    this.delete_url = Charcoal.Admin.admin_url() + 'action/json/object/delete';

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
        widget_type: 'charcoal/admin/widget/objectForm',
        widget_options: {
            obj_type: this.obj_type,
            obj_id: this.obj_id
        }
    };

    $.post(this.load_url, data, function (response) {
        var dlg = BootstrapDialog.show({
            title: 'Quick Edit',
            message: '...',
            nl2br: false
        });
        if (response.success) {
            dlg.setMessage(response.widget_html);
        } else {
            dlg.setType(BootstrapDialog.TYPE_DANGER);
            dlg.setMessage('Error');
        }
    });
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

            var inline_properties = response.inline_properties,
                p;

            for (p in inline_properties) {
                var td = that.element.find('.property-' + p);
                td.html(inline_properties[p]);
            }
        }
    });
};

Charcoal.Admin.Widget_Table.Table_Row.prototype.delete_object = function ()
{
    if (window.confirm('Are you sure you want to delete this object?')) {
        var that = this,
            data = {
                obj_type: that.obj_type,
                obj_id: that.obj_id
            };

        $.post(that.delete_url, data, function (response) {
            if (response.success) {
                that.widget_table.reload();
            } else {
                window.alert('Delete failed.');
            }
        });
    }
};
