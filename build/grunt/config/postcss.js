module.exports = function (grunt, options)
{
    return {
        options: {
            map: false,
            processors: [
                require('autoprefixer')(),
                require('postcss-banner')({
                    banner: grunt.template.process('<%= package.name %>', { data: options }),
                    inline: true,
                    important: true
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
