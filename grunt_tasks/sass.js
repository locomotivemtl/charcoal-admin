module.exports = {
    options: {
        sourceMap: false
    },
    dist: {
        files: {
            'assets/dist/styles/main.css': 'assets/src/styles/**/**/main.scss'
        }
    }
};
