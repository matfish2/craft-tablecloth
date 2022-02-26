<?php

namespace matfish\Tablecloth\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class DataTableQuery extends ElementQuery
{
    public ?string $handle = null;
    public ?string $source = null;

    public function handle($value): self
    {
        $this->handle = $value;

        return $this;
    }

    public function name($name): self
    {
        $this->name = $name;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the tablecloth table
        $this->joinElementTable('tablecloth');

        $this->query->select([
            'tablecloth.id',
            'tablecloth.name',
            'tablecloth.handle',
            'tablecloth.source',
            'tablecloth.sectionId',
            'tablecloth.typeId',
            'tablecloth.groupId',
            'tablecloth.externalApiDetails',
            'tablecloth.allUsers',
            'tablecloth.userGroups',
            'tablecloth.variantsStrategy',
            'tablecloth.serverTable',
            'tablecloth.columns',
            'tablecloth.filterPerColumn',
            'tablecloth.initialSortColumn',
            'tablecloth.initialSortAsc',
            'tablecloth.enableChildRows',
            'tablecloth.childRowMatrixFields',
            'tablecloth.childRowTableFields',
            'tablecloth.datasetPrefilter',
            'tablecloth.overrideGeneralSettings',
            'tablecloth.components',
            'tablecloth.debounce',
            'tablecloth.perPageValues',
            'tablecloth.initialPerPage',
            'tablecloth.paginationChunk',
            'tablecloth.dateFormat',
            'tablecloth.datetimeFormat',
            'tablecloth.timeFormat',
            'tablecloth.thumbnailWidth',
            'tablecloth.height',
            'tablecloth.preset'
        ]);

        if ($this->source) {
            $this->subQuery->andWhere(Db::parseParam('tablecloth.source', $this->source));
        }

        if ($this->handle) {
            $this->subQuery->andWhere(Db::parseParam('tablecloth.handle', $this->handle));
        }

        return parent::beforePrepare();
    }
}