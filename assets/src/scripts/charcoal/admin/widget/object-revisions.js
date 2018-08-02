/* globals commonL10n, objectRevisionsWidgetL10n */
/**
 * Quick form is called by JS and must be
 * added in the component manager manually.
 *
 * @param {Object} opts Widget options
 * @return {thisArg}
 */
Charcoal.Admin.Widget_Object_Revisions = function (opts) {
    this.widget_type = 'charcoal/admin/widget/object-revisions';

    this.extra_form_data = opts.extra_form_data || {};

    this.xhr = null;
    this.obj_id = Charcoal.Admin.parseNumber(opts.obj_id) || 0;
    this.obj_type = opts.obj_type;

    return this;
};
Charcoal.Admin.Widget_Object_Revisions.prototype = Object.create(Charcoal.Admin.Widget.prototype);
Charcoal.Admin.Widget_Object_Revisions.prototype.constructor = Charcoal.Admin.Widget_Object_Revisions;
Charcoal.Admin.Widget_Object_Revisions.prototype.parent = Charcoal.Admin.Widget.prototype;

Charcoal.Admin.Widget_Object_Revisions.prototype.init = function () {
    this.bind_events();
};

Charcoal.Admin.Widget_Object_Revisions.prototype.bind_events = function () {
    var that = this;

    $('#' + this.id()).on('click.object.revisions', '.js-obj-revert', this.revert.bind(this));

    $('#' + this.id()).on(
        'click.charcoal.bs.dialog',
        '[data-dismiss="dialog"]',
        function (event) {
            if ($.isFunction(that.cancel_callback)) {
                that.cancel_callback(event);
            }
        }
    );
};

Charcoal.Admin.Widget_Object_Revisions.prototype.revert = function (event) {
    event.preventDefault();

    var url = Charcoal.Admin.admin_url() + 'object/revert-revision';
    var data = {
        obj_type: this.obj_type,
        obj_id: this.obj_id,
        rev_num: $(event.currentTarget).attr('data-rev-num')
    };

    BootstrapDialog.show({
        title: objectRevisionsWidgetL10n.title,
        message: objectRevisionsWidgetL10n.message,
        buttons: [{
            id: 'ok-btn',
            label: objectRevisionsWidgetL10n.restore,
            action: function () {
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            Charcoal.Admin.feedback().push([
                                {
                                    msg: objectRevisionsWidgetL10n.restoreError,
                                    level: 'error'
                                }
                            ]);
                            Charcoal.Admin.feedback().dispatch();
                        }
                    },
                    error: function () {
                        Charcoal.Admin.feedback().push([
                            {
                                msg: objectRevisionsWidgetL10n.restoreError,
                                level: 'error'
                            }
                        ]);
                        Charcoal.Admin.feedback().dispatch();
                    }
                });
            }
        }]
    });
};

Charcoal.Admin.Widget_Object_Revisions.prototype.disable_form = Charcoal.Admin.Widget_Form.prototype.disable_form;

Charcoal.Admin.Widget_Object_Revisions.prototype.enable_form = Charcoal.Admin.Widget_Form.prototype.enable_form;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_url = Charcoal.Admin.Widget_Form.prototype.request_url;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_done = Charcoal.Admin.Widget_Form.prototype.request_done;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_failed = Charcoal.Admin.Widget_Form.prototype.request_failed;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_complete = Charcoal.Admin.Widget_Form.prototype.request_complete;

Charcoal.Admin.Widget_Object_Revisions.prototype.request_success = function ($form, $trigger, response/* ... */) {
    if (response.feedbacks && !this.suppress_feedback) {
        Charcoal.Admin.feedback(response.feedbacks);
    }

    if (response.next_url) {
        // @todo "dynamise" the label
        Charcoal.Admin.feedback().add_action({
            label: commonL10n.continue,
            callback: function () {
                window.location.href = Charcoal.Admin.admin_url() + response.next_url;
            }
        });
    }
};
