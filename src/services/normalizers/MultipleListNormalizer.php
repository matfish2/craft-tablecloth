<?php


namespace matfish\Tablecloth\services\normalizers;


class MultipleListNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = [];

    protected array $list;

    /**
     * MultipleListNormalizer constructor.
     * @param array $list
     */
    public function __construct(array $list)
    {
        $this->list = $list;
    }

    /**
     * @param $value
     * @return array|null
     * @throws \JsonException
     */
    public function normalize($value) : ?array
    {
        $values = json_decode_if($value);
        $listNormalizer = new ListNormalizer($this->list);

        return array_map(static function($value) use ($listNormalizer) {
            return $listNormalizer->normalize($value);
        },$values);
    }
}