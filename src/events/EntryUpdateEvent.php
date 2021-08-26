<?php

namespace storychief\storychiefv3\events;

use craft\elements\Entry;
use storychief\storychiefv3\models\Settings;
use yii\base\Event;

class EntryUpdateEvent extends Event
{
    /** @var array */
    public $payload;

    /** @var Settings */
    public $settings;

    /** @var Entry */
    public $entry;
}
