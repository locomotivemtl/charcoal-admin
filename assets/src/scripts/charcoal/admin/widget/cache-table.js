/**
 * Cache Summary Widget
 */

;(function ($, Admin) {
    'use strict';

    var Widget = function (opts) {
        Charcoal.Admin.Widget_Cache.call(this, opts);
    };

    Widget.prototype            = Object.create(Charcoal.Admin.Widget_Cache.prototype);
    Widget.prototype.contructor = Widget;
    Widget.prototype.parent     = Charcoal.Admin.Widget_Cache.prototype;

    Widget.prototype.init = function () {
        Charcoal.Admin.Widget_Cache.prototype.init.call(this);

        this.element().find('[data-toggle="table"]').bootstrapTable();
    };

    Widget.prototype.destroy = function () {
        Charcoal.Admin.Widget_Cache.prototype.destroy.call(this);

        this.element().find('[data-toggle="table"]').bootstrapTable('destroy');
    };

    Admin.Widget_Cache_Psr_Table = Widget;

}(jQuery, Charcoal.Admin));
