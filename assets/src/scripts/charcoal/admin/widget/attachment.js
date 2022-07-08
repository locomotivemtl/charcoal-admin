/* globals attachmentWidgetL10n,commonL10n,formWidgetL10n,widgetL10n */

/**
 * Keep track of XHR requests by group.
 *
 * @type object<string, jqXHR>
 */
var globalXHR = {};

/**
 * Attachment widget
 * You can associate a perticular object to another
 * using this widget.
 *
 * @see widget.js (Charcoal.Admin.Widget
 */
Charcoal.Admin.Widget_Attachment = function (opts) {
    this.EVENT_NAMESPACE = '.charcoal.attachments';

    Charcoal.Admin.Widget.call(this, opts);

    this.busy  = false;
    this.dirty = false;

    this.glyphs = {
        embed:      'glyphicon-blackboard',
        video:      'glyphicon-film',
        image:      'glyphicon-picture',
        file:       'glyphicon-file',
        link:       'glyphicon-link',
        text:       'glyphicon-font',
        gallery:    'glyphicon-duplicate',
        container:  'glyphicon-list',
        accordion:  'glyphicon-list'
    };

    var that = this;

    $(document)
        .on('beforelanguageswitch' + Charcoal.Admin.Widget_Form.EVENT_NAMESPACE, function (event) {
            if (that.is_busy()) {
                event.preventDefault();
                that.enqueue_is_busy_feedback().dispatch();
                return;
            }

            that.perform_save();
        })
        .on('languageswitch' + Charcoal.Admin.Widget_Form.EVENT_NAMESPACE, function () {
            that.change_locale().try_reload();
        });

    return this;
};

Charcoal.Admin.Widget_Attachment.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Attachment.prototype.constructor = Charcoal.Admin.Widget_Attachment;
Charcoal.Admin.Widget_Attachment.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
 * Called upon creation
 * Use as constructor
 * Access available configurations with `this.opts()`
 * Encapsulate all events within the current widget
 * element: `this.element()`.
 *
 *
 * @see Component_Manager.render()
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.init = function () {
    var $container = this.element().find('.js-attachment-sortable > .js-grid-container');
    if ($container.length) {
        this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
            $container.sortable('refreshPositions');
        });

        var that = this;

        $container.sortable({
            handle:      '[draggable="true"]',
            placeholder: 'card c-attachments_row -placeholder',
            start:       function (event, ui) {
                var $heading     = ui.item.children('.card-header'),
                    $collapsible = $heading.find('[data-toggle="collapse"]');

                if (!$collapsible.hasClass('collapsed')) {
                    ui.item.children('.collapse').collapse('hide');
                }
            },
            update:      function () {
                that.set_dirty_state(true);
            }
        }).disableSelection();
    }

    this.listeners();
    return this;
};

/**
 * Checks whether the widget is busy.
 *
 * Checks if {@see Widget.is_reloading()} is TRUE or {@see this.busy} is TRUE.
 *
 * @return {boolean} TRUE if the widget is busy, otherwise FALSE.
 */
Charcoal.Admin.Widget_Attachment.prototype.is_busy = function () {
    return (this.busy || this.is_reloading());
};

/**
 * Sets whether the widget is busy.
 *
 * @param  {boolean} state
 * @return {this}
 */
Charcoal.Admin.Widget_Attachment.prototype.set_busy_state = function (state) {
    this.busy = state;
    return this;
};

/**
 * Checks whether the widget has changes that should be saved.
 *
 * @return {boolean} TRUE if the widget is dirty, otherwise FALSE.
 */
Charcoal.Admin.Widget_Attachment.prototype.is_dirty = function () {
    return this.dirty;
};

/**
 * Sets whether the widget has changes that should be saved.
 *
 * @param  {boolean} state
 * @return {this}
 */
Charcoal.Admin.Widget_Attachment.prototype.set_dirty_state = function (state) {
    this.dirty = state;
    return this;
};

/**
 * Set widget lang to the given language or Charcoal's current language.
 *
 * @param  {string} [lang]
 * @return {this}
 */
