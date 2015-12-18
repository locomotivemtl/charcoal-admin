module.exports = {
    combine: {
        files: [{
            expand: true,
            cwd: 'assets/dist/styles/',
            src: '*.css',
            dest: 'assets/dist/styles/'
        }]
    }
};
