module.exports = {
    options: {
        separator: ';'
    },
    admin: {
        src: [
            '<%= paths.js.src %>/charcoal/admin/polyfill.js',
            '<%= paths.js.src %>/charcoal/admin/charcoal.js',
            '<%= paths.js.src %>/charcoal/admin/cache.js',
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
            // jQuery UI
            '<%= paths.js.dist %>/vendors/jquery/jquery-ui.min.js',

            // URL Search Params
            '<%= paths.npm %>/url-search-params/build/url-search-params.js',

            // Bootstrap Switch
            '<%= paths.npm %>/bootstrap-switch/dist/js/bootstrap-switch.min.js',

            // Bootstrap Dialog
            '<%= paths.npm %>/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js',

            // Bootstrap Select
            '<%= paths.npm %>/bootstrap-select/dist/js/bootstrap-select.js',

            // Bootstrap 3 Datepicker
            '<%= paths.npm %>/moment/min/moment.min.js',
            '<%= paths.npm %>/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',

            // BB Map
            '<%= paths.npm %>/beneroch-gmap/assets/scripts/dist/min/gmap.min.js',

            // jQuery Minicolors
            '<%= paths.npm %>/@claviska/jquery-minicolors/jquery.minicolors.min.js',

            // Multiselect Two-sides
            '<%= paths.npm %>/multiselect-two-sides/dist/js/multiselect.min.js',

            // Selectize
            '<%= paths.npm %>/selectize/dist/js/standalone/selectize.min.js',
            // '<%= paths.js.dist %>/vendors/selectize/selectize-item-color/src/plugin.js',

            // Clipboard
            '<%= paths.npm %>/clipboard/dist/clipboard.min.js',

            // jQuery Timeago
            '<%= paths.npm %>/jquery-timeago/jquery.timeago.js'
        ],
        dest: '<%= paths.js.dist %>/charcoal.admin.vendors.js',
        separator: "\n"
    }
};
