<?php


namespace matfish\Tablecloth\services\normalizers;


use craft\helpers\DateTimeHelper;

class DateNormalizer implements NormalizerInterface
{
    public const NULL_VALUE = null;

    /**
     * Dates are saved to DB in UTC timezone
     * Convert to input timezone
     * @param $value
     * @return string
     */
    public function normalize($value): ?string
    {
        try {
            return DateTimeHelper::toDateTime($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}