<?php


namespace tableclothtests\_craft\Fields\Feeders;

use craft\fields\Color;
use craft\fields\Date;
use craft\fields\Dropdown;
use craft\fields\Email;
use craft\fields\Lightswitch;
use craft\fields\Number;
use craft\fields\PlainText;
use craft\fields\Time;
use craft\fields\Url;
use tableclothtests\_craft\migrations\services\FieldFactory;

class TableFeeder extends FieldFeeder
{
    public static $map = [
        'singleline' => PlainText::class,
        'color' => Color::class,
        'url' => Url::class,
        'email' => Email::class,
        'select' => Dropdown::class,
        'checkbox' => Lightswitch::class,
        'date' => Date::class,
        'time' => Time::class,
        'lightswitch' => Lightswitch::class,
        'multiline' => PlainText::class,
        'number' => Number::class
    ];

    public function get($options = null)
    {
        $field = \Craft::$app->fields->getFieldByHandle($this->data['handle']);

        $rows = random_int(1, 4);

        $data = [];

        for ($i = 1; $i <= $rows; $i++) {
            $row = [];
            foreach ($field->columns as $key => $column) {
                $f = (new FieldFactory(self::$map[$column['type']]))->getFeederClass();
                if ($column['type'] === 'select') {
                    $row[$column['handle']] = $f->get($column['options']);
                } elseif ($column['type']==='date') {
                    $row[$column['handle']] = $this->date();
                }  elseif ($column['type']==='time') {
                    $row[$column['handle']] = $this->time();
                }
                else {
                    $row[$column['handle']] = $f->get();
                }
            }
            $data[] = $row;
        }

        return $data;
    }

    public function date()
    {
        $max = time();
        $min = $max - 3600 * 24 * 365 * 2; // two years ago

        return date("Y-m-d", mt_rand($min, $max)) . ' 00:00:00';
    }

    public function time() : string
    {
        return date('Y-m-d') . ' ' .  str_pad(random_int(0,23), 2, "0", STR_PAD_LEFT).":".str_pad(random_int(0,59), 2, "0", STR_PAD_LEFT) . ':00';
    }
}