/* globals widgetL10n */

/**
 * Base Widget (charcoal/admin/widget)
 *
 * This should be the base for all widgets
 * It is still possible to add a widget without passing
 * throught this class, but not suggested
 *
 * @see Component_Manager.render() for automatic call to widget constructor
 *
 * Interface:
 * ## Setters
 * - `set_opts`
 * - `set_id`
 * - `set_element`
 * - `set_type`
 *
 * ## Getters
 * - `opts( ident )`
 * - `id()`
 * - `element()`
 * - `type()`
 *
 * ## Others
 * - `init()`
 * - `reload( callback )`
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Widget}
 */
Charcoal.Admin.Widget = function (opts) {
    Charcoal.Admin.Component.call(this, opts);

    /* jshint ignore:start */
    this._widget_id;
    this._widget_type;
    /* jshint ignore:end */
    this._suppress_feedback = false;

    if (opts.widget_id) {
        this._widget_id = opts.widget_id;
    }

    if (opts.widget_type) {
        this._widget_type = opts.widget_type;
    }

    if ('suppress_feedback' in opts) {
        this._suppress_feedback = opts.suppress_feedback;
    }

    return this;
};

Charcoal.Admin.Widget.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Widget.prototype.constructor = Charcoal.Admin.Widget;
Charcoal.Admin.Widget.prototype.parent = Charcoal.Admin.Component.prototype;

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Widget.prototype.widget_id = function () {
    return this._widget_id || this.id();
};

/**
 * @return {?String} The component type or subtype.
 */
Charcoal.Admin.Widget.prototype.widget_type = function () {
    return this._widget_type || this.type();
};

/**
 * @return {Object} The component instance options.
 */
Charcoal.Admin.Widget.prototype.widget_options = function () {
    return this.opts();
};

Charcoal.Admin.Widget.prototype.suppress_feedback = function (flag) {
    if (arguments.length) {
        if (typeof flag === 'boolean') {
            this._suppress_feedback = flag;
        } else {
            throw new TypeError('Must be a boolean, received ' + (typeof flag));
        }
    }

    return this._suppress_feedback || false;
};

/**
 * Called upon save by the component manager
 *
 * @return {Boolean} Default action is set to true.
 */
Charcoal.Admin.Widget.prototype.save = function () {
    return true;
};

/**
 * Animate the widget out on reload
 * Use callback to define what to do after the animation.
 *
 * @param  {Function} [callback] - What to do after the anim_out?
 * @return {this}
 */
Charcoal.Admin.Widget.prototype.anim_out = function (callback) {
    if (typeof callback !== 'function') {
        callback = null;
    }
    this.element().fadeOut(400, callback);
    return this;
};

/**
 * @param  {Function} [callback]  - What to do after the reload?
 * @param  {*}        [with_data] - Additional data to passthrough.
 * @return {this}
 */
Charcoal.Admin.Widget.prototype.reload = function (callback, with_data) {
    var that = this;

    var url  = Charcoal.Admin.admin_url() + 'widget/load' + window.location.search;
    var data = {
        widget_type:    that.widget_type(),
        widget_options: that.widget_options(),
        with_data:      with_data
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    if (this.reloadXHR) {
        this.reloadXHR.abort();
    }

    this.element().addClass('is-loading');

    this.reloadXHR = $.ajax({
        type:        'POST',
        url:         url,
        data:        JSON.stringify(data),
        dataType:    'json',
        contentType: 'application/json'
    });

    var success, failure, complete;

    success = function (response) {
        if (typeof response.widget_id !== 'string') {
            response.feedbacks.push({
                level: 'error',
                message: widgetL10n.loadingFailed
            });

            failure.call(this, response);
            return;
        }

        var wid = response.widget_id;
        that.set_id(wid);
        that.add_opts('id', wid);
        that.add_opts('widget_id', wid);

        if (with_data) {
            that.add_opts('data', response.widget_data);
        }

        that.widget_id = wid;
        that.anim_out(function () {
            that.element().replaceWith(response.widget_html);
            that.set_element($('#' + that.id()));

            // Pure dompe.
            that.element().removeClass('is-loading');
            that.element().hide().fadeIn();
            that.init();
            // Callback
            if (typeof callback === 'function') {
                callback.call(that, response);
            }
        });
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: widgetL10n.loadingFailed
            } ]);
        }
    };

    complete = function () {
        if (!that.suppress_feedback()) {
            Charcoal.Admin.feedback().dispatch();
        }
    };

    Charcoal.Admin.resolveSimpleJsonXhr(
        this.reloadXHR,
        success,
        failure,
        complete
    );

    return this;
};

