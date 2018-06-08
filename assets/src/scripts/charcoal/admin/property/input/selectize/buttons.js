/* global Selectize */
Selectize.define('buttons', function () {
    /**
     * Escapes a string for use within HTML.
     *
     * @param {string} str
     * @returns {string}
     */
    var escape_html = function (str) {
        return (str + '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    };

    this.buttonOffset = 40;
    this.currentButtonOffset = 0;

    this.addButton = function (thisRef, options, callback) {
        var self = thisRef;
        var html = '<button type="button" ' +
            'class="selectize-button ' + options.className + '" ' +
            'tabindex="-1" ' +
            'title="' + escape_html(options.title) + '" ' +
            'style="right:' + self.currentButtonOffset + 'px">' +
            options.label + '</a>';

        self.currentButtonOffset += self.buttonOffset;

        /**
         * Appends an element as a child (with raw HTML).
         *
         * @param {string} html_container
         * @param {string} html_element
         * @return {string}
         */
        var append = function (html_container, html_element) {
            var $item = $(html_container);

            if ($item.hasClass('item')) {
                $item.append($(html_element));

                return $item[0];
            }

            return html_container;
        };

        var adjustContainerPadding = function (html_container, offset) {
            var $item = $(html_container);

            if ($item.hasClass('item')) {
                $item.css('padding-right', (offset + 8) + 'px');

                return $item[0];
            }

            return html_container;
        };

        thisRef.setup = (function () {
            var original = self.setup;
            return function () {
                // override the item rendering method to add the button to each
                if (options.append) {
                    var render_item = self.settings.render.item;

                    self.settings.render.item = function () {
                        return append(
                            adjustContainerPadding(
                                render_item.apply(thisRef, arguments),
                                self.currentButtonOffset
                            ),
                            html
                        );
                    };
                }

                original.apply(thisRef, arguments);

                // Prevent drag and drop while pressing button
                thisRef.$control.on('mousedown', '.' + options.className, function (e) {
                    e.preventDefault();
                    var sortable = self.$control.data('ui-sortable');

                    if (sortable) {
                        self.$control.sortable('disable');

                        $(document).on('mouseup.sortable', function () {
                            $(document).off('mouseup.sortable');
                            self.$control.sortable('enable');
                        });
                    }
                });

                // add event listener to button
                thisRef.$control.on('click', '.' + options.className, function (e) {
                    if (typeof callback === 'function') {
                        callback(e);
                    }
                });
            };
        })();
    };
});
