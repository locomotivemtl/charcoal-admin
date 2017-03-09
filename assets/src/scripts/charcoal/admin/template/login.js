/* globals commonL10n,authL10n */
/**
 * charcoal/admin/template/login
 *
 * Require:
 * - jQuery
 * - Boostrap3
 * - Boostrap3-Dialog
 *
 * @todo Implement feedback from server-side
 */

// Charcoal.Admin.Template_Login = new Charcoal.Admin.Widget();  // Here's where the inheritance occurs

Charcoal.Admin.Template_Login = function (opts)
{
    // Common Template properties
    this.template_type = 'charcoal/admin/template/login';

    this.init(opts);
};

Charcoal.Admin.Template_Login.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Login.prototype.constructor = Charcoal.Admin.Template_Login;
Charcoal.Admin.Template_Login.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Login.prototype.init = function (opts)
{
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Login.prototype.bind_events = function ()
{
    $('#login-form').on('submit.charcoal.login', function (event) {
        event.preventDefault();

        var $form = $(this);
        var url   = ($form.prop('action') || window.location.href);
        var data  = $form.serialize();

        $.post(url, data, function (response) {
            window.console.debug(response);
            if (response.success) {
                window.location.href = response.next_url;
            } else {
                //window.alert('Error');
                BootstrapDialog.show({
                    title:   authL10n.login,
                    message: commonL10n.authFailed,
                    type:    BootstrapDialog.TYPE_DANGER
                });
            }
        }, 'json').fail(function () {
            //window.alert('Error');
            BootstrapDialog.show({
                title:   authL10n.login,
                message: commonL10n.authFailed,
                type:    BootstrapDialog.TYPE_DANGER
            });
        });
    });
};
