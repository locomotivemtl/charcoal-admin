/* globals authL10n */
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
        this.submitForm.call(this, $form);
    }
};

/**
 * @this  {Charcoal.Admin.Template_Login}
 * @param {HTMLFormElement|jQuery} $form - The form element.
 */
Charcoal.Admin.Template_Login.prototype.submitForm = function ($form)
{
    var that = this,
        url  = ($form.prop('action') || window.location.href),
        data = $form.serialize();

    var urlParams = Charcoal.Admin.queryParams();

    if ('redirect_to' in urlParams) {
        data = data.concat('&next_url=' + encodeURIComponent(urlParams.redirect_to));
    }

    $.post(url, data, Charcoal.Admin.resolveJqXhrFalsePositive.bind(this), 'json')
     .done(function (response) {
        var message = that.parseFeedbackAsHtml(response) || authL10n.authSuccess;
        BootstrapDialog.show({
            title:    authL10n.login,
            message:  message,
            type:     BootstrapDialog.TYPE_SUCCESS,
        });

        setTimeout(function () {
            window.location.href = response.next_url || Charcoal.Admin.admin_url();
        }, 300);
    }).fail(function (jqxhr, status, error) {
        var response = Charcoal.Admin.parseJqXhrResponse(jqxhr, status, error),
            message  = that.parseFeedbackAsHtml(response) || authL10n.authFailed;

        BootstrapDialog.show({
            title:    authL10n.login,
            message:  message,
            type:     BootstrapDialog.TYPE_DANGER,
            onhidden: function () {
                window.grecaptcha.reset();
            }
        });
    });
};

/**
 * Generate HTML from the given feedback.
 *
 * @param  {array}  entries  - Collection of feedback entries.
 * @return {string|null} - The merged feedback messages as HTML paragraphs.
 */
Charcoal.Admin.Template_Login.prototype.parseFeedbackAsHtml = function (entries)
{
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

    console.log(grouped);

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
