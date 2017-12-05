module.exports = {
    options: {
        open      : false,
        proxy     : 'charcoal-project-boilerplate.test',
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
