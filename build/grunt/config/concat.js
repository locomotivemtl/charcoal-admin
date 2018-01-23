module.exports = {
    options: {
        separator: ';'
    },
    admin: {
        src: [
            '<%= paths.js.src %>/charcoal/admin/polyfill.js',
            '<%= paths.js.src %>/charcoal/admin/charcoal.js',
            '<%= paths.js.src %>/charcoal/admin/component_manager.js',
            '<%= paths.js.src %>/charcoal/admin/feedback.js',
            '<%= paths.js.src %>/charcoal/admin/widget.js',
            '<%= paths.js.src %>/charcoal/admin/widget/*.js',
            '<%= paths.js.src %>/charcoal/admin/property.js',
            '<%= paths.js.src %>/charcoal/admin/property/*.js',
            '<%= paths.js.src %>/charcoal/admin/property/input/**/*.js',
            '<%= paths.js.src %>/charcoal/admin/template.js',
            '<%= paths.js.src %>/charcoal/admin/template/*.js',
            '<%= paths.js.src %>/charcoal/admin/template/**/*.js',
        ],
        dest: '<%= paths.js.dist %>/charcoal.admin.js'
    },
    vendors: {
        src: [
            // jQuery
            'node_modules/jquery/dist/jquery.js',

            // jQuery UI
            'node_modules/jquery-ui-bundle/jquery-ui.js',

            // Bootstrap
            'node_modules/bootstrap/dist/js/bootstrap.bundle.js',

            // URL Search Params
            'node_modules/url-search-params/build/url-search-params.js',

            /**
             * Bootstrap Dialog (temporary)
             *
             * @todo Too precarious
             * @see  https://github.com/pYr0x/bootstrap-dialog)
             * @see  https://gist.github.com/dominiclord/49d0a84cca789a5be3c532d8f0bc8b75)
             */
            'node_modules/bootstrap-dialog-temporary/bootstrap-dialog.js',

            // Bootstrap 3 Datepicker
            // 'bower_components/moment/min/moment.min.js',
            // 'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',

            // BB Map
            'node_modules/beneroch-gmap/assets/scripts/dist/min/gmap.min.js',

            // Bootstrap Select (@see paramono/bootstrap-select)
            'node_modules/bootstrap-select-temporary/dist/js/bootstrap-select.js',
            // 'node_modules/bootstrap-select/dist/js/bootstrap-select.js',

            // jQuery MiniColors
            'node_modules/jquery-minicolors/jquery.minicolors.min.js',

            // Multiselect Two-sides
            'node_modules/multiselect/dist/js/multiselect.min.js',

            // Selectize
            'node_modules/selectize/dist/js/standalone/selectize.min.js',

            // Selectize
            'node_modules/clipboard/dist/clipboard.min.js',

            // jQuery Timeago
            'node_modules/timeago/jquery.timeago.js',
        ],
        dest: '<%= paths.js.dist %>/charcoal.admin.vendors.js',
        separator: "\n"
    }
};
