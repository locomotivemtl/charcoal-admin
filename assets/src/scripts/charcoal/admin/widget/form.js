/**
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

//Charcoal.Admin.Widget_Form = new Charcoal.Admin.Widget();        // Here's where the inheritance occurs

Charcoal.Admin.Widget_Form = function (opts)
{
    // Common Widget properties
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.obj_type = null;
    this.obj_id = null;

    this.init(opts);

};

Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;
Charcoal.Admin.Widget_Form.prototype.admin = new Charcoal.Admin();

Charcoal.Admin.Widget_Form.prototype.init = function (opts)
{
    var data = $.extend(true, {}, opts);
    this.set_data(data);

    this.bind_events();
};

Charcoal.Admin.Widget_Form.prototype.set_data = function (data)
{
    this.obj_type = data.obj_type || null;
    this.obj_id = data.obj_id || null;
    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    $('.form-submit').on('click', function (e) {
        e.preventDefault();

        var url;
        if (that.obj_id) {
            url = that.admin.admin_url() + 'action/json/object/update';
        } else {
            url = that.admin.admin_url() + 'action/json/object/save';
        }
        var form_data = $(this).parents('form').serializeArray();
        var obj_data = {};
        $(form_data).each(function (index, obj) {
            obj_data[obj.name] = obj.value;
        });
        var data = {
            obj_type: that.obj_type,
            obj_id: that.obj_id,
            obj_data: obj_data
        };
        $.post(url, data, function (response) {
            window.console.debug(response);
            if (response.success) {
                window.alert('Save successful!');
            } else {
                window.alert('Error. Could not save object.');
            }
        }).fail(function () {
            window.alert('Error attempting to save form.');
        });
    });
};
