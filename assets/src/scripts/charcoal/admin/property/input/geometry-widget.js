/***
 * `charcoal/admin/property/input/geometry-widget`
 * Property_Input_Geometry_Widget Javascript class
 *
 */
Charcoal.Admin.Property_Input_Geometry_Widget = function (data) {
    // Input type
    data.input_type = 'charcoal/admin/property/input/geometry-widget';

    Charcoal.Admin.Property.call(this, data);

    // Scope
    var that = this;

    // Controller
    this._controller = undefined;
    // Create uniq ident for every entities on the map
    this._object_inc = 0;
    this._startGeometry = false;

    this._map_options = data.data.map_options;
    // Never send multiple true to BB gmap
    this._map_options.multiple = false;

    var EVENT_NAMESPACE = 'geolocation';
    var EVENT = {
        GOOGLE_MAP_LOADED: 'google-map-loaded.' + EVENT_NAMESPACE
    };

    if (typeof google === 'undefined') {
        if (window._geolocation_tmp_google !== true) {
            window._geolocation_tmp_google = true;
            $.getScript(
                'https://maps.googleapis.com/maps/api/js?sensor=false' +
                '&callback=_geolocation_tmp_google_onload_function&key=' + this._map_options.api_key,
                function () {}
            );

            // If google is undefined,
            window._geolocation_tmp_google_onload_function = function () {
                document.dispatchEvent(new Event(EVENT.GOOGLE_MAP_LOADED));
            };
        }

        document.addEventListener(EVENT.GOOGLE_MAP_LOADED, function () {
            that.init();
        }, { once: true })
    }

};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Geometry_Widget;
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.init = function () {
    if (typeof window._tmp_google_onload_function !== 'undefined') {
        delete window._tmp_google_onload_function;
    }
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

    var default_styles = this.default_styles();
    var map_options = this.default_map_options();

    map_options = $.extend(true, map_options, this._map_options);

    // Get current map state from DB
    // This is located in the hidden input
    var current_value = this.element().find('input[type=hidden]').val();

    if (current_value) {
        // Parse the value
        var places = {
            object1: {
                ident:		'object1',
                paths:		this.reverse_translate_coords(current_value),
                editable:  true,
                draggable: true,
                type:      map_options.geometry_type,
                styles:    default_styles
            }
        };

        // Merge places with default styles
        var merged_places = {};
        var index = 0;
        for (var ident in places) {
            index++;
            merged_places[ ident ] = places[ ident ];
            merged_places[ ident ].styles = $.extend(places[ ident ].styles, default_styles);
        }

        if (merged_places) {
            map_options.places = merged_places;
        }

        if (index) {
            this._object_inc = index;
        }
    }

    this.$map_maker = this.element().find('.js-map-maker');

    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-maker-map').get(0),
        map_options
    );

    // Init new map instance
    this.controller().init().ready(
        function (ctrl) {
            ctrl.fit_bounds();
            ctrl.remove_focus();
        }
    );

    this.controller().set_styles([
        {
            featureType: 'poi',
            elementType: 'all',
            stylers: [
                { visibility: 'off' }
            ]
        }
    ]);

    this.controller().remove_focus();

    // link related properties to current widget
    this.link_related_property();

    // Scope
    var that = this;

    var key = 'object';

    var type = map_options.geometry_type;
    that.hide_marker_toolbar();

    var object_id = key + that.object_index();

    while (that.controller().get_place(object_id)) {
        object_id = key + that.object_index();
    }

    this.element().on('click', function () {
        var raw = that.controller().export();
        if (raw && Object.keys(raw.places).length !== 0) {
            return false;
        }

        if (!that._startGeometry) {
            that._startGeometry = true;

            switch (type) {
                case 'marker':
                case 'line':
                case 'polygon':
                    that.controller().create_new(type, object_id);
                    break;
            }
        }
    });

    this.element().on('click', '.js-reset', function (e) {
        that._startGeometry = false;
        e.preventDefault();
        that.controller().reset();
    });
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.link_related_property = function () {
    var related_property = this.data.data.related_property;
    if (!related_property) {
        return false;
    }

    for (var obj in related_property) {
        switch (related_property[obj].obj_type) {
            case 'charcoal/admin/object/geometry-blueprint':
                this.related_object_geometry(obj);
                break;
        }
    }
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.related_object_geometry = function (obj) {
    // retrieve obj_type
    var type = this.data.data.related_property[obj].obj_type;
    if (!type) {
        return false;
    }

    var geometry_objects = [];
    var geometry_objects_request_done = false;
    var that = this;

    // retrieve data
    $.ajax({
        url: Charcoal.Admin.admin_url() + 'object/load',
        data: {
            obj_type: type
        },
        type: 'GET',
        error: function () {},
        success: function (res) {
            geometry_objects_request_done = true;
            geometry_objects = res.collection;
        }
    });

    // on select
    this.element().parents('fieldset').on('change', '[name="' + obj + '"]', function (event) {
        if (!geometry_objects_request_done) {
            return false;
        }
        that.controller().reset();

        for (var index in geometry_objects) {
            if (geometry_objects[index].id !== $(event.currentTarget).val()) {
                continue;
            }

            var geometry = geometry_objects[index].geometry;

            var default_styles = that.default_styles();
            var map_options = that.default_map_options();

            map_options = $.extend(true, map_options, that._map_options);

            var object1  = {
                paths:     that.reverse_translate_coords(geometry),
                editable:  true,
                draggable: true,
                type:      map_options.geometry_type,
                styles:    default_styles
            };

            that.controller().add_place('object1', object1);
            that.controller().fit_bounds();
        }
    });
};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.controller = function () {
    return this._controller;
};

/**
 * This is to prevent any ident duplication
 * Return {Int} Object index
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.object_index = function () {
    return ++this._object_inc;
};

/**
 * This is to retrieve the defaults map styles
 * Return {Object}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.default_styles = function () {
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

/**
 * This is to retrieve the default map options
 * Return {Object}
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.default_map_options = function () {
    return {
        default_styles: this.default_styles(),
        use_clusterer: false,
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
    };
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.display_marker_toolbar = function () {
    this.$map_maker.addClass('is-header-open');
};

Charcoal.Admin.Property_Input_Geometry_Widget.prototype.hide_marker_toolbar = function () {
    this.$map_maker.removeClass('is-header-open');
};

/**
 * @var array coords
 * @return string coords (fits for sql geoshit)
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.translate_coords = function (coords) {
    var i = 0;
    var total = coords.length;
    var ret = [];
    for (; i < total; i++) {
        ret.push(coords[ i ].join(' '));
    }
    if (total) {
        // Duplicate first point!
        ret.push(coords[ 0 ].join(' '));
    }
    return ret.join(',');
};

/**
 * @var array coords
 * @return reverse string coords (fits for sql geoshit)
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.reverse_translate_coords = function (coords) {
    var first_level = coords.split(',');
    var i = 0;
    var total = first_level.length;
    for (; i < total; i++) {
        first_level[ i ] = first_level[ i ].split(' ');
    }
    // We do NOT duplicate
    first_level.pop();
    return first_level;
};

/**
 * I believe this should fit the PHP model
 * Added the save() function to be called on form submit
 * Could be inherited from a global Charcoal.Admin.Property Prototype
 * Extra ideas:
 * - save
 * - validate
 * @return this (chainable)
 */
Charcoal.Admin.Property_Input_Geometry_Widget.prototype.save = function () {
    // Get raw map datas
    if (!this.controller()) {
        return this;
    }
    var raw = this.controller().export();

    // We might wanna save ONLY the places values
    var places = (typeof raw.places === 'object') ? raw.places : {};

    // transform map data to geometry data for "geoshit" ¯\_(ツ)_/¯
    var coords = (Object.keys(places).length) ? this.translate_coords(places[Object.keys(places)[0]].paths) : '';

    // Affect to the current property's input
    // I see no reason to have more than one input hidden here.
    // Split with classes or data if needed
    this.element().find('input[type=hidden]').val(JSON.stringify(coords));

    return this;
};
