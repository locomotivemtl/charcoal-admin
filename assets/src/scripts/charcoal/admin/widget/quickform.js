/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Quick_Form = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.quickform';

    Charcoal.Admin.Widget.call(this, opts);

    this.save_callback   = opts.save_callback || '';
    this.cancel_callback = opts.cancel_callback || '';

    this.form_selector = opts.data.form_selector;
    this.$form         = $(this.form_selector);

    this.save_action     = opts.save_action || 'object/save';
    this.update_action   = opts.update_action || 'object/update';
    this.extra_form_data = opts.extra_form_data || {};

    this.group_conditions = opts.data.group_conditions;
    this.group_display_mode = opts.data.group_display_mode || '';
    this.show_language_switch = opts.data.show_language_switch || false;
    this.form_working = false;
    this.is_new_object = false;
    this.xhr = null;
    this.obj_id = Charcoal.Admin.parseNumber(opts.obj_id) || 0;
};
Charcoal.Admin.Widget_Quick_Form.prototype = Object.create(Charcoal.Admin.Widget_Form.prototype);
Charcoal.Admin.Widget_Quick_Form.prototype.constructor = Charcoal.Admin.Widget_Quick_Form;
Charcoal.Admin.Widget_Quick_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Quick_Form.prototype.init = function () {
    this.bind_events();
    this.parse_group_conditions();

    if (this.show_language_switch) {
        $('.nav-link.nav-lang[data-tab-ident="' + Charcoal.Admin.lang() + '"]').trigger('click')
    }
};

Charcoal.Admin.Widget_Quick_Form.prototype.bind_events = function () {
    var that  = this;
    var $form = this.$form;

    $form
        .on('submit' + this.EVENT_NAMESPACE, function (event) {
            event.preventDefault();
            that.request_submit();
        })
        .on('click' + this.EVENT_NAMESPACE, '[data-dismiss="dialog"]', function (event) {
            if ($.isFunction(that.cancel_callback)) {
                that.cancel_callback(event);
            }
        })

    if (this.show_language_switch) {
        $form.on('click.nav-link.nav-lang', 'a.nav-link.nav-lang', function (event) {
            event.preventDefault();
            that.trigger_lang_tab($(this).attr('data-tab-ident'))
        });
    }
};

Charcoal.Admin.Widget_Quick_Form.prototype.request_success = function (response/* ... */) {
    var successEvent = $.Event('success' + this.EVENT_NAMESPACE, {
        subtype:   'submission',
        component: this,
        response:  response
    });

    this.$form.trigger(successEvent);

    if (successEvent.isDefaultPrevented()) {
        return;
    }

    this.confirmed = false;

    if (response.feedbacks && !this.suppress_feedback()) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.need_confirmation) {
        this.add_actions_for_confirmation(response.confirmation_label);
        return;
    }

    if (response.next_url) {
        this.add_action_for_next_url(response.next_url, response.next_url_label);
        return;
    }

    this.enable_form();
    this.form_working = false;

    if (typeof this.save_callback === 'function') {
        this.save_callback(response);
    }
};

Charcoal.Admin.Widget_Quick_Form.prototype.trigger_lang_tab = function (currentLangTab) {
    $('.modal .form-field').each(function () {
        var dataLang = $(this).attr('data-lang');
        if (!dataLang) {
            return;
        }

        if (currentLangTab !== dataLang) {
            this.style.setProperty('display', 'none', 'important');
        } else {
            this.style.setProperty('display', 'block', 'important');
        }
    });
};
