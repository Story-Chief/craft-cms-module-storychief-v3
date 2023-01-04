<?php

/**
 * Storychief v3 plugin for Craft CMS 3.x
 *
 * Craft CMS plugin to use with Storychief
 *
 * @link      https://github.com/Story-Chief/
 * @copyright Copyright (c) 2019 Storychief
 */

namespace storychief\storychief;

use Craft;
use yii\base\Event;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\PluginEvent;
use storychief\storychief\models\Settings;
use storychief\storychief\variables\StoryChiefVariable;
use craft\events\RegisterUrlRulesEvent;

/**
 * Class storychief
 *
 * @author    Storychief
 * @package   storychief
 * @since     1.0.0
 *
 */
class storychief extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var storychief
     */
    public static $plugin;


    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';
    public $hasCpSettings = true;

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

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function (Event $event) {
            /** @var CraftVariable $variable */
            $variable = $event->sender;
            $variable->set('storyChief', StoryChiefVariable::class);
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function (RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'storychief/webhook' => 'storychief/webhook/callback',
            ]);
        });

        Craft::info(
            Craft::t(
                'storychief',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================
    protected function createSettingsModel()
    {
        return new Settings();
    }
    protected function settingsHtml()
    {
        $settings = $this->getSettings();
        $settings->validate();

        return Craft::$app->getView()->renderTemplate('storychief/settings', [
            'plugin' => $this,
            'title'  => $this->handle,
            'settings' => $settings,
        ]);
    }
}
