am4core.ready(function () {
  am4core.useTheme(am4themes_animated);

  var graph_config = {
    'data': drupalSettings.graphData,
    'xAxes': [{
      'type': 'CategoryAxis',
      'dataFields': {
        'category': 'month'
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
        'categoryX': 'month',
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
  am4core.createFromConfig(graph_config, document.getElementById('line-graph'), am4charts['XYChart']);
});
