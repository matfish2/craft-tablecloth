<?php


namespace tableclothtests\_craft\Fields\Retrievers;


class CategoriesRetriever extends FieldValueRetriever
{

    public function transform($value)
    {
        $res = [];

        foreach ($value->all() as $category) {
            $res[] = [
                'data' => [
                    'title' => $category->title,
                    'url' => $category->getUrl()
                ],
                'value' => $category->id
            ];
        }

        return $res;
    }
}