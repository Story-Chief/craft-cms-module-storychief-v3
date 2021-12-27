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
        if ($field->useSingleFolder) {
            $volumeUID = explode(':', $field->singleUploadLocationSource)[1];
            $subPath = $field['singleUploadLocationSubpath'];
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

        file_put_contents($tempPath, fopen($fieldData, 'r'));

        $filename = Assets::prepareAssetName($imageInfo['basename']);

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = $filename;
        $asset->volumeId = $volumeID;
        $asset->folderId = $folderID;
        $asset->folderPath = $subPath;
        $asset->avoidFilenameConflicts = true;

        $response = Craft::$app->elements->saveElement($asset);

        // if the response is a success, get the file id
        if ($response) {
            $preppedData[] = $asset->id;
        }

        return $preppedData;
    }
}
