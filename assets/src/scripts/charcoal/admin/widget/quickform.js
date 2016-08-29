/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts)
{
    this.widget_type   = 'charcoal/admin/widget/quick-form';
    this.save_callback = opts.save_callback || '';
    this.obj_id        = Charcoal.Admin.parseNumber(opts.obj_id) || 0;

    return this;
};
Charcoal.Admin.Widget_Quick_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Quick_Form.prototype.constructor = Charcoal.Admin.Widget_Quick_Form;
Charcoal.Admin.Widget_Quick_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Quick_Form.prototype.init = function ()
{
    this.bind_events();
};

Charcoal.Admin.Widget_Quick_Form.prototype.bind_events = function ()
{
    var that = this;
    $(document).on('submit', '#' + this.id(), function (e) {
        e.preventDefault();
        that.submit_form(this);
    });
};

Charcoal.Admin.Widget_Quick_Form.prototype.submit_form = function (form)
{
    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    var that = this,
        form_data = new FormData(form),
        url,
        is_new_object;

    if (that.obj_id) {
        url = Charcoal.Admin.admin_url() + 'object/update';
        is_new_object = false;
    } else {
        url = Charcoal.Admin.admin_url() + 'object/save';
        is_new_object = true;
    }

    $.ajax({
        url: url,
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: 'json',
        data: form_data,
        success: function (response) {
            if (response.success) {

                // Default, add feedback to list
                Charcoal.Admin.feedback().add_data(response.feedbacks);

                if (response.next_url) {
                    // @todo "dynamise" the label
                    Charcoal.Admin.feedback().add_action({
                        label: 'Continuer',
                        callback: function () {
                            window.location.href =
                                Charcoal.Admin.admin_url() +
                                response.next_url;
                        }
                    });
                }

                if (typeof that.save_callback === 'function') {
                    that.save_callback(response);
                }

                // Charcoal.Admin.feedback().call();

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
