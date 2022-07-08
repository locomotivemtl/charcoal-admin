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
    this.EVENT_NAMESPACE = '.charcoal.form';

    Charcoal.Admin.Widget.call(this, opts);

    // Widget_Form properties
    this.widget_id         = null;
    this.obj_type          = null;
    this.obj_id            = null;
    this.save_action       = opts.save_action || 'object/save';
    this.update_action     = opts.update_action || 'object/update';
    this.extra_form_data   = opts.extra_form_data || {};
    this.form_selector     = null;
    this.form_working      = false;
    this.attempts          = 0;
    this.max_attempts      = 5;
    this.submitted_via     = null;
    this.is_new_object     = false;
    this.xhr               = null;
    this.useDefaultAction  = false;
    this.confirmed         = false;

    var lang = $('[data-lang]:not(.d-none)').data('lang');
    if (lang) {
        Charcoal.Admin.setLang(lang);
    }

    this._on_popstate_tab = this._on_popstate_tab.bind(this);
    this._on_shown_tab    = this._on_shown_tab.bind(this);

    this.set_properties(opts);
};
Charcoal.Admin.Widget_Form.prototype             = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent      = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.init = function () {
    this.update_tab_ident();

    this.bind_events();

    this.parse_group_conditions();
};

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

Charcoal.Admin.Widget_Form.prototype.widget_options = function () {
    var options = this.parent.widget_options.call(this);

    return $.extend({}, options, this.opts('data'));
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function () {
    var that  = this;
    var $form = this.$form;

    // Submit the form via ajax
    $form
        .on('submit' + this.EVENT_NAMESPACE, function (event) {
            event.preventDefault();
            that.request_submit();
        })
        .find(':submit')
        .on('click' + this.EVENT_NAMESPACE, function () {
            that.submitted_via = this;
        });

    var $sidebar = $('.js-sidebar-widget', this.form_selector);

    // Any delete button should trigger the delete-object method.
    $sidebar.on('click' + this.EVENT_NAMESPACE, '.js-obj-delete', function (event) {
        event.preventDefault();
        that.delete_object(this);
    });

    // Reset button
    $sidebar.on('click' + this.EVENT_NAMESPACE, '.js-reset-form', function (event) {
        event.preventDefault();
        $form[0].reset();
    });

    // Revision button
    $sidebar.on('click' + this.EVENT_NAMESPACE, '.js-obj-revision', function (event) {
        event.preventDefault();
        that.view_revision(this);
    });

    // Back-to-list button
    $sidebar.on('click' + this.EVENT_NAMESPACE, '.js-obj-list', function (event) {
        event.preventDefault();
        that.back_to_list(this);
    });

    // Language switcher
    $sidebar.on('click' + this.EVENT_NAMESPACE, '.js-lang-switch button', function (event) {
        event.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    window.addEventListener('popstate', this._on_popstate_tab);

    // crappy push state
    if (this.isTab) {
        $form.on('shown.bs.tab', '.js-group-tabs', this._on_shown_tab);
    }
};

Charcoal.Admin.Widget_Form.prototype._on_popstate_tab = function (/* event */) {
    this.update_tab_ident();
};

Charcoal.Admin.Widget_Form.prototype._on_shown_tab = function (event) {
    var $tab   = $(event.target); // active tab
    var params = [];

    var urlParams = Charcoal.Admin.queryParams();

    // Skip push state for same state.
    if (
        urlParams.tab_ident !== undefined &&
        $tab.data('tab-ident') === urlParams.tab_ident
    ) {
        return;
    }

    urlParams.tab_ident = $tab.data('tab-ident');

    for (var param in urlParams) {
        params.push(param + '=' + urlParams[param]);
    }

    history.pushState('', '', window.location.pathname + '?' + params.join('&'));
};

/**
 * @return {void}
 */
Charcoal.Admin.Widget_Form.prototype.parse_group_conditions = function () {
    var that  = this;
    var $form = this.$form;

    $.each(this.group_conditions, function (target, conditions) {
        var isValid = that.validate_group_conditions(target);
        if (!isValid) {
            that.toggle_conditional_group(target, isValid, false);
        }

        $.each(conditions, function (index, condition) {
            $form.on('change' + this.EVENT_NAMESPACE, '#' + condition.input_id, {
                condition_target: target
            }, function (event) {
                var isValid = that.validate_group_conditions(event.data.condition_target);
                that.toggle_conditional_group(event.data.condition_target, isValid);
            });
        });
    });
};

/**
 * @return {boolean}
 */
Charcoal.Admin.Widget_Form.prototype.validate_group_conditions = function (target) {
    var conditions = this.group_conditions[target];
    var that       = this;
    var $form      = this.$form;
    var valid      = true;

    $.each(conditions, function (index, condition) {
        var $input    = $form.find('#' + condition.input_id);
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
 * @return {void}
 */
Charcoal.Admin.Widget_Form.prototype.toggle_conditional_group = function (group, flag, animate) {
    var $group  = this.$form.find('#' + group);
    var $inputs = $group.find('select, input, textarea');

    animate = animate !== undefined ? animate : true;

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
 * @return {*}
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
 * @return {void}
 */
Charcoal.Admin.Widget_Form.prototype.update_tab_ident = function () {
    var urlParams = Charcoal.Admin.queryParams();

    if ('tab_ident' in urlParams) {
        $('.js-group-tabs[data-tab-ident="' + urlParams.tab_ident + '"]').tab('show');
    }
};

/**
 * @return {FormData}
 */
Charcoal.Admin.Widget_Form.prototype.get_form_data = function () {
    var form_data;

    if (this.$form.length) {
        form_data = new FormData(this.$form[0]);
    } else {
        form_data = new FormData();
    }

    if (this.submitted_via && this.submitted_via.name) {
        form_data.append(this.submitted_via.name, this.submitted_via.value || true);
    }

    if (this.confirmed) {
        form_data.append('confirmed', true);
    }

    /*
    // Use this loop if ever cascading checkbox inputs end up not
    // working properly in checkbox.mustache
    this.$form.find('input[type="checkbox"]').each(function () {
        var $input = $(this);
        var inputName = $input.attr('name');

        // Prevents affecting switch type radio inputs
        if (typeof inputName !== 'undefined') {b
            if (!form_data.has(inputName)) {
                form_data.set(inputName, '');
            }
        }
    });
    */

    if (this.extra_form_data) {
        for (var data in this.extra_form_data) {
            if (this.extra_form_data.hasOwnProperty(data)){
                form_data.append(data, this.extra_form_data[data]);
            }
        }
    }

    return form_data;
};

/**
 * @see    Charcoal.Admin.Widget_Quick_Form.prototype.request_submit()
 * @return {void}
 */
Charcoal.Admin.Widget_Form.prototype.request_submit = function () {
    this.attempts++;

    if (this.form_working) {
        var message;

        if (this.attempts >= this.max_attempts) {
            message = commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': formWidgetL10n.isBlocked,
                '[[ errorThrown ]]':  commonL10n.tryAgainLater
            });
        } else {
            message = commonL10n.errorTemplate.replaceMap({
                '[[ errorMessage ]]': formWidgetL10n.isProcessing,
                '[[ errorThrown ]]':  commonL10n.pleaseWait
            });
        }

        Charcoal.Admin.feedback([ {
            level:   'warning',
            display: 'toast',
            message: message
        } ]);
        return;
    }

    this.form_working = true;

    this.is_new_object = !this.obj_id;

    // Calls the `validate` and `save` functions on all components.
    if (Charcoal.Admin.manager().prepare_submit(this) !== true) {
        var failedEvent = $.Event('failed' + this.EVENT_NAMESPACE, {
            subtype:   'validation',
            component: this
        });

        this.$form.trigger(failedEvent);

        this.request_complete();
        return;
    }

    this.disable_form();

    this.submit_form();
};

/**
 * @return {void}
 */
Charcoal.Admin.Widget_Form.prototype.submit_form = function () {
    this.xhr = $.ajax({
        type:        'POST',
        url:         this.request_url(),
        data:        this.get_form_data(),
        dataType:    'json',
        processData: false,
        contentType: false,
    });

    this.xhr
        .then($.proxy(this.request_done, this))
        .done($.proxy(this.request_success, this))
        .fail($.proxy(this.request_failed, this))
        .always($.proxy(this.request_complete, this));
};

Charcoal.Admin.Widget_Form.prototype.request_done = function (response, textStatus, jqXHR) {
    if (!response || !response.success) {
        if (response.feedbacks) {
            return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
        } else {
            return $.Deferred().reject(jqXHR, textStatus, commonL10n.errorOccurred);
        }
    }

    return $.Deferred().resolve(response, textStatus, jqXHR);
};

Charcoal.Admin.Widget_Form.prototype.request_success = function (response/* textStatus, jqXHR */) {
    this.attempts = 0;

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

    if (response.feedbacks) {
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

    if (!this.useDefaultAction && this.is_new_object) {
        this.suppress_feedback(true);

        if (response.next_url) {
            Charcoal.Admin.redirect_to_url(response.next_url);
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
            window.location.reload();
        } else if (this.allow_reload) {
            var widgets = Charcoal.Admin.manager().get_widgets();

            $.each(widgets, function (i, widget) {
                widget.reload();
            }.bind(this));
        }
    }
};

Charcoal.Admin.Widget_Form.prototype.request_failed = function (jqXHR, textStatus, errorThrown) {
    var failedEvent = $.Event('failed' + this.EVENT_NAMESPACE, {
        subtype:   'submission',
        component: this,
        response:  (jqXHR.responseJSON || {})
    });

    this.$form.trigger(failedEvent);

    if (failedEvent.isDefaultPrevented()) {
        return;
    }

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

Charcoal.Admin.Widget_Form.prototype.request_complete = function (/* ... */) {
    var completeEvent = $.Event('complete' + this.EVENT_NAMESPACE, {
        subtype:   'submission',
        component: this
    });

    this.$form.trigger(completeEvent);

    if (completeEvent.isDefaultPrevented()) {
        return;
    }

    if (!this.suppress_feedback()) {
        if (this.attempts >= this.max_attempts) {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': formWidgetL10n.isBlocked,
                    '[[ errorThrown ]]':  commonL10n.tryAgainLater
                })
            } ]);
        }

        Charcoal.Admin.feedback().dispatch();
        this.enable_form();
    }

    this.submitted_via = null;

    this.suppress_feedback(false);

    this.form_working = this.is_new_object = false;
};

/**
 * @param  string url     The URL.
 * @param  string [label] The action label.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.add_action_for_next_url = function (url, label) {
    Charcoal.Admin.feedback().add_action({
        label:    (label || commonL10n.continue),
        callback: function () {
            Charcoal.Admin.redirect_to_url(url);
        }
    });
};

/**
 * @param  string [label] The action label.
 * @return self
 */
Charcoal.Admin.Widget_Form.prototype.add_actions_for_confirmation = function (label) {
    Charcoal.Admin.feedback()
        .add_action({
            label:    commonL10n.cancel,
            cssClass: 'btn-danger',
            callback: function (dialog) {
                dialog.close();
            }
        })
        .add_action({
            label:    (label || commonL10n.continue),
            callback: (function (dialog) {
                dialog.close();

                this.confirmed = true;
                this.request_submit();
            }).bind(this)
        });
};

Charcoal.Admin.Widget_Form.prototype.disable_form = function () {
    var $form = this.$form;

    if ($form.length) {
        $form.prop('disabled', true);

        var $submitters = $form.find('[type="submit"]');

        if ($submitters.length) {
            $submitters.prop('disabled', true);
        }
    }

    if (this.submitted_via) {
        this.disable_button($(this.submitted_via));
    }

    return this;
};

Charcoal.Admin.Widget_Form.prototype.enable_form = function () {
    var $form = this.$form;

    if ($form.length) {
        $form.prop('disabled', false);

        var $submitters = $form.find('[type="submit"]');

        if ($submitters.length) {
            $submitters.prop('disabled', false);
        }
    }

    if (this.submitted_via) {
        this.enable_button($(this.submitted_via));
    }

    return this;
};

/**
 * @param  {jQuery<Element>} $button - The form's submit button.
 * @return {self}
 */
Charcoal.Admin.Widget_Form.prototype.disable_button = function ($button) {
    $button.prop('disabled', true)
        .children('.fa').removeClass('d-none')
        .next('.btn-label').addClass('sr-only');

    return this;
};

/**
 * @param  {jQuery<Element>} $button - The form's submit button.
 * @return {self}
 */
Charcoal.Admin.Widget_Form.prototype.enable_button = function ($button) {
    $button.prop('disabled', false)
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
    this.destroy();

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

Charcoal.Admin.Widget_Form.prototype.destroy = function () {
    this.$form.off(this.EVENT_NAMESPACE);

    $('.js-sidebar-widget', this.form_selector).off(this.EVENT_NAMESPACE);

    window.removeEventListener('popstate', this._on_popstate_tab);

    if (this.isTab) {
        this.$form.off('shown.bs.tab', '.js-group-tabs', this._shown_tab_handler);
    }
};
