/**
* Form widget that manages data sending
* charcoal/admin/widget/form
*
* Require:
* - jQuery
* - Boostrap3-Dialog
*
* @param  {Object}  opts Options for widget
*/

Charcoal.Admin.Widget_Form = function (opts)
{
    this.widget_type = 'charcoal/admin/widget/form';

    // Widget_Form properties
    this.widget_id = null;
    this.obj_type = null;
    this.obj_id = null;
    this.form_selector = null;

    this.set_properties(opts).bind_events();
};
Charcoal.Admin.Widget_Form.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Form.prototype.constructor = Charcoal.Admin.Widget_Form;
Charcoal.Admin.Widget_Form.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Form.prototype.set_properties = function (opts)
{
    this.widget_id     = opts.id || this.widget_id;
    this.obj_type      = opts.data.obj_type || this.obj_type;
    this.obj_id        = Charcoal.Admin.filterNumeric(opts.data.obj_id || this.obj_id);
    this.form_selector = opts.data.form_selector || this.form_selector;
    this.isTab         = opts.data.tab;

    return this;
};

Charcoal.Admin.Widget_Form.prototype.bind_events = function ()
{
    var that = this;

    // Submit the form via ajax
    $(that.form_selector).on('submit', function (e) {
        e.preventDefault();
        that.submit_form(this);
    });

    // Any delete button should trigger the delete-object method.
    $('.js-obj-delete').on('click', function (e) {
        e.preventDefault();
        that.delete_object(this);
    });

    // Reset button
    $('.js-reset-form').on('click', function (e) {
        e.preventDefault();
        $(that.form_selector)[0].reset();
    });

    // Language switcher
    $('.js-lang-switch button').on('click', function (e) {
        e.preventDefault();

        var $this = $(this),
            lang  = $this.attr('data-lang-switch');

        that.switch_language(lang);
    });

    /*if (that.isTab) {
        $(that.form_selector).on('click', '.js-group-tabs', function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $(that.form_selector).find('.js-group-tab').addClass('hidden');
            $(that.form_selector).find('.js-group-tab.' + href).removeClass('hidden');
            $(this).parent().addClass('active').siblings('.active').removeClass('active');
        });
    }*/

};

Charcoal.Admin.Widget_Form.prototype.submit_form = function (form)
{
    // Let the component manager prepare the submit first
    // Calls the save function on each properties
    Charcoal.Admin.manager().prepare_submit();

    var that = this,
        form_data = new FormData(form),
        url,
        is_new_object;

    if (that.obj_id) {
        url = Charcoal.Admin.admin_url() + 'object/update';
        is_new_object = false;
    } else {
        url = Charcoal.Admin.admin_url() + 'object/save';
        is_new_object = true;
    }

    $.ajax({
        url: url,
        type: 'POST',
        processData: false,
        contentType: false,
        dataType: 'json',
        data: form_data,
        success: function (response) {
            if (response.success) {

                // Default, add feedback to list
                Charcoal.Admin.feedback().add_data(response.feedbacks);

                if (response.next_url) {
                    // @todo "dynamise" the label
                    Charcoal.Admin.feedback().add_action({
                        label: 'Continuer',
                        callback: function () {
                            window.location.href =
                                Charcoal.Admin.admin_url() +
                                response.next_url;
                        }
                    });
                }

                if (!is_new_object) {
                    Charcoal.Admin.feedback().call();
                } else {
                    if (response.next_url) {
                        window.location.href =
                            Charcoal.Admin.admin_url() +
                            response.next_url;
                    } else {
                        window.location.href =
                            Charcoal.Admin.admin_url() +
                            'object/edit?obj_type=' + that.obj_type +
                            '&obj_id=' + response.obj_id;
                    }
                }
            } else {
                Charcoal.Admin.feedback().add_data(
                    [{
                        level: 'An error occurred and the object could not be saved.',
                        msg: 'error'
                    }]
                );
                Charcoal.Admin.feedback().call();
            }
        },
        error: function () {
            Charcoal.Admin.feedback().add_data(
                [{
                    level: 'An error occurred and the object could not be saved.',
                    msg: 'error'
                }]
            );
            Charcoal.Admin.feedback().call();
        }
    });
};

/**
* Handle the "delete" button / action.
*/
Charcoal.Admin.Widget_Form.prototype.delete_object = function (form)
{
    var that = this;
    console.debug(form);
    BootstrapDialog.confirm({
        title: 'Confirmer la suppression',
        type: BootstrapDialog.TYPE_DANGER,
        message:'Êtes-vous sûr de vouloir supprimer cet objet? Cette action est irréversible.',
        btnOKLabel: 'Supprimer',
        btnCancelLabel: 'Annuler',
        callback: function (result) {
            if (result) {
                var url = Charcoal.Admin.admin_url() + 'object/delete';
                var data = {
                    obj_type: that.obj_type,
                    obj_id: that.obj_id
                };
                $.ajax({
                    method: 'POST',
                    url: url,
                    data: data,
                    dataType: 'json'
                }).done(function (response) {
                    console.debug(response);
                    if (response.success) {
                        var url = Charcoal.Admin.admin_url() + 'object/collection?obj_type=' + that.obj_type;
                        window.location.href = url;
                    } else {
                        window.alert('Erreur. Impossible de supprimer cet objet.');
                    }
                });
            }
        }
    });

};

/**
* Switch languages for all l10n elements in the form
*/
Charcoal.Admin.Widget_Form.prototype.switch_language = function (lang)
{
    $('[data-lang][data-lang!=' + lang + ']').addClass('hidden');
    $('[data-lang][data-lang=' + lang + ']').removeClass('hidden');

    $('[data-lang-switch][data-lang-switch!=' + lang + ']')
        .removeClass('btn-info')
        .addClass('btn-default');

    $('[data-lang-switch][data-lang-switch=' + lang + ']')
        .removeClass('btn-default')
        .addClass('btn-info');
};
