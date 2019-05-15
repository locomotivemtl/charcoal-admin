module.exports = {
    options: {
        implementation: require('node-sass'),
        sourceMap:      false,
        outputStyle:    'expanded'
    },
    app: {
        files: {
            '<%= paths.css.dist %>/charcoal.admin.css': '<%= paths.css.src %>/**/charcoal.admin.scss'
        }
    },
    vendors: {
        files: {
            '<%= paths.css.dist %>/charcoal.admin.vendors.css': '<%= paths.css.src %>/**/charcoal.admin.vendors.scss'
        }
    },
    elfinder: {
        files: {
            '<%= paths.elfinder.dist %>/themes/moono/css/theme.css': '<%= paths.css.src %>/**/vendors/elfinder.moono.scss'
        }
    }
};
