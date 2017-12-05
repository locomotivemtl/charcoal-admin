/**
 * Placeholder Text for TinyMCE
 *
 * Based on {@link https://github.com/mohan/tinymce-placeholder Mohan's implementation}
 */

(function (tinymce) {

    tinymce.PluginManager.add('placeholder', function (editor) {
        editor.on('init', function () {
            var placeholder = editor.getElement().getAttribute('placeholder') || editor.settings.placeholder;
            if (
                typeof placeholder === 'undefined' ||
                placeholder === null ||
                placeholder === '' ||
                placeholder === [] ||
                placeholder === {}
            ) {
                return;
            }

            if (typeof placeholder === 'string') {
                placeholder = {
                    text: placeholder
                };
            }

            if (typeof placeholder.attr === 'undefined') {
                placeholder.attr = editor.settings.placeholder_attr || { class: 'mce-placeholder-area' };
            }
            if (typeof placeholder.tag === 'undefined') {
                placeholder.tag = editor.settings.placeholder_tag  || 'label';
            }

            var label = new Label(
                placeholder,
                editor.getContentAreaContainer() || editor.getBody()
            );

            tinymce.DOM.bind(label.el, 'click', onFocus);

            // When focus is in main editor window
            editor.on('focus', onFocus);

            // When focus is outside of main editor area
            editor.on('blur', onBlur);

            // Whenever content is changed, including when a toolbar item is pressed (bold, italic, bullets, etc)
            editor.on('change', onChange);

            // Called when switching between Visual/Text
            editor.on('setcontent', onSetContent);

            function onFocus() {
                console.log('onFocus', label.text);
                label.hide();
                editor.focus();
            }

            function onBlur() {
                console.log('onBlur', label.text);
                label.check();
            }

            function onChange() {
                console.log('onChange', label.text);
                label.check();
            }

            function onSetContent() {
                console.log('onSetContent', label.text);
                label.check();
            }

            // Add 1 second timeout to delay execution until after
            // vendor plugings adjust the toolbars
            setTimeout(function () {
                console.log('timeOut', label.text);
                label.check();
            }, 1000);
        });

        var Label = function (data, area) {
            this.data = data;
            this.text = data.text;

            tinymce.DOM.setStyle(area, 'position', 'relative');

            // Create label el
            this.el = tinymce.DOM.add(area, data.tag, data.attr, tinymce.DOM.decode(data.text));
        };

        Label.prototype.hide = function () {
            tinymce.DOM.setStyle(this.el, 'display', 'none');
        };

        Label.prototype.show = function () {
            tinymce.DOM.setStyle(this.el, 'display', '');
        };

        Label.prototype.check = function () {
            var textContent = editor.getBody().textContent.replace(this.el.textContent, '').trim();
            if (textContent === '') {
                this.show();
            } else {
                this.hide();
            }
        };
    });

}(window.tinymce));
