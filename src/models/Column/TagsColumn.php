<?php


namespace matfish\Tablecloth\models\Column;


use craft\elements\Tag;
use craft\records\TagGroup;

class TagsColumn extends RelationsColumn
{
    protected function getOptions(): array
    {
        $field = $this->getField();

        $res = [];

        $source = explode(":", $field->getSourceOptions()[0]['value'])[1];
        $tagGroup = TagGroup::find()->where("uid='{$source}'")->one();
        $tags = Tag::find()->group($tagGroup)->all();

        foreach ($tags as $tag) {
            $res[] = [
                'value' => $tag->id,
                'data' => [
                    'title'=> $tag->title,
                    'url'=>$tag->getUrl()
                ],
            ];
        }

        return $res;

    }
}