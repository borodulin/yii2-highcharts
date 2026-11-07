<?php
/**
 * @link https://github.com/borodulin/yii2-highcharts
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-highcharts/blob/master/LICENSE
 */

namespace conquer\highcharts;

use Yii;
use yii\base\InvalidArgumentException;
use yii\web\AssetBundle;

/**
 * Highcharts with its modules, themes and language packs
 *
 * ```php
 * HighchartsAsset::register($view)
 *     ->addModules([HighchartsAsset::MODULE_EXPORTING])
 *     ->setTheme(HighchartsAsset::THEME_GRID_LIGHT);
 * ```
 *
 * @link https://www.highcharts.com/docs/getting-started/installation
 * @author Andrey Borodulin
 */
class HighchartsAsset extends AssetBundle
{
    const THEME_ADAPTIVE = 'adaptive';
    const THEME_AVOCADO = 'avocado';
    const THEME_DARK_UNICA = 'dark-unica';
    const THEME_GRID_LIGHT = 'grid-light';
    const THEME_HIGH_CONTRAST = 'high-contrast';
    const THEME_SAND_SIGNIKA = 'sand-signika';
    const THEME_SUNSET = 'sunset';

    const MODULE_ACCESSIBILITY = 'accessibility';
    const MODULE_ANNOTATIONS = 'annotations';
    const MODULE_BOOST = 'boost';
    const MODULE_BROKEN_AXIS = 'broken-axis';
    const MODULE_DATA = 'data';
    const MODULE_DRILLDOWN = 'drilldown';
    const MODULE_EXPORT_DATA = 'export-data';
    const MODULE_EXPORTING = 'exporting';
    const MODULE_FUNNEL = 'funnel';
    const MODULE_HEATMAP = 'heatmap';
    const MODULE_NETWORKGRAPH = 'networkgraph';
    const MODULE_NO_DATA_TO_DISPLAY = 'no-data-to-display';
    const MODULE_OFFLINE_EXPORTING = 'offline-exporting';
    const MODULE_ORGANIZATION = 'organization';
    const MODULE_SANKEY = 'sankey';
    const MODULE_SERIES_LABEL = 'series-label';
    const MODULE_SOLID_GAUGE = 'solid-gauge';
    const MODULE_SUNBURST = 'sunburst';
    const MODULE_TIMELINE = 'timeline';
    const MODULE_TREEMAP = 'treemap';
    const MODULE_VARIABLE_PIE = 'variable-pie';
    const MODULE_WORDCLOUD = 'wordcloud';
    const MODULE_XRANGE = 'xrange';

    /**
     * @inheritdoc
     */
    public $sourcePath = '@bower/highcharts-release';

    /**
     * @inheritdoc
     */
    public $js = ['highcharts.js'];

    /**
     * Only the minified scripts are published: the distribution has about 80 MB of sources,
     * ES modules and option files
     * @inheritdoc
     */
    public $publishOptions = [
        'only' => ['*.js'],
        'except' => ['*.src.js', '/es-modules/', '/esm/', '/gfx/', '/options/', '/css/'],
    ];

    /**
     * @var string[] files loaded after the core, in this order
     */
    private $_core;
    private $_more = [];
    private $_modules = [];
    private $_language;
    private $_theme;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->_core = $this->js;
    }

    /**
     * Loads `highcharts-more.js` (polar, gauge, arearange, boxplot, waterfall... series)
     * @return $this
     */
    public function useMore()
    {
        $this->_more['more'] = 'highcharts-more.js';
        $this->updateJs();
        return $this;
    }

    /**
     * Loads `highcharts-3d.js`
     * @return $this
     */
    public function use3d()
    {
        $this->_more['3d'] = 'highcharts-3d.js';
        $this->updateJs();
        return $this;
    }

    /**
     * @param string[] $modules module names, see the MODULE_* constants or the `modules` directory
     * of Highcharts. Paths of conquer/highcharts 1.0 (`modules/exporting.js`) are accepted too.
     * @return $this
     * @throws InvalidArgumentException for an unknown module
     */
    public function addModules(array $modules)
    {
        foreach ($modules as $module) {
            $file = 'modules/' . $this->normalizeName($module, 'modules') . '.js';
            if (!$this->fileExists($file)) {
                throw new InvalidArgumentException("Unknown Highcharts module \"$module\": $file is not found in "
                    . $this->sourcePath . '.');
            }
            $this->_modules[$file] = $file;
        }
        $this->updateJs();
        return $this;
    }

    /**
     * Sets the theme of the page. Highcharts themes change the global options, so one theme
     * is used for all the charts of the page.
     * @param string|null $theme theme name, see the THEME_* constants or the `themes` directory of Highcharts
     * @return $this
     * @throws InvalidArgumentException for an unknown theme
     */
    public function setTheme($theme)
    {
        if ($theme === null || $theme === '') {
            $this->_theme = null;
        } else {
            $file = 'themes/' . $this->normalizeName($theme, 'themes') . '.js';
            if (!$this->fileExists($file)) {
                throw new InvalidArgumentException("Unknown Highcharts theme \"$theme\": $file is not found in "
                    . $this->sourcePath . '.');
            }
            $this->_theme = $file;
        }
        $this->updateJs();
        return $this;
    }

    /**
     * Loads the language pack of Highcharts for the language, if there is one
     * @param string $language e.g. `fr-FR` or `fr`
     * @return bool whether the language pack is found
     */
    public function setLanguagePack($language)
    {
        $found = null;
        $prefix = strtolower(explode('-', str_replace('_', '-', $language))[0]);
        foreach ((array)glob(Yii::getAlias($this->sourcePath) . '/i18n/*.js') as $path) {
            $name = basename($path, '.js');
            if (substr($name, -4) === '.src') {
                continue;
            }
            if (strcasecmp($name, str_replace('_', '-', $language)) === 0) {
                $found = $name;
                break;
            }
            if ($found === null && strtolower(explode('-', $name)[0]) === $prefix) {
                $found = $name;
            }
        }
        $this->_language = $found === null ? null : "i18n/$found.js";
        $this->updateJs();
        return $found !== null;
    }

    /**
     * @param string $file path relative to the source path
     * @return bool
     */
    protected function fileExists($file)
    {
        return is_file(Yii::getAlias($this->sourcePath) . '/' . $file);
    }

    /**
     * @param string $name `exporting`, `modules/exporting.js` or `modules/exporting`
     * @param string $dir
     * @return string
     */
    protected function normalizeName($name, $dir)
    {
        $name = (string)$name;
        if (strncmp($name, "$dir/", strlen($dir) + 1) === 0) {
            $name = substr($name, strlen($dir) + 1);
        }
        if (substr($name, -3) === '.js') {
            $name = substr($name, 0, -3);
        }
        if (!preg_match('/^[a-z0-9][a-z0-9.-]*$/i', $name) || strpos($name, '..') !== false) {
            throw new InvalidArgumentException("Invalid Highcharts $dir name \"$name\".");
        }
        return $name;
    }

    /**
     * Core, more/3d, modules, language pack, theme
     */
    private function updateJs()
    {
        $this->js = array_values(array_unique(array_merge(
            (array)$this->_core,
            array_values($this->_more),
            array_values($this->_modules),
            $this->_language === null ? [] : [$this->_language],
            $this->_theme === null ? [] : [$this->_theme]
        )));
    }
}
