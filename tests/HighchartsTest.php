<?php

namespace conquer\highcharts\tests;

use conquer\highcharts\Highcharts;
use conquer\highcharts\HighchartsAsset;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\InvalidArgumentException;
use yii\base\Widget;
use yii\helpers\FileHelper;
use yii\web\Application;
use yii\web\JsExpression;
use yii\web\View;

class HighchartsTest extends TestCase
{
    private $assetPath;

    protected function setUp(): void
    {
        $this->assetPath = sys_get_temp_dir() . '/highcharts-test-' . uniqid();
        mkdir($this->assetPath);
        new Application([
            'id' => 'test',
            'basePath' => __DIR__,
            'vendorPath' => dirname(__DIR__) . '/vendor',
            'language' => 'en-US',
            'aliases' => ['@bower' => '@vendor/bower-asset', '@npm' => '@vendor/npm-asset'],
            'components' => [
                'request' => ['cookieValidationKey' => 'test', 'scriptFile' => __DIR__ . '/index.php', 'scriptUrl' => '/index.php'],
                'assetManager' => ['basePath' => $this->assetPath, 'baseUrl' => '/assets'],
                'view' => ['class' => View::class],
            ],
        ]);
        Widget::$counter = 0;
    }

    protected function tearDown(): void
    {
        FileHelper::removeDirectory($this->assetPath);
        Yii::$app = null;
    }

    /**
     * @param array[] $configs widget configurations
     * @return array [widgets html, page html]
     */
    private function render(array ...$configs)
    {
        $widgets = '';
        foreach ($configs as $config) {
            $widgets .= Highcharts::widget($config);
        }
        $html = Yii::$app->view->renderFile(__DIR__ . '/views/page.php');
        return [$widgets, $html];
    }

    /**
     * @return string[] script files of the page relative to the published directory
     */
    private function scripts($html)
    {
        preg_match_all('#<script src="/assets/\w+/([^"]+)"#', $html, $m);
        return $m[1];
    }

    public function testChart()
    {
        list($widget, $html) = $this->render([
            'options' => [
                'title' => ['text' => 'Fruit'],
                'tooltip' => ['formatter' => new JsExpression('function () { return this.y; }')],
            ],
        ]);
        $this->assertSame('<div id="w0"></div>', $widget);
        $this->assertSame(['highcharts.js', 'modules/exporting.js', 'modules/accessibility.js'], $this->scripts($html));
        $this->assertStringContainsString('Highcharts.setOptions({"lang":{"locale":"en-US"}});', $html);
        $this->assertStringContainsString('Highcharts.chart(document.getElementById("w0"), '
            . '{"title":{"text":"Fruit"},"tooltip":{"formatter":function () { return this.y; }}});', $html);
        // no jQuery
        $this->assertStringNotContainsString('jquery', $html);
        $this->assertStringNotContainsString('jQuery(', $html);
        // the language goes before the chart
        $this->assertLessThan(strpos($html, 'Highcharts.chart('), strpos($html, 'Highcharts.setOptions('));
    }

    public function testEmptyOptions()
    {
        list(, $html) = $this->render(['modules' => []]);
        $this->assertSame(['highcharts.js'], $this->scripts($html));
        $this->assertStringContainsString('Highcharts.chart(document.getElementById("w0"), {});', $html);
    }

    public function testFilesOfAllWidgetsAreLoadedInOrder()
    {
        list(, $html) = $this->render(
            ['theme' => HighchartsAsset::THEME_GRID_LIGHT, 'modules' => []],
            ['useMore' => true, 'modules' => [HighchartsAsset::MODULE_SOLID_GAUGE, HighchartsAsset::MODULE_EXPORTING]],
            ['use3d' => true, 'modules' => ['modules/exporting.js', 'export-data']]
        );
        $this->assertSame([
            'highcharts.js',
            'highcharts-more.js',
            'highcharts-3d.js',
            'modules/solid-gauge.js',
            'modules/exporting.js',
            'modules/export-data.js',
            'themes/grid-light.js',
        ], $this->scripts($html));
        foreach ($this->scripts($html) as $file) {
            preg_match('#<script src="/assets/(\w+)/highcharts\.js"#', $html, $m);
            $this->assertFileExists($this->assetPath . '/' . $m[1] . '/' . $file);
        }
    }

