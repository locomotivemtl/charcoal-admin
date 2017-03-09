/* globals authL10n */
/**
 * charcoal/admin/template/account/reset-password
 *
 * Require:
 * - jQuery
 * - Boostrap3
 * - Boostrap3-Dialog
 *
 * @todo Implement feedback from server-side
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
    $('#reset-password-form').on('submit.charcoal.password', function (event) {
        event.preventDefault();

        var $form = $(this);
        var url   = ($form.prop('action') || window.location.href);
        var data  = $form.serialize();

        $.post(url, data, function (response) {
            window.console.debug(response);
            BootstrapDialog.show({
                title:    authL10n.passwordReset,
                message:  authL10n.resetPassSuccess,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.href = Charcoal.Admin.admin_url() + 'login';
                }
            });
        }, 'json').fail(function () {
            BootstrapDialog.show({
                title:    authL10n.passwordReset,
                message:  authL10n.resetPassFailed,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: function () {
                    window.grecaptcha.reset();
                }
            });
        });
    });
};
