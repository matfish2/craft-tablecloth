<?php

namespace tableclothtests\_craft\Fields\Feeders;

use Craft;
use craft\elements\Asset;
use tableclothtests\_craft\migrations\services\FakerService;

class AssetsFeeder extends FieldFeeder
{

    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        $volume = Craft::$app->volumes->getVolumeByHandle('volume');
        $assets = Asset::find()->volume($volume)->all();
        $opts = array_map(static function($asset) {
            return $asset->filename;
        }, $assets);

        return FakerService::arrayElements($opts,random_int(1,2));

    }
}