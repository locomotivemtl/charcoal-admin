module.exports = function (grunt, options)
{
    return {
        options: {
            map: false,
            processors: [
                require('autoprefixer')(),
                require('postcss-banner')({
                    banner: grunt.template.process('! <%= package.title %> - <%= grunt.template.today("yyyy-mm-dd") %> ', { data: options })
                })
            ]
        },
        charcoal: {
            files: [
                {
                    expand:  true,
                    cwd:     '<%= paths.css.dist %>',
                    src:     [ '**/*.css', '!**/*.min.css' ],
                    dest:    '<%= paths.css.dist %>'
                }
            ]
        }
    }
};
