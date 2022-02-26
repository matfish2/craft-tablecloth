<?php


namespace matfish\Tablecloth\services\elementqueries\ProductVariant;

class ProductVariantCombiner
{
    public function combine($data, $variantsData): array
    {
        $data = $this->attachOwnerIdAsKey($data, 'id');

        $variantsData = $this->attachOwnerIdAsKey($variantsData, 'productId');

        foreach ($variantsData as $key => $rows) {

            if (!isset($data[$key]['variants'])) {
                $data[$key]['variants'] = [];
            }

            $data[$key]['variants'] = $rows;
        }

        return array_values($data);
    }

    /**
     * @param array $array
     * @param string $idKey
     * @return array
     */
    private function attachOwnerIdAsKey(array $array, string $idKey)
    {
        $res = [];

        foreach ($array as $row) {
            $id = $row[$idKey];
            if ($idKey === 'id') {
                $res[$id] = $row;
            } else {
                $res[$id][] = $row;
            }
        }

        return $res;
    }
}