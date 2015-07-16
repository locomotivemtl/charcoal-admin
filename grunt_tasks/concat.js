module.exports = {
    options:{
        separator:';'
    },
    dist: {
        src: [
            'assets/src/scripts/charcoal/admin/charcoal.js',
            'assets/src/scripts/charcoal/admin/property.js',
            'assets/src/scripts/charcoal/admin/property/*.js',
            'assets/src/scripts/charcoal/admin/property/input/*.js',
            'assets/src/scripts/charcoal/admin/template.js',
            'assets/src/scripts/charcoal/admin/template/*.js',
            'assets/src/scripts/charcoal/admin/widget.js',
            'assets/src/scripts/charcoal/admin/widget/*.js'
        ],
        dest: 'assets/dist/scripts/charcoal.admin.js'
    }
};
