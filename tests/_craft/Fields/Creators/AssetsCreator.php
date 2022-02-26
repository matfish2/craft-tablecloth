<?php


namespace tableclothtests\_craft\Fields\Creators;


use Craft;
use craft\base\VolumeInterface;
use craft\elements\Asset;
use craft\helpers\Assets;
use craft\records\User;
use craft\records\VolumeFolder;
use craft\services\Path;
use craft\volumes\Local;

class AssetsCreator extends FieldCreator
{
    public function getFieldData(): array
    {
        $volume = $this->generateAssets();
        $imagesFolderId = $this->getImagesFolderId($volume->id);

        return [
            $this->settingsKey => [
                'sources' => '*',
                'defaultUploadLocationSource' => $imagesFolderId,
                'singleUploadLocationSource' => $imagesFolderId,
                'defaultUploadLocationSubpath' => '',
                'singleUploadLocationSubpath' => '',
            ]
        ];
    }

    /**
     * @throws \Exception
     */
    private function generateAssets() : VolumeInterface
    {
        $volume = Craft::$app->volumes->getVolumeByHandle('volume');

        if ($volume) {
            return $volume;
        }

        $assetVolume = $this->generateVolume();
        $assets = Asset::find()->volume($assetVolume)->all();

        // Generate new assets
        if (count($assets) === 0) {
            // Find folder
            $folder = VolumeFolder::findOne([
                'volumeId' => $assetVolume->id
            ]);

            // images in plugin folder
            $dir = dirname(__DIR__, 2) . '/assets/images';

            // temp folder in project
            $path = new Path();
            $tempDirPath = $path->getTempPath();

            for ($i = 1; $i <= 11; $i++) {
                $image = "img_{$i}";
                // move file from plugin assets to project temp folder
                $filename = $image . '.jpg';
                $filenameUnique = $image . '_' . random_int(100, 10000) . '.jpg';
                $path = $dir . DIRECTORY_SEPARATOR . $filename;
                $tempFilePath = $tempDirPath . DIRECTORY_SEPARATOR . $filenameUnique;
                file_put_contents($tempFilePath, file_get_contents($path));
//                codecept_debug($tempFilePath);
//                codecept_debug($path);


                // Upload asset to permanent folder
                // and create DB record
                $result = $this->uploadNewAsset($folder, $tempFilePath, $filenameUnique);

                $assets[] = $result;
            }
        }

        return $assetVolume;

    }

    private function uploadNewAsset($folder, string $path, $filename)
    {
        $filename = Assets::prepareAssetName($filename);

        $asset = new Asset();
        $asset->tempFilePath = $path;
        $asset->filename = $filename;
        $asset->newFolderId = $folder->id;
        $asset->setVolumeId($folder->volumeId);
        $asset->uploaderId = User::find()->one()->id;
        $asset->avoidFilenameConflicts = true;
        $asset->setScenario(Asset::SCENARIO_CREATE);

        $res = Craft::$app->getElements()->saveElement($asset);


        return $asset;
    }

    private function generateVolume(): VolumeInterface
    {
        $volume = Craft::$app->volumes->getVolumeByHandle('volume');

        if ($volume) {
            return $volume;
        }

        $volumesService = Craft::$app->getVolumes();

        $volume = $volumesService->createVolume([
            'type' => Local::class,
            'name' => 'Volume',
            'handle' => 'volume',
            'hasUrls' => 1,
            'url' => '@web/volume',
            'settings' => [
                'path' => '@webroot/volume'
            ]
        ]);

        $volumesService->saveVolume($volume);

        return $volume;
    }

    private function getImagesFolderId($volumeId): string
    {
        $folder = Craft::$app->assets->getRootFolderByVolumeId($volumeId);

        return 'folder:' . $folder->uid;
    }

}