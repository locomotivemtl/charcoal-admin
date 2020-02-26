Charcoal.Admin.Template_MenuHeader = function () {
    // toggle-class.js
    // ==========================================================================
    $('.js-toggle-class').click(function (event) {
        event.preventDefault();

        var $this = $(this);
        var dataClass = $this.data('class');
        var dataTarget = $this.data('target');

        $(dataTarget).toggleClass(dataClass);
    });

    // accordion.js
    // ==========================================================================
    $(document).on('click', '.js-accordion-header', function (event) {
        event.preventDefault();

        var $this = $(this);

        $this
            .toggleClass('is-open')
            .siblings('.js-accordion-content')
            .stop()
            .slideToggle();
    });
};
