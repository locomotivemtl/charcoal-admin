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
    var $form = $('#login-form');

    /**
     * @fires Charcoal.Admin.Template_Login.prototype.onSubmit~event:submit.charcoal.login
     */
    $form.on('submit.charcoal.login', $.proxy(this.onSubmit, this));

    window.CharcoalCaptchaLoginCallback = this.submitForm.bind(this, $form);
};

/**
 * @listens Charcoal.Admin.Template_Login~event:submit.charcoal.login
 * @this    {Charcoal.Admin.Template_Login}
 * @param   {Event} event - The submit event.
 */
Charcoal.Admin.Template_Login.prototype.onSubmit = function (event) {
    event.preventDefault();

    var $form      = $(event.currentTarget),
        $challenge = $form.find('#g-recaptcha-challenge');

    if ($challenge.exists() && $challenge.data('size') === 'invisible') {
        window.grecaptcha.execute();
    } else {
        this.submitForm.call($form);
    }
};

/**
 * @this {HTMLFormElement|jQuery}
 */
Charcoal.Admin.Template_Login.prototype.submitForm = function ($form)
{
    $form = $($form);
    var url   = ($form.prop('action') || window.location.href);
    var data  = $form.serialize();

    var queryParams = this.queryParams();

    if (queryParams.hasOwnProperty('redirect')) {
        data = data.concat('&next_url=' + encodeURIComponent(queryParams.redirect));
    }

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
            title:    authL10n.login,
            message:  commonL10n.authFailed,
            type:     BootstrapDialog.TYPE_DANGER,
            onhidden: function () {
                window.grecaptcha.reset();
            }
        });
    });
};

Charcoal.Admin.Template_Login.prototype.queryParams = function ()
{
    var pairs = location.search.slice(1).split('&');

    var result = {};
    pairs.forEach(function (pair) {
        pair = pair.split('=');
        if (pair[1]) {
            result[pair[0]] = decodeURIComponent(pair[1] || '');
        }
    });

    return JSON.parse(JSON.stringify(result));
};
