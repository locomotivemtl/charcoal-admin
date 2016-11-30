/**
* charcoal/admin/template/account/lost-password
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
*/

Charcoal.Admin.Template_Account_ResetPassword = function (opts)
{
    // Common Template properties
    this.template_type = 'charcoal/admin/template/account/reset-password';

    this.init(opts);
};

Charcoal.Admin.Template_Account_ResetPassword.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Account_ResetPassword.prototype.constructor = Charcoal.Admin.Template_Account_ResetPassword;
Charcoal.Admin.Template_Account_ResetPassword.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Account_ResetPassword.prototype.init = function (opts)
{
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Account_ResetPassword.prototype.bind_events = function ()
{

    $('.js-reset-password-submit').on('click', function (e) {
        e.preventDefault();

        var form = $(this).parents('form');
        var url = Charcoal.Admin.admin_url() + 'account/reset-password';
        var data = form.serialize();
        $.post(url, data, function (response) {
            window.console.debug(response);
            BootstrapDialog.show({
                title:   'Password reset',
                message: 'The password was successfully reset.',
                type:    BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.href = Charcoal.Admin.admin_url() + 'login';
                }
            });
        }, 'json').fail(function () {
            BootstrapDialog.show({
                title:   'Reset password error',
                message: 'There was an error attempting to reset password.',
                type:    BootstrapDialog.TYPE_DANGER,
                onhidden: function () {
                    window.grecaptcha.reset();
                }
            });
        });
    });
};