/**
 * Load the widget into a dialog
 *
 * @param  {Object}   [dialog_opts] - Dialog settings.
 * @param  {Function} [callback]    - What to do after the dialog?
 * @return {BootstrapDialog}
 */
Charcoal.Admin.Widget.prototype.dialog = function (dialog_opts, callback) {
    var title       = dialog_opts.title || '',
        type        = dialog_opts.type || BootstrapDialog.TYPE_DEFAULT,
        size        = dialog_opts.size || BootstrapDialog.SIZE_NORMAL,
        cssClass    = dialog_opts.cssClass || '',
        showHeader  = dialog_opts.showHeader || true,
        showFooter  = dialog_opts.showFooter || true,
        userOptions = dialog_opts.dialog_options || {};

    delete dialog_opts.title;
    delete dialog_opts.type;
    delete dialog_opts.size;
    delete dialog_opts.cssClass;
    delete dialog_opts.dialog_options;

    var defaultOptions = {
        title: title,
        type: type,
        size: size,
        cssClass: cssClass,
        nl2br: false,
        showHeader: showHeader,
        showFooter: showFooter
    };

    var dialogOptions = $.extend({}, defaultOptions, userOptions);
    var alertTemplate = '<div class="alert alert-{type}" role="alert">{text}</div>';

    dialogOptions.onshown = function (dialog) {
        var xhr,
            url  = Charcoal.Admin.admin_url() + 'widget/load',
            data = dialog_opts;

        xhr = $.ajax({
            method:   'POST',
            url:      url,
            data:     data,
            dataType: 'json'
        });

        xhr
            .then(function (response, textStatus, jqXHR) {
                if (!response || !response.success) {
                    if (response.feedbacks) {
                        return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
                    } else {
                        return $.Deferred().reject(jqXHR, textStatus, widgetL10n.loadingFailed);
                    }
                }

                return $.Deferred().resolve(response, textStatus, jqXHR);
            })
            .done(function (response/*, textStatus, jqXHR*/) {
                dialog.setMessage(response.widget_html);

                if (typeof callback === 'function') {
                    callback(response);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                dialog.setType(BootstrapDialog.TYPE_DANGER);
                dialog.setMessage(widgetL10n.loadingFailed);

                var errorHtml = '';

                if ($.type(errorThrown) === 'string') {
                    if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
                        errorThrown = jqXHR.responseJSON.feedbacks;
                    }
                }

                if ($.isArray(errorThrown)) {
                    $.each(errorThrown, function (i, error) {
                        if (error.message) {
                            if (error.level === 'error') {
                                error.level = 'danger';
                            }
                            errorHtml += alertTemplate.replaceMap({
                                '{type}': error.level,
                                '{text}': error.message
                            });
                        }
                    });
                } else if ($.type(errorThrown) === 'string') {
                    errorHtml = alertTemplate.replaceMap({
                        '{type}': 'danger',
                        '{text}': errorThrown
                    });
                }

                if (errorHtml) {
                    dialog.setMessage(errorHtml);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            });

        Charcoal.Admin.manager().render();
    };

    dialogOptions.message = function (dialog) {
        var $message = $(
            alertTemplate.replaceMap({
                '{type}': 'warning',
                '{text}': widgetL10n.loading
            })
        );

        if (!showHeader) {
            dialog.getModalHeader().addClass('d-none');
        }

        if (!showFooter) {
            dialog.getModalFooter().addClass('d-none');
        }

        dialog.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dialog },
            function (event) {
                event.data.dialog.close();
            }
        );

        return $message;
    };

    return new BootstrapDialog.show(dialogOptions);
};

/**
 * Load the widget into a dialog
 *
 * @param  {Object}   [dialog_opts]        - Dialog settings.
 * @param  {Function} [confirmed_callback] - What to do after the dialog is confirmed?
 * @param  {Function} [cancel_callback]    - What to do after the dialog is canceled?
 * @return {void}
 */
Charcoal.Admin.Widget.prototype.confirm = function (dialog_opts, confirmed_callback, cancel_callback) {
    var defaults = {
        type:     BootstrapDialog.TYPE_DANGER,
        callback: function (result) {
            if (result) {
                if (typeof confirmed_callback === 'function') {
                    confirmed_callback();
                }
            } else {
                if (typeof cancel_callback === 'function') {
                    cancel_callback();
                }
            }
        }
    };

    var opts = $.extend(defaults, dialog_opts);

    BootstrapDialog.confirm(opts);
};
