module.exports = {
    options: {
        open:      false,
        proxy:     'charcoal-redux-greenbeaver.test',
        port:      3000,
        watchTask: true,
        notify:    false
    },
    dev: {
        bsFiles: {
            src: [
                'templates/charcoal/admin/**/*',
                '<%= paths.prod %>/**/*'
            ]
        }
    }
};
