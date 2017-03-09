/* globals authL10n */
/**
 * charcoal/admin/template/account/lost-password
 *
 * Require:
 * - jQuery
 * - Boostrap3
 * - Boostrap3-Dialog
 *
 * @todo Implement feedback from server-side
 */

Charcoal.Admin.Template_Account_LostPassword = function (opts)
{
    // Common Template properties
    this.template_type = 'charcoal/admin/template/account/lost-password';

    this.init(opts);
};

Charcoal.Admin.Template_Account_LostPassword.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Account_LostPassword.prototype.constructor = Charcoal.Admin.Template_Account_LostPassword;
Charcoal.Admin.Template_Account_LostPassword.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Account_LostPassword.prototype.init = function (opts)
{
    window.console.debug(opts);
    this.bind_events();
};

Charcoal.Admin.Template_Account_LostPassword.prototype.bind_events = function ()
{
    $('#lost-password-form').on('submit.charcoal.password', function (event) {
        event.preventDefault();

        var $form = $(this);
        var url   = ($form.prop('action') || window.location.href);
        var data  = $form.serialize();

        $.post(url, data, function (response) {
            window.console.debug(response);
            BootstrapDialog.show({
                title:    authL10n.lostPassword,
                message:  authL10n.lostPassSuccess,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.reload();
                }
            });
        }, 'json').fail(function () {
            BootstrapDialog.show({
                title:    authL10n.lostPassword,
                message:  authL10n.lostPassFailed,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: function () {
                    window.grecaptcha.reset();
                }
            });
        });
    });
};
