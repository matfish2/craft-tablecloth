<?php


namespace matfish\Tablecloth\services\elementqueries\ProductVariant;


use craft\db\Query;
use craft\db\Table;
use matfish\Tablecloth\services\elementqueries\TableclothQuery;

class ProductVariantQueryBuilder
{
    protected string $baseTable = 'variants';
    protected array $products;

    /**
     * ProductVariationQueryBuilder constructor.
     * @param array $products
     */
    public function __construct(array $products)
    {
        $this->products = $products;
    }


    public function getBaseQuery(): TableclothQuery
    {
        $products = implode(",", $this->products);

        $subquery = (new Query())->select([
            'elementsId' => 'elements.id',
            'elementsSitesId' => 'elements_sites.id',
            'contentId' => 'variant_content.id',
            'productId' => 'variants.productId'
        ])->from(['elements' => Table::ELEMENTS])
            ->innerJoin(['elements_sites' => Table::ELEMENTS_SITES], '[[elements_sites.elementId]] = [[elements.id]]')
            ->innerJoin(['variants' => '{{%commerce_variants}}'], '[[variants.id]] = [[elements.id]]')
            ->innerJoin(['variant_content' => Table::CONTENT], '[[variant_content.elementId]] = [[elements.id]]')
            ->where("[[variants.productId]] in ({$products})")
            ->andWhere([
                'elements.enabled'=>true,
                'elements.archived' => false,
                'elements.dateDeleted' => null,
                'elements.draftId' => null,
                'elements.revisionId' => null
            ])->orderBy([
                'variants.sortOrder' => SORT_ASC
            ]);

        return (new TableclothQuery())
            ->select([
                'variants.productId',
            ])->from(['subquery' => $subquery])
            ->innerJoin(['variants' => '{{%commerce_variants}}'], '[[variants.id]] = [[subquery.elementsId]]')
            ->innerJoin(['elements' => Table::ELEMENTS], '[[elements.id]] = [[subquery.elementsId]]')
            ->innerJoin(['elements_sites' => Table::ELEMENTS_SITES], '[[elements_sites.id]] = [[subquery.elementsSitesId]]')
            ->innerJoin(['variant_content' => Table::CONTENT], '[[variant_content.id]] = [[subquery.contentId]]')
            ->orderBy([
                'variants.sortOrder' => SORT_ASC
            ]);
    }
}