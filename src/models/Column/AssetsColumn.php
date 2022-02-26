<?php


namespace matfish\Tablecloth\models\Column;


use craft\elements\Asset;

class AssetsColumn extends RelationsColumn
{
    public int $thumbnailWidth;

    protected function getOptions(): array
    {
        $field = $this->getField();

        $sql = "SELECT [[targetId]] [[assetId]] from {{%relations}} [[relations]] 
WHERE [[fieldId]]=$field->id
GROUP BY 1";

        $rows = array_map(static function ($row) {
            return $row['assetId'];
        }, \Craft::$app->db->createCommand($sql)->queryAll());
        $rows = implode(',', $rows);

        $assets = Asset::find()->where("[[assets.id]] in ($rows)")->all();

        $res = [];
        $width = $this->thumbnailWidth;

        foreach ($assets as $asset) {
            $res[] = [
                'value' => $asset->id,
                'data' => [
                    'title' => $asset->title,
                    'url' => $asset->getUrl(),
                    'thumbnailUrl' => str_replace(':80', '', $asset->getUrlsBySize(["{$width}w"])["{$width}w"])
                ]
            ];
        }

        return $res;
    }

    protected function templateVars(): array
    {
        return [
            'width' => $this->thumbnailWidth
        ];
    }
}