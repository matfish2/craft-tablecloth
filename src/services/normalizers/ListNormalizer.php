<?php


namespace matfish\Tablecloth\services\normalizers;


class ListNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = null;

    protected array $list;

    /**
     * ListNormalizer constructor.
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
        $label = $this->list[$value] ?? 'Unknown';

        return [
            'value' => $value,
            'label' => $label
        ];
    }
}