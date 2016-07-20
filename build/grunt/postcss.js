module.exports = {
    options: {
        processors: [
            require('autoprefixer')({
                browsers: ['last 2 versions', '> 1%', 'ie >= 9']
            }),
        ]
    },
    charcoal: {
        files  : [
            {
                src    : ['assets/dist/styles/*.css'],
                dest   : 'assets/dist/styles/',
                expand : true,
                flatten: true
            }
        ]
    }
};
