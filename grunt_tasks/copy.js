module.exports = {
    bootstrap_fonts: {
        expand: true,
        cwd: 'bower_components/bootstrap-sass/assets/fonts/bootstrap/',
        src: ['**', '*'],
        dest: 'assets/dist/fonts/'
    },
    echarts: {
        expand: true,
        cwd: 'bower_components/echarts/build/dist',
        //src: ['echarts.js'],
        src: ['**/*.*', '*/*.*', '*.*'],
        dest: 'assets/dist/scripts/vendors/echarts/'
    },
    jquery: {
        expand: true,
        cwd: 'bower_components/jquery/dist/',
        src: ['jquery.*'],
        dest: 'assets/dist/scripts/vendors/jquery/'
    },
    qunit: {
        expand: true,
        cwd: 'bower_components/qunit/qunit/',
        src: ['qunit.*'],
        dest: 'assets/dist/scripts/vendors/qunit/'
    },
    tinymce: {
        expand: true,
        cwd: 'bower_components/tinymce',
        src: ['skins/**/*.*', 'plugins/**/plugin.min.js', 'plugins/**/*.{css,gif,swf}', 'themes/modern/theme.min.js', 'tinymce.min.js'],
        dest: 'assets/dist/scripts/vendors/tinymce/'
    },
    admin: {
        expand: true,
        cwd: 'assets/dist/',
        src: ['**', '*'],
        dest: '../../../www/assets/admin/'
    }
};
