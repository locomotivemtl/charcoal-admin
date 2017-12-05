module.exports = {
    vendors: {
        files: [
            {
                expand : true,
                cwd    : 'node_modules/echarts/dist/',
                src    : [
                    '**/*.*',
                    '*/*.*',
                    '*.*'
                ],
                dest   : '<%= paths.js.dist %>/vendors/echarts/'
            },
            {
                expand : true,
                cwd    : 'vendor/studio-42/elfinder/',
                src    : [
                    'css/*',
                    'img/*',
                    'js/*',
                    'js/**/*',
                    'sounds/*'
                ],
                dest   : '<%= paths.elfinder %>/'
            },
            {
                expand : true,
                cwd    : 'node_modules/elfinder-theme-moono/',
                src    : [ '**/*.css' ],
                dest   : '<%= paths.elfinder %>/themes/'
            },
            {
                expand : true,
                cwd    : 'node_modules/tinymce/',
                src    : [
                    'skins/**/*.*',
                    'plugins/**/plugin.min.js',
                    'plugins/**/*.{css,gif,swf}',
                    'themes/modern/theme.min.js',
                    'tinymce.min.js'
                ],
                dest   : '<%= paths.js.dist %>/vendors/tinymce/'
            },
            // {
            //     expand: true,
            //     cwd   : 'node_modules/beneroch-gmap/assets/scripts/dist/min/',
            //     src   : [ '*.js' ],
            //     dest  : '<%= paths.js.dist %>/vendors/beneroch-gmap/'
            // },
            {
                expand : true,
                src    : [ 'node_modules/recorderjs/recorderWorker.js' ],
                dest   : '<%= paths.js.dist %>/vendors/'
            }
        ]
    },
    admin  : {
        expand : true,
        cwd    : 'assets/dist/',
        src    : [ '**', '*' ],
        dest   : '../../../www/assets/admin/'
    }
};
