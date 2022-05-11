/**
 * Charcoal Component Manager
 *
 * Implements its own deferred "ready list" based on `jQuery.fn.ready`.
 */

;(function ($, document) {
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

    /**
     * Adds a property input component.
     *
     * @param  {object} component_data - The component definition.
     * @return {boolean}
     */
    Manager.prototype.add_property_input = function (component_data) {
        return this.add_component('property_inputs', component_data);
    };

    /**
     * Adds a widget component.
     *
     * @param  {object} component_data - The component definition.
     * @return {boolean}
     */
    Manager.prototype.add_widget = function (component_data) {
        return this.add_component('widgets', component_data);
    };

    /**
     * Adds a template component.
     *
     * @param  {object} component_data - The component definition.
     * @return {boolean}
     */
    Manager.prototype.add_template = function (component_data) {
        return this.add_component('templates', component_data);
    };

    /**
     * Add component of Type and Options
     *
     * @param   {string} component_group Either "widgets", "inputs", or "properties".
     * @param   {object} component_data
     * @returns {boolean}
     */
    Manager.prototype.add_component = function (component_group, component_data) {
        if (!component_data.type) {
            console.error('Was not able to store component: missing type');
            return false;
        }

        // Figure out which component to instanciate
        var component_class_name = Charcoal.Admin.get_object_name(component_data.type);

        // Make sure component class exists first
        if (typeof Charcoal.Admin[component_class_name] !== 'function') {
            console.error('Was not able to store component [Charcoal.Admin.' + component_class_name + ']: missing class');
            return false;
        }

        component_data.ident = component_class_name;

        if (Array.isArray(this.components[component_group])) {
            if (component_data.id && this.components[component_group].length) {
                var component = this.components[component_group].find(function (component) {
                    return (
                        // Compare against an instantiated component
                        (component._id && component._id === component_data.id) ||
                        // Compare against component data
                        (component.id && component.id === component_data.id)
                    );
                });

                if (component) {
                    var message = 'Was not able to store component [Charcoal.Admin.' + component_class_name + ']: ' +
                                component_data.id + ' already registered';

                    if (
                        // Compare against an instantiated component
                        (component._type && component._type === component_data.type) ||
                        // Compare against component data
                        (component.type && component.type === component_data.type)
                    ) {
                        // Assume its a reloaded component
                        console.warn(message);
                    } else {
                        // Something is not right
                        console.error(message);
                    }

                    return false;
                }
            }
        } else {
            this.components[component_group] = [];
        }

        this.components[component_group].push(component_data);

        return true;
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
     * @param   {string} component_group Either "widgets", "inputs", or "properties".
     * @param   {string} component_id
     * @returns {?Component}
     */
    Manager.prototype.get_component = function (component_group, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_group in this.components) {
            return this.components[component_group].find(function (component) {
                return component._id === component_id;
            });
        }

        return null;
    };

    /**
     * Check if component from Type and ID exists.
     *
     * @param   {string} component_group Either "widgets", "inputs", or "properties".
     * @param   {string} component_id
     * @returns {boolean}
     */
    Manager.prototype.has_component = function (component_group, component_id) {
        if (component_group in this.components) {
            return this.components[component_group].some(function (component) {
                // Compare against an instantiated component
                if (component._id && component._id === component_id) {
                    return true;
                }

                // Compare against component data
                if (component.id && component.id === component_id) {
                    return true;
                }

                return false;
            });
        }

        return false;
    };

    /**
     * Destroy component and remove from the manager
     *
     * @param   {string} component_group Either "widgets", "inputs", or "properties".
     * @param   {string} component_id
     * @returns {void}
     */
    Manager.prototype.destroy_component = function (component_group, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_group in this.components) {
            this.components[component_group] = this.components[component_group].filter(function (component) {
                if (component._id !== component_id) {
                    return true;
                }

                if (typeof component.destroy === 'function') {
                    component.destroy();
                }

                return false;
            });
        }
    };

    /**
     * Remove component from the manager
     *
     * @param   {string} component_group Either "widgets", "inputs", or "properties".
     * @param   {string} component_id
     * @returns {void}
     */
    Manager.prototype.remove_component = function (component_group, component_id) {
        if (!this.isReady) {
            throw new Error('Components must be rendered.');
        }

        if (component_group in this.components) {
            this.components[component_group] = this.components[component_group].filter(function (component) {
                return component._id !== component_id;
            });
        }
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

        for (var component_group in this.components) {
            var super_class = Charcoal;

            switch (component_group) {
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

            for (var i = 0, len = this.components[component_group].length; i < len; i++) {
                var component_data = this.components[component_group][i];

                // If we are already dealing with a full on component
                if (component_data instanceof super_class) {
                    if (typeof component_data.destroy === 'function') {
                        component_data.destroy();
                        component_data.init();
                    }
                    continue;
                }

                try {
                    var component = this.create_component(component_data.ident, component_data);
                    this.components[component_group][i] = component;

                    // Automatic supra class call
                    switch (component_group) {
                        case 'widgets':
                            // Automatic call on superclass
                            Charcoal.Admin.Widget.call(component, component_data);
                            component.init();
                            break;
                    }

                } catch (error) {
                    if (component_data.id) {
                        console.error('Was not able to instantiate component [Charcoal.Admin.' + component_data.ident + '] (' + component_data.id + ')');
                    } else {
                        console.error('Was not able to instantiate component [Charcoal.Admin.' + component_data.ident + ']');
                    }
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
     * Creates a component.
     *
     * @param  {string} component_ident - The component object name.
     * @param  {object} component_data  - The component definition.
     * @thows  {TypeError} If component definition is invalid
     *     or component object does not exist.
     * @return {?Component}
     */
    Manager.prototype.create_component = function (component_ident, component_data) {
        if (!component_ident) {
            throw new TypeError(
                'Expected component data to include ident'
            );
        }

        if (!Charcoal.Admin[component_ident]) {
            throw new TypeError(
                'Expected component [Charcoal.Admin.' + component_ident + '] to exist'
            );
        }

        return new Charcoal.Admin[component_data.ident](component_data);
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
