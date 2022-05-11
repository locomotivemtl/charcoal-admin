/* globals commonL10n,relationWidgetL10n */
/**
 * Relation widget
 * You can associate a specific object to another
 * using this widget.
 *
 * @see widget.js (Charcoal.Admin.Widget)
 */
Charcoal.Admin.Widget_Relation = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this.dirty = false;
    return this;
};

Charcoal.Admin.Widget_Relation.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Relation.prototype.constructor = Charcoal.Admin.Widget_Relation;
Charcoal.Admin.Widget_Relation.prototype.parent = Charcoal.Admin.Widget.prototype;

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
Charcoal.Admin.Widget_Relation.prototype.init = function () {
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-relation-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel c-attachment_placeholder',
        start:       function (event, ui) {
            var $heading     = ui.item.children('.panel-heading'),
                $collapsible = $heading.find('[data-toggle="collapse"]');

            if (!$collapsible.hasClass('collapsed')) {
                ui.item.children('.panel-collapse').collapse('hide');
            }
        }
    }).disableSelection();

    this.listeners();
    return this;
};

/**
 * Check if the widget has something a dirty state that needs to be saved.
 * @return Boolean     Widget dirty of not.
 */
Charcoal.Admin.Widget_Relation.prototype.is_dirty = function () {
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Widget_Relation Chainable.
 */
Charcoal.Admin.Widget_Relation.prototype.set_dirty_state = function (bool) {
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Relation.prototype.listeners = function () {
    // Scope
    var that = this;

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.relation', '.js-add-relation', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var id = $(this).data('id');
            if (id) {
                that.add({
                    id: id,
                    type: type
                });
                that.create_relation(function () {
                    that.reload();
                });
            } else {
                var title = $(this).data('title') || relationWidgetL10n.editObject;
                that.create_relation_dialog({
                    title: title,
                    widget_options: {
                        form_data: {
                            target_object_type: type,
                            target_object_id: null
                        }
                    }
                }, function (response) {
                    if (response.success) {
                        response.obj.id = response.obj_id;
                        that.add(response.obj);
                        that.create_relation(function () {
                            that.reload();
                        });
                    }
                });
            }
        })
        .on('click.charcoal.relation', '.js-relation-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit':
                    var type = _this.data('type');
                    var id = _this.data('id');
                    if (!type || !id) {
                        break;
                    }
                    var title = _this.data('title') || relationWidgetL10n.editObject;
                    that.create_relation_dialog({
                        title: title,
                        widget_options: {
                            form_data: {
                                target_object_type: type,
                                target_object_id: null
                            }
                        }
                    }, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'unlink':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title:      relationWidgetL10n.confirmRemoval,
                            message:    commonL10n.confirmAction,
                            btnOKLabel: commonL10n.removeObject,
                            callback:   function (result) {
                                if (result) {
                                    that.remove_relation(_this.data('id'), function () {
                                        that.reload();
                                    });
                                }
                            }
                        }
                    );
                    break;
            }
        });
};

/**
 * Dialog that will be used to create a relation between two existing objects.
 *
 * @param  {Object} widgetOptions A set of options for the dialog creation.
 * @return {void}
 */
Charcoal.Admin.Widget_Relation.prototype.create_relation_dialog = function (widgetOptions, callback) {
    widgetOptions = widgetOptions || {};

    var sourceOptions = this.opts().data;
    var defaultOptions = {
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quick-form',
        widget_options: {
            obj_type:           'charcoal/relation/pivot',
            obj_id:             0,
            form_data: {
                group: sourceOptions.group,
                source_object_type: sourceOptions.obj_type,
                source_object_id: sourceOptions.obj_id,
                target_object_type: '',
                target_object_id:   0
            }
        }
    };

    var immutableOptions = {};
    var dialogOptions = $.extend(true, {}, defaultOptions, widgetOptions, immutableOptions);

    var dialog = this.dialog(dialogOptions, function (response) {
        if (response.success) {
            // Call the quickForm widget js.
            // Really not a good place to do that.
            if (!response.widget_id) {
                return false;
            }

            Charcoal.Admin.manager().add_widget({
                id:   response.widget_id,
                type: 'charcoal/admin/widget/quick-form',
                data: {
                    obj_type: dialogOptions.widget_options.type
                },
                obj_id: dialogOptions.widget_options.id,
                save_callback: function (response) {
                    callback(response);

                    if ((this instanceof Charcoal.Admin.Component) && this.id()) {
                        Charcoal.Admin.manager().destroy_component('widgets', this.id());
                    }

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
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Relation.prototype.add = function (obj) {
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);
    var $template = this.element().find('.js-relation-template').clone();
    $template.find('.js-relation').attr({
        'data-id': obj.target_object_id,
        'data-type': obj.target_object_type
    });
    this.element().find('.js-relation-sortable').find('.js-grid-container').append($template);

    return this;
};

/**
 * Determines if the component is a candidate for saving.
 *
 * @param  {Component} [scope] - The parent component that calls for save.
 * @return {boolean}
 */
Charcoal.Admin.Widget_Relation.prototype.will_save = function (scope) {
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
Charcoal.Admin.Widget_Relation.prototype.save = function () {
    if (this.is_dirty()) {
        return false;
    }

    this.create_relation();

    return true;
};

Charcoal.Admin.Widget_Relation.prototype.create_relation = function (cb) {
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type: opts.data.obj_type,
        obj_id: opts.data.obj_id,
        group: opts.data.group,
        pivots: []
    };

    this.element().find('.js-relation-container').find('.js-relation').each(function (i) {
        var $this = $(this);
        var id    = $this.attr('data-id');
        var type  = $this.attr('data-type');

        data.pivots.push({
            target_object_id:   id,
            target_object_type: type,
            position: i
        });
    });

    $.post('relation/link', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * [remove_relation description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Relation.prototype.remove_relation = function (id, cb) {
    if (!id) {
        return false;
    }

    // Scope
    var that = this;
    var data = {
        pivot_id: id
    };

    $.post('relation/unlink', data, function () {
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
Charcoal.Admin.Widget_Relation.prototype.widget_options = function () {
    return this.opts('widget_options');
};
