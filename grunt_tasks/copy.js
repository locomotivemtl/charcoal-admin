module.exports = {
    bootstrap3_dialog_js:{
        expand:true,
        cwd: 'bower_components/bootstrap3-dialog/dist/js',
        src: ['**', '*'],
        dest: 'assets/dist/scripts/vendors/'
    },
    bootstrap3_dialog_css:{
        expand:true,
        cwd: 'bower_components/bootstrap3-dialog/dist/css',
        src: ['**', '*'],
        dest: 'assets/dist/styles/vendors/'
    },
    echarts:{
        expand:true,
        cwd: 'bower_components/echarts/build/dist',
        //src: ['echarts.js'],
        src: ['**/*.*', '*/*.*', '*.*'],
        dest: 'assets/dist/scripts/vendors/echarts/'
    }
};
