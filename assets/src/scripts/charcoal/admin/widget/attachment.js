/* globals commonL10n,attachmentWidgetL10n */

/**
 * Keep track of XHR by group
 * @type {{}}
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
    Charcoal.Admin.Widget.call(this, opts);

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
    $(document).on('switch_language.charcoal', function () {
        var opts = that.opts();
        // Set widget lang to current Charcoal Admin Lang
        opts.widget_options.lang = Charcoal.Admin.lang();
        that.set_opts(opts);
        that.reload();
    });

    this.dirty = false;
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

        $container.sortable({
            handle:      '[draggable="true"]',
            placeholder: 'card c-attachments_row -placeholder',
            start:       function (event, ui) {
                var $heading     = ui.item.children('.card-header'),
                    $collapsible = $heading.find('[data-toggle="collapse"]');

                if (!$collapsible.hasClass('collapsed')) {
                    ui.item.children('.collapse').collapse('hide');
                }
            }
        }).disableSelection();
    }

    this.listeners();
    return this;
};

/**
 * Check if the widget has something a dirty state that needs to be saved.
 * @return Boolean     Widget dirty of not.
 */
Charcoal.Admin.Widget_Attachment.prototype.is_dirty = function () {
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Attachment_Widget Chainable.
 */
Charcoal.Admin.Widget_Attachment.prototype.set_dirty_state = function (bool) {
    this.dirty = bool;
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
        .off('click')
        .on('click.charcoal.attachments', '.js-attachments-collapse', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $attachments.find('.collapse.show').collapse('hide');
        })
        .on('click.charcoal.attachments', '.js-attachments-expand', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.find('.card-header.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $attachments.find('.collapse:not(.show)').collapse('show');
        })
        .on('click.charcoal.attachments', '.js-add-attachment', function (e) {
            e.preventDefault();

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
        .on('click.charcoal.attachments', '.js-attachment-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
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

Charcoal.Admin.Widget_Attachment.prototype.create_attachment = function (type, id, parent, customOpts, callback) {
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    if (!customOpts) {
        customOpts = {};
    }

    // Scope
    var that = this;

    if (!parent) {
        var opts = that.opts();
        parent   = {
            obj_type: opts.data.obj_type,
            obj_id:   opts.data.obj_id,
            group:    opts.data.group
        };
    }

    // Skip quick form
    if (customOpts.skipForm) {
        this.xhr = $.ajax({
            type: 'POST',
            url: 'object/save',
            data: {
                obj_type:  type,
                obj_id:    id,
                pivot:     parent
            }
        });

        this.xhr.done(function (response) {
            if (response.feedbacks) {
                Charcoal.Admin.feedback(response.feedbacks).dispatch();
            }
            callback(response);
        });

        Charcoal.Admin.manager().render();
        return;
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

    var dialog = this.dialog(dialogOpts, function (response) {
        if (response.success) {
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
                    callback(response);
                    dialog.close();
                }
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * Add an attachment to an existing container.
 *
 * @param {object} attachment - The attachment to add to the container.
 * @param {object} container  - The container attachment.
 */
Charcoal.Admin.Widget_Attachment.prototype.add_object_to_container = function (attachment, container, grouping) {
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
            group: grouping || container.group || ''
        };

    $.post('add-join', data, function () {
        that.reload();
    }, 'json');
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.add = function (obj) {
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);

    var template = this.element().find('.js-attachment-template').clone();
    template.find('.js-attachment').data('id', obj.id).data('type', obj.type);
    this.element().find('.c-attachments_container > .js-grid-container').append(template);

    return this;
};

/**
 * Determines if the component is a candidate for saving.
 *
 * @param  {Component} [scope] - The parent component that calls for save.
 * @return {boolean}
 */
Charcoal.Admin.Widget_Attachment.prototype.will_save = function (scope) {
    return (scope && $.contains(scope.element()[0], this.element()[0]));
};

/**
 * Prepares the component to be saved.
 *
 * This method triggers the update of relationships between
 * the primary model and its attachment.
 *
 * @return {boolean}
 */
Charcoal.Admin.Widget_Attachment.prototype.save = function () {
    if (this.is_dirty()) {
        return false;
    }

    this.join();

    return true;
};

Charcoal.Admin.Widget_Attachment.prototype.join = function (cb) {
    if (!$('#' + this.element().attr('id')).length) {
        return;
    }
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

    if (typeof globalXHR[opts.data.group] !== 'undefined') {
        globalXHR[opts.data.group].abort();
    }

    globalXHR[opts.data.group] = $.post('join', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
        delete globalXHR[opts.data.group];
    }, 'json');
};

/**
 * [remove_join description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.remove_join = function (id, cb) {
    if (!id) {
        // How could this possibly be!
        return false;
    }

    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type:      opts.data.obj_type,
        obj_id:        opts.data.obj_id,
        attachment_id: id,
        group:         opts.data.group
    };

    $.post('remove-join', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * Widget options as output by the widget itself.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.widget_options = function () {
    return this.opts('widget_options');
};
