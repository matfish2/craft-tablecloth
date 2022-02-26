<?php


namespace matfish\Tablecloth\models\Column;


use craft\elements\Entry;

class EntriesColumn extends RelationsColumn
{
    protected function getOptions(): array
    {
        $field = $this->getField();

        $sql = "SELECT [[targetId]] [[entryId]] from {{%relations}} [[relations]] 
WHERE [[fieldId]]=$field->id 
GROUP BY 1";

        $rows = array_map(static function ($row) {
            return $row['entryId'];
        }, \Craft::$app->db->createCommand($sql)->queryAll());

        $rows = implode(',', $rows);

        $entries = Entry::find()->where("[[entries.id]] in ($rows)")->all();

        $res = [];

        foreach ($entries as $entry) {
            $res[] = [
                'value' => $entry->id,
                'data' => [
                    'title' => $entry->title,
                    'slug' => $entry->slug,
                    'url' => $entry->getUrl(),
                ]
            ];
        }

        return $res;
    }
}