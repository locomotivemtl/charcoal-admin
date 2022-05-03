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
    var Manager = function () {
        // Are the Components and the DOM ready to be used? Set to true once it occurs.
        this.isReady = false;

        // The collection of registered components
        this.components = {};

        var that = this;

        $(document).ready(function () {
            that.render();
        });
    };

    Manager.prototype.add_property_input = function (opts) {
        this.add_component('property_inputs', opts);
    };

    Manager.prototype.add_widget = function (opts) {
        this.add_component('widgets', opts);
    };

    Manager.prototype.add_template = function (opts) {
        this.add_component('templates', opts);
    };

    Manager.prototype.add_component = function (component_type, opts) {
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

    Manager.prototype.get_property_inputs = function () {
        return Array.isArray(this.components.property_inputs)
            ? this.components.property_inputs
            : [];
    };

    Manager.prototype.get_property_input = function (id) {
        return this.get_component('property_inputs', id);
    };

    Manager.prototype.get_widgets = function () {
        return Array.isArray(this.components.widgets)
            ? this.components.widgets
            : [];
    };

    Manager.prototype.get_widget = function (id) {
        return this.get_component('widgets', id);
    };

    Manager.prototype.get_templates = function () {
        return Array.isArray(this.components.templates)
            ? this.components.templates
            : [];
    };

    Manager.prototype.get_template = function (id) {
        return this.get_component('templates', id);
    };

    /**
     * Get component from Type and ID
     *
     * @param component_type (widgets, inputs, properties)
     * @param component_id
     * @returns {*}
     */
    Manager.prototype.get_component = function (component_type, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_type in this.components) {
            return this.components[component_type].find(function (component) {
                return component._id === component_id;
            });
        }

        return undefined;
    };

    /**
     * Remove component from the manager
     *
     * @param component_type (widgets, inputs, properties)
     * @param component_id
     * @returns {undefined}
     */
    Manager.prototype.remove_component = function (component_type, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_type in this.components) {
            this.components[component_type] = this.components[component_type].filter(function (component) {
                return component._id !== component_id;
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
    Manager.prototype.ready = function (fn) {
        readyList.promise().done(fn);

        return this;
    };

    Manager.prototype.render = function () {
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
     * Prepares components for form submission.
     *
     * This method is called by {@see Charcoal.Admin.Widget_Form.prototype.request_submit form widgets}.
     *
     * @param  {Component} [scope] - The component that called this method.
     * @return {boolean}
     */
    Manager.prototype.prepare_submit = function (scope) {
        return this.prepare_inputs(scope) && this.prepare_widgets(scope);
    };

    /**
     * Validates and "saves" property inputs for form submission.
     *
     * @param  {Component[]} components - Zero or more components to prepare.
     * @param  {Component}   [scope]    - The component that called this method.
     * @return {boolean}
     */
    Manager.prototype.prepare_inputs = function (scope) {
        var inputs = this.get_property_inputs();

        return this.prepare_components(inputs, scope);
    };

    /**
     * Validates and "saves" widgets for form submission.
     *
     * @param  {Component[]} components - Zero or more components to prepare.
     * @param  {Component}   [scope]    - The component that called this method.
     * @return {boolean}
     */
    Manager.prototype.prepare_widgets = function (scope) {
        var widgets = this.get_widgets();

        return this.prepare_components(widgets, scope);
    };

    /**
     * Validates and "saves" components for form submission.
     *
     * The "save" is, most often, used to serialize the value of
     * a complex UI into a hidden form control.
     *
     * Each component is expected to add their own feedback if their
     * value is invalid or errored (via `validate` or `save`).
     *
     * @param  {Component[]} components - Zero or more components to prepare.
     * @param  {Component}   [scope]    - The component that called this method.
     * @return {boolean}
     */
    Manager.prototype.prepare_components = function (components, scope) {
        if (!components.length) {
            return true;
        }

        var length = components.length,
            component,
            result,
            i;

        for (i = 0; i < length; i++) {
            component = components[i];

            if (typeof component.will_validate === 'function') {
                if (component.will_validate(scope) === false) {
                    continue;
                }
            }

            if (typeof component.validate === 'function') {
                result = component.validate();
                if (result === false) {
                    return result;
                }
            }
        }

        for (i = 0; i < length; i++) {
            component = components[i];

            if (typeof component.will_save === 'function') {
                if (component.will_save(scope) === false) {
                    continue;
                }
            }

            if (typeof component.save === 'function') {
                result = component.save();
                if (result === false) {
                    return result;
                }
            }
        }

        return true;
    };

    Charcoal.Admin.ComponentManager = Manager;

}(jQuery, document));
