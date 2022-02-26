<?php


namespace matfish\Tablecloth\collections;


use matfish\Tablecloth\models\Column\Column;

class Collection
{
    /**
     * @var Column[]
     */
    protected array $columns = [];

    /**
     * ColumnsCollection constructor.
     * @param Column[] $columns
     */
    public function __construct(array $columns)
    {
        $this->columns = $columns;
    }

    /**
     * @return Column[]
     */
    public function all(): array
    {
        return $this->columns;
    }
}