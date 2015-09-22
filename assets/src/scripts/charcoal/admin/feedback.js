/**
* charcoal/admin/feedback
* Class that deals with all the feedbacks throughout the admin
* Feedbacks uses the LEVEL concept which could be:
* - `success`
* - `warning`
* - `error`
*
* It uses BootstrapDialog to display all of this.
*
*/

/**
* @return this
*/
Charcoal.Admin.Feedback = function ()
{
    this.msgs = [];
    this.actions = [];

    this.context_definitions = {
        success: {
            title: 'Succès!',
            type: BootstrapDialog.TYPE_SUCCESS
        },
        warning: {
            title: 'Attention!',
            type: BootstrapDialog.TYPE_WARNING
        },
        error: {
            title: 'Une erreur s\'est produite!',
            type: BootstrapDialog.TYPE_DANGER
        }
    };
    return this;
};

/**
* Expects and array of object that looks just like this:
* [
*   { 'level' : 'success', 'msg' : 'Good job!' },
*   { 'level' : 'success', 'msg' : 'Good job!' }
* ]
*
* You can add other parameters as well.
*
* You can set a context, in order to display in a SEPARATE popup
* The default context would be GLOBAL.
* Example of context:
* - `save`
* - `update`
* - `edit`
* - `refresh`
* - `display`
* etc.
*
*
* This will class all success object by level in order to display a FULL LIST
* once the call method is...called
* @param {object} data
* @param {string} context // OR OBJECT? { name : 'global', title : '' }
* @return this
*/
Charcoal.Admin.Feedback.prototype.add_data = function (data/*, context*/)
{
    if (typeof data !== 'object') {
        // Bad values.
        return this;
    }

    // if (typeof context === 'object' &&
    //(typeof context.name === 'undefined' || typeof context.title === 'undefined')) {
    //     return this;
    // }

    // if (!context) {
    //     // Default context
    //     context = { name : 'global' };
    // }

    // if (typeof this.msgs[ context ] === 'undefined') {
    //     this.msgs[ context ] = [];
    // }

    // Add to all msgs
    this.msgs = this.msgs.concat(data);

    // Chainable
    return this;
};

/**
* A context is basicly a DIFFERENT POPUP
* That way, you can separate feedback even if there on the same level
* @return this
*/
Charcoal.Admin.Feedback.prototype.add_context = function (context) {
    if (!context) {
        return this;
    }

    if (typeof context.name === 'undefined' || typeof context.title === 'undefined') {
        return this;
    }

    this.context_definitions[ context.name ] = context;
    // for (var k in context) {
    //     if (typeof context[ k ].title === 'undefined') {
    //         // WRONG
    //         return this;
    //         break;
    //     }
    // }

    return this;
};

/**
* Actions in the dialog box
*/
Charcoal.Admin.Feedback.prototype.add_action = function (opts)
{
    this.actions.push(opts);
};

/**
* Outputs the results of all feedback accumulated on the page load
* @return this
*/
Charcoal.Admin.Feedback.prototype.call = function ()
{
    if (!this.msgs) {
        return this;
    }

    var i = 0;
    var total = this.msgs.length;

    var ret = {};

    for (; i < total; i++) {
        if (typeof this.msgs[ i ].level === 'undefined') {
            continue;
        }

        if (typeof ret[ this.msgs[i].level ] === 'undefined') {
            ret[ this.msgs[i].level ] = [];
        }
        ret[ this.msgs[i].level ].push(this.msgs[i].msg);
    }

    for (var level in ret) {
        if (typeof this.context_definitions[ level ] === 'undefined') {
            continue;
        }

        var buttons = [];

        if (this.actions.length) {
            var k = 0;
            var count = this.actions.length;
            for (; k < count; k++) {
                var action = this.actions[ k ];
                buttons.push({
                    label: action.label,
                    action: action.callback
                });
            }
        }

        BootstrapDialog.show({
            title: this.context_definitions[ level ].title,
            message: ret[ level ].join('<br/>'),
            type: this.context_definitions[ level ].type,
            buttons: buttons
        });

    }

    // Reset
    this.reset();

    return this;
};

/**
* Resets the feedback object
* When you call the feedback, no need to keep it in memory
* @return this (chainable)
*/
Charcoal.Admin.Feedback.prototype.reset = function ()
{
    this.msgs = [];
};
