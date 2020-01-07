/* globals echarts, widgetL10n */
/**
 * Graph widget used to display graphical charts
 * charcoal/admin/widget/graph
 *
 * Require:
 * - jQuery
 * - echarts {@link https://ecomfe.github.io/echarts-doc/public/en/api.html#echarts}
 *
 * @param  {Object}  opts Options for widget
 */

var Graph = function (opts) {
    Charcoal.Admin.Widget.call(this, opts);
};

Graph.prototype            = Object.create(Charcoal.Admin.Widget.prototype);
Graph.prototype.contructor = Graph;
Graph.prototype.parent     = Charcoal.Admin.Widget.prototype;

Graph.prototype.init = function () {
    // Elements
    this.$widget = this.element();

    var chart = echarts.init(this.$widget.find('.js-graph-container').get(0));

    chart.showLoading({
        text: widgetL10n.loading,
    });
    chart.hideLoading();

    chart.setOption(this.echartsOptions());

    $(window).on('resize', function () {
        chart.resize();
    });
};

Graph.prototype.echartsOptions = function () {
    var defaultOpts = {
        color:   this._opts.data.colors,
        tooltip: {
            trigger: 'item'
        },
        toolbox: {
            show: true
        }
    };

    return $.extend(true, defaultOpts, this._opts.data.options);
};

Charcoal.Admin.Widget_Graph = Graph;
