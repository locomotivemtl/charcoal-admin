module.exports = {
    options: {
        sourceMap   : false,
        outputStyle : 'expanded'
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
            '<%= paths.css.dist %>/vendors/elfinder.css': '<%= paths.css.src %>/**/elfinder.scss'
        }
    }
};
