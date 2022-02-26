<?php


namespace matfish\Tablecloth;

use craft\web\AssetBundle;

class TableclothCpAssetBundle extends AssetBundle
{
    public function init()
    {
        parent::init();

        $this->jsOptions = ['position' => \yii\web\View::POS_END, 'defer' => true];

        // define the path that your publishable resources live
        $this->sourcePath = '@matfish/Tablecloth/assets';

        // define the dependencies
        $this->depends = [
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'https://code.jquery.com/ui/1.13.0/jquery-ui.js',
            'compiled/craft-tablecloth-cp.min.js'
        ];

        $this->css = [
        ];
    }
}