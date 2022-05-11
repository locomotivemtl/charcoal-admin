/**
 * elFinder Client for Charcoal
 *
 * Main script for RequireJS.
 *
 * Renamed from `main.default.js` to configure elFinder.
 * e.g. `<script data-main="./main.js" src="./require.js"></script>`
 */

(function () {
    'use strict';
    var jqver = '3.4.1',    // jQuery
        uiver = '1.12.1',   // jQuery UI version
        cdnjs = 'https://cdnjs.cloudflare.com/ajax/libs',

        // Detect language (optional)
        lang = (function () {
            var locq = window.location.search,
                fullLang, locm, lang;
            if (locq && (locm = locq.match(/lang=([a-zA-Z_-]+)/))) {
                // detection by url query (?lang=xx)
                fullLang = locm[1];
            } else {
                // detection by browser language
                fullLang = (navigator.browserLanguage || navigator.language || navigator.userLanguage);
            }
            lang = fullLang.substr(0,2);
            switch (lang) {
                case 'pt':
                    lang = 'pt_BR';
                    break;

                case 'ug':
                    lang = 'ug_CN';
                    break;

                case 'zh':
                    lang = (fullLang.substr(0,5).toLowerCase() === 'zh-tw') ? 'zh_TW' : 'zh_CN';
                    break;
            }

            return lang;
        }()),

        // Start elFinder (REQUIRED)
        start = function (elFinder, editors, config, translations) {
            // Load jQuery UI CSS
            elFinder.prototype.loadCss(
                cdnjs + '/jqueryui/' + uiver + '/themes/smoothness/jquery-ui.css'
            );

            $(function () {
                var optEditors = {
                        commandsOptions: {
                            edit: {
                                editors: Array.isArray(editors) ? editors : []
                            }
                        }
                    },
                    opts = {
                        theme: null,
                        themeOptions: {},
                        cssAutoLoad: false
                    };

                // Interpretation of "elFinderConfig"
                if (config && config.managers) {
                    $.each(config.managers, function (id, mOpts) {
                        opts = Object.assign(opts, config.defaultOpts || {});

                        // Merge theme settings
                        if (
                            opts.theme &&
                            opts.themeOptions &&
                            opts.themeOptions[opts.theme] &&
                            opts.themeOptions[opts.theme].cssAutoLoad
                        ) {
                            // Load elFinder CSS
                            elFinder.prototype.loadCss(
                                require.toUrl('css/elfinder.min.css')
                            );

                            opts = $.extend(true, opts, opts.themeOptions[opts.theme]);
                        } else {
                            opts.cssAutoLoad = true;
                        }

                        // Editors merge to opts.commandOptions.edit
                        try {
                            mOpts.commandsOptions.edit.editors = mOpts.commandsOptions.edit.editors.concat(
                                editors || []
                            );
                        } catch (e) {
                            Object.assign(mOpts, optEditors);
                        }

                        // Disable commands from custom-list of commands
                        if (opts.disabledCommands) {
                            if (!opts.commands) {
                                var disabled = opts.disabledCommands,
                                    commands = elFinder.prototype._options.commands;

                                if ($.inArray('*', commands) !== -1) {
                                    commands = Object.keys(elFinder.prototype.commands);
                                }

                                $.each(disabled, function (i, cmd) {
                                    var idx = $.inArray(cmd, commands);
                                    delete commands[idx];
                                });

                                opts.commands = commands;
                            }

                            delete opts.disabledCommands;
                        }

                        if (opts.cssAutoLoad) {
                            // Convert CSS setting to array
                            if (typeof opts.cssAutoLoad === 'string') {
                                opts.cssAutoLoad = [ opts.cssAutoLoad ];
                            }

                            // Import custom CSS
                            if (Array.isArray(opts.cssAutoLoad)) {
                                elFinder.prototype.loadCss(
                                    opts.cssAutoLoad.map(function (uri) {
                                        return require.toUrl(uri);
                                    })
                                );
                            }

                            opts.cssAutoLoad = false;
                        }

                        // Make elFinder
                        $('#' + id).elfinder(
                            // 1st Arg - options
                            $.extend(true, { lang: lang }, opts, mOpts || {}),
                            // 2nd Arg - before boot up function
                            function (fm /* , extraObj */) {
                                // `init` event callback function
                                fm.bind('init', function () {
                                    // Import third-party translations
                                    if (translations) {
                                        $.extend(fm.messages, translations.en || {}, translations[fm.lang] || {});
                                    }

                                    // Optional for Japanese decoder "encoding-japanese"
                                    if (fm.lang === 'ja') {
                                        require(
                                            [ 'encoding-japanese' ],
                                            function (Encoding) {
                                                if (Encoding && Encoding.convert) {
                                                    fm.registRawStringDecoder(function (s) {
                                                        return Encoding.convert(s, {
                                                            to: 'UNICODE',
                                                            type: 'string'
                                                        });
                                                    });
                                                }
                                            }
                                        );
                                    }
                                });
                            }
                        );
                    });
                } else {
                    window.alert('"elFinderConfig" object is wrong.');
                }
            });
        },

        // JavaScript loader (REQUIRED)
        load = function () {
            // 1. Load text, image editors
            // 2. Optional preview for GoogleApps contents on the GoogleDrive volume
            require(
                [
                    'elfinder',
                    'editors.default',         // [1]
                    'elFinderConfig',
                    'elFinderL10n'
                    // 'quicklook.googledocs'  // [2]
                ],
                start,
                function (error) {
                    window.alert(error.message);
                }
            );
        },

        // is IE8? for determine the jQuery version to use (optional)
        ie8 = (typeof window.addEventListener === 'undefined' &&
               typeof document.getElementsByClassName === 'undefined');

    // Configure RequireJS (REQUIRED)
    require.config({
        // baseUrl: './js',
        paths: {
            'jquery':                cdnjs + '/jquery/' + (ie8 ? '1.12.4' : jqver) + '/jquery.min',
            'jquery-ui':             cdnjs + '/jqueryui/' + uiver + '/jquery-ui.min',
            'elfinder':              'js/elfinder.min',
            'editors.default':       'js/extras/editors.default.min',
            'quicklook.googledocs':  'js/extras/quicklook.googledocs.min',
            'encoding-japanese':     'https://cdn.rawgit.com/polygonplanet/encoding.js/1.0.26/encoding.min'
        },
        waitSeconds: 10 // optional
    });

    load();

}());
