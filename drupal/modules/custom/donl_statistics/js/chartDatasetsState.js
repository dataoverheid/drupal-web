am4core.ready(function () {
  am4core.useTheme(am4themes_animated);

  var chart = am4core.create('chart-datasets-state', am4charts.XYChart);

  chart.data = drupalSettings.chartDatasetsState;

  var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
  categoryAxis.dataFields.category = 'month';

  var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
  valueAxis.min = 0;
  valueAxis.title.text = 'Datasets';

  function createSeries(field, name, stacked, color) {
    var series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = field;
    series.dataFields.categoryX = 'month';
    series.name = name;
    series.columns.template.tooltipText = '{name}: [bold]{valueY}[/]';
    series.stacked = stacked;
    series.fill = color;
    series.stroke = color;
  }

  createSeries('beschikbaar', Drupal.t('Available'), true, '#007bc7');
  createSeries('in_onderzoek', Drupal.t('In research'), true, '#ffb612');
  createSeries('niet_beschikbaar', Drupal.t('Not available'), true, 'red');

  chart.legend = new am4charts.Legend();
});
