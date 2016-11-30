/**
* charcoal/admin/template/account/lost-password
*
* Require:
* - jQuery
* - Boostrap3
* - Boostrap3-Dialog
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

    $('.js-lost-password-submit').on('click', function (e) {
        e.preventDefault();

        var form = $(this).parents('form');
        console.debug(form.attr('action'));
        var url = Charcoal.Admin.admin_url() + 'account/lost-password';
        var data = form.serialize();
        $.post(url, data, function (response) {
            window.console.debug(response);
            BootstrapDialog.show({
                title:   'Password request sent',
                message: 'If any user matches the username or ' +
                'email given, a password request link will be sent ' +
                'to the email adress linked with account. ' +
                '\n' +
                'The link will be valid for 15 minutes.',
                type:    BootstrapDialog.TYPE_SUCCESS,
                onhidden: function () {
                    window.location.reload();
                }
            });
        }, 'json').fail(function () {
            BootstrapDialog.show({
                title:   'Lost password error',
                message: 'There was an error attempting to retrieve lost password.',
                type:    BootstrapDialog.TYPE_DANGER,
                onhidden: function () {
                    window.grecaptcha.reset();
                }
            });
        });
    });
};
