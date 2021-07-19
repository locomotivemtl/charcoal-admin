module.exports = {
    options: {
        banner: '/*! <%= package.name %> */\n'
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
            '<%= paths.dist %>/tinymce/plugins/charcoal/plugin.min.js': [
                '<%= paths.js.src %>/tinymce/plugins/charcoal/plugin.js'
            ],
            '<%= paths.dist %>/tinymce/plugins/placeholder/plugin.min.js': [
                '<%= paths.js.src %>/tinymce/plugins/placeholder/plugin.js'
            ],
            '<%= paths.js.dist %>/charcoal.admin.vendors.min.js': [
                '<%= concat.vendors.dest %>'
            ]
        }
    }
};
