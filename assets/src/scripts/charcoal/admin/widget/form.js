/* eslint-disable consistent-this */
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
    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Form properties
    this.widget_id         = null;
    this.obj_type          = null;
    this.obj_id            = null;
    this.save_action       = 'object/save';
    this.update_action     = 'object/update';
    this.form_selector     = null;
    this.form_working      = false;
    this.submitted_via     = null;
    this.is_new_object     = false;
    this.xhr               = null;
    this.useDefaultAction  = false;
    this.confirmed         = false;

    this.update_tab_ident();

    var lang = $('[data-lang]:not(.d-none)').data('lang');
    if (lang) {
        Charcoal.Admin.setLang(lang);
    }

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype             = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent      = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts) {
    this.widget_id         = opts.id || this.widget_id;
    this.obj_type          = opts.data.obj_type || this.obj_type;
    this.obj_id            = Charcoal.Admin.parseNumber(opts.data.obj_id || this.obj_id);
    this.form_selector     = opts.data.form_selector || this.form_selector;
    this.isTab             = opts.data.tab;
    this.group_conditions  = opts.data.group_conditions;
    this.$form             = $(this.form_selector);
    this.allow_reload      = opts.data.allow_reload;
    this.force_page_reload = opts.data.force_page_reload;
    this.useDefaultAction  = opts.data.use_default_action;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.init = function () {
};

Charcoal.Admin.Widget_Form.prototype.widget_options = function () {
    var options = this.parent.widget_options.call(this);

    return $.extend({}, options, this.opts('data'));
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function () {
    var that = this;

    var $sidebar = $('.js-sidebar-widget', this.form_selector);

    // Submit the form via ajax
    $(that.form_selector)
        .on('submit.charcoal.form', function (event) {
            event.preventDefault();
            that.submit_form(this);
        })
        .find(':submit')
        .on('click.charcoal.form', function () {
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

    // Revision button
    $('.js-obj-revision', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.view_revision(this);
    });

    // Back-to-list button
    $('.js-obj-list', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();
        that.back_to_list(this);
    });

    // Language switcher
    $('.js-lang-switch button', $sidebar).on('click.charcoal.form', function (event) {
        event.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    window.onpopstate = function () {
        that.update_tab_ident();
    };

    this.parse_group_conditions();

    // crappy push state
    if (that.isTab) {
        $(this.form_selector).on('shown.bs.tab', '.js-group-tabs', function (event) {
            var $tab   = $(event.target); // active tab
            var params = [];

            var urlParams = Charcoal.Admin.queryParams();

            // Skip push state for same state.
            if (urlParams.tab_ident !== undefined &&
                $tab.data('tab-ident') === urlParams.tab_ident
            ) {
                return;
            }

            urlParams.tab_ident = $tab.data('tab-ident');

            for (var param in urlParams) {
                params.push(param + '=' + urlParams[param]);
            }

            history.pushState('', '', window.location.pathname + '?' + params.join('&'));
        });
    }

    /*if (that.isTab) {
         $(that.form_selector).on('click', '.js-group-tabs', function (event) {
             event.preventDefault();
             var href = $(this).attr('href');
             $(that.form_selector).find('.js-group-tab').addClass('d-none');
             $(that.form_selector).find('.js-group-tab.' + href).removeClass('d-none');
             $(this).parent().addClass('active').siblings('.active').removeClass('active');
         });
     }*/

};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.parse_group_conditions = function () {
    var that = this;

    $.each(this.group_conditions, function (target, conditions) {
        var isValid = that.validate_group_conditions(target);
        if (!isValid) {
            that.toggle_conditional_group(target, isValid, false);
        }

        $.each(conditions, function (index, condition) {
            $(that.form_selector).on('change.charcoal.form', '#' + condition.input_id, {
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
Charcoal.Admin.Widget_Form.prototype.validate_group_conditions = function (target) {
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
Charcoal.Admin.Widget_Form.prototype.toggle_conditional_group = function (group, flag, animate) {
    var $group  = this.$form.find('#' + group);
    var $inputs = $group.find('select, input, textarea');
    animate     = animate !== undefined ? animate : true;

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
Charcoal.Admin.Widget_Form.prototype.get_input_value = function ($input) {
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

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.submit_form()
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.update_tab_ident = function () {
    var urlParams = Charcoal.Admin.queryParams();

    if ('tab_ident' in urlParams) {
        $('.js-group-tabs[data-tab-ident="' + urlParams.tab_ident + '"]').tab('show');
    }
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

    if (this.confirmed) {
        form_data.append('confirmed', true);
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
    this.confirmed = false;

    if (response.feedbacks) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.need_confirmation) {
        Charcoal.Admin.feedback()
            .add_action({
                label:    commonL10n.cancel,
                cssClass: 'btn-danger',
                callback: function () {
                    BootstrapDialog.closeAll();
                }
            })
            .add_action({
                label:    commonL10n.continue,
                callback: function () {
                    //TODO THIS IS NOT IDEAL ... In the future,
                    // receiving an instance of BootstrapDialog would be better,
                    // unfortunately, this is not the case. Good day sir.
                    BootstrapDialog.closeAll();

                    this.confirmed = true;
                    this.submit_form($form[0]);
                }.bind(this)
            });
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label:    commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }

    if (!this.useDefaultAction && this.is_new_object) {
        this.suppress_feedback(true);

        if (response.next_url) {
            window.location.href = Charcoal.Admin.admin_url() + response.next_url;
        } else {
            var params = new URLSearchParams(window.location.search);

            window.location.href =
                Charcoal.Admin.admin_url() +
                'object/edit?' +
                (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
                (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
                'obj_type=' + this.obj_type +
                '&obj_id=' + response.obj_id;
        }
    } else {
        if (this.force_page_reload) {
            Charcoal.Admin.feedback().add_callback(function () {
                window.location.reload();
            });
        }

        if (this.allow_reload) {
            var manager = Charcoal.Admin.manager();
            var widgets = manager.components.widgets;

            $.each(widgets, function (i, widget) {
                widget.reload();
            }.bind(this));
        }
    }
};

Charcoal.Admin.Widget_Form.prototype.request_failed = function ($form, $trigger, jqXHR, textStatus, errorThrown) {
    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
        Charcoal.Admin.feedback(jqXHR.responseJSON.feedbacks);
    } else {
        var message = (this.is_new_object ? formWidgetL10n.createFailed : formWidgetL10n.updateFailed);
        var error   = errorThrown || commonL10n.errorOccurred;

        Charcoal.Admin.feedback([ {
            level:   'error',
            message: commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': message,
                '[[ errorThrown ]]':  error
            })
        } ]);
    }
};

Charcoal.Admin.Widget_Form.prototype.request_complete = function ($form, $trigger/*, .... */) {
    if (!this.suppress_feedback()) {
        Charcoal.Admin.feedback().dispatch();
        this.enable_form($form, $trigger);
    }

    this.submitted_via = null;

    this.suppress_feedback(false);

    this.form_working = this.is_new_object = false;
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
        .children('.fa').removeClass('d-none')
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
        .children('.fa').addClass('d-none')
        .next('.btn-label').removeClass('sr-only');

    return this;
};

/**
 * @return string The requested URL for processing the form.
 */
Charcoal.Admin.Widget_Form.prototype.request_url = function () {
    if (this.useDefaultAction) {
        return this.$form.attr('action');
    } else if (this.is_new_object) {
        return Charcoal.Admin.admin_url() + this.save_action;
    } else {
        return Charcoal.Admin.admin_url() + this.update_action;
    }
};

/**
 * Handle the "revision" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.view_revision = function (/* form */) {
    var type = this.obj_type,
        id   = this.obj_id;

    var defaultOpts = {
        size:           BootstrapDialog.SIZE_WIDE,
        title:          formWidgetL10n.revisions,
        widget_type:    'charcoal/admin/widget/object-revisions',
        widget_options: {
            obj_type:  type,
            obj_id:    id
        }
    };

    var dialogOpts = $.extend({}, defaultOpts);

    this.dialog(dialogOpts, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id:   response.widget_id,
                type: 'charcoal/admin/widget/object-revisions',
                obj_type: type,
                obj_id: id
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * Hande the "back to list" button / action.
 */
Charcoal.Admin.Widget_Form.prototype.back_to_list = function () {
    var params     = new URLSearchParams(window.location.search);
    window.location.href = 'object/collection?' +
        (params.has('main_menu') ? 'main_menu=' + params.get('main_menu') + '&' : '') +
        (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
        'obj_type=' + this.obj_type;
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
        (params.has('secondary_menu') ? 'secondary_menu=' + params.get('secondary_menu') + '&' : '') +
        'obj_type=' + this.obj_type;

    if (!that.obj_type || !that.obj_id) {
        var error = {
            level: 'warning',
            message: commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': formWidgetL10n.deleteFailed,
                '[[ errorThrown ]]': commonL10n.invalidObject
            })
        };
        Charcoal.Admin.feedback([ error ]).dispatch();
        return;
    }

    BootstrapDialog.confirm({
        title:      formWidgetL10n.confirmDeletion,
        type:       BootstrapDialog.TYPE_DANGER,
        message:    $('<p>' + commonL10n.confirmAction + '</p><p class="mb-0">' + commonL10n.cantUndo + '</p>'),
        btnOKLabel: commonL10n.delete,
        callback:   function (result) {
            if (result) {
                var url  = Charcoal.Admin.admin_url() + 'object/delete';
                var data = {
                    obj_type: that.obj_type,
                    obj_id:   that.obj_id
                };
                var xhr = $.ajax({
                    method:   'POST',
                    url:      url,
                    data:     data,
                    dataType: 'json'
                });

                Charcoal.Admin.resolveSimpleJsonXhr(
                    xhr,
                    // Success
                    function () {
                        window.location.href = successUrl;
                    },
                    // Failure
                    function (response) {
                        if (response.feedbacks.length) {
                            Charcoal.Admin.feedback(response.feedbacks);
                        } else {
                            Charcoal.Admin.feedback([ {
                                level:   'error',
                                message: formWidgetL10n.deleteFailed
                            } ]);
                        }
                    },
                    // Complete
                    function () {
                        if (!that.suppress_feedback()) {
                            Charcoal.Admin.feedback().dispatch();
                        }
                    }
                );
            }
        }
    });
};

/**
 * reload callback
 */
Charcoal.Admin.Widget_Form.prototype.reload = function (callback) {
    // Call supra class
    Charcoal.Admin.Widget.prototype.reload.call(this, function (that, response) {
        // Callback
        if (typeof callback === 'function') {
            callback.call(that, response);
        }
        // Re render.
        // This is not good.
        Charcoal.Admin.manager().render();
    }, true);

    $(document).off('charcoal.form');

    return this;
};

/**
 * Switch languages for all l10n elements in the form
 */
Charcoal.Admin.Widget_Form.prototype.switch_language = function (lang) {
    var currentLang = Charcoal.Admin.lang();
    if (currentLang !== lang) {
        Charcoal.Admin.setLang(lang);
        $('[data-lang][data-lang!=' + lang + ']').addClass('d-none');
        $('[data-lang][data-lang=' + lang + ']').removeClass('d-none');

        $('[data-lang-switch][data-lang-switch!=' + lang + ']')
            .removeClass('btn-primary')
            .addClass('btn-outline-primary');

        $('[data-lang-switch][data-lang-switch=' + lang + ']')
            .removeClass('btn-outline-primary')
            .addClass('btn-primary');

        $(document).triggerHandler({
            type: 'switch_language.charcoal'
        });
    }
};