    public function testOnlyMinifiedScriptsArePublished()
    {
        list(, $html) = $this->render([]);
        preg_match('#<script src="/assets/(\w+)/highcharts\.js"#', $html, $m);
        $dir = $this->assetPath . '/' . $m[1];
        $this->assertFileExists("$dir/modules/exporting.js");
        $this->assertFileDoesNotExist("$dir/highcharts.src.js");
        $this->assertFileDoesNotExist("$dir/modules/exporting.src.js");
        $this->assertFileDoesNotExist("$dir/highcharts.d.ts");
        $this->assertDirectoryDoesNotExist("$dir/es-modules");
        $this->assertDirectoryDoesNotExist("$dir/esm");
        $this->assertDirectoryDoesNotExist("$dir/options");
    }

    public function testUnknownModule()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Highcharts module "canvas-tools": modules/canvas-tools.js is not found');
        $this->render(['modules' => ['canvas-tools']]);
    }

    public function testUnknownTheme()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown Highcharts theme "themes/dark-blue.js"');
        $this->render(['theme' => 'themes/dark-blue.js']);
    }

    public function testPathTraversal()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid Highcharts modules name "../highcharts"');
        $this->render(['modules' => ['../highcharts']]);
    }

    public function testRussian()
    {
        Yii::$app->language = 'ru-RU';
        list(, $html) = $this->render([], ['language' => 'fr-FR']);
        preg_match('#Highcharts\.setOptions\((.*?)\);#', $html, $m);
        $lang = json_decode($m[1], true)['lang'];
        $this->assertSame('ru-RU', $lang['locale']);
        $this->assertSame('Нет данных', $lang['noData']);
        $this->assertSame('Скачать изображение PNG', $lang['downloadPNG']);
        // month and weekday names come from Intl for the locale
        $this->assertArrayNotHasKey('weekdays', $lang);
        $this->assertArrayNotHasKey('months', $lang);
        // the first chart sets the language of the page
        $this->assertSame(1, substr_count($html, '"lang"'));
        $this->assertNotContains('i18n/fr-FR.js', $this->scripts($html));
    }

    public function testLanguagePackOfHighcharts()
    {
        list(, $html) = $this->render(['language' => 'fr']);
        $this->assertSame(['highcharts.js', 'modules/exporting.js', 'modules/accessibility.js', 'i18n/fr-FR.js'], $this->scripts($html));
        $this->assertStringContainsString('Highcharts.setOptions({"lang":{"locale":"fr"}});', $html);
    }

    public function testLanguageDisabled()
    {
        list(, $html) = $this->render(['language' => false]);
        $this->assertStringNotContainsString('setOptions', $html);
    }

    public function testGlobalOptions()
    {
        list(, $html) = $this->render(
            ['globalOptions' => ['time' => ['timezone' => 'Europe/Moscow']]],
            ['globalOptions' => ['lang' => ['noData' => '</script>']]]
        );
        $this->assertStringContainsString('Highcharts.setOptions({"time":{"timezone":"Europe\/Moscow"}});', $html);
        $u = '\\' . 'u';
        $this->assertStringContainsString('Highcharts.setOptions({"lang":{"noData":"' . $u . '003C\/script' . $u . '003E"}});', $html);
        // each widget's global options go before its chart
        $this->assertLessThan(strpos($html, 'getElementById("w1")'), strpos($html, '"noData"'));
        $this->assertGreaterThan(strpos($html, 'getElementById("w0")'), strpos($html, '"noData"'));
    }

    public function testGlobalOptionsAreNotSharedBetweenApplications()
    {
        $this->render(['globalOptions' => ['lang' => ['noData' => 'x']]]);
        $this->tearDown();
        $this->setUp();
        list(, $html) = $this->render([]);
        $this->assertStringContainsString('Highcharts.setOptions({"lang":{"locale":"en-US"}});', $html);
        $this->assertStringNotContainsString('noData', $html);
    }

    public function testIdAndOptionsAreEscaped()
    {
        list($widget, $html) = $this->render([
            'htmlOptions' => ['id' => "a').remove();alert(1);//"],
            'options' => ['title' => ['text' => 'js:alert(2)</script><script>alert(3)']],
        ]);
        $this->assertStringContainsString('id="a&#039;).remove();alert(1);//"', $widget);
        $u = '\\' . 'u';
        $this->assertStringContainsString('Highcharts.chart(document.getElementById("a' . $u . '0027).remove();alert(1);\/\/"), ', $html);
        $this->assertStringContainsString('{"title":{"text":"js:alert(2)' . $u . '003C\/script' . $u . '003E' . $u . '003Cscript' . $u . '003Ealert(3)"}}', $html);
        $this->assertStringNotContainsString('<script>alert(3)', $html);
    }
}
