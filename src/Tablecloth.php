<?php

namespace matfish\Tablecloth;

use Craft;
use craft\base\Plugin;
use craft\commerce\elements\Product;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Tag;
use craft\elements\User;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\elements\Entry;
use craft\services\Elements;
use craft\web\UrlManager;
use matfish\Tablecloth\models\Settings;
use matfish\Tablecloth\services\elementfields\AssetFields;
use matfish\Tablecloth\services\elementfields\CategoryFields;
use matfish\Tablecloth\services\elementfields\EntryFields;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\services\elementfields\ProductFields;
use matfish\Tablecloth\services\elementfields\TagFields;
use matfish\Tablecloth\services\elementfields\UserFields;
use matfish\Tablecloth\services\elementqueries\Asset\AssetQuery;
use matfish\Tablecloth\services\elementqueries\Category\CategoryQuery;
use matfish\Tablecloth\services\elementqueries\Entry\EntryQuery;
use matfish\Tablecloth\services\elementqueries\Product\ProductQuery;
use matfish\Tablecloth\services\elementqueries\Tag\TagQuery;
use matfish\Tablecloth\services\elementqueries\User\UserQuery;
use matfish\Tablecloth\twig\TableclothTwigExtension;
use yii\base\Event;

class Tablecloth extends Plugin
{
    public bool $hasCpSection = true;
    public bool $hasCpSettings = true;

    /**
     * @var array
     */
    public static array $elementFields = [
        Entry::class => EntryFields::class,
        Category::class => CategoryFields::class,
        Tag::class => TagFields::class,
        Asset::class => AssetFields::class,
        User::class => UserFields::class,
        Product::class => ProductFields::class
    ];

    /**
     * @var array
     */
    public static array $sourceQueries = [
        Entry::class => EntryQuery::class,
        Category::class => CategoryQuery::class,
        Tag::class => TagQuery::class,
        Asset::class => AssetQuery::class,
        User::class => UserQuery::class,
        Product::class => ProductQuery::class
    ];

    public function init()
    {
        parent::init();

        Craft::$app->view->registerTwigExtension(new TableclothTwigExtension());

        $this->registerElementType();
        $this->registerEditRoutes();

        if (Craft::$app->request->isConsoleRequest) {
            $this->controllerNamespace = 'matfish\\Tablecloth\\controllers\\console';
        } else {
            $this->controllerNamespace = 'matfish\\Tablecloth\\controllers';
        }
    }

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    protected function settingsHtml(): string
    {
        return \Craft::$app->getView()->renderTemplate(
            'tablecloth/settings',
            ['settings' => $this->getSettings()]
        );
    }

    /**
     *
     */
    public function registerElementType(): void
    {
        Event::on(Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = DataTable::class;
            }
        );
    }

    /**
     * Edit routes
     */
    public function registerEditRoutes(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['tablecloth/tables/new'] = 'tablecloth/tables/edit';
                $event->rules['tablecloth/tables/<tableId:\d+>'] = 'tablecloth/tables/edit';
            }
        );
    }

    /**
     * @return array
     */
    public function getCpNavItem(): array
    {
        $item = parent::getCpNavItem();
        $item['label'] = 'Tables';

        return $item;
    }
}