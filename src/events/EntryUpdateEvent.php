<?php

namespace storychief\storychief\events;

use craft\elements\Entry;
use storychief\storychief\models\Settings;
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
