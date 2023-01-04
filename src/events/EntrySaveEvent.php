<?php
/**
 * Created by Wouter Van Scharen.
 * Date: 16.07.19
 */

namespace storychief\storychief\events;

use yii\base\Event;

class EntrySaveEvent extends Event
{
    /**
     * @var \craft\elements\Entry List of registered component types classes.
     */
    public $entry;
}
