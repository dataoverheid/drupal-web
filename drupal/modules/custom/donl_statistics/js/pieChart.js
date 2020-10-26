am4core.useTheme(am4themes_animated);

var hourly_chart_config = {
  'data': drupalSettings.pieData,
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
        'stroke': '#fff',
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

am4core.createFromConfig(hourly_chart_config, document.getElementById('pie-chart'), am4charts['PieChart']);
