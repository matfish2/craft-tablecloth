<?php


namespace matfish\Tablecloth\models\Column;


class FullNameColumn extends Column
{
    public function getDbColumn(string $context = self::CONTEXT_SELECT): string
    {
        $addAlias = $context === self::CONTEXT_SELECT;

        $handle = "CONCAT([[users]].[[firstName]], ' ' ,[[users]].[[lastName]])";

        if ($addAlias) {
            $handle .= ' as [[fullName]]';
        }

        return $handle;
    }
}