Charcoal.Admin.Widget_Attachment.prototype.change_locale = function (lang) {
    var opts = this.opts();
    opts.widget_options.lang = (lang || Charcoal.Admin.lang());
    this.set_opts(opts);

    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.listeners = function () {
    // Scope
    var that = this,
        $container = this.element().find('.c-attachments_container > .js-grid-container');

    // Prevent multiple binds
    this.element()
        .off(this.EVENT_NAMESPACE)
        .on('click' + this.EVENT_NAMESPACE, '.js-attachments-collapse', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $attachments.find('.collapse.show').collapse('hide');
        })
        .on('click' + this.EVENT_NAMESPACE, '.js-attachments-expand', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $attachments.find('.collapse:not(.show)').collapse('show');
        })
        .on('click' + this.EVENT_NAMESPACE, '.js-add-attachment', function (event) {
            event.preventDefault();

            var _this = $(this);

            var type = _this.data('type');
            if (!type) {
                return false;
            }

            var id = _this.data('id');
            if (id) {
                that.add({
                    id:   id,
                    type: type
                });
                that.join(function () {
                    that.reload();
                });
            } else {
                var attachment_struct = {
                    title:     _this.data('title') || attachmentWidgetL10n.editObject,
                    formIdent: _this.data('form-ident'),
                    skipForm:  _this.data('skip-form')
                };

                that.create_attachment(type, 0, null, attachment_struct, function (response) {
                    if (response.success) {
                        response.obj.id = response.obj_id;
                        that.add(response.obj);
                        that.join(function () {
                            that.reload();
                        });
                    }
                });
            }
        })
        .on('click' + this.EVENT_NAMESPACE, '.js-attachment-actions a', function (event) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            event.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit':
                    var type = _this.data('type'),
                        id = _this.data('id');

                    if (!type || !id) {
                        break;
                    }

                    var attachment_struct = {
                        title:     _this.data('title') || attachmentWidgetL10n.editObject,
                        formIdent: _this.data('form-ident')
                    };

                    that.create_attachment(type, id, null, attachment_struct, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm({
                        title:      attachmentWidgetL10n.confirmRemoval,
                        message:    commonL10n.confirmAction,
                        btnOKLabel: commonL10n.removeObject,
                        callback:   function (result) {
                            if (result) {
                                that.remove_join(_this.data('id'), function () {
                                    that.reload();
                                });
                            }
                        }
                    });
                    break;

                case 'add-object':
                    var attachment_title = _this.data('title'),
                        attachment_type  = _this.data('attachment'),
                        container_type   = _this.data('type'),
                        container_id     = _this.data('id'),
                        container_group  = _this.data('group'),
                        form_ident       = _this.data('form-ident'),
                        skip_form        = _this.data('skip-form'),
                        container_struct = {
                            id:       container_id,
                            type:     container_type,
                            group:    container_group
                        };
                    attachment_struct = {
                        title:     attachment_title,
                        formIdent: form_ident,
                        skipForm:  skip_form
                    };

                    that.create_attachment(
                        attachment_type,
                        0,
                        container_struct,
                        attachment_struct,
                        function (response) {
                            if (response.success) {
                                that.add_object_to_container(
                                    {
                                        id:   response.obj_id,
                                        type: response.obj.type
                                    },
                                    container_struct
                                );
                            }
                        }
                    );

                    break;
            }
        });
};

/**
 * Select an attachment from the list
 *
 * @param  {jQuery Object} elem Clicked element
 * @return {thisArg}            (Chainable)
 */
Charcoal.Admin.Widget_Attachment.prototype.select_attachment = function (elem) {
    if (!elem.data('id') || !elem.data('type')) {
        // Invalid
        return this;
    }
};

/**
 * @param  {string}        type
 * @param  {number|string} [id]
 * @param  {object}        [parent]
 * @param  {object}        [customOpts]
 * @param  {function}      [callback]
 * @return {jqXHR|BootstrapDialog}
 */
Charcoal.Admin.Widget_Attachment.prototype.create_attachment = function (type, id, parent, customOpts, callback) {
    // Skip quick form
    if (customOpts && customOpts.skipForm) {
        return this.create_quick_attachment(type, id, parent, customOpts, callback);
    }

    return this.create_dialog_attachment(type, id, parent, customOpts, callback);
};

/**
 * @param  {string}        type
 * @param  {number|string} [id]
 * @param  {object}        [parent]
 * @param  {object}        [customOpts]
 * @param  {function}      [callback]
 * @return {jqXHR}
 */
Charcoal.Admin.Widget_Attachment.prototype.create_quick_attachment = function (type, id, parent, customOpts, callback) {
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    if (!customOpts) {
        customOpts = {};
    }

    if (!parent) {
        parent = this.get_parent_container();
    }

    this.xhr = $.ajax({
        type: 'POST',
        url: 'object/save',
        data: {
            obj_type:  type,
            obj_id:    id,
            pivot:     parent
        }
    });

    var success, failure, complete;

    success = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        }

        if (typeof callback === 'function') {
            callback(response);
        }
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            var message = (id ? formWidgetL10n.updateFailed : formWidgetL10n.createFailed);
            var error   = commonL10n.errorOccurred;

            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': message,
                    '[[ errorThrown ]]':  error
                })
            } ]);
        }
    };

    complete = function () {
        Charcoal.Admin.feedback().dispatch();

        Charcoal.Admin.manager().render();
    };

    return Charcoal.Admin.resolveSimpleJsonXhr(
        this.xhr,
        success,
        failure,
        complete
    );
};

