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
            'bower_components/bootstrap-switch/dist/js/bootstrap-switch.min.js',
            'bower_components/summernote/dist/summernote.js',
        ],
        dest: 'assets/dist/scripts/charcoal.admin.vendors.js'
    },
    css : {
        src: [
           'bower_components/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css',
           'assets/dist/styles/charcoal.admin.vendors.css'

        ],
        dest: 'assets/dist/styles/charcoal.admin.vendors.css'
    }
};
