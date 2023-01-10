<?php namespace storychief\storychiefv3\storychief\FieldTypes;

use Craft;
use  craft\base\Field;
use craft\elements\Asset;
use craft\helpers\Assets;

class AssetsStoryChiefFieldType implements StoryChiefFieldTypeInterface
{
    public function supportedStorychiefFieldTypes()
    {
        return [
            'featured_image',
            'image',
        ];
    }

    public function prepFieldData(Field $field, $fieldData)
    {
        if ($field->restrictLocation) {
            $volumeUID = explode(':', $field->restrictedLocationSource)[1];
            $subPath = $field['restrictedLocationSubpath'];
        } else {
            $volumeUID = explode(':', $field->defaultUploadLocationSource)[1];
            $subPath = $field['defaultUploadLocationSubpath'];
        }

        $volumeID = Craft::$app->getVolumes()->getVolumeByUid($volumeUID)->id;
        $folderID = Craft::$app->assets->getRootFolderByVolumeId($volumeID)->id;

        $preppedData = [];        

        // get remote image and store in temp path
        $imageInfo = pathinfo($fieldData);
        $tempPath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . $imageInfo['basename'];
        $filename = Assets::prepareAssetName($imageInfo['basename']);

        // Look if the filename already exists and so the existing asset
        $asset = Asset::find()->where(
            [
                'assets.volumeID' => $volumeID, 
                'assets.folderId' => $folderID, 
                'assets.filename' => $filename
            ]
        )->one();

        if (!$asset) {            
            file_put_contents($tempPath, fopen($fieldData, 'r'));

            $asset = new Asset();
            $asset->tempFilePath = $tempPath;
            $asset->filename = $filename;
            $asset->volumeId = $volumeID;
            $asset->folderId = $folderID;
            $asset->folderPath = $subPath;
            $asset->avoidFilenameConflicts = true;

            if (!Craft::$app->elements->saveElement($asset)) {
                $asset = null; // The response failed
            }
        }

        // Get the file id
        if ($asset) {
            $preppedData[] = $asset->id;
        }

        return $preppedData;
    }
}
