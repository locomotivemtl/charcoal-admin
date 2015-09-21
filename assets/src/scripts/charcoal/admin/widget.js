/**
* charcoal/admin/widget
*/

/**
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

Charcoal.Admin.Widget.prototype.reload = function (cb)
{
    var that = this;

    var url = Charcoal.Admin.admin_url() + 'action/json/widget/load';
    var data = {
        widget_type:    that.widget_type,
        widget_options: that.widget_options()
    };

    // Response from the reload action should always include a
    // widget_id and widget_html in order to work accordingly.
    // @todo add nice styles and stuffs.
    $.post(url, data, function (response) {
        if (typeof response.widget_id === 'string') {
            that.set_id(response.widget_id);
            that.element().addClass('fade').addClass('out');
            setTimeout(function () {
                that.element().replaceWith(response.widget_html);
                that.set_element($('#' + that.id()));

                // Pure dompe.
                that.element().addClass('invisible').addClass('fade').addClass('in').removeClass('invisible');
                that.init();
            }, 600);
        }
        // Callback
        cb(response);
    });

};
