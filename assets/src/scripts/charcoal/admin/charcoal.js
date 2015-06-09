var Charcoal = Charcoal || {};
Charcoal.Admin = function()
{
	// This is a singleton
	if(arguments.callee._singleton_instance) {
		return arguments.callee._singleton_instance;
	}
	arguments.callee._singleton_instance = this;
	
	this.url = '';
	this.admin_path = '';

	this.admin_url = function()
	{
		return this.url + this.admin_path + '/';
	};

};
