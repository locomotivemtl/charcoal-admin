Charcoal.Admin.Template_MenuHeader = function ()
{
    $('[data-toggle="class"]').click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var dataClass = $this.data('class');
        var dataTarget = $this.data('target');

        $(dataTarget).toggleClass(dataClass);
    });
};
