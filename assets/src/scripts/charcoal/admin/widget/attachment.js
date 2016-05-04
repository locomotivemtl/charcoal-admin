/**
* Attachment widget
* You can associate a perticular object to another
* using this widget.
*
* @see widget.js (Charcoal.Admin.Widget
*/
Charcoal.Admin.Widget_Attachment = function ()
{
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
    // Necessary assets.
    if (typeof $.fn.sortable !== 'function') {
        var that = this;
        this.load_assets(function () {
            that.init();
        });
        return this;
    }
    // var config = this.opts();
    this.element().find('.js-attachment-sortable').sortable({
        connectWith: '.js-attachment-sortable'
    }).disableSelection();
    return this;
};

Charcoal.Admin.Widget_Attachment.prototype.load_assets = function (cb)
{
    $.getScript('https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js',
    function () {
        if (typeof cb === 'function') {
            cb();
        }
    });
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
    var that = this;

    // Jquery element
    // var el = this.element();

    // Prevent multiple binds
    this.element().off('click');

    this.element().on('click', '.js-attachments-manager .js-attachment', function (e)
    {
        e.preventDefault();
        that.select_attachment($(this));
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

    // var id = elem.data('id'),
    //     type = elem.data('type');

    var clone = elem.clone();
    this.element().find('.js-attachment-container').append(clone);

};

Charcoal.Admin.Widget_Attachment.prototype.save = function ()
{
    // Scope
    var that = this;

    var opts = that.opts();
    var data = {
        obj_type: opts.data.obj_type,
        obj_id: opts.data.obj_id,
        attachments: []
    };

    this.element().find('.js-attachment-container').find('.js-attachment').each(function (i)
    {
        var $this = $(this);
        var id = $this.data('id');
        var type = $this.data('type');

        data.attachments.push({
            attachment_id: id,
            attachment_type: type, // Further use.
            position: i
        });
    });

    $.post('join', data, function (response)
    {
        if (response.success) {
            window.alert('yay!');
        }
    });

};
