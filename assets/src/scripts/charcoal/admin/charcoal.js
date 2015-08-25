var Charcoal = Charcoal || {};
/**
* Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
* It gives access to private properties and public methods
* @return  {object}  Charcoal.Admin
*/
Charcoal.Admin = (function ()
{
    'use strict';

    var options = {
            base_url: null,
            admin_path: null,
        },
        manager;

    /**
    * Object function that acts as a container for public methods
    */
    function Admin()
    {
    }

    /**
    * Set data that can be used by public methods
    * @param  {object}  data  Object containing data that needs to be set
    */
    Admin.set_data = function (data)
    {
        options = $.extend(true, options, data);
    };

    /**
    * Generates the admin URL used by forms and other objects
    * @return  {string}  URL for admin section
    */
    Admin.admin_url = function ()
    {
        return options.base_url + options.admin_path + '/';
    };

    /**
    * Provides an access to our instanciated ComponentManager
    * @return  {object}  ComponentManager instance
    */
    Admin.manager = function ()
    {
        if (typeof(manager) === 'undefined') {
            manager = new Charcoal.Admin.ComponentManager();
        }

        return manager;
    };

    /**
    * Convert an object namespace string into a usable object name
    * @param   {string}  name  String that respects the namespace structure : charcoal/admin/property/input/switch
    * @return  {string}  name  String that respects the object name structure : Property_Input_Switch
    */

    Admin.get_object_name = function (name)
    {
        // Getting rid of Charcoal.Admin namespacing
        var string_array = name.split('/');
        string_array = string_array.splice(2,string_array.length);

        // Uppercasing
        string_array.forEach(function (element, index, array) {

            // Camel case when splitted by '-'
            // Joined back with '_'
            var substr_array = element.split('-');
            if (substr_array.length > 1) {
                substr_array.forEach(function (e, i) {
                    substr_array[ i ] = e.charAt(0).toUpperCase() + e.slice(1);
                });
                element = substr_array.join('_');
            }

            array[index] = element.charAt(0).toUpperCase() + element.slice(1);
        });

        name = string_array.join('_');

        return name;
    };

    return Admin;

}());
var Charcoal = Charcoal || {};
/**
* Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
* It gives access to private properties and public methods
* @return  {object}  Charcoal.Admin
*/
Charcoal.Admin = (function ()
{
    'use strict';

    var options = {
            base_url: null,
            admin_path: null,
        },
        manager;

    /**
    * Object function that acts as a container for public methods
    */
    function Admin()
    {
    }

    /**
    * Set data that can be used by public methods
    * @param  {object}  data  Object containing data that needs to be set
    */
    Admin.set_data = function (data)
    {
        options = $.extend(true, options, data);
    };

    /**
    * Generates the admin URL used by forms and other objects
    * @return  {string}  URL for admin section
    */
    Admin.admin_url = function ()
    {
        return options.base_url + options.admin_path + '/';
    };

    /**
    * Provides an access to our instanciated ComponentManager
    * @return  {object}  ComponentManager instance
    */
    Admin.manager = function ()
    {
        if (typeof(manager) === 'undefined') {
            manager = new Charcoal.Admin.ComponentManager();
        }

        return manager;
    };

    /**
    * Convert an object namespace string into a usable object name
    * @param   {string}  name  String that respects the namespace structure : charcoal/admin/property/input/switch
    * @return  {string}  name  String that respects the object name structure : Property_Input_Switch
    */

    Admin.get_object_name = function (name)
    {
        // Getting rid of Charcoal.Admin namespacing
        var string_array = name.split('/');
        string_array = string_array.splice(2,string_array.length);

        // Uppercasing
        string_array.forEach(function (element, index, array) {

            // Camel case when splitted by '-'
            // Joined back with '_'
            var substr_array = element.split('-');
            if (substr_array.length > 1) {
                substr_array.forEach(function (e, i) {
                    substr_array[ i ] = e.charAt(0).toUpperCase() + e.slice(1);
                });
                element = substr_array.join('_');
            }

            array[index] = element.charAt(0).toUpperCase() + element.slice(1);
        });

        name = string_array.join('_');

        return name;
    };
    return Admin;

}());
