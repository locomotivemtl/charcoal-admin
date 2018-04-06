/**
 * Grunt Task Wrangler
 *
 * @copyright Copyright © 2016 Locomotive
 * @license   Licensed under the MIT license.
 */

'use strict';

module.exports = function (grunt)
{
    var path = require('path');

    require('load-grunt-config')(grunt, {
        configPath: path.join(process.cwd(), 'build/grunt/config'),
        data: {
            paths: {
                grunt:    'build/grunt',
                npm:      'node_modules',
                composer: 'vendor',
                dist:     'assets/dist',
                prod:     '../../../www/assets/admin',
                fonts:    'assets/dist/fonts',
                elfinder: {
                    src:  'vendor/studio-42/elfinder',
                    dist: 'assets/dist/elfinder'
                },
                js: {
                    src:  'assets/src/scripts',
                    dist: 'assets/dist/scripts'
                },
                css: {
                    src:  'assets/src/styles',
                    dist: 'assets/dist/styles'
                },
                img: {
                    src:  'assets/src/images',
                    dist: 'assets/dist/images'
                }
            }
        }
    });
};
