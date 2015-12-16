module.exports = {
    options: {
        // the banner is inserted at the top of the output
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
    },
    app: {
        files: {
            'assets/dist/scripts/charcoal.admin.min.js': ['<%= concat.admin.dest %>']
        }
    },
    vendors: {
        files: {
            'assets/dist/scripts/charcoal.admin.vendors.min.js': ['<%= concat.vendors.dest %>']
        }
    }
};
