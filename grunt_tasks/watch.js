module.exports = {
    php: {
        files :[
            'src/**/*.php',
            'tests/**/*.php',
        ],
        tasks: ['phplint']
    },
    javascript: {
        files: [
            'assets/src/scripts/**/*.js'
        ],
        tasks: ['jshint', 'concat', 'uglify', 'notify:concat']
    },
    sass: {
        files: ['assets/src/styles/**/**/**/*.scss'],
        tasks: ['sass', 'notify:sass'],
        options: {
            spawn: false,
            livereload: true
        }
    },
    svg: {
        files: ['assets/src/images/**/*.svg'],
        tasks: ['svgstore', 'notify:svg']
    },
    tasks: {
        files: ['grunt_tasks/*.js'],
        options: {
            reload: true
        }
    }
};
