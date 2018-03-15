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
    var $form = $('#lost-password-form');

    /**
     * @fires Charcoal.Admin.Template_Account_LostPassword.prototype.onSubmit~event:submit.charcoal.password
     */
    $form.on('submit.charcoal.password', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaResetPassCallback = this.submitForm.bind(this, $form);
};

/**
 * @listens Charcoal.Admin.Template_Account_LostPassword~event:submit.charcoal.password
 * @this    {Charcoal.Admin.Template_Account_LostPassword}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.onSubmit = Charcoal.Admin.Template_Login.prototype.onSubmit;

/**
 * Generate HTML from the given feedback.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.parseFeedbackAsHtml = Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml;

/**
 * @this  {Charcoal.Admin.Template_Account_LostPassword}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Account_LostPassword.prototype.submitForm = function ($form)
{
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
     .done(function (response) {
        var message = that.parseFeedbackAsHtml(response) || authL10n.lostPassSuccess;

        BootstrapDialog.show({
            title:    authL10n.lostPassword,
            message:  message,
            type:     BootstrapDialog.TYPE_SUCCESS,
            onhidden: function () {
                window.location.href = response.next_url || Charcoal.Admin.admin_url('login?notice=resetpass');
            }
        });
    }).fail(function (jqxhr, status, error) {
        var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
            message  = (that.parseFeedbackAsHtml(response) || authL10n.lostPassFailed),
            captcha  = Charcoal.Admin.recaptcha(),
            callback = null;

        if (captcha.hasApi()) {
            callback = function () {
                captcha.getApi().reset();
            };
        }

        BootstrapDialog.show({
            title:    authL10n.lostPassword,
            message:  message,
            type:     BootstrapDialog.TYPE_DANGER,
            onhidden: callback
        });
    });
};
