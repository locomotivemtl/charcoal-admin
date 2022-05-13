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

    Admin.Widget_Cache_Psr_Summary = Widget;

}(jQuery, Charcoal.Admin));
