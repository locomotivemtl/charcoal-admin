var Charcoal = Charcoal || {};
Charcoal.Admin = function()
{
	// This is a singleton
	if(arguments.callee._singleton_instance) {
		return arguments.callee._singleton_instance;
	}
	arguments.callee._singleton_instance = this;
	
	this.url = '';
	this.admin_path = '';

	this.admin_url = function()
	{
		return this.url + this.admin_path + '/';
	};

};
;/**
* charcoal/admin/widget
*/

Charcoal.Admin.Widget = function(opts)
{
	window.alert('Widget ' + opts);
};

Charcoal.Admin.Widget.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Widget.prototype.reload = function(cb)
{
	var that = this;

	var url = that.admin.admin_url() + 'action/json/widget/load';
	var data = {
		widget_type: 	that.widget_type,
		widget_options: that.widget_options()
	};
	$.post(url, data, cb);
};
;/**
* charcoal/admin/widget/table
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

//Charcoal.Admin.Widget_Table = new Charcoal.Admin.Widget();        // Here's where the inheritance occurs 

Charcoal.Admin.Widget_Table = function(opts)
{
    // Common Widget properties
    this.widget_type = 'charcoal/admin/widget/table';
    
    // Widget_Table properties
    this.obj_type = null;
    this.widget_id = null;
    this.properties = null;
    this.properties_options = null;
    this.filters = null;
    this.orders = null;
    this.pagination = null;
    this.filters = null;

    this.init(opts);
};

Charcoal.Admin.Widget_Table.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Table.prototype.constructor = Charcoal.Admin.Widget_Table;
Charcoal.Admin.Widget_Table.prototype.parent = Charcoal.Admin.Widget.prototype;
Charcoal.Admin.Widget_Table.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Widget_Table.prototype.init = function(opts)
{
    // Set properties
    var data = $.extend(true, {}, this.default_data(), opts);
    this.set_data(data);

    this.bind_events();
};

Charcoal.Admin.Widget_Table.prototype.default_data = function()
{
    return {
        obj_type:   '',
        widget_id:  null,
        properties: null,
        properties_options: null,
        filters:    null,
        orders:     null,
        pagination:{
            page:           1,
            num_per_page:   50
        }

    };
};

Charcoal.Admin.Widget_Table.prototype.set_data = function(data)
{
    this.obj_type = data.obj_type || '';
    this.widget_id = data.widget_id || null;
    return this;
};

Charcoal.Admin.Widget_Table.prototype.bind_events = function()
{
    this.bind_obj_events();
    this.bind_list_events();
    this.bind_sublist_events();
};

Charcoal.Admin.Widget_Table.prototype.bind_obj_events = function()
{
    var that = this;

    $('.obj-edit').on('click', function(e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');
        window.alert('Edit ' + obj_id);
    });
    $('.obj-quick-edit').on('click', function(e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');
        
        var url = that.admin.admin_url() + 'action/json/widget/load';
        var data = {
            widget_type: 'charcoal/admin/widget/objectForm',
            widget_options: {
                obj_type: that.obj_type,
                obj_id: obj_id
            }
        };
        $.post(url, data, function (response) {
            var dlg = BootstrapDialog.show({
                title: 'Quick Edit',
                message: '...',
                nl2br: false
            });
            if(response.success) {
                dlg.setMessage(response.widget_html);   
            }
            else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        });
            
        
    });
    $('.obj-inline-edit').on('click', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var obj_id = row.data('id');
        var url = that.admin.admin_url() + 'action/json/widget/table/inline';
        var data = {
            obj_type: that.obj_type,
            obj_id: obj_id
        };
        $.post(url, data, function (response) {
            if(response.success) {
                var inline_properties = response.inline_properties;
                var p;
                for(p in inline_properties) {
                    var td = row.find('.property-'+p);
                    td.html(inline_properties[p]);
                }
            }
        });
    });
    $('.obj-delete').on('click', function(e) {
        e.preventDefault();
        var obj_id = $(this).parents('tr').data('id');
        if(window.confirm('Are you sure you want to delete this object?')) {
            var url = that.admin.admin_url() + 'action/json/object/delete';
            var data = {
                obj_type: that.obj_type,
                obj_id: obj_id
            };
            $.post(url, data, function (response) {
                if(response.success) {
                    that.reload();
                }
                else {
                    window.alert('Delete failed.');
                }
            });
        }
    });
    
};

Charcoal.Admin.Widget_Table.prototype.bind_list_events = function()
{
    var that = this;

    $('.list-quick-create').on('click', function(e) {
        e.preventDefault();
        var url = that.admin.admin_url() + 'action/json/widget/load';
        var data = {
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
            if(response.success) {
                dlg.setMessage(response.widget_html);   
            }
            else {
                dlg.setType(BootstrapDialog.TYPE_DANGER);
                dlg.setMessage('Error');
            }
        });
            
        
    });
};

Charcoal.Admin.Widget_Table.prototype.bind_sublist_events = function()
{
    var that = this;

    $('.sublist-inline-edit').on('click', function(e) {
        e.preventDefault();
        var sublist = that.sublist();
        //console.debug(sublist);
        var url = that.admin.admin_url() + 'action/json/widget/table/inlinemulti';
        var data = {
            obj_type: that.obj_type,
            obj_ids: sublist.obj_ids
        };
        $.post(url, data, function (response) {
            //console.debug(response);
            if(response.success) {
                var objects = response.objects;
                //console.debug(objects);
                //console.debug(objects.length);
                for(var i=0;i<=objects.length-1;i++) {
                    //console.debug(i);
                    window.console.debug(objects[i]);
                    var inline_properties = objects[i].inline_properties;
                    var row = $(sublist.elems[i]).parents('tr');

                    var p = 0;
                    for(p in inline_properties) {
                        var td = row.find('.property-'+p);
                        td.html(inline_properties[p]);
                    }
                }
            }
        });

    });
};

Charcoal.Admin.Widget_Table.prototype.sublist = function()
{
    //var that = this;

    var selected = $('.select-row:checked');
    var ret = {
        elems: [],
        obj_ids: []
    };
    selected.each(function(i, el) {
        ret.obj_ids.push($(el).parents('tr').data('id'));
        ret.elems.push(el);
    });
    return ret;
};

Charcoal.Admin.Widget_Table.prototype.widget_options = function()
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

Charcoal.Admin.Widget_Table.prototype.reload = function()
{
    var that = this;

    var url = that.admin.admin_url() + 'action/json/widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };
    $.post(url, data, function (response) {
        //console.debug(that.elem_id);
        if(response.success && response.widget_html) {
            //console.debug(response.widget_html);
            $('#'+that.widget_id).replaceWith(response.widget_html);
            that.widget_id = response.widget_id;
            // Rebind events
            that.bind_events();
        }

    });
    
};
