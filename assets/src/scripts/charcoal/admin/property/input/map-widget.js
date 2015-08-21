/***
* `charcoal/admin/property/input/map-widget`
* Property_Input_Map_Widget Javascript class
*
*/
Charcoal.Admin.Property_Input_Map_Widget = function (data)
{
    // Scope
    var that = this;

    // Input type
    this.input_type = 'charcoal/admin/property/input/map-widget';

    // Controller
    this.controller = undefined;

    // Set options
    this.data = data;

    if (typeof google === 'undefined') {
        // If google is undefined,
        window._tmp_google_onload_function = function () {
            that.load_plugin();
        };

        $.getScript(
            'http://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' +
            '&language=fr&callback=_tmp_google_onload_function',
            function () {}
        );
    } else {
        that.init();
    }

};

Charcoal.Admin.Property_Input_Map_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Map_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Map_Widget;
Charcoal.Admin.Property_Input_Map_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Map_Widget.prototype.load_plugin = function ()
{
    // Scope
    var that = this;

    // Remove unnecessary tmp function
    delete window._tmp_google_onload_function;

    // Add the actual plugin
    $.getScript(Charcoal.Admin.admin_url() +
        '../../vendor/locomotivemtl/charcoal-admin/bower_components/bb-gmap/assets/scripts/dist/min/gmap.min.js',
        function () {
            that.init();
        });
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.init = function ()
{
    if (typeof BB === 'undefined' || typeof google === 'undefined') {
        // We don't have what we need
        console.error('Plugins not loaded');
        return false;
    }

    var _data = this.data;

    // Shouldn't happen at that point
    if (typeof _data.id === 'undefined') {
        console.error('Missing ID');
    }

    // Create new map instance
    this.controller = new window.BB.gmap.controller(
        document.getElementById(this.data.id),
        {
            use_clusterer: true,
            map: {
                center: {
                    x: 45.3712923,
                    y: -73.9820994
                },
                zoom: 14,
                mapType: 'roadmap',
                coordsType: 'inpage', // array, json? (vs ul li)
                map_mode: 'default'
            }
        }
    );

    // Init new map instance
    this.controller.init();

};
