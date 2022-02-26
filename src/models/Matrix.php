<?php

namespace matfish\Tablecloth\models;

use craft\fields\Matrix as MatrixField;
use craft\web\View;
use matfish\Tablecloth\exceptions\TableclothException;

class Matrix
{
    protected MatrixField $matrix;
    protected string $tableHandle;

    /**
     * Matrix constructor.
     * @param MatrixField $matrix
     * @param string $tableHandle
     */
    public function __construct(MatrixField $matrix, string $tableHandle)
    {
        $this->matrix = $matrix;
        $this->tableHandle = $tableHandle;
    }

    /**
     * @return \craft\models\MatrixBlockType[]
     */
    public function getBlocks(): array
    {
        return $this->matrix->getBlockTypes();
    }

    /**
     * @return string|null
     */
    public function getHandle() : string
    {
        return $this->matrix->handle;
    }

    /**
     * @param $block
     * @return string
     * @throws TableclothException
     * @throws \yii\base\Exception
     */
    public function getBlockPath($block): string
    {
        $view = \Craft::$app->view;

        \Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

        $path = "_tablecloth/tables/{$this->tableHandle}/fields/matrices/{$this->getHandle()}/{$block['handle']}";

        if (!$view->doesTemplateExist($path, View::TEMPLATE_MODE_SITE)) {
            throw new TableclothException("Matrix block template not found at {$path}");
        }


        return $path;
    }

}