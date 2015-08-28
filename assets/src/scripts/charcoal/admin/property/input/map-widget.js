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
    this._controller = undefined;

    // HTML DOM element
    this._element = undefined;

    this._object_inc = 0;

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
        // '../../vendor/locomotivemtl/charcoal-admin/bower_components/bb-gmap/assets/scripts/dist/bb.gmap.js',
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
    this._controller = new window.BB.gmap.controller(
        this.element().find('.map-maker_map').get(0),
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
    this.controller().init();

    // Start listeners for controls.
    this.controls();

};

/**
* Return the DOMElement element
* @return {jQuery Object} $( '#' + this.data.id );
* If not set, creates it
*/
Charcoal.Admin.Property_Input_Map_Widget.prototype.element = function ()
{
    if (!this._element) {
        if (!this.data.id) {
            // Error...
            return false;
        }
        this._element = $('#' + this.data.id);
    }
    return this._element;
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

        // already picked
        if ($(this).hasClass('-active')) {
            $(this).removeClass('-active');
            return false;
        }

        // Active state
        $(this).siblings('.-active').removeClass('-active');
        $(this).addClass('-active');

        that.controller().create_new('marker', key + that.object_index());
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

        that.controller().create_new('line', key + that.object_index());
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

        that.controller().create_new('polygon', key + that.object_index());
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
            // After loading the marker object
            loaded_callback: function (marker) {
                that.controller().map().setCenter(marker.object().getPosition());
            }
        });

    });

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
