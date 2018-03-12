module.exports = {
    options: {
        open:      false,
        proxy:     'charcoal-admin.test',
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
