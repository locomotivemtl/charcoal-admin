/**
* Pivot widget
* You can associate a specific object to another
* using this widget.
*
* @see widget.js (Charcoal.Admin.Widget)
*/
Charcoal.Admin.Widget_Pivot = function ()
{
    this.dirty = false;
    return this;
};

Charcoal.Admin.Widget_Pivot.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Pivot.prototype.constructor = Charcoal.Admin.Widget_Pivot;
Charcoal.Admin.Widget_Pivot.prototype.parent = Charcoal.Admin.Widget.prototype;

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
Charcoal.Admin.Widget_Pivot.prototype.init = function ()
{
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-pivot-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel js-pivot-placeholder',
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
Charcoal.Admin.Widget_Pivot.prototype.is_dirty = function ()
{
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Pivot_Widget Chainable.
 */
Charcoal.Admin.Widget_Pivot.prototype.set_dirty_state = function (bool)
{
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Pivot.prototype.listeners = function ()
{
    // Scope
    var that = this,
        $container = this.element().find('.js-pivot-sortable .js-grid-container');

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.pivots', '.js-pivots-collapse', function () {
            var $pivots = $container.children('.js-pivot');

            if ($container.hasClass('js-pivot-preview-only')) {
                $pivots.children('.panel-heading.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $pivots.children('.panel-collapse.in').collapse('hide');
        })
        .on('click.charcoal.pivots', '.js-pivots-expand', function () {
            var $pivots = $container.children('.js-pivot');

            if ($container.hasClass('js-pivot-preview-only')) {
                $pivots.children('.panel-heading.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $pivots.children('.panel-collapse:not(.in)').collapse('show');
        })
        .on('click.charcoal.pivots', '.js-add-pivot', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var title = $(this).data('title') || 'Edit';
            that.create_pivot(type, title, 0, function (response) {
                if (response.success) {
                    response.obj.id = response.obj_id;

                    that.add(response.obj);
                    that.join(function () {
                        that.reload();
                    });
                }
            });
        })
        .on('click.charcoal.pivots', '.js-pivot-actions a', function (e) {
            var _this = $(this);
            if (!_this.data('action')) {
                return ;
            }

            e.preventDefault();
            var action = _this.data('action');
            switch (action) {
                case 'edit' :
                    var type = _this.data('type');
                    var id = _this.data('id');
                    if (!type || !id) {
                        break;
                    }
                    var title = _this.data('title') || 'Édition';
                    that.create_pivot(type, title, id, function (response) {
                        if (response.success) {
                            that.reload();
                        }
                    });

                    break;

                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title: 'Voulez-vous vraiment supprimer cet item?'
                        },
                        function () {
                            that.remove_join(_this.data('id'), function () {
                                that.reload();
                            });
                        }
                    );
                    break;

                case 'add-object':
                    var container_type   = _this.data('type'),
                        container_group  = _this.data('group'),
                        container_id     = _this.data('id'),
                        pivot_title = _this.data('title'),
                        pivot_type  = _this.data('pivot');

                    that.create_pivot(pivot_type, pivot_title, 0, function (response) {
                        if (response.success) {
                            that.add_object_to_container(
                                {
                                    id:   response.obj_id,
                                    type: response.obj.type
                                },
                                {
                                    id:    container_id,
                                    type:  container_type,
                                    group: container_group
                                }
                            );
                        }
                    });

                    break;
            }
        });
};

/**
 * Select an pivot from the list
 *
 * @param  {jQuery Object} elem Clicked element
 * @return {thisArg}            (Chainable)
 */
Charcoal.Admin.Widget_Pivot.prototype.select_pivot = function (elem)
{
    if (!elem.data('id') || !elem.data('type')) {
        // Invalid
        return this;
    }
};

Charcoal.Admin.Widget_Pivot.prototype.create_pivot = function (type, title, id, cb)
{
    // Id = EDIT mod.
    if (!id) {
        id = 0;
    }

    var data = {
        title:          title,
        size:           BootstrapDialog.SIZE_WIDE,
        cssClass:       '-quick-form',
        widget_type:    'charcoal/admin/widget/quickForm',
        widget_options: {
            obj_type: type,
            obj_id:   id
        }
    };
    this.dialog(data, function (response) {
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
                    obj_type: type
                },
                obj_id: id,
                save_callback: function (response) {
                    cb(response);
                    BootstrapDialog.closeAll();
                }
            });

            // Re render.
            // This is not good.
            Charcoal.Admin.manager().render();
        }
    });
};

/**
 * Add an pivot to an existing container.
 *
 * @param {object} pivot - The pivot to add to the container.
 * @param {object} container  - The container pivot.
 */
Charcoal.Admin.Widget_Pivot.prototype.add_object_to_container = function (pivot, container, grouping)
{
    var that = this,
        data = {
            obj_type:    container.type,
            obj_id:      container.id,
            pivots: [
                {
                    pivot_id:   pivot.id,
                    pivot_type: pivot.type,
                    position: 0
                }
            ],
            group: grouping || container.group || ''
        };

    $.post('pivot/add', data, function () {
        that.reload();
    }, 'json');
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.add = function (obj)
{
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);
    // console.log(obj);
    var $template = this.element().find('.js-pivot-template').clone();
    $template.find('.js-pivot').data('id', obj.id).data('type', obj.type);
    // console.log($template.data());
    // console.log($template.prop('outerHTML'));
    // console.log(this.element());
    this.element().find('.js-pivot-sortable').find('.js-grid-container').append($template);
    // return false;
    return this;

};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.save = function ()
{
    if (this.is_dirty()) {
        return false;
    }

    // Create join from current list.
    this.join();
};

Charcoal.Admin.Widget_Pivot.prototype.join = function (cb)
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        source_obj_type: opts.data.obj_type,
        source_obj_id:   opts.data.obj_id,
        target_obj_type: opts.data.group,
        pivots:          []
    };

    this.element().find('.js-pivot-container').find('.js-pivot').each(function (i)
    {
        var $this = $(this);
        var id    = $this.data('id');

        data.pivots.push({
            target_obj_id: id,
            position: i
        });
    });

    $.post('pivot/create', data, function () {
        if (typeof cb === 'function') {
            cb();
        }
        that.set_dirty_state(false);
    }, 'json');
};

/**
 * [remove_join description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.remove_join = function (id, cb)
{
    if (!id) {
        // How could this possibly be!
        return false;
    }

    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        source_obj_type:      opts.data.obj_type,
        source_obj_id:        opts.data.obj_id,
        pivot_id: id,
        group:         opts.data.group
    };

    $.post('pivot/remove', data, function () {
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
Charcoal.Admin.Widget_Pivot.prototype.widget_options = function ()
{
    return this.opts('widget_options');
};
