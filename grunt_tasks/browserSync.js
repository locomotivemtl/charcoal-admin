module.exports = {
    dev: {
        bsFiles: {
            src : [
                 'assets/dist/styles/*.css'
                ,'assets/dist/scripts/*.js'
                ,'assets/dist/images/*'
                ,'templates/charcoal/admin/**/**/*'
            ]
        },
        options: {
            proxy: "localhost",
            watchTask: true,
            notify: false
        }
    }
}
