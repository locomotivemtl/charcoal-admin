module.exports = {
    src:{
        src: ['src/**/*.php']
    },
    options: {
        //bin: '<%= directories.composerBin %>/phpcs',
        standard: 'phpcs.xml',
        //ignore: 'database',
        extensions: 'php',
        showSniffCodes: true
    }
};
