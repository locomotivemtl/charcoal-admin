/**
* charcoal/admin/property
*/

Charcoal.Admin.Property = function (opts)
{
    window.alert('Property ' + opts);
};

/**
* Interface?
* I suggest we use this for every property, just as PHP does
* We do not have default action but return the current object
* This will prevent further errors, and this will allow easy
* validation throughout the form
* @return this (chainable)
*/
Charcoal.Admin.Property.prototype.save = function ()
{
    // Default action = nothing
    return this;
};
