module.exports = {
    options: {
        // the banner is inserted at the top of the output
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("dd-mm-yyyy") %> */\n'
    },
    dist: {
        files: {
            'assets/dist/scripts/charcoal.admin.min.js': ['<%= concat.dist.dest %>']
        }
    }
};
