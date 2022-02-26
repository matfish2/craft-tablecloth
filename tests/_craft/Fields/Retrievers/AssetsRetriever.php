<?php


namespace tableclothtests\_craft\Fields\Retrievers;

class AssetsRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $res = [];
        $width = 100;

        foreach ($value->orderBy(['sortOrder' => SORT_ASC])->all() as $asset) {

            $res[] = [
                'data' => [
                    'title' => $asset->title,
                    'url' => $asset->getUrl(),
                    'thumbnailUrl' => str_replace(':80', '', $asset->getUrlsBySize(["{$width}w"])["{$width}w"])
                ],
                'value' => (string) $asset->id
            ];
        }

        return $res;
    }
}