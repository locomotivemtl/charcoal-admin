/**
 * charcoal/admin/widget
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
 */

Charcoal.Admin.Widget = function (opts) {
    this._element = undefined;
    this._id      = undefined;
    this._type    = undefined;
    this._opts    = undefined;

    if (!opts) {
        return this;
    }

    if (typeof opts.id === 'string') {
        this.set_element($('#' + opts.id));
        this.set_id(opts.id);
    }

    if (typeof opts.type === 'string') {
        this.set_type(opts.type);
        this.widget_type = opts.type;
    }

    this.set_opts(opts);

    return this;
};

/**
 * Set options
 * @param {Object} opts
 * @return this (chainable)
 */
Charcoal.Admin.Widget.prototype.set_opts = function (opts) {
    this._opts = opts;

    return this;
};

/**
 * If a ident is specified, the method tries to return
 * the options pointed out.
 * If no ident is specified, the method returns
 * the whole opts object
 *
 * @param {String} ident | falcultative
 * @return {Object|Mixed|false}
 */
Charcoal.Admin.Widget.prototype.opts = function (ident) {
    if (typeof ident === 'string') {
        if (typeof this._opts[ident] === 'undefined') {
            return false;
        }
        return this._opts[ident];
    }

    return this._opts;
};

/**
 * Default init
 * @return this (chainable)
 */
Charcoal.Admin.Widget.prototype.init = function () {
    // Default init. Nothing!
    return this;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_id = function (id) {
    this._id = id;
};

Charcoal.Admin.Widget.prototype.id = function () {
    return this._id;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_type = function (type) {
    //
    this._type = type;

    // Should we update anything? Change the container ID or replace it?
    // Maybe reinit the plugin?
};

Charcoal.Admin.Widget.prototype.type = function () {
    return this._type;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.set_element = function (elem) {
    this._element = elem;

    return this;
};

/**
 *
 */
Charcoal.Admin.Widget.prototype.element = function () {
    return this._element;
};

/**
 * Default widget options
 * Can be overwritten by widget
 * @return {Object}
 */
Charcoal.Admin.Widget.prototype.widget_options = function () {
    return this.opts();
};

/**
 * Default widget type
 * Can be overwritten by widget
 * @return {String}
 */
Charcoal.Admin.Widget.prototype.widget_type = function () {
    return this.type();
};

/**
 * Called upon save by the component manager
 *
 * @return {boolean} Default action is set to true.
 */
Charcoal.Admin.Widget.prototype.save = function () {
    return true;
};

/**
 * Animate the widget out on reload
 * Use callback to define what to do after the animation.
 *
 * @param  {Function} callback What to do after the anim_out?
 * @return {thisArg}           Chainable
 */
Charcoal.Admin.Widget.prototype.anim_out = function (callback) {
    if (typeof callback !== 'function') {
        callback = function () {
        };
    }
    this.element().fadeOut(400, callback);
    return this;
};

Charcoal.Admin.Widget.prototype.reload = function (callback) {
    var that = this;

    var url  = Charcoal.Admin.admin_url() + 'widget/load';
    var data = {
        widget_type: that.type(),
        widget_options: that.widget_options()
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    $.post(url, data, function (response) {
        if (typeof response.widget_id === 'string') {
            that.set_id(response.widget_id);
            that.anim_out(function () {
                that.element().replaceWith(response.widget_html);
                that.set_element($('#' + that.id()));

                // Pure dompe.
                that.element().hide().fadeIn();
                that.init();
            });
        }
        // Callback
        if (typeof callback === 'function') {
            callback.call(that, response);
        }
    }, 'json');

};

/**
 * Load the widget into a dialog
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
        showFooter: showFooter,
        onshown: function () {
            Charcoal.Admin.manager().render();
        }
    };

    var dialogOptions = $.extend({}, defaultOptions, userOptions);

    dialogOptions.message = function (dialog) {
        var xhr,
            url      = Charcoal.Admin.admin_url() + 'widget/load',
            data     = dialog_opts,
            $message = $('<div>Loading...</div>');

        if (!showHeader) {
            dialog.getModalHeader().addClass('hidden');
        }

        if (!showFooter) {
            dialog.getModalFooter().addClass('hidden');
        }

        dialog.getModalBody().on(
            'click.charcoal.bs.dialog',
            '[data-dismiss="dialog"]',
            { dialog: dialog },
            function (event) {
                event.data.dialog.close();
            }
        );

        xhr = $.ajax({
            method: 'POST',
            url: url,
            data: data,
            dataType: 'json'
        });

        xhr.then(function (response, textStatus, jqXHR) {
            if (!response || !response.success) {
                if (response.feedbacks) {
                    return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
                } else {
                    return $.Deferred().reject(jqXHR, textStatus, 'An unknown error occurred.');
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
                dialog.setMessage('<div class="alert alert-danger" role="alert">An unknown error occurred.</div>');

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
                            errorHtml += '<div class="alert alert-' + error.level + '" role="alert">' +
                                error.message +
                                '</div>';
                        }
                    });
                } else if ($.type(errorThrown) === 'string') {
                    errorHtml = '<div class="alert alert-danger" role="alert">' + errorThrown + '</div>';
                }

                if (errorHtml) {
                    dialog.setMessage(errorHtml);
                }

                $('[data-toggle="tooltip"]', dialog.getModalBody()).tooltip();
            });

        return $message;
    };

    BootstrapDialog.show(dialogOptions);
};

Charcoal.Admin.Widget.prototype.confirm = function (dialog_opts, confirmed_callback, cancel_callback) {
    var defaults = {
        title: 'Voulez-vous vraiment effectuer cette action?',
        confirm_label: 'Oui',
        cancel_label: 'Non'
    };

    var opts = $.extend(defaults, dialog_opts);

    BootstrapDialog.show({
        title: opts.title,
        buttons: [{
            label: opts.cancel_label,
            action: function (dialog) {
                if (typeof cancel_callback === 'function') {
                    cancel_callback();
                }
                dialog.close();
            }
        }, {
            label: opts.confirm_label,
            action: function (dialog) {
                if (typeof confirmed_callback === 'function') {
                    confirmed_callback();
                }
                dialog.close();
            }
        }]
    });
};
