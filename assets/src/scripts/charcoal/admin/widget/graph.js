/* globals echarts */
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

;(function ($, Admin, window) {
    'use strict';

    var Graph = function (opts) {
        this.EVENT_NAMESPACE = '.charcoal.widget.graph';

        Charcoal.Admin.Widget.call(this, opts);

        this.graph_options = opts.graph_options || opts.data.graph_options || {};
    };

    Graph.prototype            = Object.create(Charcoal.Admin.Widget.prototype);
    Graph.prototype.contructor = Graph;
    Graph.prototype.parent     = Charcoal.Admin.Widget.prototype;

    Graph.prototype.init = function () {
        if (!echarts) {
            console.error('Could not initialize graph widget:', 'eCharts is missing');
            return;
        }

        var chart = echarts.init(this.element()[0]);

        this.chart = chart;

        // TODO: Add support for defered data loading (when data is heavy)
        // chart.showLoading({
        //     text: widgetL10n.loading,
        // });
        // chart.hideLoading();

        chart.setOption(this.graph_options);

        $(window).on('resize' + this.EVENT_NAMESPACE, function () {
            chart.resize();
        });
    };

    Graph.prototype.destroy = function () {
        if (this.chart) {
            this.chart.dispose();
        }

        $(window).off('resize' + this.EVENT_NAMESPACE);
    };

    Admin.Widget_Graph = Graph;

}(jQuery, Charcoal.Admin, window));
