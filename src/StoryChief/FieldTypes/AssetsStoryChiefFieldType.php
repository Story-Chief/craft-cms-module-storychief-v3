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
        } else {
            $volumeUID = explode(':', $field->defaultUploadLocationSource)[1];
        }
        $volumeID = Craft::$app->getVolumes()->getVolumeByUid($volumeUID)->id;
        
        $preppedData = [];
        
        $tempFolder = Craft::$app->getPath()->tempAssetUploadsPath;

        // get remote image and store in temp path
        $imageInfo = pathinfo($fieldData);
        $tempPath = $tempFolder . $imageInfo['basename'];
        file_put_contents($tempPath, fopen($fieldData, 'r'));

        $filename = Assets::prepareAssetName($imageInfo['basename']);

        $asset = new Asset();
        $asset->tempFilePath = $tempPath;
        $asset->filename = $filename;
        $asset->folderId = $volumeID;
        $asset->avoidFilenameConflicts = true;
        $response = Craft::$app->elements->saveElement($asset);


        // if the response is a success, get the file id
        if ($response) {
            $preppedData[] = $asset->id;
        }

        return $preppedData;
    }
}
