/**
 * Range Input
 */

Charcoal.Admin.Property_Input_Range = function (opts) {
    this.EVENT_NAMESPACE = Charcoal.Admin.Property_Input_Range.EVENT_NAMESPACE;

    Charcoal.Admin.Property.call(this, opts);

    this.input_type = 'charcoal/admin/property/input/range';

    this.data    = opts.data;
    this.data.id = opts.id;

    this.$output = null;
    this.$input  = null;

    this.init();
};

Charcoal.Admin.Property_Input_Range.EVENT_NAMESPACE = '.charcoal.property.range';

Charcoal.Admin.Property_Input_Range.prototype = Object.create(Charcoal.Admin.Property.prototype);
Charcoal.Admin.Property_Input_Range.prototype.constructor = Charcoal.Admin.Property_Input_Range;
Charcoal.Admin.Property_Input_Range.prototype.parent = Charcoal.Admin.Property.prototype;

Charcoal.Admin.Property_Input_Range.prototype.init = function () {
    if (this.data.show_range_value !== true) {
        return;
    }

    if (typeof this.data.range_value_location !== 'string') {
        return;
    }

    var input_id, location, event_name;

    input_id = this.id();

    location = this.data.range_value_location;
    switch (location) {
        case 'prefix':
            this.$output = $('#' + input_id + '_prefix_text');
            break;

        case 'suffix':
            this.$output = $('#' + input_id + '_suffix_text');
            break;

        default:
            if (location[0] === '#' || location[0] === '.') {
                this.$output = $(location);
            } else {
                this.$output = $('#' + input_id + '_' + location);
            }
            break;
    }

    this.$output.addClass('js-show-range-value');

    this.$input = $('#' + input_id);

    if (!this.$input.exists() || !this.$output.exists()) {
        return;
    }

    this.on_change(this.$input, this.$output);

    event_name = ('oninput' in this.$input[0]) ? 'input' : 'change';

    this.$input.on(event_name + this.EVENT_NAMESPACE, this.on_change.bind(this, this.$input, this.$output));
};

/**
 * Display the range value on change.
 *
 * @listens input
 *
 * @param  {Element[]|jQuery} $input  - The field's input range element.
 * @param  {Element[]|jQuery} $output - The field's output element.
 * @param  {Event}            event   - The change event.
 * @return {void}
 */
Charcoal.Admin.Property_Input_Range.prototype.on_change = function ($input, $output/*, event*/) {
    $output.text($output.text().replace(/[\d\.]+/, $input.val()));
};

Charcoal.Admin.Property_Input_Range.prototype.destroy = function () {
    this.element().off(this.EVENT_NAMESPACE);

    if (this.$input) {
        this.$input.off(this.EVENT_NAMESPACE);
    }
};
