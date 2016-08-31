/**
 * Charcoal Enhancements for TinyMCE
 *
 * @copyright Locomotive Inc.
 * @license   LGPL
 */

(function (tinymce, $) {

    var each, extend, isArray;

    each    = tinymce.util.Tools.each;
    extend  = tinymce.util.Tools.extend;
    isArray = tinymce.util.Tools.isArray;

    tinymce.create('tinymce.plugins.Charcoal', {
        dependencies: ['advlist'],
        listItems: ['bullist', 'numlist', 'outdent', 'indent'],
        alignItems: ['alignleft', 'aligncenter', 'alignright', 'alignjustify'],
        editor: null,

        /**
         * Plugin Initialization
         *
         * @method init
         * @param  {tinymce.Editor} editor
         * @param  {string}         url
         */
        init: function (editor) {
            var that, listMenuItems, alignMenuItems;

            that = this;
            this.editor = editor;

            listMenuItems  = this.filterItems(this.listItems);
            alignMenuItems = this.filterItems(this.alignItems);

            editor.addMenuItem('lists', {
                text: 'Lists',
                context: 'format',
                menu: listMenuItems
            });

            editor.addMenuItem('alignment', {
                text: 'Alignment',
                context: 'format',
                menu: alignMenuItems
            });

            editor.addButton('alignment', {
                type: 'menubutton',
                title: 'Alignment',
                icon: that.getDefaultAlignment(),
                menu: alignMenuItems,
                /**
                 * Fires after the control has been rendered.
                 *
                 * @event AlignmentButton#onPostRender
                 * @param {tinymce.Event} event
                 * @this  {tinymce.ui.MenuButtonPrint}
                 * @fires AlignmentButton#NodeChange
                 */
                onPostRender: function (/* event */) {
                    var ctrl = this;

                    /**
                     * Fires when the selection is moved to a new location
                     * or is the DOM is updated by some command.
                     *
                     * @event AlignmentButton#NodeChange
                     * @this  {tinymce.Editor}
                     * @param {tinymce.Event} event
                     */
                    editor.on('NodeChange', function (event) {
                        var node, match;

                        match = this.formatter.matchAll(that.alignItems);

                        if (isArray(match)) {
                            match = match.pop();
                        }

                        if (match) {
                            if (event.element.nodeName === 'IMG') {
                                node = editor.dom.getParent(event.element, 'figure') || event.element;

                                if (match === 'alignnone') {
                                    ctrl.active(!/\bu-align-(left|center|right)\b/.test(node.className));
                                } else {
                                    ctrl.active(editor.dom.hasClass(node, match));
                                }
                            }
                        } else {
                            match = that.getDefaultAlignment();
                        }

                        ctrl.icon(match);

                    });
                }
            });

            editor.on('init', function () {
                var env, doc, dom, body_class;

                env = tinymce.Env;
                doc = editor.getDoc();
                dom = editor.dom;

                body_class = ['charcoal-editor'];

                /** Backwards compatibility for projects that use this. */
                body_class.push('mceContentBody');

                if (editor.getParam('directionality') === 'rtl') {
                    body_class.push('rtl');
                    dom.setAttrib(doc.documentElement, 'dir', 'rtl');
                }

                dom.setAttrib(doc.documentElement, 'lang', editor.getParam('root_lang_attr'));

                if (env.ie) {
                    if (parseInt(env.ie, 10) === 9) {
                        body_class.push('lt-ie10');
                    } else if (parseInt(env.ie, 10) === 8) {
                        body_class.push('lt-ie9');
                    } else if (env.ie < 8) {
                        body_class.push('lt-ie8');
                    }
                }

                each(body_class, function (class_name) {
                    if (class_name) {
                        dom.addClass(doc.body, class_name);
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
        },

        /**
         * Retrieve an alignment format based on the direction
         * of content flow within the editor.
         *
         * @method   getDefaultAlignment
         * @requires tinymce.Editor
         * @return   {string}
         */
        getDefaultAlignment: function () {
            if (this.editor.getParam('directionality') === 'rtl') {
                return 'alignright';
            } else {
                return 'alignleft';
            }
        },

        /**
         * Retrieve an alignment format based on the direction
         * of content flow within the editor.
         *
         * @method   filterItems
         * @requires tinymce.Editor.buttons
         * @requires tinymce.util.Tools.isArray
         * @requires tinymce.util.Tools.extend
         * @requires tinymce.util.Tools.each
         *
         * @param    {string|string[]} items
         * @return   {mixed[]}
         */
        filterItems: function (items) {
            var editor, filtered;

            editor   = this.editor;
            filtered = [];

            if (!isArray(items)) {
                items = items.split(' ');
            }

            each(items, function (name) {
                var item;

                if (name === '|') {
                    filtered.push({ text: '-' });
                } else if (name in editor.buttons) {
                    item = extend({}, editor.buttons[name]);

                    item.text = item.tooltip;
                    delete item.tooltip;
                    delete item.type;

                    filtered.push(item);
                }
            });

            return filtered;
        }
    });

    tinymce.PluginManager.add('charcoal', tinymce.plugins.Charcoal, tinymce.plugins.Charcoal.dependencies);

}(window.tinymce, window.jQuery));
