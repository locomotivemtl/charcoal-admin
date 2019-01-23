/**
 * Charcoal Component Manager
 *
 * Implements its own deferred "ready list" based on `jQuery.fn.ready`.
 */

;(function ($, document, undefined) {
    'use strict';

    // Stored for quick usage
    var $document = $(document);

    // The deferred used when the Components and the DOM are ready
    var readyList = $.Deferred();

    // A counter to track how many items to wait for before the ready event fires.
    var readyWait = 1;

    /**
     * Creates a new component manager.
     *
     * @class
     */
    var Manager = function ()
    {
        // Are the Components and the DOM ready to be used? Set to true once it occurs.
        this.isReady = false;

        // The collection of registered components
        this.components = {};

        var that = this;

        $(document).ready(function () {
            that.render();
        });
    };

    Manager.prototype.add_property_input = function (opts)
    {
        this.add_component('property_inputs', opts);
    };

    Manager.prototype.add_widget = function (opts)
    {
        this.add_component('widgets', opts);
    };

    Manager.prototype.add_template = function (opts)
    {
        this.add_component('templates', opts);
    };

    Manager.prototype.add_component = function (component_type, opts)
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
     * Retrieve Components
     */

    Manager.prototype.get_property_input = function (id)
    {
        return this.get_component('property_inputs', id);
    };

    Manager.prototype.get_widget = function (id)
    {
        return this.get_component('widgets', id);
    };

    Manager.prototype.get_template = function (id)
    {
        return this.get_component('templates', id);
    };

    /**
     * Get component from Type and ID
     *
     * @param type (widgets, inputs, properties)
     * @param id
     * @returns {*}
     */
    Manager.prototype.get_component = function (type, id)
    {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (type in this.components) {
            return this.components[type].find(function (component/*, index, components*/) {
                return component._id === id;
            });
        }

        return undefined;
    };

    /**
     * Remove component from the manager
     *
     * @param type (widgets, inputs, properties)
     * @param id
     * @returns {undefined}
     */
    Manager.prototype.remove_component = function (type, id)
    {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (type in this.components) {
            this.components[type] = this.components[type].filter(function (c) {
                return c._id !== id;
            });
        }

        return undefined;
    };

    /**
     * Specify a function to execute when the components are rendered.
     *
     * The `.ready()` method is also constrained by the DOM's readiness.
     *
     * @param  {Function} fn - A function to execute after the DOM is ready.
     * @return {this}
     */
    Manager.prototype.ready = function (fn)
    {
        readyList.promise().done(fn);

        return this;
    };

    Manager.prototype.render = function ()
    {
        var renderEvent = $.Event('render.charcoal.components', {
            relatedTarget: this
        });

        $document.trigger(renderEvent);

        if (renderEvent.isDefaultPrevented()) {
            return;
        }

        for (var component_type in this.components) {
            var super_class = Charcoal;

            switch (component_type) {
                case 'widgets':
                    super_class = Charcoal.Admin.Widget;
                    break;

                case 'property_inputs':
                    super_class = Charcoal.Admin.Property;
                    break;

                case 'templates':
                    super_class = Charcoal.Admin.Template;
                    break;
            }

            for (var i = 0, len = this.components[component_type].length; i < len; i++) {
                var component_data = this.components[component_type][i];

                // If we are already dealing with a full on component
                if (component_data instanceof super_class) {
                    if (typeof component_data.destroy === 'function') {
                        component_data.destroy();
                        component_data.init();
                    }
                    continue;
                }

                try {
                    var component = new Charcoal.Admin[component_data.ident](component_data);
                    this.components[component_type][i] = component;

                    // Automatic supra class call
                    switch (component_type) {
                        case 'widgets':
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

        // Handle it asynchronously to allow scripts the opportunity to delay ready
        if (this.isReady) {
            return this;
        }

        // Remember that the DOM is ready
        this.isReady = true;

        // If a normal DOM Ready event fired, decrement, and wait if need be
        if (--readyWait > 0) {
            return;
        }

        // If there are functions bound, to execute
        readyList.resolveWith(this);

        var renderedEvent = $.Event('rendered.charcoal.components', {
            relatedTarget: this
        });

        $document.trigger(renderedEvent);

        return this;
    };

    /**
     * This is called by the widget.form on form submit
     * Called save because it's calling the save method on the properties' input
     * @see admin/widget/form.js submit_form()
     * @return boolean Success (in case of validation)
     */
    Manager.prototype.prepare_submit = function ()
    {
        this.prepare_inputs();
        this.prepare_widgets();
        return true;
    };

    Manager.prototype.prepare_inputs = function ()
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

    Manager.prototype.prepare_widgets = function ()
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

    Charcoal.Admin.ComponentManager = Manager;

}(jQuery, document));
