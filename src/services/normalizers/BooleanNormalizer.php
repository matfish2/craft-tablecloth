<?php


namespace matfish\Tablecloth\services\normalizers;


class BooleanNormalizer implements NormalizerInterface
{

    public const NULL_VALUE = null;

    /**
     * @param $value
     * @return bool
     */
    public function normalize($value): bool
    {
        return $value === '1' || $value===true;
    }
}