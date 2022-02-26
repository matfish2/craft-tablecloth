<?php


namespace matfish\Tablecloth\services\normalizers;


class TimeNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = null;

    public function normalize($value): string
    {
        $ps = explode(' ', $value);
        $t = array_pop($ps);

        $val = explode(':', $t);
        array_pop($val);

        return implode(':', $val);
    }
}