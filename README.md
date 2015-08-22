Yii2 Highcharts
=================

## Description

Highcharts is a charting library written in pure JavaScript, offering an easy way of adding interactive charts to your web site or web application. Highcharts currently supports line, spline, area, areaspline, column, bar, pie, scatter, angular gauges, arearange, areasplinerange, columnrange, bubble, box plot, error bars, funnel, waterfall and polar chart types.
For more information please visit [Highcharts](http://www.highcharts.com/) 

# Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). 

To install, either run

```
$ php composer.phar require conquer/highcharts "*"
```
or add

```
"conquer/highcharts": "*"
```

to the ```require``` section of your `composer.json` file.

# Usage

Basic usage:

```php
// Form edit view
<?php
use conquer\highcharts\Highcharts;
?>
...
<?= Highcharts::widget([
    'options' => [
        'credits' => false,
        'chart' => [ 'type' => 'column' ],
        'title' => [ 'text' => 'Stacked column chart' ],
        'xAxis' => [
            'type' => 'datetime',
            'maxZoom' => 48 * 3600 * 1000,
        ],
        'yAxis' => [
            'min' => 0,
            'title' => [ 'text' => 'Total fruit consumption' ],
            'stackLabels' => [
                'enabled' => true,
                'style' => [
                    'fontWeight' => 'bold',
                    'color' => new JsExpression("(Highcharts.theme && Highcharts.theme.textColor) || 'gray'")
                ]
            ]
        ],
        'legend' => [
            'align' => 'left',
            'x' => 40,
            'verticalAlign' => 'bottom',
            'y' => 0,
            'floating' => false,
            'backgroundColor' => new JsExpression("(Highcharts.theme && Highcharts.theme.background2) || 'white'"),
            'borderColor' => '#CCC',
            'borderWidth' => 1,
            'borderRadius' => 10,
            'shadow' => false
        ],
        'tooltip' => [
            'headerFormat' => '<b>{point.x}</b><br/>',
            'pointFormat' => '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
        ],
        'plotOptions' => [
            'column' => [
                'stacking' => 'normal',
                'dataLabels' => [
                    'enabled' => true,
                    'color' => new JsExpression("(Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'"),
                    'style' => [
                        'textShadow' => '0 0 3px black'
                    ]
                ]
            ]
        ],
        'series' => [[
            'name' => 'John',
            'data' => [5, 3, 4, 7, 2],
            'pointStart' => new JsExpression('Date.UTC(2010, 0, 1)'),
            'pointInterval' => 24 * 3600 * 1000 // one day
        ], [
            'name' => 'Jane',
            'data' => [2, 2, 3, 2, 1],
            'pointStart' => new JsExpression('Date.UTC(2010, 0, 1)'),
            'pointInterval' => 24 * 3600 * 1000 // one day
        ], [
            'name' => 'Joe',
            'data' => [3, 4, 4, 2, 5],
            'pointStart' => new JsExpression('Date.UTC(2010, 0, 1)'),
            'pointInterval' => 24 * 3600 * 1000 // one day
        ]]
    ]
]); ?>
```

# License

**conquer/highcharts** is released under the MIT License. See the bundled `LICENSE.md` for details.
