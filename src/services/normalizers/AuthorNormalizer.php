<?php


namespace matfish\Tablecloth\services\normalizers;


class AuthorNormalizer implements NormalizerInterface
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

    public function normalize($value): array
    {
        return [
            'value' => $value,
            'data' => $this->list[$value]
        ];
    }
}