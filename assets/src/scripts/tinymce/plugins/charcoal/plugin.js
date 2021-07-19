/* eslint-disable consistent-this */
/**
 * Charcoal Enhancements for TinyMCE
 *
 * @copyright Locomotive Inc.
 * @license   LGPL
 */

(function (tinymce, $) {

    var each = tinymce.util.Tools.each;

    tinymce.create('tinymce.plugins.Charcoal', {
        /**
         * Plugin Initialization
         *
         * @method init
         * @param  {tinymce.Editor} editor - The active editor.
         * @param  {string}         url    - The editor's base URL.
         */
        init: function (editor) {
            editor.on('init', function () {
                var doc, dom, bodyClassNames;

                doc = editor.getDoc();
                dom = editor.dom;

                bodyClassNames = [ 'charcoal-editor' ];

                /** Backwards compatibility for projects that use this. */
                bodyClassNames.push('mceContentBody');

                if (editor.getParam('directionality') === 'rtl') {
                    bodyClassNames.push('rtl');
                    dom.setAttrib(doc.documentElement, 'dir', 'rtl');
                }

                dom.setAttrib(doc.documentElement, 'lang', editor.getParam('root_lang_attr'));

                each(bodyClassNames, function (className) {
                    if (className) {
                        dom.addClass(doc.body, className);
                    }
                });

                if (editor.getParam('autohide_toolbar')) {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-toolbar-grp').hide();
                }

                if (editor.getParam('autohide_menubar')) {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-menubar').hide();
                }
            });

            if (editor.getParam('autohide_toolbar')) {
                editor.on('focus', function () {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-toolbar-grp').show();
                });

                editor.on('blur', function () {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-toolbar-grp').hide();
                });
            }

            if (editor.getParam('autohide_menubar')) {
                editor.on('focus', function () {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-menubar').show();
                });

                editor.on('blur', function () {
                    $(editor.getContentAreaContainer().parentElement).find('div.mce-menubar').hide();
                });
            }

            // Prevent resizing of images if the 'image_dimensions' setting is set to FALSE
            if (editor.getParam('image_dimensions') === false) {
                editor.on('NodeChange', function (event) {
                    if (event && event.element.nodeName.toLowerCase() === 'img') {
                        tinymce.DOM.setAttribs(event.element, { width: null, height: null });
                    }
                });
            }
        }
    });

    tinymce.PluginManager.add('charcoal', tinymce.plugins.Charcoal, tinymce.plugins.Charcoal.dependencies);

}(window.tinymce, window.jQuery));
