<?php


namespace matfish\Tablecloth\services\normalizers;


interface NormalizerInterface
{
    /**
     * @param $value
     * @return mixed
     */
    public function normalize($value);

}