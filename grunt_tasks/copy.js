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
    }
};
