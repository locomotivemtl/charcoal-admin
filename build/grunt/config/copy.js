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
                cwd:   '<%= paths.npm %>/bootstrap-select/dist/js/',
                src:   [ 'i18n/**/*.*' ],
                dest:  '<%= paths.js.dist %>/vendors/bootstrap-select/'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/recorderjs/dist/',
                src:   [ '**/*.*', '*/*.*', '*.*' ],
                dest:  '<%= paths.js.dist %>/vendors/recorderjs/'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/tinymce/',
                src:   [ 'icons/**/*.*', 'plugins/**/plugin.min.js', 'plugins/**/*.{css,gif,swf}', 'skins/**/*.*', 'themes/**/theme.min.js', 'tinymce.min.js' ],
                dest:  '<%= paths.dist %>/tinymce/'
            },
            {
                expand: true,
                cwd:   '<%= paths.npm %>/tinymce-i18n/langs5/',
                src:   [ '*.js' ],
                dest:  '<%= paths.dist %>/tinymce/langs/'
            },
            {
                expand: true,
                cwd:    '<%= paths.npm %>/jsoneditor/dist/',
                src:    [ '**/*.*', '*/*.*', '*.*' ],
                dest:   '<%= paths.dist %>/jsoneditor/'
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
