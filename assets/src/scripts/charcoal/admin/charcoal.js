var Charcoal = Charcoal || {};
Charcoal.Admin = function ()
{
    // This is a singleton
    if (arguments.callee.singleton_instance) {
        return arguments.callee.singleton_instance;
    }
    arguments.callee.singleton_instance = this;

    this.url = '';
    this.admin_path = '';

    this.admin_url = function ()
    {
        return this.url + this.admin_path + '/';
    };

};
