module.exports = {
    options: {
        open      : false,
        proxy     : 'localhost',
        port      : 3000,
        watchTask : true,
        notify    : false
    },
    dev: {
        bsFiles : {
            src : [
                'templates/charcoal/admin/**/*',
                '../../../www/assets/admin/**/*'
            ]
        }
    }
};
