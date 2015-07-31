module.exports = {
    dev: {
        bsFiles: {
            src : [
                'templates/charcoal/admin/**/*',
                '../../../www/assets/admin/**/*'
            ]
        },
        options: {
            proxy: "localhost",
            watchTask: true,
            notify: false
        }
    }
};
