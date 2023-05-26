/***
 * `charcoal/admin/property/input/map-widget`
 * Property_Input_Map_Widget Javascript class
 *
 */
Charcoal.Admin.Property_Input_Map_Widget = function (data) {
    // Input type
    data.input_type = 'charcoal/admin/property/input/map-widget';

    Charcoal.Admin.Property.call(this, data);

    // Controller
    this._controller;
    // Create uniq ident for every entities on the map
    this._object_inc = 0;
};

Charcoal.Admin.Property_Input_Map_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Map_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Map_Widget;
Charcoal.Admin.Property_Input_Map_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Map_Widget.prototype.init = function () {
    Charcoal.Admin.maps.whenMapsApiReady(this.init_map.bind(this));

    return this;
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.init_map = function () {
    if (typeof BB === 'undefined' || typeof google === 'undefined') {
        // We don't have what we need
        console.error('Plugins not loaded');
        return false;
    }

    var _data = this.opts();

    // Shouldn't happen at that point
    if (typeof _data.id === 'undefined') {
        console.error('Missing ID');
    }

    var controller_options = $.extend(true, this.get_controller_options(), _data.data);

    // Get current map state from DB
    // This is located in the hidden input
    var current_value = this.element().find('input[type=hidden]').val();

    if (current_value) {
        // Parse the value
        var places = JSON.parse(current_value);

        // Merge places with default styles
        var merged_places = {};
        var index = 0;
        for (var ident in places) {
            index++;
            merged_places[ ident ] = places[ ident ];
            merged_places[ ident ].styles = $.extend(places[ ident ].styles, default_styles);
        }

        if (merged_places) {
            controller_options.places = merged_places;
        }

        if (index) {
            this._object_inc = index;
        }
    }

    this.$map_maker = this.element().find('.js-map-maker');

    // Create new map instance
    this._controller = new window.BB.gmap.controller(
        this.element().find('.js-map-maker-map').get(0),
        controller_options
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

    // Scope
    var that = this;

    var key = 'object';

    this.element().on('change', '[name="' + this.opts('controls_name') + '"]', function (event) {
        var type = $(event.currentTarget).val();
        switch (type) {
            case 'display_marker_toolbar':
                that.display_marker_toolbar();

                break;
            case 'add_line':
            case 'add_polygon':
                that.hide_marker_toolbar();

                var object_id = key + that.object_index();

                while (that.controller().get_place(object_id)) {
                    object_id = key + that.object_index();
                }

                that.controller().create_new(type.replace('add_', ''), object_id);

                break;
        }
    });

    this.element().on('click', '.js-add-marker', function (e) {
        e.preventDefault();

        // Find uniq item ident
        var object_id = key + that.object_index();
        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        // Start creation of a new object
        that.controller().create_new('marker', object_id);
    });

    this.element().on('click', '.js-add_place_by_address', function (e) {
        e.preventDefault();

        var value = that.element().find('.js-address').text();
        if (!value) {
            // No value specified, no need to go further
            return false;
        }

        that.controller().add_place_by_address('object' + that.object_index(), value, {
            type: 'marker',
            draggable: true,
            editable: true,
            // After loading the marker object
            loaded_callback: function (marker) {
                that.controller().map().setCenter(marker.object().getPosition());
            }
        });
    });

    this.element().on('click', '.js-reset', function (e) {
        e.preventDefault();
        that.controller().reset();
    });
};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.controller = function () {
    return this._controller;
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.get_controller_options = function () {
    return {
        use_clusterer:  false,
        map:            this.get_map_options(),
        places:         this.get_places(),
        default_styles: this.get_default_styles()
    };
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.get_map_options = function () {
    return {
        center: {
            lat: 45.3712923,,
            lng: -73.9820994
        },
        zoom: 14
    };
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.get_default_styles = function () {
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
 * This is to prevent any ident duplication
 * Return {Int} Object index
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.object_index = function () {
    return ++this._object_inc;
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.display_marker_toolbar = function () {
    this.$map_maker.addClass('is-header-open');
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.hide_marker_toolbar = function () {
    this.$map_maker.removeClass('is-header-open');
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
Charcoal.Admin.Property_Input_Map_Widget.prototype.save = function () {
    // Get raw map datas
    var raw = this.controller().export();

    // We might wanna save ONLY the places values
    var places = (typeof raw.places === 'object') ? raw.places : {};

    // Affect to the current property's input
    // I see no reason to have more than one input hidden here.
    // Split with classes or data if needed
    this.element().find('input[type=hidden]').val(JSON.stringify(places));

    return this;
};
