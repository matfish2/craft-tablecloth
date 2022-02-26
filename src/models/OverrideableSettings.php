<?php


namespace matfish\Tablecloth\models;


trait OverrideableSettings
{
    public ?int $initialPerPage = 10;
    public ?array $perPageValues = [10, 25, 50, 100];

    public ?string $dateFormat = 'dd/MM/yyyy';
    public ?string $datetimeFormat = 'dd/MM/yyyy HH:ii:ss';
    public ?string $timeFormat = 'HH:ii';
    public ?int $paginationChunk = 5;
    public ?int $thumbnailWidth = 100;
    // Table height in pixels
    public ?int $height = 450;

    public $components = [
        'filters',
        'pagination',
        'entriesCount'
    ];
    public ?int $debounce = 300;

    /**
     * @return array
     */
    public function getPerPageList(): array
    {
        return array_map(static function ($item) {
            return [
                'label' => $item,
                'value' => $item
            ];
        }, $this->getTableOption('perPageValues'));
    }


    public function getPerPageValues()
    {
        return is_array($this->perPageValues) ? $this->perPageValues : explode(',', $this->perPageValues);
    }
}