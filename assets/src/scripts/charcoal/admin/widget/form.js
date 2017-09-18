/* globals commonL10n,formWidgetL10n,URLSearchParams */
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

Charcoal.Admin.Widget_Form = function (opts) {
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id         = null;
    this.obj_type          = null;
    this.obj_id            = null;
    this.save_action       = 'object/save';
    this.update_action     = 'object/update';
    this.form_selector     = null;
    this.form_working      = false;
    this.submitted_via     = null;
    this.suppress_feedback = false;
    this.is_new_object     = false;
    this.xhr               = null;

    var urlParams = Charcoal.Admin.queryParams();

    if ('tab_ident' in urlParams) {
        $('.js-group-tabs[data-tab-ident="' + urlParams.tab_ident + '"]').tab('show');
    }

    var lang = $('[data-lang]:not(.hidden)').data('lang');
    if (lang) {
        Charcoal.Admin.setLang(lang);
    }

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype             = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent      = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts) {
    this.widget_id     = opts.id || this.widget_id;
    this.obj_type      = opts.data.obj_type || this.obj_type;
    this.obj_id        = Charcoal.Admin.parseNumber(opts.data.obj_id || this.obj_id);
    this.form_selector = opts.data.form_selector || this.form_selector;
    this.isTab         = opts.data.tab;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function () {
    var that = this;

    var $sidebar = $('.c-form-sidebar', this.form_selector);

    // Submit the form via ajax
    $(that.form_selector)
        .on('submit.charcoal.form', function (event) {
            console.log('form.submit', event);
            event.preventDefault();
            that.submit_form(this);
        })
        .find(':submit')
            .on('click.charcoal.form', function (event) {
                console.log('click.submit', event);
                that.submitted_via = this;
            });

    // Any delete button should trigger the delete-object method.
    $('.js-obj-delete', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.delete_object(this);
    });

    // Reset button
    $('.js-reset-form', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        $(that.form_selector)[0].reset();
    });

    // Language switcher
    $('.js-lang-switch button', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    /*if (that.isTab) {
         $(that.form_selector).on('click', '.js-group-tabs', function (event) {
             event.preventDefault();
             var href = $(this).attr('href');
             $(that.form_selector).find('.js-group-tab').addClass('hidden');
             $(that.form_selector).find('.js-group-tab.' + href).removeClass('hidden');
             $(this).parent().addClass('active').siblings('.active').removeClass('active');
         });
     }*/

};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @param  Element form - The submitted form.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.submit_form = function (form) {
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

    if (this.submitted_via && this.submitted_via.name) {
        form_data.append(this.submitted_via.name, this.submitted_via.value || true);
    }

    this.disable_form($form, $trigger);

    // Use this loop if ever cascading checkbox inputs end up not
    // working properly in checkbox.mustache
    // $form.find('input[type="checkbox"]').each(function () {
    //     var $input = $(this);
    //     var inputName = $input.attr('name');

    //     // Prevents affecting switch type radio inputs
    //     if (typeof inputName !== 'undefined') {b
    //         if (!form_data.has(inputName)) {
    //             form_data.set(inputName, '');
    //         }
    //     }
    // });

    this.xhr = $.ajax({
        type:        'POST',            // ($form.prop('method') || 'POST')
        url:         this.request_url(),  // ($form.data('action') || this.request_url())
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

Charcoal.Admin.Widget_Form.prototype.request_done = function ($form, $trigger, response, textStatus, jqXHR) {
    if (!response || !response.success) {
        if (response.feedbacks) {
            return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
        } else {
            return $.Deferred().reject(jqXHR, textStatus, commonL10n.errorOccurred);
        }
    }

    return $.Deferred().resolve(response, textStatus, jqXHR);
};

Charcoal.Admin.Widget_Form.prototype.request_success = function ($form, $trigger, response/* textStatus, jqXHR */) {
    if (response.feedbacks) {
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

    if (this.is_new_object) {
        this.suppress_feedback = true;

        if (response.next_url) {
            window.location.href = Charcoal.Admin.admin_url() + response.next_url;
        } else {
            var params = new URLSearchParams(window.location.search);

            window.location.href =
                Charcoal.Admin.admin_url() +
                'object/edit?' +
                (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
                (params.has('sidemenu') ? 'sidemenu=' + params.get('sidemenu') + '&' : '') +
                'obj_type=' + this.obj_type +
                '&obj_id=' + response.obj_id;
        }
    }
};

Charcoal.Admin.Widget_Form.prototype.request_failed = function ($form, $trigger, jqXHR, textStatus, errorThrown) {
    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
        Charcoal.Admin.feedback(jqXHR.responseJSON.feedbacks);
    } else {
        var message = (this.is_new_object ? formWidgetL10n.createFailed : formWidgetL10n.updateFailed);
        var error   = errorThrown || commonL10n.errorOccurred;

        Charcoal.Admin.feedback([{
            message: commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': message,
                '[[ errorThrown ]]':  error
            }),
            level:   'error'
        }]);
    }
};

Charcoal.Admin.Widget_Form.prototype.request_complete = function ($form, $trigger/*, .... */) {
    if (!this.suppress_feedback) {
        Charcoal.Admin.feedback().dispatch();
        this.enable_form($form, $trigger);
    }

    this.submitted_via = null;

    this.form_working = this.is_new_object = this.suppress_feedback = false;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.disable_form = function ($form, $trigger) {
    if ($form) {
        $form.prop('disabled', true);
    }

    if ($trigger) {
        $trigger.prop('disabled', true);
    }

    if (this.submitted_via) {
        this.disable_button(this.submitted_via);
    }

    return this;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.enable_form = function ($form, $trigger) {
    if ($form) {
        $form.prop('disabled', false);
    }

    if ($trigger) {
        $trigger.prop('disabled', false);
    }

    if (this.submitted_via) {
        this.enable_button(this.submitted_via);
    }

    return this;
};

/**
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.disable_button = function ($trigger) {
    if (!($trigger instanceof jQuery)) {
        $trigger = $($trigger);
    }

    $trigger.prop('disabled', true)
        .children('.glyphicon').removeClass('hidden')
        .next('.btn-label').addClass('sr-only');

    return this;
};

/**
 * @param  Element $form    The submitted form.
 * @param  Element $trigger The form's submit button.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.enable_button = function ($trigger) {
    if (!($trigger instanceof jQuery)) {
        $trigger = $($trigger);
    }

    $trigger.prop('disabled', false)
        .children('.glyphicon').addClass('hidden')
        .next('.btn-label').removeClass('sr-only');

    return this;
};

/**
 * @return string The requested URL for processing the form.
 */
Charcoal.Admin.Widget_Form.prototype.request_url = function () {
    if (this.is_new_object) {
        return Charcoal.Admin.admin_url() + this.save_action;
    } else {
        return Charcoal.Admin.admin_url() + this.update_action;
    }
};

/**
 * Handle the "delete" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.delete_object = function (/* form */) {
    var that       = this;
    var params     = new URLSearchParams(window.location.search);
    var successUrl = Charcoal.Admin.admin_url() +
        'object/collection?' +
        (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
        (params.has('sidemenu') ? 'sidemenu=' + params.get('sidemenu') + '&' : '') +
        'obj_type=' + this.obj_type;

    //console.debug(form);
    BootstrapDialog.confirm({
        title:          formWidgetL10n.confirmDeletion,
        type:           BootstrapDialog.TYPE_DANGER,
        message:        $('<p>' + commonL10n.confirmAction + '</p><p>' + commonL10n.cantUndo + '</p>'),
        btnOKLabel:     commonL10n.delete,
        callback: function (result) {
            if (result) {
                var url  = Charcoal.Admin.admin_url() + 'object/delete';
                var data = {
                    obj_type: that.obj_type,
                    obj_id: that.obj_id
                };
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    //console.debug(response);
                    if (response.success) {
                        window.location.href = successUrl;
                    } else {
                        window.alert(formWidgetL10n.deleteFailed);
                    }
                });
            }
        }
    });
};

/**
 * Switch languages for all l10n elements in the form
 */
Charcoal.Admin.Widget_Form.prototype.switch_language = function (lang) {
    var currentLang = Charcoal.Admin.lang();
    if (currentLang !== lang) {
        Charcoal.Admin.setLang(lang);
        $('[data-lang][data-lang!=' + lang + ']').addClass('hidden');
        $('[data-lang][data-lang=' + lang + ']').removeClass('hidden');

        $('[data-lang-switch][data-lang-switch!=' + lang + ']')
            .removeClass('btn-info')
            .addClass('btn-default');

        $('[data-lang-switch][data-lang-switch=' + lang + ']')
            .removeClass('btn-default')
            .addClass('btn-info');

        $(document).triggerHandler({
            type: 'switch_language.charcoal'
        });
    }
};
