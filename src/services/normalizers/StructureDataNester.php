<?php

namespace matfish\Tablecloth\services\normalizers;

class StructureDataNester
{
    protected array $data;
    protected array $lookup;
    protected $finalArray = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function nest(): array
    {
        $res = [];

        $this->lookup = [];

        foreach ($this->data as $row) {
            $parentId = $row['parentElementId'];
            if ($parentId) {
                $id = (string)$row['id'];
                $this->lookup['row_' . $id] = $parentId;
            }
        }

        foreach ($this->data as $row) {
            $parentId = $row['parentElementId'];

            if ($parentId) {
                $res = $this->setChildRow($res, $row, $row['id']);
            } else {
                $id = (string)$row['id'];
                $res['row_' . $id] = $row;
                $res['row_' . $id]['children'] = [];
            }
        }

        $res = array_values($res);

        foreach ($res as $index => $row) {
            $res[$index] = $this->assocToIndex($row);
        }

        return $res;
    }

    private function setChildRow($res, $row, $rowId): array
    {
        $refs = [];
        $reference = &$res;

        // Go from child all the way to root parent
        $pId = 'row_' . $rowId;
        while (isset($this->lookup[$pId])) {
            array_unshift($refs, 'children');
            array_unshift($refs, 'row_' . $this->lookup[$pId]);
            $pId = 'row_' . $this->lookup[$pId];
        }

        foreach ($refs as $ref) {
            if (!array_key_exists($ref, $reference)) {
                $reference[$ref] = [];
            }

            $reference = &$reference[$ref];
        }

        $reference['row_' . $rowId] = [];
        $reference = &$reference['row_' . $rowId];

        $reference = $row;

        unset($reference);

        return $res;
    }

    private function assocToIndex($row): array
    {
        if (isset($row['children'])) {
            $ref = &$row['children'];
            $ref = array_values($ref);

            foreach ($ref as &$r) {
                $r = $this->assocToIndex($r);
            }

            unset($r);
        }

        unset($ref);

        return $row;
    }
}