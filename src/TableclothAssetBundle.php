<?php


namespace matfish\Tablecloth;

use craft\web\AssetBundle;

class TableclothAssetBundle extends AssetBundle
{
    public function init()
    {
        parent::init();

        $this->jsOptions = ['position' => \yii\web\View::POS_HEAD, 'defer' => true];

        // define the path that your publishable resources live
        $this->sourcePath = '@matfish/Tablecloth/assets';

        // define the dependencies
        $this->depends = [
        ];

        // define the relative path to CSS/JS files that should be registered with the page
        // when this asset bundle is registered
        $this->js = [
            'compiled/craft-tablecloth.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.7.0/cdn.min.js',
        ];

        $this->css = [
            'compiled/craft-tablecloth.min.css'
        ];
    }
}