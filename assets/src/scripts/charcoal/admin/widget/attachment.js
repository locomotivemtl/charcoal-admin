/**
* Attachment widget
* You can associate a perticular object to another
* using this widget.
*
* @see widget.js (Charcoal.Admin.Widget
*/
Charcoal.Admin.Widget_Attachment = function ()
{
    this.glyphs = {
        video:   'glyphicon-facetime-video',
        image:   'glyphicon-picture',
        file:    'glyphicon-file',
        text:    'glyphicon-font',
        gallery: 'glyphicon-duplicate'
    };

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
Charcoal.Admin.Widget_Attachment.prototype.init = function ()
{
    console.log(this.element());
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var url = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js';
        Charcoal.Admin.loadScript(url, this.init.bind(this));

        return this;
    }
    // var config = this.opts();
    var $container = this.element().find('.js-attachment-sortable .js-grid-container');

    this.element().on('hidden.bs.collapse', '[data-toggle="collapse"]', function () {
        $container.sortable('refreshPositions');
    });

    $container.sortable({
        handle:      '[draggable="true"]',
        placeholder: 'panel js-attachment-placeholder',
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
Charcoal.Admin.Widget_Attachment.prototype.is_dirty = function ()
{
    return this.dirty;
};

/**
 * Set the widget to dirty or not to prevent unnecessary save
 * action.
 * @param Boolean bool Self explanatory.
 * @return Add_Attachment_Widget Chainable.
 */
Charcoal.Admin.Widget_Attachment.prototype.set_dirty_state = function (bool)
{
    this.dirty = bool;
    return this;
};

/**
 * Bind listeners
 *
 * @return {thisArg} Chainable
 */
Charcoal.Admin.Widget_Attachment.prototype.listeners = function ()
{
    // Scope
    var that = this,
        $container = this.element().find('.js-attachment-sortable .js-grid-container');

    // Prevent multiple binds
    this.element()
        .off('click')
        .on('click.charcoal.attachments', '.js-attachments-collapse', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.children('.panel-heading.sr-only').removeClass('sr-only').addClass('sr-only-off');
            }

            $attachments.children('.panel-collapse.in').collapse('hide');
        })
        .on('click.charcoal.attachments', '.js-attachments-expand', function () {
            var $attachments = $container.children('.js-attachment');

            if ($container.hasClass('js-attachment-preview-only')) {
                $attachments.children('.panel-heading.sr-only-off').removeClass('sr-only-off').addClass('sr-only');
            }

            $attachments.children('.panel-collapse:not(.in)').collapse('show');
        })
        .on('click.charcoal.attachments', '.js-add-attachment', function (e) {
            e.preventDefault();
            var type = $(this).data('type');
            if (!type) {
                return false;
            }
            var title = $(this).data('title') || 'Edit';
            that.create_attachment(type, title, 0, function (response) {
                if (response.success) {
                    response.obj.id = response.obj_id;
                    that.add(response.obj);
                    that.join(function () {
                        that.reload();
                    });
                }
            });
        })
        .on('click.charcoal.attachments', '.js-attachment-actions a', function (e) {
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
                    that.create_attachment(type, title, id, function (response) {
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
                        title: 'Voulez-vous vraiment supprimer cet item?'
                    }, function () {
                        that.remove_join(_this.data('id'), function () {
                            that.reload();
                        });
                    });
                    break;

                case 'add-object':
                    var gallery_type = _this.data('type');
                    var gallery_id = _this.data('id');
                    var gallery_title = _this.data('title');
                    var gallery_attachment = _this.data('attachment');
                    that.create_attachment(gallery_attachment, gallery_title, 0, function (response) {
                        if (response.success) {
                            that.add_object_to_container({
                                id: response.obj_id,
                                type: response.obj.type
                            },{
                                id: gallery_id,
                                type: gallery_type
                            });
                        }
                    });

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
Charcoal.Admin.Widget_Attachment.prototype.select_attachment = function (elem)
{
    if (!elem.data('id') || !elem.data('type')) {
        // Invalid
        return this;
    }
};

Charcoal.Admin.Widget_Attachment.prototype.create_attachment = function (type, title, id, cb)
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
            obj_type:   type,
            obj_id:     id
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
 * Add an image to an existing gallery.
 * @param {[type]} image   [description]
 * @param {[type]} gallery [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.add_object_to_container = function (image, gallery)
{
    // Scope.
    var that = this;

    var type = gallery.type;
    var id = gallery.id;

    var data = {
        obj_type: type,
        obj_id: id,
        attachments: [
            {
                attachment_id:image.id,
                attachment_type:image.type,
                position:0
            }
        ],
        group: 'inception-gallery'
    };

    $.post('add-join', data, function () {
        that.reload();
    }, 'json');
};

/**
 * This should use mustache templating. That'd be great.
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.add = function (obj)
{
    if (!obj) {
        return false;
    }

    // There is something to save.
    this.set_dirty_state(true);

    var template = this.element().find('.js-attachment-template').clone();
    template.find('.js-attachment').data('id', obj.id).data('type', obj.type);
    this.element().find('.js-attachment-sortable').find('.js-grid-container').append(template);

    return this;

};

/**
 * [save description]
 * @return {[type]} [description]
 */
Charcoal.Admin.Widget_Attachment.prototype.save = function ()
{
    if (this.is_dirty()) {
        return false;
    }

    // Create join from current list.
    this.join();
};

Charcoal.Admin.Widget_Attachment.prototype.join = function (cb)
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type:    opts.data.obj_type,
        obj_id:      opts.data.obj_id,
        attachments: [],
        group:       opts.data.group
    };

    this.element().find('.js-attachment-container').find('.js-attachment').each(function (i)
    {
        var $this = $(this);
        var id    = $this.data('id');
        var type  = $this.data('type');

        data.attachments.push({
            attachment_id:   id,
            attachment_type: type, // Further use.
            position:        i
        });
    });

    $.post('join', data, function () {
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
Charcoal.Admin.Widget_Attachment.prototype.remove_join = function (id, cb)
{
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
Charcoal.Admin.Widget_Attachment.prototype.widget_options = function ()
{
    return this.opts('widget_options');
};
