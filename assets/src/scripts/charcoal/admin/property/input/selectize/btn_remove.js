/* global Selectize */
Selectize.define('btn_remove', function (options) {
    options = $.extend({
        label: '<span class="glyphicon glyphicon-trash"></span>',
        title: 'Remove',
        className: 'btn-remove',
        append: true,
    }, options);

    this.require('buttons');

    var multiUpdate = function (thisRef, options) {
        var self = thisRef;
        self.addButton(thisRef, options, function (e) {
            e.preventDefault();

            if (self.isLocked) {
                return;
            }

            var $item = $(e.currentTarget).parent();
            self.setActiveItem($item);
            if (self.deleteSelection()) {
                self.setCaret(self.items.length);
            }
        });
    };

    if (this.settings.mode !== 'single') {
        multiUpdate(this, options);
    }
});
