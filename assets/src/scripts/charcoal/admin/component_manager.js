/**
* charcoal/admin/component_manager
*/

Charcoal.Admin.ComponentManager = function ()
{
    this.input_properties = [];
    this.widgets = [];
    this.templates = [];
};

Charcoal.Admin.ComponentManager.prototype.add_property_input = function (opts)
{
    this.add_component(this.input_properties, opts);
};

Charcoal.Admin.ComponentManager.prototype.add_widget = function (opts)
{
    this.add_component(this.widgets, opts);
};

Charcoal.Admin.ComponentManager.prototype.add_template = function (opts)
{
    this.add_component(this.templates, opts);
};

Charcoal.Admin.ComponentManager.prototype.add_component = function (component_array, opts)
{
    // Figure out which component to instanciate
    var ident = Charcoal.Admin.get_object_name(opts.type);

    // Make sure it exists first
    if (typeof(Charcoal.Admin[ident]) === 'function') {

        var component = new Charcoal.Admin[ident](opts);

        component_array.push(component);

    } else {
        console.log('Was not able to instanciate ' + ident);
    }
};
