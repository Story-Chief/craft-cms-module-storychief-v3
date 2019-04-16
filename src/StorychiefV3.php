<?php
/**
 * Storychief v3 plugin for Craft CMS 3.x
 *
 * Craft CMS plugin to use with Storychief
 *
 * @link      https://github.com/Story-Chief/
 * @copyright Copyright (c) 2019 Storychief
 */

namespace storychief\storychiefv3;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class StorychiefV3
 *
 * @author    Storychief
 * @package   StorychiefV3
 * @since     1.0.0
 *
 */
class StorychiefV3 extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var StorychiefV3
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'storychief-v3',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
