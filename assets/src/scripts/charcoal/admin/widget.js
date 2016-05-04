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

Charcoal.Admin.Widget = function (opts)
{
    this._element = undefined;
    this._id = undefined;
    this._type = undefined;
    this._opts = undefined;

    if (!opts) {
        return this;
    }

    if (typeof opts.id === 'string') {
        this.set_element($('#' + opts.id));
        this.set_id(opts.id);
    }

    if (typeof opts.type === 'string') {
        this.set_type(opts.type);
    }

    this.set_opts(opts);

    return this;
};

/**
* Set options
* @param {Object} opts
* @return this (chainable)
*/
Charcoal.Admin.Widget.prototype.set_opts = function (opts)
{
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
Charcoal.Admin.Widget.prototype.opts = function (ident)
{
    if (typeof ident === 'string') {
        if (typeof this._opts[ ident ] === 'undefined') {
            return false;
        }
        return this._opts[ ident ];
    }

    return this._opts;
};

/**
* Default init
* @return this (chainable)
*/
Charcoal.Admin.Widget.prototype.init = function ()
{
    // Default init. Nothing!
    return this;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_id = function (id)
{
    this._id = id;
};

Charcoal.Admin.Widget.prototype.id = function ()
{
    return this._id;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_type = function (type)
{
    //
    this._type = type;

    // Should we update anything? Change the container ID or replace it?
    // Maybe reinit the plugin?
};

Charcoal.Admin.Widget.prototype.type = function ()
{
    return this._type;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.set_element = function (elem)
{
    this._element = elem;

    return this;
};

/**
*
*/
Charcoal.Admin.Widget.prototype.element = function ()
{
    return this._element;
};

/**
* Default widget options
* Can be overwritten by widget
* @return {Object}
*/
Charcoal.Admin.Widget.prototype.widget_options = function ()
{
    return this.opts();
};

/**
* Default widget type
* Can be overwritten by widget
* @return {String}
*/
Charcoal.Admin.Widget.prototype.widget_type = function ()
{
    return this.type();
};

/**
 * Called upon save by the component manager
 *
 * @return {boolean} Default action is set to true.
 */
Charcoal.Admin.Widget.prototype.save = function ()
{
    return true;
};

/**
 * Animate the widget out on reload
 * Use callback to define what to do after the animation.
 *
 * @param  {Function} callback What to do after the anim_out?
 * @return {thisArg}           Chainable
 */
Charcoal.Admin.Widget.prototype.anim_out = function (callback)
{
    if (typeof callback !== 'function') {
        callback = function () {};
    }
    this.element().fadeOut(400, callback);
    return this;
};

Charcoal.Admin.Widget.prototype.reload = function (cb)
{
    var that = this;

    var url = Charcoal.Admin.admin_url() + 'widget/load';
    var data = {
        widget_type:    that.type(),
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
        if (typeof cb === 'function') {
            cb(response);
        }
    });

};

/**
* Load the widget into a dialog
*/
Charcoal.Admin.Widget.prototype.dialog = function (dialog_opts)
{
    //var that = this;

    var title = dialog_opts.title || '',
        type = dialog_opts.type || BootstrapDialog.TYPE_DEFAULT;

    BootstrapDialog.show({
        title: title,
        type: type,
        nl2br: false,
        message: function (dialog) {
            console.debug(dialog);
            var url = Charcoal.Admin.admin_url() + 'widget/load',
                data = {
                    widget_type:    dialog_opts.widget_type//that.widget_type//,
                    //widget_options: that.widget_options()
                },
                $message = $('<div>Loading...</div>');

            $.ajax({
                method: 'POST',
                url: url,
                data: data
            }).done(function (response) {
                console.debug(response);
                if (response.success) {
                    dialog.setMessage(response.widget_html);
                } else {
                    dialog.setType(BootstrapDialog.TYPE_DANGER);
                    dialog.setMessage('Error');
                }
            });
            return $message;
        }

    });
};