/**
 * @param  {string}        type
 * @param  {number|string} [id]
 * @param  {object}        [parent]
 * @param  {object}        [customOpts]
 * @param  {function}      [callback]
 * @return {BootstrapDialog}
 */
Charcoal.Admin.Widget_Attachment.prototype.create_dialog_attachment = function (type, id, parent, customOpts, callback) {
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    if (!customOpts) {
        customOpts = {};
    }

    if (!parent) {
        parent = this.get_parent_container();
    }

    var defaultOpts = {
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quick-form',
        with_data:      true,
        widget_options: {
            obj_type:  type,
            obj_id:    id,
            form_ident: customOpts.formIdent || null,
            form_data: {
                pivot: parent
            }
        }
    };

    var immutableOpts = {};
    var dialogOpts = $.extend({}, defaultOpts, customOpts, immutableOpts);

    return this.dialog(dialogOpts, function (response, dialog) {
        if (!response.success) {
            return false;
        }

        // Call the quickForm widget js.
        // Really not a good place to do that.
        if (!response.widget_id) {
            return false;
        }

        Charcoal.Admin.manager().add_widget({
            id:   response.widget_id,
            type: 'charcoal/admin/widget/quick-form',
            data: response.widget_data,
            obj_id: id,
            save_callback: function (response) {
                if (typeof callback === 'function') {
                    callback(response);
                }

                if ((this instanceof Charcoal.Admin.Component) && this.id()) {
                    Charcoal.Admin.manager().destroy_component('widgets', this.id());
                }

                dialog.close();
            }
        });

        // Re render.
        // This is not good.
        Charcoal.Admin.manager().render();
    });
};

/**
 * @return {object}
 */
Charcoal.Admin.Widget_Attachment.prototype.get_parent_container = function () {
    var opts = this.opts();

    return {
        obj_type: opts.data.obj_type,
        obj_id:   opts.data.obj_id,
        group:    opts.data.group
    };
};

/**
 * Add an attachment to an existing container.
 *
 * @param  {object} attachment - The attachment to add to the container.
 * @param  {object} container  - The container attachment.
 * @return {?jqXHR}
 */
Charcoal.Admin.Widget_Attachment.prototype.add_object_to_container = function (attachment, container, grouping) {
    if (this.is_busy()) {
        return null;
    }

    this.set_busy_state(true);

    var that = this,
        data = {
            obj_type:    container.type,
            obj_id:      container.id,
            attachments: [
                {
                    attachment_id:   attachment.id,
                    attachment_type: attachment.type,
                    position: 0
                }
            ],
            group: (grouping || container.group || '')
        };

    if (globalXHR[data.group] != null && globalXHR[data.group].abort) {
        globalXHR[data.group].abort();
    }

    var xhr = $.post('add-join', data);

    var success, failure, complete;

    success = function () {
        that.reload();
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': formWidgetL10n.saveFailed,
                    '[[ errorThrown ]]':  commonL10n.errorOccurred
                })
            } ]);
        }
    };

    complete = function () {
        delete globalXHR[data.group];

        that.set_busy_state(false);

        Charcoal.Admin.feedback().dispatch();
    };

    return globalXHR[data.group] = Charcoal.Admin.resolveSimpleJsonXhr(
        xhr,
        success,
        failure,
        complete
    );
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.add = function (obj) {
    if (!obj) {
        return false;
    }

    this.set_dirty_state(true);

    var template = this.element().find('.js-attachment-template').clone();
    template.find('.js-attachment').data('id', obj.id).data('type', obj.type);
    this.element().find('.c-attachments_container > .js-grid-container').append(template);

    return this;
};

/**
 * @return {Feedback}
 */
Charcoal.Admin.Widget_Attachment.prototype.enqueue_is_busy_feedback = function () {
    var opts = this.widget_options();
    var widgetName = (opts && opts.title || attachmentWidgetL10n.widgetName);

    return Charcoal.Admin.feedback([ {
        level:   'warning',
        display: 'toast',
        message: commonL10n.errorTemplate.replaceMap({
            '[[ errorMessage ]]': widgetName,
            '[[ errorThrown ]]':  widgetL10n.isBusy
        })
    } ]);
};

/**
 * Validates the component.
 *
 * @return {boolean}
 */
