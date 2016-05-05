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
        console.error('Was not able to store ' + ident + ' in ' + component_type + ' sub-array');
    }
};

/**
* @todo Document
*/
Charcoal.Admin.ComponentManager.prototype.render = function ()
{

    for (var component_type in this.components) {
        var super_class = Charcoal;

        switch (component_type)
        {
            case 'widgets' :
                super_class = Charcoal.Admin.Widget;
            break;

            case 'property_inputs' :
                super_class = Charcoal.Admin.Property;
            break;

            case 'templates' :
                super_class = Charcoal.Admin.Template;
            break;

        }

        for (var i = 0, len = this.components[component_type].length; i < len; i++) {

            var component_data = this.components[component_type][i];

            // If we are already dealing with a full on component
            if (component_data instanceof super_class) {
                continue;
            }

            try {
                var component = new Charcoal.Admin[component_data.ident](component_data);
                this.components[component_type][i] = component;

                // Automatic supra class call
                switch (component_type) {
                    case 'widgets' :
                        // Automatic call on superclass
                        Charcoal.Admin.Widget.call(component, component_data);
                        component.init();
                    break;
                }

            } catch (error) {
                console.error('Was not able to instanciate ' + component_data.ident);
                console.error(error);
            }
        }

    }
};

/**
* This is called by the widget.form on form submit
* Called save because it's calling the save method on the properties' input
* @see admin/widget/form.js submit_form()
* @return boolean Success (in case of validation)
*/
Charcoal.Admin.ComponentManager.prototype.prepare_submit = function ()
{
    this.prepare_inputs();
    this.prepare_widgets();
    return true;
};
Charcoal.Admin.ComponentManager.prototype.prepare_inputs = function ()
{
    // Get inputs
    var inputs = (typeof this.components.property_inputs !== 'undefined') ? this.components.property_inputs : [];

    if (!inputs.length) {
        // No inputs? Move on
        return true;
    }

    var length = inputs.length;
    var input;

    // Loop for validation
    var k = 0;
    for (; k < length; k++) {
        input = inputs[ k ];
        if (typeof input.validate === 'function') {
            input.validate();
        }
    }

    // We should add a check if the validation passed right here, before saving

    // Loop for save
    var i = 0;
    for (; i < length; i++) {
        input = inputs[ i ];
        if (typeof input.save === 'function') {
            input.save();
        }
    }

    return true;
};

Charcoal.Admin.ComponentManager.prototype.prepare_widgets = function ()
{
    // Get inputs
    var widgets = (typeof this.components.widgets !== 'undefined') ? this.components.widgets : [];

    if (!widgets.length) {
        // No inputs? Move on
        return true;
    }

    var length = widgets.length;
    var widget;

    // Loop for validation
    var k = 0;
    for (; k < length; k++) {
        widget = widgets[ k ];
        if (typeof widget.validate === 'function') {
            widget.validate();
        }
    }

    // We should add a check if the validation passed right here, before saving

    // Loop for save
    var i = 0;
    for (; i < length; i++) {
        widget = widgets[ i ];
        if (typeof widget.save === 'function') {
            widget.save();
        }
    }

    return true;

};
