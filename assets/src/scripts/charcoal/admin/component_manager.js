/**
* charcoal/admin/component_manager
*/

Charcoal.Admin.ComponentManager = function ()
{
    this.test = 'Test string';
};

Charcoal.Admin.ComponentManager.prototype.set_data = function (data)
{
    this.test = data.test || null;
};

Charcoal.Admin.ComponentManager.prototype.output = function ()
{
    console.log(this.test);
};

Charcoal.Admin.ComponentManager.prototype.add_property_input = function ()
{

};

Charcoal.Admin.ComponentManager.prototype.add_widget = function ()
{

};

Charcoal.Admin.ComponentManager.prototype.add_template = function ()
{

};

Charcoal.Admin.ComponentManager.prototype.render = function ()
{

};
