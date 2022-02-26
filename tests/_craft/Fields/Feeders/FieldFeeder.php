<?php


namespace tableclothtests\_craft\Fields\Feeders;


abstract class FieldFeeder
{
    protected array $data;
    protected bool $isMatrix;

    /**
     * FieldFeeder constructor.
     * @param array $data
     * @param bool $isMatrix
     */
    public function __construct(array $data, bool $isMatrix = false)
    {
        $this->data = $data;
        $this->isMatrix = $isMatrix;
    }


    abstract public function get($options = null);

    /**
     * @param null $options
     * @return array
     */
    protected function getOptions($options = null) : array
    {
        if (!$options) {
            $options = \Craft::$app->fields->getFieldByHandle($this->data['handle'])->options;
        }

        return array_map(static function ($option) {
            return $option['value'];
        }, $options);
    }
}