Charcoal.Admin.Widget_Attachment.prototype.validate = function (scope) {
    if (this.is_busy()) {
        if (scope.attempts && scope.max_attempts && scope.attempts < scope.max_attempts) {
            this.enqueue_is_busy_feedback();
        }
        return false;
    }

    return true;
};

/**
 * Determines if the component is a candidate for saving.
 *
 * @todo Disabled the function to revert to initial behaviour of always saving
 *     no matter the context. The reason for this is that this widget is often
 *     integrated as adjacent to the form instead of nested.
 *
 * @param  {Component} [scope] - The parent component that calls for save.
 * @return {boolean}
 */
// Charcoal.Admin.Widget_Attachment.prototype.will_save = function (scope) {
//     return (scope && $.contains(scope.element()[0], this.element()[0]));
// };

/**
 * Prepares the component to be saved.
 *
 * This method triggers the update of relationships between
 * the primary model and its attachment.
 *
 * @return {boolean}
 */
Charcoal.Admin.Widget_Attachment.prototype.save = function () {
    if (this.is_busy()) {
        return false;
    }

    return this.perform_save();
};

/**
 * Performs the save operation.
 *
 * @return {boolean}
 */
Charcoal.Admin.Widget_Attachment.prototype.perform_save = function () {
    if (this.is_dirty()) {
        this.join();
    }

    return true;
};

/**
 * @param  {function}  [callback]
 * @return {?jqXHR}
 */
Charcoal.Admin.Widget_Attachment.prototype.join = function (callback) {
    if (this.is_busy()) {
        return null;
    }

    if (!$('#' + this.element().attr('id')).length) {
        return null;
    }

    this.set_busy_state(true);

    // Scope
    var that = this;

    var opts = that.opts();

    var data = {
        obj_type:    opts.data.obj_type,
        obj_id:      opts.data.obj_id,
        attachments: [],
        group:       opts.data.group
    };

    this.element().find('.c-attachments_container').find('.js-attachment').each(function (i) {
        var $this = $(this);
        var id    = $this.data('id');
        var type  = $this.data('type');

        data.attachments.push({
            attachment_id:   id,
            attachment_type: type, // Further use.
            position:        i
        });
    });

    if (globalXHR[data.group] != null && globalXHR[data.group].abort) {
        globalXHR[data.group].abort();
    }

    var xhr = $.post('join', data);

    var success, failure, complete;

    success = function () {
        if (typeof callback === 'function') {
            callback();
        }
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': formWidgetL10n.saveFailed,
                    '[[ errorThrown ]]':  commonL10n.errorOccurred
                })
            } ]);
        }
    };

    complete = function () {
        delete globalXHR[data.group];

        that.set_busy_state(false);
        that.set_dirty_state(false);

        Charcoal.Admin.feedback().dispatch();
    };

    return globalXHR[data.group] = Charcoal.Admin.resolveSimpleJsonXhr(
        xhr,
        success,
        failure,
        complete
    );
};

/**
 * @param  {string|number} id
 * @param  {function}      [callback]
 * @return {?jqXHR}
 */
Charcoal.Admin.Widget_Attachment.prototype.remove_join = function (id, callback) {
    if (this.is_busy()) {
        return null;
    }

    if (!id) {
        // How could this possibly be!
        return null;
    }

    this.set_busy_state(true);

    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type:      opts.data.obj_type,
        obj_id:        opts.data.obj_id,
        attachment_id: id,
        group:         opts.data.group
    };

    if (globalXHR[data.group] != null && globalXHR[data.group].abort) {
        globalXHR[data.group].abort();
    }

    var xhr = $.post('remove-join', data);

    var success, failure, complete;

    success = function () {
        if (typeof callback === 'function') {
            callback();
        }
    };

    failure = function (response) {
        if (response.feedbacks.length) {
            Charcoal.Admin.feedback(response.feedbacks);
        } else {
            Charcoal.Admin.feedback([ {
                level:   'error',
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': attachmentWidgetL10n.removeFailed,
                    '[[ errorThrown ]]':  commonL10n.errorOccurred
                })
            } ]);
        }
    };

    complete = function () {
        delete globalXHR[data.group];

        that.set_busy_state(false);
        that.set_dirty_state(false);

        Charcoal.Admin.feedback().dispatch();
    };

    return globalXHR[data.group] = Charcoal.Admin.resolveSimpleJsonXhr(
        xhr,
        success,
        failure,
        complete
    );
};

/**
 * Widget options as output by the widget itself.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.widget_options = function () {
    var options = this.opts('widget_options');

    // Hack to persist widget ID across reloads
    if (!options.widget_id && this.widget_id()) {
        options.widget_id = this.widget_id();
    }

    return options;
};
