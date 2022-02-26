<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class EntriesRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $res = [];

        foreach ($value->all() as $entry) {
            $res[] = [
                'data' => [
                    'title' => $entry->title,
                    'slug' => $entry->slug,
                    'url' => $entry->getUrl()
                ],
                'value' => (string)$entry->id
            ];
        }

        return $res;
    }
}