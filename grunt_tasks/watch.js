module.exports = {
    javascript: {
        files: [
            'assets/src/scripts/**/*.js',
            'grunt_tasks/*.js'
        ],
        tasks: [
            'jshint',
            'jscs',
            'concat',
            'uglify',
            'notify:javascript'
        ]
    },
    json: {
        files: [
            '*.json',
            'config/*.json',
            'metadata/**/*.json'
        ],
        tasks: [
            'jsonlint',
            'notify:json'
        ]
    },
    php: {
        files :[
            'src/**/*.php',
            'tests/**/*.php',
        ],
        tasks: [
            'phplint',
            'notify:php'
        ]
    },
    sass: {
        files: ['assets/src/styles/**/*.scss'],
        tasks: [
            'sass',
            'concat:css',
            'notify:sass'
        ],
        options: {
            spawn: false,
            livereload: true
        }
    },
    svg: {
        files: ['assets/src/images/**/*.svg'],
        tasks: [
            'svgstore',
            'notify:svg'
        ]
    },
    dist: {
        files: ['assets/dist/**/*'],
        tasks: [
            'copy:admin',
            'notify:copy'
        ]
    },
    tasks: {
        files: ['grunt_tasks/*.js'],
        options: {
            reload: true
        }
    }
};
