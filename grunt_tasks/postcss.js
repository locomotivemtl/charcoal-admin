module.exports = {
    options: {
        processors: [
            require('autoprefixer-core')({
                browsers: ['last 2 versions', '> 1%', 'ie >= 9']
            }),
        ]
    },
    files: [
        {
            src : ['assets/dist/styles/*.css'],
            dest : 'assets/dist/styles/',
            expand : true,
            flatten : true
        }
    ]
};
