/* globals commonL10n */
/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this.save_callback = opts.save_callback || '';
    this.cancel_callback = opts.cancel_callback || '';

    this.form_selector = opts.data.form_selector;
    this.$form         = $(this.form_selector);

    this.save_action   = opts.save_action || 'object/save';
    this.update_action = opts.update_action || 'object/update';
    this.extra_form_data = opts.extra_form_data || {};

    this.group_conditions = opts.data.group_conditions;
    this.form_working = false;
    this.is_new_object = false;
    this.xhr = null;
    this.obj_id = Charcoal.Admin.parseNumber(opts.obj_id) || 0;

    return this;
};
Charcoal.Admin.Widget_Quick_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Quick_Form.prototype.constructor = Charcoal.Admin.Widget_Quick_Form;
Charcoal.Admin.Widget_Quick_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Quick_Form.prototype.init = function () {
    this.bind_events();
};

Charcoal.Admin.Widget_Quick_Form.prototype.bind_events = function () {
    var that = this;
    $(document).on('submit', '#' + this.id(), function (e) {
        e.preventDefault();
        that.submit_form(this);
    });
    $('#' + this.id()).on(
        'click.charcoal.bs.dialog',
        '[data-dismiss="dialog"]',
        function (event) {
            if ($.isFunction(that.cancel_callback)) {
                that.cancel_callback(event);
            }
        }
    );

    this.parse_group_conditions();
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.parse_group_conditions = function () {
    var that = this;

    $.each(this.group_conditions, function (target, conditions) {
        var isValid = that.validate_group_conditions(target);
        if (!isValid) {
            that.toggle_conditional_group(target, isValid, false);
        }

        $.each(conditions, function (index, condition) {
            that.$form.on('change.charcoal.quick.form', '#' + condition.input_id, {
                condition_target: target
            }, function (event) {
                var isValid = that.validate_group_conditions(event.data.condition_target);
                that.toggle_conditional_group(event.data.condition_target, isValid);
            });
        });
    });
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.validate_group_conditions = function (target) {
    var conditions = this.group_conditions[target];
    var that       = this;
    var valid      = true;

    $.each(conditions, function (index, condition) {
        var $input    = that.$form.find('#' + condition.input_id);
        var input_val = that.get_input_value($input);

        switch (JSON.stringify(condition.operator)) {
            case '"!=="':
            case '"!="':
            case '"!"':
            case '"not"':
                if (input_val === condition.value) {
                    valid = false;
                    return;
                }
                break;
            default:
            case '"==="':
            case '"=="':
            case '"="':
            case '"is"':
                if (input_val !== condition.value) {
                    valid = false;
                    return;
                }
                break;
        }

    });

    return valid;
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.toggle_conditional_group = function (group, flag, animate) {
    var $group  = this.$form.find('#' + group);
    var $inputs = $group.find('select, input, textarea');
    animate     = animate || true;

    var complete = function () {
        $inputs.each(function () {
            $(this).attr('disabled', !flag);
        });
    };

    if (flag) {
        if (animate) {
            $group.slideDown({
                easing: 'easeInOutQuad',
                start:  complete
            });
        } else {
            $group.show(0, complete);
        }
    } else {
        if (animate) {
            $group.slideUp({
                easing:   'easeInOutQuad',
                complete: complete
            });
        } else {
            $group.hide(0, complete);
        }
    }
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Quick_Form.prototype.get_input_value = function ($input) {
    // skip if disabled
    if ($input.attr('disabled') === 'disabled') {
        return null;
    }

    var val;

    var $inputType = $input.attr('type');
    switch ($inputType) {
        case 'select':
            val = $input.find(':selected').val();
            break;
        case 'checkbox':
            val = $input.is(':checked');
            break;
        default:
            val = $input.val();
            break;
    }

    return val;
};

Charcoal.Admin.Widget_Quick_Form.prototype.submit_form = function (form) {
    if (this.form_working) {
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    var $trigger, $form, form_data;

    $form = $(form);
    $trigger = $form.find('[type="submit"]');

    if ($trigger.prop('disabled')) {
        return false;
    }

    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    form_data = new FormData(form);

    this.disable_form($form, $trigger);

    var extraFormData = this.extra_form_data;

    for (var data in extraFormData) {
        if (extraFormData.hasOwnProperty(data)){
            form_data.append(data, extraFormData[data]);
        }
    }

    this.xhr = $.ajax({
        type: 'POST',
        url: this.request_url(),
        data: form_data,
        dataType: 'json',
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

Charcoal.Admin.Widget_Quick_Form.prototype.request_success = function ($form, $trigger, response/* ... */) {
    if (response.feedbacks && !this.suppress_feedback()) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label: commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }

    this.enable_form($form, $trigger);
    this.form_working = false;

    if (typeof this.save_callback === 'function') {
        this.save_callback(response);
    }
};

Charcoal.Admin.Widget_Quick_Form.prototype.destroy = function () {
    this.$form.off('charcoal.quick.form');
};
