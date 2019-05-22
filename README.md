
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



Brought to you by [StoryChief](https://github.com/Story-Chief/)

