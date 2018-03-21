module.exports = {
    options: {
        spawn:      false,
        livereload: false
    },
    javascript: {
        files: [ '<%= paths.js.src %>/**/*.js', '<%= paths.grunt %>/config/concat.js' ],
        tasks: [ 'concat', 'notify:javascript', 'copy:www' ]
    },
    sass: {
        files: [ '<%= paths.css.src %>/**/*.scss' ],
        tasks: [ 'sass', 'postcss', 'notify:sass', 'copy:www' ]
    },
    svg: {
        files: [ '<%= paths.img.src %>/**/*.svg' ],
        tasks: [ 'svg_sprite', 'notify:svg' ]
    },
    dist: {
        files: [ '<%= paths.dist %>/**/*' ],
        tasks: [ 'copy:www', 'notify:www' ]
    },
    tasks: {
        options: {
            reload: true
        },
        files: [ 'Gruntfile.js', '<%= paths.grunt %>/**/*' ]
    }
};
