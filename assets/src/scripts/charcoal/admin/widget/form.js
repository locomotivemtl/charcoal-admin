/**
* Form widget that manages data sending
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Form = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id = null;
    this.obj_type = null;
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
    this.obj_type = opts.data.obj_type || this.obj_type;
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
    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    var that = this,
        form_data = new FormData(form),
        url,
        is_new_object;

    if (that.obj_id) {
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
                // Default, add feedback to list
                Charcoal.Admin.feedback().add_data(response.feedbacks);

                if (!is_new_object) {
                    Charcoal.Admin.feedback().call();
                } else {
                    window.location.href =
                        Charcoal.Admin.admin_url() +
                        'object/edit?obj_type=' + that.obj_type +
                        '&obj_id=' + response.obj_id;
                }
            } else {
                Charcoal.Admin.feedback().add_data(
                    [{
                        level: 'An error occurred and the object could not be saved.',
                        msg: 'error'
                    }]
                );
                Charcoal.Admin.feedback().call();
            }
        },
        error: function () {
            Charcoal.Admin.feedback().add_data(
                [{
                    level: 'An error occurred and the object could not be saved.',
                    msg: 'error'
                }]
            );
            Charcoal.Admin.feedback().call();
        }
    });
};
