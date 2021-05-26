am4core.ready(function () {
  am4core.useTheme(am4themes_animated);

  var chart_config = {
    'data': drupalSettings.chartDatasetsPerMonth,
    'xAxes': [{
      'type': 'CategoryAxis',
      'dataFields': {
        'category': 'date'
      },
      'cursorTooltipEnabled': false
    }],
    'yAxes': [{
      'type': 'ValueAxis',
      'id': 'Datasets',
      'dataFields': {
        'category': 'value'
      },
      'title': {
        'text': 'Aantal datasets'
      },
      'cursorTooltipEnabled': false
    }],
    'series': [{
      'type': 'LineSeries',
      'name': 'Datasets',
      'stroke': '#007bc7',
      'fill': '#007bc7',
      'dataFields': {
        'categoryX': 'date',
        'valueY': 'value'
      },
      'tooltipText': '{value}',
      'bullets': [{
        'type': 'CircleBullet',
        'states': {
          'hover': {
            'properties': {
              'scale': 1.3
            }
          }
        }
      }]
    }],
    'cursor': {
      'lineY': {
        'disabled': true
      },
      'lineX': {
        'disabled': true
      },
      'xAxis': 'Datasets'
    },
  };
  am4core.createFromConfig(chart_config, document.getElementById('chart-datasets-per-month'), am4charts['XYChart']);
});
