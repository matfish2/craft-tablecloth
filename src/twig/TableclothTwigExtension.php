<?php

namespace matfish\Tablecloth\twig;

use craft\test\mockclasses\TwigExtension;
use craft\web\View;
use matfish\Tablecloth\elements\DataTable;
use matfish\Tablecloth\exceptions\TableclothException;
use matfish\Tablecloth\TableclothAssetBundle;
use Twig\TwigFunction;

class TableclothTwigExtension extends TwigExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('tablecloth', [$this, 'renderDatatable']),
        ];
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function renderDatatable($handle, $preset = 'default'): string
    {
        $dt = DataTable::find()->handle($handle)->one();

        if (!$dt) {
            throw new TableclothException("Cannot find table with handle '{$handle}'");
        }

        $tablePath = $this->getTablePath($dt->preset);

        $view = \Craft::$app->view;

        $bundle = TableclothAssetBundle::register($view);

        return $view->renderTemplate($tablePath['path'], [
            'preset' => $preset,
            'datatable' => $dt,
            'svgPath' => $bundle->baseUrl . '/site/svg/'
        ], $tablePath['mode']);

    }

    private function getTablePath($preset)
    {
        $view = \Craft::$app->view;

        $path = "tablecloth/_site/template/index";
        $mode = View::TEMPLATE_MODE_CP;

        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $generalOverridePath = "_tablecloth/presets/{$preset}/template/index";

        if ($view->doesTemplateExist($generalOverridePath, View::TEMPLATE_MODE_SITE)) {
            $path = $generalOverridePath;
            $mode = View::TEMPLATE_MODE_SITE;
        }

        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);

        return compact('path', 'mode');
    }
}