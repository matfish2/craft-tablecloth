<?php


namespace matfish\Tablecloth\services\normalizers;


class RelationsNormalizer implements NormalizerInterface
{

    public const NULL_VALUE = [];

    protected array $list;

    /**
     * RelationsNormalizer constructor.
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @param $value
     * @return array
     */
    public function normalize($value): array
    {
        $values = $value ? explode(',', $value) : [];

        return array_map(function ($value) {
            return [
                'data' => $this->list[$value],
                'value' => $value
            ];
        }, $values);
    }
}