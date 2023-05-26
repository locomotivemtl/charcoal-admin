/**
 * Map Widget
 *
 * According lat, lon or address must be specified
 * Styles might be defined as well.
 *
 * @param {Object} opts - Options for widget
 */
Charcoal.Admin.Widget_Map = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);

    this._controller;

    return this;
};

Charcoal.Admin.Widget_Map.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Map.prototype.constructor = Charcoal.Admin.Widget_Map;
Charcoal.Admin.Widget_Map.prototype.parent = Charcoal.Admin.Widget.prototype;

/**
 * Called automatically by the component manager
 * Instantiation of pretty much every thing you want!
 *
 * @return {this}
 */
Charcoal.Admin.Widget_Map.prototype.init = function () {
    Charcoal.Admin.maps.whenMapsApiReady(this.init_map.bind(this));

    return this;
};

Charcoal.Admin.Widget_Map.prototype.init_map = function () {
    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-div').get(0),
        this.get_controller_options()
    );

    this.controller().set_styles([
        {
            featureType: 'poi',
            elementType: 'all',
            stylers: [
                {
                    visibility: 'off'
                }
            ]
        }
    ]);

    this.controller().remove_focus();
    this.controller().init();
};

Charcoal.Admin.Widget_Map.prototype.controller = function () {
    return this._controller;
};

Charcoal.Admin.Widget_Map.prototype.coords = function () {
    return this.opts('coords');
};

Charcoal.Admin.Widget_Map.prototype.get_controller_options = function () {
    return {
        use_clusterer:  false,
        map:            this.get_map_options(),
        places:         this.get_places(),
        default_styles: this.get_default_styles()
    };
};

Charcoal.Admin.Widget_Map.prototype.get_map_options = function () {
    return {
        center: {
            lat: this.opts('coords')[0],
            lng: this.opts('coords')[1]
        },
        zoom: 14
    };
};

Charcoal.Admin.Widget_Map.prototype.get_places = function () {
    return {
        first: {
            type: 'marker',
            coords: this.coords()
        }
    };
};

Charcoal.Admin.Widget_Map.prototype.get_default_styles = function () {
    return {
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
};
