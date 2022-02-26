<?php

namespace matfish\Tablecloth\services\templates;

use craft\web\View;
use matfish\Tablecloth\elements\DataTable;

class TemplateFinder
{
    protected DataTable $dataTable;

    /**
     * TemplateFinder constructor.
     * @param string $handle
     */
    public function __construct(DataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    public function componentPath($component): ?string
    {
        return $this->getTemplatePath("template/{$component}");
    }

    public function defaultComponentPath($component): ?string
    {
        return $this->getTemplatePath("template/{$component}", true);
    }

    public function getTemplatePath($path, $forceDefault = false): ?string
    {
        $view = \Craft::$app->view;

        $vendorPath = 'tablecloth/_site' . DIRECTORY_SEPARATOR . $path;

        if (!$forceDefault) {
            $userPathTable = '_tablecloth/tables' . DIRECTORY_SEPARATOR . $this->dataTable->handle . DIRECTORY_SEPARATOR . $path;
            $userPathPreset = '_tablecloth/presets' . DIRECTORY_SEPARATOR . $this->dataTable->preset . DIRECTORY_SEPARATOR . $path;

            \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

            // 1. User override for this specific table
            if ($view->doesTemplateExist($userPathTable, View::TEMPLATE_MODE_SITE)) {
                return $userPathTable;
            }

            // 2. User override: preset
            if ($view->doesTemplateExist($userPathPreset, View::TEMPLATE_MODE_SITE)) {
                return $userPathPreset;
            }
        }

        // 3. Default template
        if ($view->doesTemplateExist($vendorPath, View::TEMPLATE_MODE_CP)) {
            \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

            return $vendorPath;
        }

        return null;
    }

}