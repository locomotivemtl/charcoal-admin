;(function ($, Admin) {
    'use strict';

    /**
     * Parse values as expression objects.
     *
     * @param  {*} exprs - Zero or more expression objects.
     * @return {Object|null}
     */
    function parse_exprs(exprs) {
        if (Array.isArray(exprs)) {
            return Object.assign({}, exprs);
        } else if ($.isPlainObject(exprs)) {
            return exprs;
        }

        return null;
    };

    /**
     * This provides methods used for handling collection source filters.
     *
     * @mixin
     */
    Admin.Mixin_Model_Filters = {
        filters: {},

        parse_filters: parse_exprs,

        /**
         * Parse value as filter object.
         *
         * @param  {*} filter - A filter object.
         * @return {Object|null}
         */
        parse_filter: function (filter) {
            if (Array.isArray(filter)) {
                return { filters: filter };
            } else if ($.isPlainObject(filter)) {
                return filter;
            }

            return null;
        },

        /**
         * Add a filter.
         *
         * @param  {Object|Array} filter - A filter object.
         * @return {this}
         */
        add_filter: function (filter) {
            filter = this.parse_filter(filter);
            if (filter !== null) {
                if (!this.has_filters()) {
                    this.filters = {};
                }

                var key = Charcoal.Admin.uid();
                this.filters[key] = filter;
            }

            return this;
        },

        /**
         * Add filters.
         *
         * @param  {Object|Object[]} filters - Zero or more filter objects.
         * @return {this}
         */
        add_filters: function (filters) {
            filters = this.parse_filters(filters);
            if (filters !== null) {
                if (this.has_filters()) {
                    this.filters = $.extend({}, this.filters, filters);
                } else {
                    this.filters = filters;
                }
            }

            return this;
        },

        /**
         * Remove an existing filter.
         *
         * @param  {string} name - A filter name.
         * @return {this}
         */
        remove_filter: function (name) {
            if (this.has_filters()) {
                delete this.filters[name];
            }

            return this;
        },

        /**
         * Replace an existing filter.
         *
         * @param  {string}            name   - A filter name.
         * @param  {Object|false|null} filter - A filter object or FALSE or NULL to remove.
         * @return {this}
         */
        set_filter: function (name, filter) {
            if (filter === null || filter === false) {
                this.remove_filter(name);
                return this;
            }

            filter = this.parse_filter(filter);
            if (filter !== null) {
                if (!this.has_filters()) {
                    this.filters = {};
                }

                this.filters[name] = filter;
            }

            return this;
        },

        /**
         * Replace existing filters.
         *
         * @param  {Object|Object[]} filters - Zero or more filter objectss.
         * @return {void}
         */
        set_filters: function (filters) {
            this.filters = this.parse_filters(filters);
        },

        /**
         * Determines if a named filter is defined.
         *
         * @return {boolean}
         */
        has_filter: function (key) {
            return this.filters && (key in this.filters);
        },

        /**
         * Determines if any filters are defined.
         *
         * @return {boolean}
         */
        has_filters: function () {
            return !$.isEmptyObject(this.filters);
        },

        /**
         * Retrieve all filters.
         *
         * @return {Object|null}
         */
        get_filters: function () {
            return this.filters;
        }
    };

    /**
     * This provides methods used for handling collection source orders.
     *
     * @mixin
     */
    Admin.Mixin_Model_Orders = {
        orders: {},

        parse_orders: parse_exprs,

        /**
         * Parse value as order object.
         *
         * @param  {*} order - A order object.
         * @return {Object|null}
         */
        parse_order: function (order) {
            if ($.isPlainObject(order)) {
                return order;
            }

            return null;
        },

        /**
         * Add a order.
         *
         * @param  {Object|Array} order - A order object.
         * @return {this}
         */
        add_order: function (order) {
            order = this.parse_order(order);
            if (order !== null) {
                if (!this.has_orders()) {
                    this.orders = {};
                }

                var key = Charcoal.Admin.uid();
                this.orders[key] = order;
            }

            return this;
        },

        /**
         * Add orders.
         *
         * @param  {Object|Object[]} orders - Zero or more order objects.
         * @return {this}
         */
        add_orders: function (orders) {
            orders = this.parse_orders(orders);
            if (orders !== null) {
                if (this.has_orders()) {
                    this.orders = $.extend({}, this.orders, orders);
                } else {
                    this.orders = orders;
                }
            }

            return this;
        },

        /**
         * Remove an existing order.
         *
         * @param  {string} name - A order name.
         * @return {this}
         */
        remove_order: function (name) {
            if (this.has_orders()) {
                delete this.orders[name];
            }

            return this;
        },

        /**
         * Replace an existing order.
         *
         * @param  {string}            name  - A order name.
         * @param  {Object|false|null} order - A order object or FALSE or NULL to remove.
         * @return {this}
         */
        set_order: function (name, order) {
            if (order === null || order === false) {
                this.remove_order(name);
                return this;
            }

            order = this.parse_order(order);
            if (order !== null) {
                if (!this.has_orders()) {
                    this.orders = {};
                }

                this.orders[name] = order;
            }

            return this;
        },

        /**
         * Replace existing orders.
         *
         * @param  {Object|Object[]} orders - Zero or more order objectss.
         * @return {void}
         */
        set_orders: function (orders) {
            this.orders = this.parse_orders(orders);
        },

        /**
         * Determines if a named order is defined.
         *
         * @return {boolean}
         */
        has_order: function (key) {
            return this.orders && (key in this.orders);
        },

        /**
         * Determines if any orders are defined.
         *
         * @return {boolean}
         */
        has_orders: function () {
            return !$.isEmptyObject(this.orders);
        },

        /**
         * Retrieve all orders.
         *
         * @return {Object|null}
         */
        get_orders: function () {
            return this.orders;
        }
    };

}(jQuery, Charcoal.Admin));
