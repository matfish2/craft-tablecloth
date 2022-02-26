<?php


namespace matfish\Tablecloth\models\Column;


use craft\base\Model;
use craft\web\View;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\services\templates\TemplateFinder;
use matfish\Tablecloth\TableclothAssetBundle;

class TableFieldColumn extends Model
{
    public string $datatableHandle;
    public string $tableFieldHandle;

    public string $type;
    public string $heading;
    public string $handle;
    // List fields only
    public array $options = [];

    public ?string $templatePath;
    public ?string $templateMode;

    public function isBool(): bool
    {
        return in_array($this->type, ['checkbox', 'lightswitch']);
    }

    public function isList() : bool {
        return $this->type==='select';
    }

    public function isDate() : bool {
        return $this->type === 'date';
    }

    public function getLabel($optionValue): string
    {
        $s =  array_filter($this->options, function($option) use ($optionValue) {
            return $option['value']===$optionValue;
        });

        return $s ? $s[0]['label'] : 'unknown';
    }

    public function setTemplatePath(): void
    {
        $field = strtolower($this->type);

        $type = $this->isBool() ? 'boolean' : $field;

        $finder = new TemplateFinder(DataTable::find()->handle($this->datatableHandle)->one());

        $fieldPath = $finder->getTemplatePath("fields/{$this->tableFieldHandle}/{$this->handle}");

        if ($fieldPath) {
            $this->templatePath = $fieldPath;
        } else {
            $typePath = $finder->getTemplatePath("types/{$type}");

            $this->templatePath = $typePath;
        }

        if ($this->templatePath) {
            $this->templateMode = str_contains($this->templatePath, '_tablecloth') ? View::TEMPLATE_MODE_SITE : View::TEMPLATE_MODE_CP;
        }
    }

    public function renderTemplate($valueKey): string
    {
        return \Craft::$app->view->renderTemplate($this->templatePath, ['value' => $valueKey, 'svgPath' => $this->getSvgPath()], $this->templateMode);
    }

    private function getSvgPath()
    {
        $view = \Craft::$app->view;
        $bundle = TableclothAssetBundle::register($view);

        return $bundle->baseUrl . '/site/svg/';
    }
}