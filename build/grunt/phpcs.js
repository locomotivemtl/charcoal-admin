module.exports = {
    src:{
        dir:[
            'src/**/*.php'
        ]
    },
    tests: {
        dir:[
            'tests/**/*.php'
        ]
    },
    options: {
        //bin: '<%= directories.composerBin %>/phpcs',
        standard: 'phpcs.xml',
        //ignore: 'database',
        extensions: 'php',
        showSniffCodes: true
    }
};
