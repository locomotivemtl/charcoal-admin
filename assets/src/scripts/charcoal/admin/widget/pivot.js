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
    var that = this;

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.pivots', '.js-add-pivot', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var title = $(this).data('title') || 'Edit';
            that.create_pivot_dialog(type, title, 0, function (response) {
                if (response.success) {
                    response.obj.id = response.obj_id;

                    that.add(response.obj);
                    that.create_pivot(function () {
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
                case 'delete':
                    if (!_this.data('id')) {
                        break;
                    }

                    that.confirm(
                        {
                            title: 'Are you certain that you want to remove this item?'
                        },
                        function () {
                            that.remove_pivot(_this.data('id'), function () {
                                that.reload();
                            });
                        }
                    );
                break;
            }
        });
};

Charcoal.Admin.Widget_Pivot.prototype.create_pivot_dialog = function (type, title, id, cb)
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
    var $template = this.element().find('.js-pivot-template').clone();
    $template.find('.js-pivot').data('id', obj.id).data('type', obj.type);
    this.element().find('.js-pivot-sortable').find('.js-grid-container').append($template);
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

    // Create create_pivot from current list.
    this.create_pivot();
};

Charcoal.Admin.Widget_Pivot.prototype.create_pivot = function (cb)
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type: opts.data.obj_type,
        obj_id: opts.data.obj_id,
        target_object_type: opts.data.target_object_type,
        pivots: []
    };

    this.element().find('.js-pivot-container').find('.js-pivot').each(function (i)
    {
        var $this = $(this);
        var id = $this.data('id');

        data.pivots.push({
            target_object_id: id,
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
 * [remove_pivot description]
 * @param  {Function} cb [description]
 * @return {[type]}      [description]
 */
Charcoal.Admin.Widget_Pivot.prototype.remove_pivot = function (id, cb)
{
    if (!id) {
        return false;
    }

    // Scope
    var that = this;
    var data = {
        pivot_id: id
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
