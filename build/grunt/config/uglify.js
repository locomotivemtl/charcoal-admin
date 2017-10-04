module.exports = {
    options: {
        banner: '/*! <%= package.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
    },
    app: {
        files: {
            '<%= paths.js.dist %>/charcoal.admin.min.js': [
                '<%= concat.admin.dest %>'
            ]
        }
    },
    vendors: {
        files: {
            '<%= paths.js.dist %>/vendors/tinymce/plugins/charcoal/plugin.min.js': [
                '<%= paths.js.src %>/charcoal/admin/tinymce/plugins/charcoal/plugin.js'
            ],
            '<%= paths.js.dist %>/vendors/tinymce/plugins/placeholder/plugin.min.js': [
                '<%= paths.js.src %>/charcoal/admin/tinymce/plugins/placeholder/plugin.js'
            ],
            '<%= paths.js.dist %>/charcoal.admin.vendors.min.js': [
                '<%= concat.vendors.dest %>'
            ]
        }
    }
};
