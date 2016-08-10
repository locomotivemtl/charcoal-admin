module.exports = {
    options: {
        sourceMap: false
    },
    app: {
        files: {
            'assets/dist/styles/charcoal.admin.css': 'assets/src/styles/**/charcoal.admin.scss'
        }
    },
    vendors: {
        files: {
            'assets/dist/styles/charcoal.admin.vendors.css': 'assets/src/styles/**/charcoal.admin.vendors.scss'
        }
    },
    elfinder: {
        files: {
            'assets/dist/styles/vendors/elfinder.css': 'assets/src/styles/**/elfinder.scss'
        }
    }
};
