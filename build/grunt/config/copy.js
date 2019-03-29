module.exports = {
    vendors: {
        files: [
            {
                expand: true,
                cwd:   '<%= paths.npm %>/echarts/dist/',
                src:   [ '**/*.*', '*/*.*', '*.*' ],
                dest:  '<%= paths.js.dist %>/vendors/echarts/'
            },
            {
                expand: true,
                cwd:   '<%= paths.elfinder.src %>',
                src:   [ 'css/*', 'img/*', 'js/*', 'js/**/*', 'sounds/*' ],
                dest:  '<%= paths.elfinder.dist %>'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/@claviska/jquery-minicolors/',
                src:   [ 'jquery.minicolors.png' ],
                dest:  '<%= paths.css.dist %>'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/tinymce/',
                src:   [ 'skins/**/*.*', 'plugins/**/plugin.min.js', 'plugins/**/*.{css,gif,swf}', 'themes/**/theme.min.js', 'tinymce.min.js' ],
                dest:  '<%= paths.js.dist %>/vendors/tinymce/'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/tinymce-i18n/',
                src:   [ 'langs/**/*.*' ],
                dest:  '<%= paths.js.dist %>/vendors/tinymce/'
            },
            {
                expand: true,
                cwd:    '<%= paths.npm %>/jsoneditor/dist/',
                src:    [ '**/*.*', '*/*.*', '*.*' ],
                dest:   '<%= paths.js.dist %>/vendors/jsoneditor/'
            }
        ]
    },
    www: {
        expand: true,
        cwd:    '<%= paths.dist %>',
        src:    [ '**', '*' ],
        dest:   '<%= paths.prod %>'
    }
};
