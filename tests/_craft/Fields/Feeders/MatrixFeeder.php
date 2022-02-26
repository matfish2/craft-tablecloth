<?php


namespace tableclothtests\_craft\Fields\Feeders;


use craft\fields\BaseOptionsField;
use tableclothtests\_craft\migrations\services\FieldFactory;

class MatrixFeeder extends FieldFeeder
{

    /**
     * @throws \Exception
     */
    public function get($options = null)
    {
        $field = \Craft::$app->fields->getFieldByHandle($this->data['handle']);

        $data = [];

        $blocks = $field->getBlockTypes();
        foreach ($blocks as $block) {
            $blockData = [];
            foreach ($block->getFields() as $field) {
                $feeder = (new FieldFactory(get_class($field)))->getFeederClass();
                if ($field instanceof BaseOptionsField) {
                    $blockData[$field->handle] = $feeder->get($field->options);
                } else {
                    $blockData[$field->handle] = $feeder->get();
                }
            }

            $blockData = ['MatrixBlock' => $blockData];

            $data[] = $blockData;
        }

        return $data;
    }
}