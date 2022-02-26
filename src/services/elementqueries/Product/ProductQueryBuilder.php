<?php


namespace matfish\Tablecloth\services\elementqueries\Product;

use craft\db\Query;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;
use matfish\Tablecloth\services\elementqueries\TableclothQueryBuilder;

class ProductQueryBuilder extends TableclothQueryBuilder
{
    public string $baseTable = 'products';

    protected function manipulateQuery(TableclothQuery $query): TableclothQuery
    {
        $hasVariants = $this->dataTable->hasVariants();
        $joinVariants = !$hasVariants || $this->dataTable->variantsStrategy === 'join';

        $query->andWhere('[[products.typeId]]=:typeId')->addParams([
            'typeId' => $this->dataTable->typeId
        ]);

        if ($joinVariants) {
            $query->innerJoin('{{%commerce_variants}} variants', '[[variants.id]]=[[subquery.variantId]]');
        }

        if ($hasVariants && $joinVariants) {
            $query->leftJoin(['variant_content' => '{{%content}}'], "[[variant_content.elementId]] = [[subquery.variantId]] and [[variant_content.siteId]]={$this->dataTable->siteId}")
                ->leftJoin(['variant_elements' => '{{%elements}}'], "[[variant_elements.id]] = [[variants.id]]")
                ->andWhere([
                    'variant_elements.archived' => false,
                    'variant_elements.enabled' => true,
                    'variant_elements.dateDeleted' => null,
                    'variant_elements.draftId' => null,
                    'variant_elements.revisionId' => null
                ]);
        }

        return $query;
    }

    protected function manipulateSubquery(Query $query): Query
    {
        $joinVariants = !$this->dataTable->hasVariants() || $this->dataTable->variantsStrategy === 'join';

        if ($joinVariants) {
            $query->innerJoin('{{%commerce_variants}} variants', '[[variants.productId]]=[[products.id]]')
                ->addSelect(['variantId' => 'variants.id']);
        }

        return $query;
    }
}