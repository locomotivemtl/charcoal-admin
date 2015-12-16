module.exports = {
    options: {
        plugins: [
            { removeViewBox: false },
            { cleanupIDs: false },
            { convertPathData: false },
            { mergePaths: false },
            { convertShapeToPath: false },
            { cleanupNumericValues: false },
            { convertTransform: false },
            { removeUselessStrokeAndFill: false },
            { removeTitle: true },
            { removeDesc: true }
        ]
    },
    dist: {
        expand: true,
        cwd: 'assets/dist/images',
        src: '*.svg',
        dest: 'assets/dist/images/',
        ext: '.svg',
        extDot: 'first'
    }
};
