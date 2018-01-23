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
    var $form = $('#reset-password-form');

    /**
     * @fires Charcoal.Admin.Template_Account_ResetPassword.prototype.onSubmit~event:submit.charcoal.password
     */
    $form.on('submit.charcoal.password', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaChangePassCallback = this.submitForm.bind($form);
};

/**
 * @listens Charcoal.Admin.Template_Account_ResetPassword~event:submit.charcoal.password
 * @this    {Charcoal.Admin.Template_Account_ResetPassword}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Account_ResetPassword.prototype.onSubmit = Charcoal.Admin.Template_Login.prototype.onSubmit;

/**
 * @this {HTMLFormElement|jQuery}
 */
Charcoal.Admin.Template_Account_ResetPassword.prototype.submitForm = function ()
{
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
};
