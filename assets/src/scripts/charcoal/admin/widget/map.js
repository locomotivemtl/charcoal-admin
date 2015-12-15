/**
* Map sidebar
*
* According lat, lon or address must be specified
* Styles might be defined as well.
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Map = function ()
{
    this._controller = undefined;
    this.widget_type = 'charcoal/admin/widget/map';

    return this;
};

Charcoal.Admin.Widget_Map.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Map.prototype.constructor = Charcoal.Admin.Widget_Map;
Charcoal.Admin.Widget_Map.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
* Called automatically by the component manager
* Instantiation of pretty much every thing you want!
*
* @return this
*/
Charcoal.Admin.Widget_Map.prototype.init = function ()
{
    var that = this;

    if (typeof google === 'undefined') {
        // If google is undefined,
        window._tmp_google_onload_function = function () {
            that.activate_map();
        };

        $.getScript(
            'https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false' +
            '&language=fr&callback=_tmp_google_onload_function',
            function () {}
        );
    } else {
        that.activate_map();
    }

    return this;
};

Charcoal.Admin.Widget_Map.prototype.activate_map = function ()
{
    var default_styles = {
        strokeColor: '#000000',
        strokeOpacity: 0.8,
        strokeWeight: 3,
        fillColor: '#ffffff',
        fillOpacity: 0.35,
        hover: {
            strokeColor: '#000000',
            strokeOpacity: 1,
            strokeWeight: 2,
            fillColor: '#ffffff',
            fillOpacity: 0.5
        },
        focused: {
            fillOpacity: 0.8
        }
    };

    var map_options = {
        default_styles: default_styles,
        use_clusterer: false,
        map: {
            center: {
                x: this.opts('coords')[0],
                y: this.opts('coords')[1]
            },
            zoom: 14,
            mapType: 'roadmap',
            coordsType: 'inpage', // array, json? (vs ul li)
            map_mode: 'default'
        },
        places:{
            first:{
                type: 'marker',
                coords: this.coords(),
            }
        }
    };

    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-maker-map').get(0),
        map_options
    );

    this.controller().set_styles([{ featureType:'poi',elementType:'all',stylers:[{ visibility:'off' }] }]);

    this.controller().remove_focus();
    this.controller().init();

};

Charcoal.Admin.Widget_Map.prototype.controller = function ()
{
    return this._controller;
};

Charcoal.Admin.Widget_Map.prototype.coords = function ()
{
    return this.opts('coords');
};
