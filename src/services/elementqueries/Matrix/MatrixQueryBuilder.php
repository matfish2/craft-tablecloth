<?php

namespace matfish\Tablecloth\services\elementqueries\Matrix;

use Craft;
use craft\db\Query;
use craft\db\Table;
use craft\fields\Matrix;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class MatrixQueryBuilder
{
    /**
     * @var
     */
    protected Matrix $field;

    /**
     * @var array
     */
    protected array $elements;


    /**
     * MatrixQueryBuilder constructor.
     * @param $fieldId
     * @param array $elements
     */
    public function __construct($field, array $elements)
    {
        $this->field = $field;
        $this->elements = $elements;
    }

    /**
     * @return TableclothQuery
     */
    public function getBaseQuery($siteId): TableclothQuery
    {
        $elements = implode(",", $this->elements);

        $subquery = (new Query())->select([
            'elementsId' => 'elements.id',
            'elementsSitesId' => 'elements_sites.id',
            'contentId' => 'content.id',
        ])->from(['elements' => Table::ELEMENTS])
            ->innerJoin(['elements_sites' => Table::ELEMENTS_SITES], '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin(['matrixblocks' => Table::MATRIXBLOCKS], '[[matrixblocks.id]] = [[elements.id]]')
            ->innerJoin(['content' => $this->field->contentTable], '[[content.elementId]] = [[elements.id]] AND [[content.siteId]] = [[elements_sites.siteId]]')
            ->where("[[matrixblocks.fieldId]]={$this->field->id}) AND ([[matrixblocks.ownerId]] in ({$elements})")
            ->andWhere([
                'elements.archived' => false,
                'elements.dateDeleted' => null,
                'elements.draftId' => null,
                'elements.revisionId' => null
            ])->orderBy([
                'matrixblocks.sortOrder' => SORT_ASC
            ]);

        if (Craft::$app->getIsMultiSite(false, true)) {
            $subquery->andWhere(['elements_sites.siteId' => $siteId]);
        }

        return (new TableclothQuery())
            ->select([
                'matrixblocks.ownerId',
                'matrixblocktypes.handle'
            ])->from(['subquery' => $subquery])
            ->innerJoin(['matrixblocks' => Table::MATRIXBLOCKS], '[[matrixblocks.id]] = [[subquery.elementsId]]')
            ->innerJoin(['elements' => Table::ELEMENTS], '[[elements.id]] = [[subquery.elementsId]]')
            ->innerJoin(['elements_sites' => Table::ELEMENTS_SITES], '[[elements_sites.id]] = [[subquery.elementsSitesId]]')
            ->innerJoin(['content' => $this->field->contentTable], '[[content.id]] = [[subquery.contentId]]')
            ->innerJoin(['matrixblocktypes' => Table::MATRIXBLOCKTYPES], '[[matrixblocktypes.id]] = [[typeId]]')
            ->orderBy([
                'matrixblocks.sortOrder' => SORT_ASC
            ]);
    }
}