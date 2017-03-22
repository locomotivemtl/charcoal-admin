/**
 * Placeholder Text for TinyMCE
 *
 * @link https://github.com/mohan/tinymce-placeholder
 */

(function (tinymce) {

    var default_placeholder_attrs = {
        class: 'mce-placeholder-area mce-content-body'
    };

    tinymce.PluginManager.add('placeholder', function (editor) {
        editor.on('init', function () {
            var placeholder_text = editor.getElement().getAttribute('placeholder') || editor.settings.placeholder;
            if (placeholder_text === '' || placeholder_text === null || placeholder_text === undefined) {
                return;
            }

            var label = new Label(
                placeholder_text,
                editor.settings.placeholder_attrs || default_placeholder_attrs,
                editor.getContentAreaContainer()
            );

            onBlur();

            tinymce.DOM.bind(label.el, 'click', onFocus);
            editor.on('focus', onFocus);
            editor.on('blur', onBlur);
            editor.on('change', onBlur);
            editor.on('setContent', onBlur);
            editor.on('keydown', onKeydown);

            function onFocus() {
                if (!editor.settings.readonly) {
                    label.hide();
                }

                editor.execCommand('mceFocus', false, editor.id);
            }

            function onBlur() {
                if (editor.getContent() === '') {
                    label.show();
                } else {
                    label.hide();
                }
            }

            function onKeydown() {
                label.hide();
            }
        });

        var Label = function (text, attrs, area) {
            tinymce.DOM.setStyle(area, 'position', 'relative');

            // Create label el
            this.el = tinymce.DOM.add(
                area,
                editor.settings.placeholder_tag || 'label',
                attrs,
                tinymce.DOM.decode(text)
            );
        };

        Label.prototype.hide = function () {
            tinymce.DOM.setStyle(this.el, 'display', 'none');
        };

        Label.prototype.show = function () {
            tinymce.DOM.setStyle(this.el, 'display', '');
        };
    });

}(window.tinymce));
