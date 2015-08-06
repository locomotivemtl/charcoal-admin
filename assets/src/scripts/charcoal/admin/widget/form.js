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
    this.widget_id = null;
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
    this.widget_id = data.widget_id || null;
    this.obj_type = data.obj_type || null;
    this.obj_id = data.obj_id || null;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    $('#' + that.widget_id).on('submit', function (e) {
        e.preventDefault();

        var $form = $(this),
            form_data = new FormData($form[0]),
            url;

        if (that.obj_id) {
            url = that.admin.admin_url() + 'action/json/object/update';
        } else {
            url = that.admin.admin_url() + 'action/json/object/save';
        }

        $.ajax({
            url: url,
            type: 'POST',
            processData: false,
            contentType: false,
            data: form_data,
            success: function (response) {
                console.debug(response);
                if (response.success) {
                    //window.alert('Save successful!');
                    BootstrapDialog.show({
                        title: 'Save successful!',
                        message: 'Object was successfully saved to storage.',
                        type: BootstrapDialog.TYPE_SUCCESS
                    });
                } else {
                    //window.alert('Error. Could not save object.');
                    BootstrapDialog.show({
                        title: 'Error. Could not save object.',
                        message: 'An error occurred and the object could not be saved.',
                        type: BootstrapDialog.TYPE_DANGER
                    });
                }
            },
            error: function () {
                //window.alert('Error. Could not save object.');
                BootstrapDialog.show({
                    title: 'Error. Could not save object.',
                    message: 'An error occurred and the object could not be saved.',
                    type: BootstrapDialog.TYPE_DANGER
                });
            }
        });
    });
};
