<?php


namespace tableclothtests\_craft\Fields\Creators;

use Craft;
use craft\elements\Tag;
use craft\models\TagGroup;

class TagsCreator extends FieldCreator
{
    public function getFieldData(): array
    {
        $group = $this->generateTags();

        return [
            $this->settingsKey=>[
                'source'=>'taggroup:' . $group->uid
            ]
        ];
    }

    private function generateTags() : TagGroup
    {
         $tagGroup = Craft::$app->tags->getTagGroupByHandle('tags');

         if ($tagGroup) {
             return $tagGroup;
         }

        $tagsGroup = new TagGroup([
                'name' => 'Tags',
                'handle' => 'tags'
            ]
        );

        Craft::$app->tags->saveTagGroup($tagsGroup);

        $list = ['lorem', 'ipsum', 'sid', 'amet', 'morte', 'sifur', 'janleb'];

        foreach ($list as $tag) {
            $t = new Tag([
                'groupId' => $tagsGroup->id,
                'title' => $tag
            ]);
            Craft::$app->elements->saveElement($t);
        }

        return $tagsGroup;
    }
}