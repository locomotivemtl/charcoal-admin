/**
 * Base Template (charcoal/admin/template)
 *
 * @param  {Object} opts - The component instance arguments.
 * @return {Charcoal.Admin.Template}
 */
Charcoal.Admin.Template = function (opts) {
    Charcoal.Admin.Component.call(this, opts);
    return this;
};

Charcoal.Admin.Template.prototype = Object.create(Charcoal.Admin.Component.prototype);
Charcoal.Admin.Template.prototype.constructor = Charcoal.Admin.Template;
Charcoal.Admin.Template.prototype.parent = Charcoal.Admin.Component.prototype;
