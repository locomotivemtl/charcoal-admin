/***
 * `charcoal/admin/property/input/map-widget`
 * Property_Input_Map_Widget Javascript class
 *
 */
Charcoal.Admin.Property_Input_Map_Widget = function (data)
{
    // Input type
    data.input_type = 'charcoal/admin/property/input/map-widget';

    Charcoal.Admin.Property.call(this, data);

    // Scope
    var that = this;

    // Controller
    this._controller = undefined;
    // Create uniq ident for every entities on the map
    this._object_inc = 0;

    if (typeof google === 'undefined') {
        // If google is undefined,
        window._tmp_google_onload_function = function () {
            that.init();
        };

        $.getScript(
            'https://maps.googleapis.com/maps/api/js?sensor=false' +
            '&callback=_tmp_google_onload_function&key=' + data.data.api_key,
            function () {}
        );
    } else {
        that.init();
    }

};

Charcoal.Admin.Property_Input_Map_Widget.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Map_Widget.prototype.constructor = Charcoal.Admin.Property_Input_Map_Widget;
Charcoal.Admin.Property_Input_Map_Widget.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Map_Widget.prototype.init = function ()
{
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
                x: 45.3712923,
                y: -73.9820994
            },
            zoom: 14,
            mapType: 'roadmap',
            coordsType: 'inpage', // array, json? (vs ul li)
            map_mode: 'default'
        }
    };

    map_options = $.extend(true, map_options, _data.data);

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
            map_options.places = merged_places;
        }

        if (index) {
            this._object_inc = index;
        }
    }

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

    this.controller().set_styles([{ featureType:'poi',elementType:'all',stylers:[{ visibility:'off' }] }]);

    this.controller().remove_focus();

    // Start listeners for controls.
    this.controls();

};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.controller = function ()
{
    return this._controller;
};

/**
 * This is to prevent any ident duplication
 * Return {Int} Object index
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.object_index = function ()
{
    return ++this._object_inc;
};

/**
 * Return {BB.gmap.controller}
 */
Charcoal.Admin.Property_Input_Map_Widget.prototype.controls = function ()
{
    // Scope
    var that = this;

    var key = 'object';

    this.element().on('click', '.js-add-marker', function (e)
    {
        e.preventDefault();

        // Find uniq item ident
        var object_id = key + that.object_index();
        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        // Start creation of a new object
        that.controller().create_new('marker', object_id);
    });

    this.element().on('click', '.js-display-marker-toolbox', function (e) {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            // Little helper
            that.hide_marker_toolbar();
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        // Little helper
        that.display_marker_toolbar();
    });

    this.element().on('click', '.js-add-line', function (e)
    {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        var object_id = key + that.object_index();

        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }
        that.controller().create_new('line', object_id);
    });

    this.element().on('click', '.js-add-polygon', function (e)
    {
        e.preventDefault();

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        var object_id = key + that.object_index();

        while (that.controller().get_place(object_id)) {
            object_id = key + that.object_index();
        }

        that.controller().create_new('polygon', object_id);
    });

    this.element().on('click', '.js-add_place_by_address', function (e) {
        e.preventDefault();

        var value = that.element().find('.js-address').val();
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

    that.controller().on('focus', function (obj) {
        var type = obj.data('type');

        that.element().find('.js-add-polygon').removeClass('-active');
        that.element().find('.js-display-marker-toolbox').removeClass('-active');
        // that.element().find('.js-add-marker').removeClass('-active');
        that.element().find('.js-add-line').removeClass('-active');

        switch (type) {
            case 'marker' :
                that.element().find('.js-display-marker-toolbox').addClass('-active');
                break;

            case 'polygon' :
                that.element().find('.js-add-polygon').addClass('-active');
                break;

            case 'line' :
                that.element().find('.js-add-line').addClass('-active');
                break;
        }
    });

};

Charcoal.Admin.Property_Input_Map_Widget.prototype.display_marker_toolbar = function ()
{
    // Displays the tool bar.
    $('.c-map-maker').addClass('maker_header-open');
};

Charcoal.Admin.Property_Input_Map_Widget.prototype.hide_marker_toolbar = function ()
{
    // Displays the tool bar.
    $('.c-map-maker').removeClass('maker_header-open');
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
Charcoal.Admin.Property_Input_Map_Widget.prototype.save = function ()
{
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
