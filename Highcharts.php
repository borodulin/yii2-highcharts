<?php
/**
 * @link https://github.com/borodulin/yii2-highcharts
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-highcharts/blob/master/LICENSE
 */

namespace conquer\highcharts;

use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/**
 * Highcharts chart
 *
 * ```php
 * <?= Highcharts::widget([
 *     'options' => [
 *         'chart' => ['type' => 'column'],
 *         'title' => ['text' => 'Fruit consumption'],
 *         'xAxis' => ['categories' => ['Apples', 'Bananas', 'Oranges']],
 *         'series' => [['name' => 'Jane', 'data' => [1, 0, 4]]],
 *     ],
 * ]) ?>
 * ```
 *
 * The chart object is available in JavaScript as `Highcharts.charts`, or by the container:
 * `Highcharts.charts.find(c => c && c.renderTo.id === 'my-chart')`.
 *
 * @link https://api.highcharts.com/highcharts/
 * @author Andrey Borodulin
 */
class Highcharts extends Widget
{
    /**
     * Theme of the page, see HighchartsAsset::THEME_*. Themes change the global options,
     * so the theme applies to all the charts of the page.
     * @var string|null
     */
    public $theme;

    /**
     * Modules to load, see HighchartsAsset::MODULE_* or the `modules` directory of Highcharts.
     * Without the accessibility module Highcharts warns in the console; to drop it, also set
     * `'accessibility' => ['enabled' => false]` in the options.
     * @var string[]
     */
    public $modules = [
        HighchartsAsset::MODULE_EXPORTING,
        HighchartsAsset::MODULE_ACCESSIBILITY,
    ];

    /**
     * Load `highcharts-3d.js`
     * @var bool
     */
    public $use3d = false;

    /**
     * Load `highcharts-more.js`: polar, gauge, arearange, boxplot, errorbar, waterfall... series
     * @link https://www.highcharts.com/docs/chart-and-series-types/chart-types
     * @var bool
     */
    public $useMore = false;

    /**
     * Container tag
     * @var string
     */
    public $tag = 'div';

    /**
     * HTML attributes of the container
     * @var array
     */
    public $htmlOptions = [];

    /**
     * Options passed to `Highcharts.setOptions()` before the chart is created
     * @link https://api.highcharts.com/highcharts/global
     * @var array
     */
    public $globalOptions = [];

    /**
     * Chart options. Use yii\web\JsExpression for callbacks.
     * @link https://api.highcharts.com/highcharts/
     * @var array
     */
    public $options = [];

    /**
     * Language of the page charts: null for the application language, false to keep the Highcharts defaults.
     * Sets `lang.locale` (dates and numbers are formatted by the browser) and the texts of the language
     * from the Highcharts language pack or from the `i18n` directory of this package.
     * The first chart of the page sets the language.
     * @var string|false|null
     */
    public $language;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->htmlOptions['id'])) {
            $this->htmlOptions['id'] = $this->getId();
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerAssets();
        return Html::tag($this->tag, '', $this->htmlOptions);
    }

    /**
     * Registers the scripts of the chart
     */
    public function registerAssets()
    {
        $view = $this->getView();
        $asset = HighchartsAsset::register($view);
        if ($this->useMore) {
            $asset->useMore();
        }
        if ($this->use3d) {
            $asset->use3d();
        }
        $asset->addModules((array)$this->modules);
        if ($this->theme) {
            $asset->setTheme($this->theme);
        }

        $this->registerLanguage($view, $asset);
        if (!empty($this->globalOptions)) {
            $view->registerJs('Highcharts.setOptions(' . Json::htmlEncode($this->globalOptions) . ');', View::POS_END);
        }

        $id = (string)$this->htmlOptions['id'];
        $view->registerJs('Highcharts.chart(document.getElementById(' . Json::htmlEncode($id) . '), '
            . (empty($this->options) ? '{}' : Json::htmlEncode($this->options)) . ');', View::POS_END);
    }

    /**
     * Sets the language options once per page
     * @param View $view
     * @param HighchartsAsset $asset
     */
    protected function registerLanguage($view, $asset)
    {
        $key = __CLASS__ . '::lang';
        if ($this->language === false || isset($view->js[View::POS_END][$key])) {
            return;
        }
        $language = $this->language ?: Yii::$app->language;
        $lang = ['locale' => $language];
        if (!$asset->setLanguagePack($language)) {
            $lang = ArrayHelper::merge(static::loadMessages($language), $lang);
        }
        $view->registerJs('Highcharts.setOptions(' . Json::htmlEncode(['lang' => $lang]) . ');', View::POS_END, $key);
    }

    /**
     * @param string $language
     * @return array texts from the `i18n` directory of this package
     */
    public static function loadMessages($language)
    {
        $prefix = strtolower(explode('-', str_replace('_', '-', $language))[0]);
        if (!preg_match('/^[a-z]{2,3}$/', $prefix)) {
            return [];
        }
        $file = __DIR__ . "/i18n/$prefix.php";
        return is_file($file) ? require $file : [];
    }
}
