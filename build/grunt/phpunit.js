module.exports = {
    src: {
        dir: 'tests/'
    },
    options: {
        bin: 'vendor/bin/phpunit',
        colors: true,
        coverageHtml: 'tests/tmp/report/',
        // coverageText: 'tests/tmp/report/',
        testdoxHtml: 'tests/tmp/testdox.html',
        testdoxText: 'tests/tmp/testdox.text',
        verbose: true,
        debug: false,
        bootstrap: 'tests/bootstrap.php'
    }
};
