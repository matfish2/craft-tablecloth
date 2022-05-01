<?php

namespace matfish\Tablecloth\controllers;

use Craft;
use matfish\Tablecloth\elements\DataTable;
use yii\web\NotFoundHttpException;

class SiteDataController extends \craft\web\Controller
{
    public $enableCsrfValidation = false;
    public $allowAnonymous = self::ALLOW_ANONYMOUS_OFFLINE | self::ALLOW_ANONYMOUS_LIVE;

    /**
     * server and client tables - initial request
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \JsonException
     */
    public function actionGetInitialData(): \yii\web\Response
    {
        $dataTable = $this->getTableByHandle();

        return $this->asJson($dataTable->getInitialTableData());
    }

    /**
     * server table only
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGetData(): \yii\web\Response
    {
        $dataTable = $this->getTableByHandle();

        $params = Craft::$app->request->getQueryParams();

        return $this->asJson($dataTable->getData($params));
    }

    /**
     * server table only
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionGetCount(): \yii\web\Response
    {
        $dataTable = $this->getTableByHandle();

        $params = Craft::$app->request->getQueryParams();

        return $this->asJson($dataTable->getCount($params));
    }


    public function actionEntriesLists(): \yii\web\Response
    {
        $dataTable = $this->getTableByHandle();

        return $this->asJson($dataTable->getEntriesLists());
    }

    /**
     * @return DataTable
     * @throws NotFoundHttpException
     */
    private function getTableByHandle(): DataTable
    {
        $handle = $this->request->getQueryParam('handle');
        $siteId = $this->request->getQueryParam('siteId');

        $dataTable = DataTable::find()->handle($handle)->one();
        $dataTable->siteId = $siteId;

        if (!$dataTable) {
            throw new NotFoundHttpException("Datatable {$handle} not found.");
        }

        return $dataTable;
    }
}