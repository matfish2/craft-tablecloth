<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class TagsRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $res = [];

        foreach ($value->all() as $tag) {
            $res[] = [
                'data' => [
                    'title' => $tag->title,
                    'url' => $tag->getUrl()
                ],
                'value' => $tag->id,
            ];
        }

        return $res;
    }
}