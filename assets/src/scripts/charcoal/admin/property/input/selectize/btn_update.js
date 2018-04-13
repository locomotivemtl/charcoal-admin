/* global Selectize */
Selectize.define('btn_update', function (options) {
    options = $.extend({
        label: '<span class="fa fa-pencil"></span>',
        title: 'Update',
        className: 'selectize-button-update',
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

            self.trigger('update_item',{
                item: $item,
                value: $item.eq(0).data('value'),
                callback: function (item) {
                    if (item && item.value) {
                        self.updateOption(item.value, item);
                    }
                }
            });
        });
    };

    if (this.settings.mode !== 'single') {
        multiUpdate(this, options);
    }
});
