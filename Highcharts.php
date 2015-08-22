<?php
/**
 * @link https://github.com/borodulin/yii2-highcharts
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-highcharts/blob/master/LICENSE
 */

namespace conquer\highcharts;
 
use yii\base\Widget;
use yii\helpers\Html;
use conquer\helpers\Json;
    
 /**
  * 
  * @author Andrey Borodulin
  */
 class Highcharts extends Widget
 {

     private static $globalRegistered;
     
     /**
      * You can use one of the Predefined constants HighchartsAssets::THEME_*
      * or set the name of the theme .js file
      * @var string
      */
     public $theme;
     
     /**
      * You can use predefined constants HighchartsAssets::MODULE_*
      * @var array
      */
     public $modules = [
         HighchartsAsset::MODULE_EXPORTING,
     ];
     
     /**
      * Use 3D Charts
      * @var boolean
      */
     public $use3d;
     
     /**
      * Use more chart types. [Polar chart, Spiderweb, Wind rose, Box plot, Error bar, Waterfall]
      * @link http://api.highcharts.com/highcharts#plotOptions
      * @var boolean
      */
     public $useMore;
     
     /**
      * Container Tag
      * @var string
      */
     public $tag = 'div';
     
     /**
      * Container HTML options
      * @var array
      */
     public $htmlOptions;
     
     /**
      * Highcharts global options
      * @var array
      */
     public $globalOptions;
     
     /**
      * Highcharts configuration options
      * @link http://api.highcharts.com/highcharts
      * @var array
      */
     public $options;

     /**
      * 
      * @var string
      */
     public $language;
     
     /**
      * Initializes the widget.
      * This method will register the bootstrap asset bundle. If you override this method,
      * make sure you call the parent implementation first.
      */
     public function init()
     {
         parent::init();
         if (!isset($this->htmlOptions['id'])) {
             $this->htmlOptions['id'] = $this->getId();
         }
     }
     
     /**
      * @inheritdoc
      */
     public function run()
     {
         echo Html::tag($this->tag, '', $this->htmlOptions);
         
         $this->registerAssets();
     }
     
     public function registerAssets()
     {
        $view = $this->getView();
        
        /* @var  $asset HighchartsAsset */
        $asset = HighchartsAsset::register($view); 

        if (!self::$globalRegistered) {
            self::$globalRegistered = true;
            $globalOptions = $this->globalOptions;
            if (!isset($globalOptions['lang'])) {
                $language = $this->language ? $this->language : \Yii::$app->language;
                $i18n = dirname(__FILE__).'/'.$language.'.php';
                if (file_exists($i18n)) {
                    $globalOptions['lang'] = require($i18n); 
                }
            }
            $globalOptions = Json::encode($globalOptions);
            $view->registerJs("Highcharts.setOptions($globalOptions)");
        }
        
        if ($this->use3d) {
            $asset->js[] = 'highcharts-3d.js';
        }
        if ($this->useMore) {
            $asset->js[] = 'highcharts-more.js';
        }
        if ($this->theme) {
            $asset->js[] = $this->theme;
        }
        foreach ($this->modules as $module) {
            $asset->js[] = $module;
        }
        
        $id = $this->htmlOptions['id'];
        
        $options = Json::encode($this->options);
        $view->registerJs("$('#$id').highcharts($options)");
     }
 }