/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts)
{
    this.widget_type       = 'charcoal/admin/widget/quick-form';
    this.save_callback     = opts.save_callback || '';
    this.form_working      = false;
    this.suppress_feedback = false;
    this.is_new_object     = false;
    this.xhr               = null;
    this.obj_id            = Charcoal.Admin.parseNumber(opts.obj_id) || 0;

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
    if (this.form_working) {
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    var $trigger, $form, form_data;

    $form    = $(form);
    $trigger = $form.find('[type="submit"]');

    if ($trigger.prop('disabled')) {
        return false;
    }

    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    form_data = new FormData(form);

    this.disable_form($form, $trigger);

    this.xhr = $.ajax({
        type:        'POST',
        url:         this.request_url(),
        data:        form_data,
        dataType:    'json',
        processData: false,
        contentType: false,
    });

    this.xhr
        .then($.proxy(this.request_done, this, $form, $trigger))
        .done($.proxy(this.request_success, this, $form, $trigger))
        .fail($.proxy(this.request_failed, this, $form, $trigger))
        .always($.proxy(this.request_complete, this, $form, $trigger));
};

Charcoal.Admin.Widget_Quick_Form.prototype.disable_form = Charcoal.Admin.Widget_Form.prototype.disable_form;

Charcoal.Admin.Widget_Quick_Form.prototype.enable_form = Charcoal.Admin.Widget_Form.prototype.enable_form;

Charcoal.Admin.Widget_Quick_Form.prototype.request_url = Charcoal.Admin.Widget_Form.prototype.request_url;

Charcoal.Admin.Widget_Quick_Form.prototype.request_done = Charcoal.Admin.Widget_Form.prototype.request_done;

Charcoal.Admin.Widget_Quick_Form.prototype.request_failed = Charcoal.Admin.Widget_Form.prototype.request_failed;

Charcoal.Admin.Widget_Quick_Form.prototype.request_complete = Charcoal.Admin.Widget_Form.prototype.request_complete;

Charcoal.Admin.Widget_Quick_Form.prototype.request_success = function ($form, $trigger, response/* ... */)
{
    if (response.feedbacks) {
        Charcoal.Admin.feedback().add_data(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label:    'Continuer',
            callback: function () {
                window.location.href =
                    Charcoal.Admin.admin_url() +
                    response.next_url;
            }
        });
    }

    if (typeof this.save_callback === 'function') {
        this.save_callback(response);
    }
};
