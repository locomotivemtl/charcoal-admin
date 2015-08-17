/**
* charcoal/admin/component_manager
*/

Charcoal.Admin.ComponentManager = function ()
{
    var that = this;

    that.components = {};

    $(document).on('ready', function () {
        that.render();
    });
};

Charcoal.Admin.ComponentManager.prototype.add_property_input = function (opts)
{
    this.add_component('property_inputs', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_widget = function (opts)
{
    this.add_component('widgets', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_template = function (opts)
{
    this.add_component('templates', opts);
};

Charcoal.Admin.ComponentManager.prototype.add_component = function (component_type, opts)
{
    // Figure out which component to instanciate
    var ident = Charcoal.Admin.get_object_name(opts.type);

    // Make sure it exists first
    if (typeof(Charcoal.Admin[ident]) === 'function') {

        opts.ident = ident;

        // Check if component type array exists in components array
        this.components[component_type] = this.components[component_type] || [];
        this.components[component_type].push(opts);

    } else {
        console.log('Was not able to store ' + ident + ' in ' + component_type + ' sub-array');
    }

};

Charcoal.Admin.ComponentManager.prototype.render = function ()
{

    for (var component_type in this.components) {

        for (var i = 0, len = this.components[component_type].length; i < len; i++) {

            var component_data = this.components[component_type][i];

            try {
                var component = new Charcoal.Admin[component_data.ident](component_data);
                this.components[component_type][i] = component;
            } catch (error) {
                console.log('Was not able to instanciate ' + component_data.ident);
                console.log(error);
            }
        }

    }
};
