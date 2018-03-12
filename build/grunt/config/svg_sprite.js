module.exports = {
    options: {
        svg: {
            xmlDeclaration:       false,
            namespaceIDs:         true,
            doctypeDeclaration:   false,
            cleanupNumericValues: true,
            removeTitle:          true,
            removeDesc:           true
        }
    },
    dist: {
        expand:  true,
        cwd:     '<%= paths.img.src %>/svgs',
        src:     [ '**/*.svg', '!svgs.svg' ],
        dest:    '<%= paths.img.dist %>',
        options: {
            mode: {
                symbol: {
                    dest:    '.',
                    sprite:  'svgs.svg',
                    example: false
                }
            }
        }
    }
}
