module.exports = {
    options: {
        spawn      : false,
        livereload : false
    },
    javascript: {
        files: [ '<%= paths.js.src %>/**/*.js', '<%= paths.grunt %>/config/concat.js' ],
        tasks: [ 'concat', 'notify:javascript', 'copy:admin' ]
    },
    sass: {
        files: [ '<%= paths.css.src %>/**/*.scss' ],
        tasks: [ 'sass', 'postcss', 'notify:sass', 'copy:admin' ]
    },
    svg: {
        files: [ '<%= paths.img.src %>/**/*.svg' ],
        tasks: [ 'svg_sprite', 'notify:svg' ]
    },
    dist: {
        files: [ 'assets/dist/**/*' ],
        tasks: [ 'copy:admin', 'notify:copy' ]
    },
    tasks: {
        options: {
            reload: true
        },
        files: [ 'Gruntfile.js', '<%= paths.grunt %>/**/*' ]
    }
};
