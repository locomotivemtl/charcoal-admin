var Charcoal = Charcoal || {};
/**
* Charcoal.Admin is meant to act like a static class that can be safely used without being instanciated.
* It gives access to private properties and public methods
* @return  {object}  Charcoal.Admin
*/
Charcoal.Admin = (function ()
{
    'use strict';

    this.options = {
        base_url: null,
        admin_path: null,
        manager: null
    };

    /**
    * Object function that acts as Admin initialization code and container for public methods
    */
    function Admin()
    {
        this.options.manager = new Charcoal.Admin.ComponentManager();
    }

    /**
    * Set data that can be used by public methods
    * @param  {object}  data  Object containing data that needs to be set
    */
    Admin.set_data = function (data)
    {
        this.options = $.extend(true, this.options, data);
    };

    /**
    * Generates the admin URL used by forms and other objects
    * @return  {string}  URL for admin section
    */
    Admin.admin_url = function ()
    {
        return this.options.base_url + this.options.admin_path + '/';
    };

    /**
     * Provides an access to our instanciated ComponentManager
     * @return  {object}  ComponentManager instance
     */
    Admin.manager = function ()
    {
        return this.options.manager;
    };

    return Admin;

}());
