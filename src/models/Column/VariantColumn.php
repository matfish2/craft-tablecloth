<?php


namespace matfish\Tablecloth\models\Column;


use craft\base\FieldInterface;

class VariantColumn extends Column
{

    public function getDbColumn(string $context = self::CONTEXT_SELECT): string
    {
        $v = "variants.{$this->getHandle()}";
        if ($context === self::CONTEXT_SELECT) {
            $v .= " {$this->getFrontEndHandle()}";
        }

        return $v;
    }

    public function getFrontEndHandle(): string
    {
        return 'variant__' . $this->getHandle();
    }

    public function getHandle()
    {
        return explode(':', $this->handle)[1];
    }

    public function isProduct(): bool
    {
        return true;
    }

    /**
     * @return FieldInterface
     */
    public function getField(): FieldInterface
    {
        return \Craft::$app->fields->getFieldByHandle($this->getHandle());
    }


}