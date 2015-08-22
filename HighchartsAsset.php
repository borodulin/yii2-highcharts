<?php
/**
 * @link https://github.com/borodulin/yii2-highcharts
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-highcharts/blob/master/LICENSE
 */

 namespace conquer\highcharts;
 
use yii\web\AssetBundle;

class HighchartsAsset extends AssetBundle
{
    const THEME_DARK_BLUE       = 'themes/dark-blue.js';
    const THEME_DARK_GREEN      = 'themes/dark-green.js';
    const THEME_DARK_UNICA      = 'themes/dark-unica.js';
    const THEME_GRAY            = 'themes/gray.js';
    const THEME_GRID_LIGHT      = 'themes/grid-light.js';
    const THEME_GRID            = 'themes/grid.js';
    const THEME_SAND_SIGNIKA    = 'themes/sand-signika.js';
    const THEME_SKIES           = 'themes/skies.js';
    
    const MODULE_BROKEN_AXIS        = 'modules/broken-axis.js';
    const MODULE_CANVAS_TOOLS       = 'modules/canvas-tools.js';
    const MODULE_DATA               = 'modules/data.js';
    const MODULE_DRILLDOWN          = 'modules/drilldown.js';
    const MODULE_EXPORTING          = 'modules/exporting.js';
    const MODULE_FUNNEL             = 'modules/funnel.js';
    const MODULE_HEATMAP            = 'modules/heatmap.js';
    const MODULE_NO_DATA_TO_DISPLAY = 'modules/no-data-to-display.js';
    const MODULE_SOLID_GAUGE        = 'modules/solid-gauge.js';
    const MODULE_TREEMAP            = 'modules/treemap.js';
    
    public static $globalOptions;
    
    public $sourcePath = '@vendor/bower/highcharts-release/';
    
    public $js = [
        'highcharts.js',
    ];
    
    public $depends = [
        'yii\web\JqueryAsset',
    ];    
}