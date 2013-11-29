/**
 * Rendering of histogram using Highcharts.
 * You have to set up global `app.categories` and `app.series` variables to contain some real data to show.
 */
$(function () {
    $('.histogram-target').highcharts({
        chart: {
            type: 'bar'
        },
        title: {
            text: 'Траты по типам'
        },
        xAxis: {
            categories: app.categories
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Траты в рублях'
            }
        },
        legend: {
            backgroundColor: '#FFFFFF',
            reversed: true
        },
        plotOptions: {
            series: {
                stacking: 'normal'
            }
        },
        series: app.series
    });
});
