<?php

/**
 * @throws JsonException
 */
function json_encode_if($value)
{
    if (is_string($value) && $value) {
        return $value;
    }

    return $value ? json_encode($value, JSON_THROW_ON_ERROR) : '[]';

}

/**
 * @throws JsonException
 */
function json_decode_if($value): array
{
    if (is_array($value)) {
        return $value;
    }

    return $value ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : [];
}

function custom_array_merge(&$array1, &$array2): array
{
    $result = [];

    foreach ($array1 as &$value_1) {
        foreach ($array2 as $value_2) {
            if ($value_1['handle'] === $value_2['handle']) {
                $result[$value_1['handle']] = array_merge($value_1, $value_2);
            }
        }
    }

    foreach ($array1 as $value) {
        if (!isset($result[$value['handle']])) {
            $result[$value['handle']] = $value;
        }
    }
    return array_values($result);
}

function ecommerce_installed() : bool {
    return Craft::$app->plugins->isPluginInstalled('commerce');
}