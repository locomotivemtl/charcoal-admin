module.exports = {
    options:{
        separator:';'
    },
    app: {
        src: [
            'assets/src/scripts/charcoal/admin/charcoal.js',
            'assets/src/scripts/charcoal/admin/component_manager.js',
            'assets/src/scripts/charcoal/admin/property.js',
            'assets/src/scripts/charcoal/admin/property/*.js',
            'assets/src/scripts/charcoal/admin/property/input/*.js',
            'assets/src/scripts/charcoal/admin/template.js',
            'assets/src/scripts/charcoal/admin/template/*.js',
            'assets/src/scripts/charcoal/admin/widget.js',
            'assets/src/scripts/charcoal/admin/widget/*.js'
        ],
        dest: 'assets/dist/scripts/charcoal.admin.js'
    },
    vendors: {
        src: [
            // Bootstrap Switch
            'bower_components/bootstrap-switch/dist/js/bootstrap-switch.min.js',
            // Bootstrap Dialog
            'bower_components/bootstrap3-dialog/dist/js/bootstrap-dialog.min.js',
            // Bootstrap 3 Datepicker
            'bower_components/moment/min/moment.min.js',
            'bower_components/eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js',
            // Summernote
            'bower_components/summernote/dist/summernote.js',
            // BB Map
            'bower_components/bb-gmap/assets/scripts/dist/min/gmap.min.js'
        ],
        dest: 'assets/dist/scripts/charcoal.admin.vendors.js'
    }
};
