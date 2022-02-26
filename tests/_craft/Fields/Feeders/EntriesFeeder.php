<?php


namespace tableclothtests\_craft\Fields\Feeders;

use craft\records\Entry;
use tableclothtests\_craft\migrations\services\FakerService;

class EntriesFeeder extends FieldFeeder
{
    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        $entries = Entry::find()->limit(20)->all();

        $opts = array_map(static function($entry) {
            return $entry->id;
        }, $entries);

        if (!count($entries)) {
            return null;
        }

        if (count($entries)===1) {
            $n = 1;
        } else {
            $n = random_int(1,2);
        }

        return FakerService::arrayElements($opts,$n);
    }
}