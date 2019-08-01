module.exports = {
    options: {
        open:      false,
        proxy:     'charcoal.test',
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
