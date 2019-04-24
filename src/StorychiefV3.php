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
use yii\base\Event;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\PluginEvent;
use storychief\storychiefv3\models\Settings;
use storychief\storychiefv3\variables\StoryChiefVariable;
use craft\events\RegisterUrlRulesEvent;

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
                'storychief/webhook' => 'storychief-v3/webhook/callback',
            ]);
        });

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
    protected function createSettingsModel()
    {
        return new Settings();
    }
    protected function settingsHtml()
    {
        $settings = $this->getSettings();
        $settings->validate();

        return Craft::$app->getView()->renderTemplate('storychief-v3/settings', [
            'plugin' => $this,
            'title'  => $this->handle,
            'settings' => $settings,
            'redirect' => 'settings/plugins/storychief-v3',
        ]);
    }
}
