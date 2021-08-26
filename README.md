
# StoryChief v3 plugin for Craft CMS 3.x

Craft CMS plugin to use with [StoryChief](https://storychief.io).


## Requirements

This plugin requires Craft CMS 3.0.0 or later. (If you are using Craft CMS 2.x, you can find the [right plugin here](https://github.com/Story-Chief/craft-cms-module-storychief).)


## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Storychief”. Then click on the “Install” button in its modal window.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require storychief/craft-cms-v3-storychief

# tell Craft to install the plugin
./craft install/plugin storychief-v3
```


## Activate
To activate the plugin you first need to set up a new Craft CMS channel on your StoryChief admin panel. As soon as you create one, it will give you an **encryption key** .

In your CRAFT CMS, go to your Settings/Plugins and activate your StoryChief plugin. Go to its Settings and fill the encryption key and website URL. 

Save it.

Finally, back to your StoryChief CRAFT CMS channel configuration, fill up your CRAFT CMS site URL and save

:)

## Events

Note: this is mostly for developers that know basic PHP and Composer Packages.

### `beforeEntryPublish` and `beforeEntryUpdate` event

This allows developers to execute custom functionality before saving a new or updating an entry, to modify data of a 
new entry.

```php 

use storychief\storychiefv3\events\EntryPublishEvent;

$this->on('beforeEntryPublish', function (EntryPublishEvent $event) {
    $payload = $event->payload;
    $settings = $event->settings;
    $entry = $event->entry;
    
    // Example 1:
    $entry->sectionId = 2; // BLog
    $entry->typeId = 2;
    
    // Example 2:
    if ($payload['data']['custom_fields']) {
        foreach ($payload['data']['custom_fields'] as $customField) {
            if ($customField['key'] === 'custom_field_name') {                
                $entry->sectionId = $customField['value'];
                $entry->typeId = 2;
            }
        }
    }
});

use storychief\storychiefv3\events\EntryUpdateEvent;

$this->on('beforeEntryUpdate', function (EntryUpdateEvent $event) {
    // ...
});
```

### `afterEntryPublish` event

This allows developers to execute custom functionality after a new entry, pushed by Storychief, is saved in Craft.

### `afterEntryUpdate` event

This allows developers to execute custom functionality after an update to an entry, pushed by Storychief, is saved in Craft.

Both events send out a `EntrySaveEvent` with the saved `Entry` object as its property.

---

Brought to you by [StoryChief](https://github.com/Story-Chief/)

