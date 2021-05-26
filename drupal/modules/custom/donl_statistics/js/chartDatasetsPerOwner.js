am4core.ready(function () {
  am4core.useTheme(am4themes_animated);

  var chart_config = {
    'data': drupalSettings.chartDatasetsPerOwner,
    'series': [{
      'type': 'PieSeries',
      'dataFields': {
        'value': 'value',
        'category': 'name'
      },
      'labels': {
        'template': {
          'disabled': true
        }
      },
      'ticks': {
        'template': {
          'disabled': true
        }
      },
      'slices': {
        'template': {
          'stroke': '#ffffff',
          'strokeWidth': 1,
          'strokeOpacity': 1,
        },
      },
      'colors': {
        'list': getColors(),
      },
    },],
    'legend': {
      'type': 'Legend',
      'position': 'right',
      'width': '400',
    },
  };

  am4core.createFromConfig(chart_config, document.getElementById('chart-datasets-per-owner'), am4charts['PieChart']);
});
