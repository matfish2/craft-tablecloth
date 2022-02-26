<?php


namespace matfish\Tablecloth\services\elementqueries;

use craft\db\Query;
use craft\db\Table;
use matfish\Tablecloth\models\Column\Column;

class TableclothQuery extends Query
{
    public string $baseTable;

    /**
     * @param string $baseTable
     */
    public function setBaseTable(string $baseTable): self
    {
        $this->baseTable = $baseTable;

        return $this;
    }

    /**
     * @return array|\yii\db\DataReader
     * @throws \yii\db\Exception
     * @throws \JsonException
     */
    public function get()
    {
        return $this->createCommand()->queryAll();
    }


    /**
     * @param $query
     * @return mixed|string
     * @throws \Exception
     */
    public function addSingleCustomList(Column $column)
    {
        $list = $column->getList();
        $dbCol = $column->getDbColumn(Column::CONTEXT_FILTER);
        $qs = [];

        $first = true;
        foreach ($list as $value => $label) {
            $q = (new Query())->select("'$value' [[value]], '$label' [[label]]");
            if ($first) {
                $main = $q;
            } else {
                $qs[] = $q;
            }
            $first = false;
        }

        foreach ($qs as $q) {
            $main->union($q);
        }

        $this->withQuery($main, "[[list_{$column->handle}]]");
        $this->leftJoin("list_$column->handle", "$dbCol=[[list_$column->handle.value]]");
    }

    /**
     * @param Column $column
     */
    public function addMultipleList(Column $column) : void {
        $handle = $column->getFrontEndHandle();
        $alias = "searchindex_{$handle}";

        $this->leftJoin([$alias=>Table::SEARCHINDEX],
            "[[$alias.elementId]]=[[{$column->getContentTable()}.elementId]] AND
             [[$alias.fieldId]]={$column->fieldId} 
            AND [[$alias.attribute]]='field'
            AND [[$alias.siteId]]={$column->getDatatable()->siteId}");
    }


    /**
     * @param $fieldId
     * @param $relationName
     * @return $this
     */
    public function joinRelationIds($fieldId, $relationName, $isProductVariant = false): self
    {
        $prefix = $isProductVariant ? 'variant_' : '';
        $table = "{$prefix}{$relationName}_$fieldId";
        $driver = \Craft::$app->db->driverName;
        $baseTable = $isProductVariant ? 'variants' : $this->baseTable;

        $targetExp = $driver === 'pgsql' ?
            "string_agg([[targetId]]::varchar,',' order by [[sortOrder]])" :
            "group_concat([[targetId]] order by [[sortOrder]])";

        return $this->leftJoin(
            "(select [[sourceId]],{$targetExp} [[{$relationName}]] from {{%relations}} where [[fieldId]]={$fieldId} group by [[sourceId]]) [[$table]]",
            "[[{$baseTable}.id]]=[[$table.sourceId]]"
        );
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function joinCategories($fieldId, $isVariant = false): self
    {
        return $this->joinRelationIds($fieldId, 'categories', $isVariant);
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function joinUsers($fieldId, $isVariant = false): self
    {
        return $this->joinRelationIds($fieldId, 'users', $isVariant);
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function joinAssets($fieldId, $isVariant = false): self
    {
        return $this->joinRelationIds($fieldId, 'assets', $isVariant);
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function joinEntries($fieldId, $isVariant = false): self
    {
        return $this->joinRelationIds($fieldId, 'entries', $isVariant);
    }

    /**
     * @param $fieldId
     * @return $this
     */
    public function joinTags($fieldId, $isVariant = false): self
    {
        return $this->joinRelationIds($fieldId, 'tags', $isVariant);
    }
}