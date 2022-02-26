<?php


namespace matfish\Tablecloth\services\normalizers;


class NumberNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = null;

    /**
     * @param $value
     * @return float|null
     */
    public function normalize($value) : ?float
    {
        return is_null($value) ? null : (float)$value;
    }
}