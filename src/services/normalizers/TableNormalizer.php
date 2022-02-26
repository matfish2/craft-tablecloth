<?php


namespace matfish\Tablecloth\services\normalizers;


use craft\helpers\DateTimeHelper;

class TableNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = [];

    protected array $columns;

    /**
     * TableNormalizer constructor.
     * @param array $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @param $value
     * @return array
     * @throws \JsonException
     */
    public function normalize($value): array
    {
        $columns = $this->columns;
        $rows = json_decode_if($value);
        $res = [];

        foreach ($rows as $row) {
            $r = [];
            foreach ($columns as $columnId => $data) {
                $r[$data['handle']] = $this->getValue($row[$columnId], $data);
            }
            $res[] = $r;
        }

        return $res;
    }

    private function getLabel($value, $options)
    {
        $f = array_values(array_filter($options, static function ($option) use ($value) {
            return $option['value'] === $value;
        }));

        return $f ? $f[0]['label'] : '';
    }

    private function getValue($value, $data)
    {
        if ($data['type'] === 'select') {
            $label = $this->getLabel($value, $data['options']);

            return [
                'label' => $label,
                'value' => $value
            ];
        }

        if ($data['type'] === 'date') {
            return DateTimeHelper::toDateTime($value)->format('Y-m-d H:i:s');
        }

        if ($data['type'] === 'time') {
            return DateTimeHelper::toDateTime($value)->format('H:i');
        }

        return $value;
    }
}