# Highcharts widget for Yii2 framework

[![Latest Version](https://img.shields.io/packagist/v/conquer/highcharts.svg)](https://packagist.org/packages/conquer/highcharts)
[![Total Downloads](https://img.shields.io/packagist/dt/conquer/highcharts.svg)](https://packagist.org/packages/conquer/highcharts)
[![CI](https://github.com/borodulin/yii2-highcharts/actions/workflows/ci.yml/badge.svg)](https://github.com/borodulin/yii2-highcharts/actions/workflows/ci.yml)
[![License](https://img.shields.io/packagist/l/conquer/highcharts.svg)](LICENSE)

## Description

[Highcharts](https://www.highcharts.com/) is a JavaScript charting library: line, area, column, bar, pie,
scatter, gauge, polar, heatmap, treemap, sankey and many other chart types. This extension provides a widget
and an asset bundle for Highcharts 13 with its modules, themes and language settings.

> **Highcharts license.** This extension is MIT-licensed, Highcharts itself is not free software:
> commercial use requires a [Highcharts license](https://www.highcharts.com/license), non-commercial use is
> governed by the [Highsoft EULA](https://www.highcharts.com/license-eula).

## Requirements

- PHP 7.4 or 8.x
- Yii 2.0.14 or later
- [asset-packagist](https://asset-packagist.org) repository configured in your application's `composer.json` (standard for Yii2 app templates)

## Installation

```bash
composer require conquer/highcharts:^2.0
```

## Usage

```php
use conquer\highcharts\Highcharts;
use yii\web\JsExpression;

<?= Highcharts::widget([
    'options' => [
        'chart' => ['type' => 'column'],
        'title' => ['text' => 'Stacked column chart'],
        'xAxis' => ['type' => 'datetime'],
        'yAxis' => [
            'min' => 0,
            'title' => ['text' => 'Total fruit consumption'],
            'stackLabels' => ['enabled' => true],
        ],
        'tooltip' => [
            'headerFormat' => '<b>{point.x:%e %b}</b><br/>',
            'pointFormat' => '{series.name}: {point.y}<br/>Total: {point.stackTotal}',
        ],
        'plotOptions' => [
            'column' => ['stacking' => 'normal', 'dataLabels' => ['enabled' => true]],
        ],
        'series' => [[
            'name' => 'John',
            'data' => [5, 3, 4, 7, 2],
            'pointStart' => new JsExpression('Date.UTC(2025, 0, 1)'),
            'pointInterval' => 24 * 3600 * 1000, // one day
        ], [
            'name' => 'Jane',
            'data' => [2, 2, 3, 2, 1],
            'pointStart' => new JsExpression('Date.UTC(2025, 0, 1)'),
            'pointInterval' => 24 * 3600 * 1000,
        ]],
    ],
]) ?>
```

| Property | Default | Description |
|---|---|---|
| `options` | `[]` | [Chart options](https://api.highcharts.com/highcharts/); `yii\web\JsExpression` for callbacks |
| `modules` | exporting, accessibility | Modules to load, `HighchartsAsset::MODULE_*` or any file name of the `modules` directory |
| `useMore` | `false` | Load `highcharts-more.js`: polar, gauge, arearange, boxplot, errorbar, waterfall... |
| `use3d` | `false` | Load `highcharts-3d.js` |
| `theme` | `null` | Theme of the page, `HighchartsAsset::THEME_*` |
| `globalOptions` | `[]` | Options for `Highcharts.setOptions()`, applied before the chart is created |
| `language` | application language | Language of the page charts, `false` keeps the Highcharts defaults |
| `htmlOptions` | `[]` | HTML attributes of the container |
| `tag` | `'div'` | Container tag |

Charts are created with `Highcharts.chart()` at the end of the page, jQuery is not required.
Every widget may request its own modules: all of them are loaded once, in this order: `highcharts.js`,
`highcharts-more.js`, `highcharts-3d.js`, modules, language pack, theme. Some modules depend on others
(`solid-gauge` needs `useMore`, `export-data` needs `exporting`). An unknown module or theme throws
`yii\base\InvalidArgumentException`.

The chart object:

```js
var chart = Highcharts.charts.find(c => c && c.renderTo.id === 'sales-chart');
chart.series[0].setData([1, 2, 3]);
```

### Language

The widget sets `lang.locale` to the application language (or `language`): month and weekday names, dates
and numbers are formatted by the browser. Texts (menu, "Loading...", "No data to display"...) come from the
Highcharts language pack if there is one (`fr-FR`, `nb-NO`, `zh-CN`), or from the `i18n` directory of this
package (`ru`). The first chart of the page sets the language. Override texts with
`'globalOptions' => ['lang' => [...]]`.

### Themes

Themes change the global options of Highcharts, so the theme of the first widget that sets it applies to
every chart of the page. Some themes (`dark-unica`, `grid-light`, `sand-signika`)
load fonts from Google Fonts.

## Security

- Options are encoded with `yii\helpers\Json::htmlEncode()`: strings are never turned into JavaScript,
  use `JsExpression` for callbacks.
- Highcharts filters the markup of texts (titles, labels, series names, tooltips) with an allow-list,
  so `<img onerror=...>` in data taken from users is not executed. Callbacks (`formatter`) returning
  user data as HTML are your responsibility.

## Upgrading from 1.0

- Highcharts 13 (`bower-asset/highcharts-release ^13.0`) instead of Highcharts 4.1. See the
  [Highcharts changelog](https://www.highcharts.com/blog/changelog/) for the option changes.
- Strings with the `js:` prefix are no longer JavaScript: use `yii\web\JsExpression`. `conquer/helpers`
  is no longer required.
- Charts are created with `Highcharts.chart()`, jQuery is not loaded; `$('#id').highcharts()` is not available.
- `HighchartsAsset::THEME_*` and `MODULE_*` are names (`'exporting'`), the 1.0 paths (`'modules/exporting.js'`)
  are still accepted. Removed themes: dark-blue, dark-green, gray, grid, skies; removed module: canvas-tools.
- The accessibility module is loaded by default.
- `run()` returns the container instead of echoing it.

## License

**conquer/highcharts** is released under the MIT License. See the bundled [`LICENSE`](LICENSE) for details.
Highcharts is licensed separately, see above.
