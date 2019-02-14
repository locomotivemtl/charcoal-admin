/* globals commonL10n */
/**
 * Charcoal Action
 *
 * Handles bindings for actionable buttons.
 */

;(function ($, document, undefined) {
    'use strict';

    // Stored for quick usage
    var $document = $(document);

    /**
     * Creates a new action manager.
     *
     * @class
     */
    var Manager = function () {
        // Submit the form via ajax
        $($document).on('click', '.js-action-button', function (event) {
            this.handle_action(event);
        }.bind(this));
    };

    Manager.prototype.handle_action = function (event) {
        event.preventDefault();

        var url = $(event.target).attr('href');

        this.xhr = $.ajax({
            type:        'GET',
            url:         url,
            processData: false,
            contentType: false,
        });

        this.xhr
            .then($.proxy(this.request_done, this))
            .done($.proxy(this.request_success, this))
            .fail($.proxy(this.request_failed, this))
            .always($.proxy(this.request_complete, this));
    };

    Manager.prototype.request_done = function (response, textStatus, jqXHR) {
        if (!response || !response.success) {
            if (response.feedbacks) {
                return $.Deferred().reject(jqXHR, textStatus, response.feedbacks);
            }
            // else {
            // return $.Deferred().reject(jqXHR, textStatus, commonL10n.errorOccurred);
            // }
        }

        return $.Deferred().resolve(response, textStatus, jqXHR);
    };

    Manager.prototype.request_success = function (response/* textStatus, jqXHR */) {
        if (response.feedbacks) {
            Charcoal.Admin.feedback(response.feedbacks);
        }
    };

    Manager.prototype.request_failed = function (jqXHR, textStatus, errorThrown) {
        if (jqXHR.responseJSON && jqXHR.responseJSON.feedbacks) {
            Charcoal.Admin.feedback(jqXHR.responseJSON.feedbacks);
        } else {
            var error   = errorThrown || commonL10n.errorOccurred;
            Charcoal.Admin.feedback([{
                message: commonL10n.errorTemplate.replaceMap({
                    '[[ errorMessage ]]': 'There was an error. Sorry for the inconvenience.',
                    '[[ errorThrown ]]':  error
                }),
                level:   'error'
            }]);
        }
    };

    Manager.prototype.request_complete = function (/*, .... */) {
        Charcoal.Admin.feedback().dispatch();
    };

    Charcoal.Admin.ActionManager = Manager;

    new Charcoal.Admin.ActionManager();

}(jQuery, document));
