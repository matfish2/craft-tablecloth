<?php

namespace tableclothtests\_craft\migrations\services;

use Craft;
use craft\models\Section;
use craft\models\Section_SiteSettings;
use craft\records\Site;

class SectionService
{

    public function add($name, $handle, $type, $url, $template): bool
    {

        $section = Craft::$app->sections->getSectionByHandle($handle);

        if ($section) {
            return true;
        }

        $section = new Section([
            'name' => $name,
            'handle' => $handle,
            'type' => $type,
            'siteSettings' => [
                new Section_SiteSettings([
                    'siteId' => Craft::$app->sites->primarySite->id,
                    'enabledByDefault' => true,
                    'hasUrls' => true,
                    'uriFormat' => 'blog' . $url,
                    'template' => 'blogify/' . $template,
                ]),
            ]
        ]);

        if (!Craft::$app->sections->saveSection($section)) {
            throw new \Exception("Failed to create section {$name} " . json_encode($section->getErrors()));
        }

        return true;
    }

    public function remove($handle): bool
    {
        $section = Craft::$app->sections->getSectionByHandle($handle);

        if ($section) {
            return Craft::$app->sections->deleteSection($section);
        }

        return false;
    }
}