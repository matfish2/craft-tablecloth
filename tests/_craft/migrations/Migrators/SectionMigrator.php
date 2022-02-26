<?php


namespace tableclothtests\_craft\migrations\Migrators;

use craft\models\Section;
use tableclothtests\_craft\migrations\services\SectionService;

class SectionMigrator extends Migrator
{
    const SECTION_HANDLE = 'posts';

    public static function add(): bool
    {
        return (new SectionService())->add('Posts',
            self::SECTION_HANDLE,
            Section::TYPE_CHANNEL,
            '/{slug}',
            'post/_entry');
    }

    public static function remove(): bool
    {
        return (new SectionService())->remove(self::SECTION_HANDLE);
    }
}