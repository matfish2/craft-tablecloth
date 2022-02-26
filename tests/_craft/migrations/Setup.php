<?php

namespace tableclothtests\_craft\migrations;

use Craft;
use craft\behaviors\FieldLayoutBehavior;
use craft\commerce\elements\Product;
use craft\commerce\elements\Variant;
use craft\commerce\models\ProductType;
use craft\commerce\models\ProductTypeSite;
use craft\db\Query;
use craft\records\FieldLayout;
use craft\models\FieldLayoutTab;
use craft\records\Site;
use tableclothtests\_craft\migrations\Migrators\FieldsMigrator;
use tableclothtests\_craft\migrations\Migrators\SectionMigrator;
use craft\commerce\Plugin as Commerece;

class Setup extends \craft\db\Migration
{
    protected $_productFieldLayoutId;
    protected $_variantFieldLayoutId;

    public function safeUp()
    {
        SectionMigrator::add();
        Craft::$app->getPlugins()->installPlugin('commerce');

        $siteId = Craft::$app->sites->getPrimarySite()->id;
        $siteSettings = new ProductTypeSite([
            'siteId' => $siteId,
            'hasUrls' => false,
        ]);

        $types = [
            [
                'name' => 'Books',
                'handle' => 'books',
                'hasDimensions' => true,
                'hasVariants' => false,
                'hasVariantTitleField' => true,
                'skuFormat' => '',
                'descriptionFormat' => '',
            ],
            [
                'name' => 'Phones',
                'handle' => 'phones',
                'hasDimensions' => false,
                'hasVariants' => true,
                'hasVariantTitleField' => false,
                'skuFormat' => '',
                'descriptionFormat' => '',
            ]
        ];

        foreach ($types as $data) {
            $this->insert(FieldLayout::tableName(), ['type' => Product::class]);
            $this->_productFieldLayoutId = $this->db->getLastInsertID(FieldLayout::tableName());
            $this->insert(FieldLayout::tableName(), ['type' => Variant::class]);
            $this->_variantFieldLayoutId = $this->db->getLastInsertID(FieldLayout::tableName());

            $data = array_merge($data,[
                'fieldLayoutId'=>$this->_productFieldLayoutId,
                'variantFieldLayoutId'=>$this->_variantFieldLayoutId
            ]);

            $productType = new ProductType($data);

            $siteIds = (new Query())
                ->select(['id'])
                ->from(Site::tableName())
                ->column();

            $allSiteSettings = [];

            foreach ($siteIds as $siteId) {
                $siteSettings = new ProductTypeSite();

                $siteSettings->siteId = $siteId;
                $siteSettings->hasUrls = true;
                $siteSettings->uriFormat = 'shop/products/{slug}';
                $siteSettings->template = 'shop/products/_product';

                $allSiteSettings[$siteId] = $siteSettings;
            }

            $productType->setSiteSettings($allSiteSettings);

            Commerece::getInstance()->getProductTypes()->saveProductType($productType);
        }


        FieldsMigrator::add();
    }

    public function safeDown()
    {
        SectionMigrator::remove();
    }
}