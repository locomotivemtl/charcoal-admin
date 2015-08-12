/**
* Form widget that manages data sending
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - bootstrapSwitch
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Form = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id = null;
    this.obj_id = null;
    this.form_selector = null;

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts)
{
    this.widget_id = opts.id || this.widget_id;
    this.obj_id = opts.data.obj_id || this.obj_id;
    this.form_selector = opts.data.form_selector || this.form_selector;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    $(that.form_selector).on('submit', function (e) {
        e.preventDefault();
        that.submit_form(this);
    });
};

Charcoal.Admin.Widget_Form.prototype.submit_form = function (form)
{
    var form_data = new FormData(form),
        url,
        is_new_object;

    if (this.obj_id) {
        url = Charcoal.Admin.admin_url() + 'action/json/object/update';
        is_new_object = false;
    } else {
        url = Charcoal.Admin.admin_url() + 'action/json/object/save';
        is_new_object = true;
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
                if (!is_new_object) {
                    BootstrapDialog.show({
                        title: 'Save successful!',
                        message: 'Object was successfully saved to storage.',
                        type: BootstrapDialog.TYPE_SUCCESS
                    });
                } else {
                    window.location.href =
                        Charcoal.Admin.admin_url() +
                        'object/edit?obj_type=' + this.obj_type +
                        '&obj_id=' + response.obj_id;
                }
            } else {
                BootstrapDialog.show({
                    title: 'Error. Could not save object.',
                    message: 'An error occurred and the object could not be saved.',
                    type: BootstrapDialog.TYPE_DANGER
                });
            }
        },
        error: function () {
            BootstrapDialog.show({
                title: 'Error. Could not save object.',
                message: 'An error occurred and the object could not be saved.',
                type: BootstrapDialog.TYPE_DANGER
            });
        }
    });
};
