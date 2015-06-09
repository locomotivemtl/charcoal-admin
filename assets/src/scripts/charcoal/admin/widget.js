/**
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
