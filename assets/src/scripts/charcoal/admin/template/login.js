/* globals authL10n */
/**
 * charcoal/admin/template/login
 */

Charcoal.Admin.Template_Login = function (opts) {
    // Common Template properties
    this.template_type = 'charcoal/admin/template/login';

    this.init(opts);
};

Charcoal.Admin.Template_Login.prototype = Object.create(Charcoal.Admin.Template.prototype);
Charcoal.Admin.Template_Login.prototype.constructor = Charcoal.Admin.Template_Login;
Charcoal.Admin.Template_Login.prototype.parent = Charcoal.Admin.Template.prototype;

Charcoal.Admin.Template_Login.prototype.init = function (/*opts*/) {
    this.bind_events();
};

Charcoal.Admin.Template_Login.prototype.bind_events = function () {
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

    var $form   = $(event.currentTarget),
        captcha = Charcoal.Admin.recaptcha();

    if (captcha.hasInvisibleWidget($form, '#g-recaptcha-challenge')) {
        captcha.getApi().execute();
    } else {
        this.submitForm.call(this, $form);
    }
};

/**
 * @this  {Charcoal.Admin.Template_Login}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Login.prototype.submitForm = function ($form) {
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    var urlParams = Charcoal.Admin.queryParams();

    if ('redirect_to' in urlParams) {
        data = data.concat('&next_url=' + encodeURIComponent(urlParams.redirect_to));
    }

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
        .done(function (response) {
            var nextUrl  = (response.next_url || Charcoal.Admin.admin_url()),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.authSuccess),
                redirect = function () {
                    window.location.href = nextUrl;
                };

            message += '<p>' + authL10n.postLoginRedirect + ' ' +
                        authL10n.postLoginFallback.replace('[[ url ]]', nextUrl) + '</p>';

            BootstrapDialog.show({
                title:    authL10n.loginTitle,
                message:  message,
                type:     BootstrapDialog.TYPE_SUCCESS,
                onhidden: redirect
            });

            setTimeout(redirect, 300);
        }).fail(function (jqxhr, status, error) {
            var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
                message  = (that.parseFeedbackAsHtml(response) || authL10n.authFailed),
                captcha  = Charcoal.Admin.recaptcha(),
                callback = null;

            if (captcha.hasApi()) {
                callback = function () {
                    captcha.getApi().reset();
                };
            }

            BootstrapDialog.show({
                title:    authL10n.loginTitle,
                message:  message,
                type:     BootstrapDialog.TYPE_DANGER,
                onhidden: callback
            });
        });
};

/**
 * Generate HTML from the given feedback.
 *
 * @param  {array}  entries  - Collection of feedback entries.
 * @return {string|null} - The merged feedback messages as HTML paragraphs.
 */
Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml = function (entries) {
    if (entries.feedbacks) {
        entries = entries.feedbacks;
    }

    if (Array.isArray(entries) === false || entries.length === 0) {
        return null;
    }

    if (entries.length === 0) {
        return null;
    }

    var key,
        out,
        manager = Charcoal.Admin.feedback(entries),
        grouped = manager.getMessagesMap();

    out  = '<p>';
    for (key in grouped) {
        out += grouped[key].join('</p><p>');
    }
    out += '</p>';

    manager.empty();

    if (out === '<p></p>') {
        return null;
    }

    return out;
};
