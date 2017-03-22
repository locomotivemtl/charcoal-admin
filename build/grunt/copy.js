module.exports = {
    vendors: {
        files: [
            {
                expand: true,
                cwd   : 'bower_components/bootstrap-sass/assets/fonts/bootstrap/',
                src   : ['**', '*'],
                dest  : 'assets/dist/fonts/'
            },
            {
                expand: true,
                cwd   : 'bower_components/echarts/dist/',
                // src: ['echarts.js'],
                src   : ['**/*.*', '*/*.*', '*.*'],
                dest  : 'assets/dist/scripts/vendors/echarts/'
            },
            {
                expand: true,
                cwd   : 'vendor/studio-42/elfinder/',
                src   : ['css/*', 'img/*', 'js/*', 'js/**/*', 'sounds/*'],
                dest  : 'assets/dist/elfinder/'
            },
            {
                expand: true,
                cwd   : 'bower_components/elfinder-theme-moono/',
                src   : ['**/*.css'],
                dest  : 'assets/dist/elfinder/themes/'
            },
            {
                expand: true,
                cwd   : 'bower_components/jquery/dist/',
                src   : ['jquery.*'],
                dest  : 'assets/dist/scripts/vendors/jquery/'
            },
            {
                expand: true,
                cwd   : 'bower_components/tinymce/',
                src   : ['skins/**/*.*', 'plugins/**/plugin.min.js', 'plugins/**/*.{css,gif,swf}', 'themes/modern/theme.min.js', 'tinymce.min.js'],
                dest  : 'assets/dist/scripts/vendors/tinymce/'
            },
            {
                expand: true,
                cwd   : 'bower_components/bb-gmap/assets/scripts/dist/min/',
                src   : ['*.js'],
                dest  : 'assets/dist/scripts/vendors/bb-gmap/'
            }
        ]
    },
    admin  : {
        expand: true,
        cwd:   'assets/dist/',
        src:    ['**', '*'],
        dest:   '../../../www/assets/admin/'
    }
};
