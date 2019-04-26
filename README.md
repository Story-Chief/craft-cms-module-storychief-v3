
# StoryChief v3 plugin for Craft CMS 3.x

Craft CMS plugin to use with [StoryChief](https://storychief.io).

If you are using Craft CMS 2.x, you can find the [right plugin here](https://github.com/Story-Chief/craft-cms-module-storychief).

## How to install

First you need to save your plugin to a directory in your project. Craft doesn't require it to be a specific directory, but we are creating a plugins folder in our root and unziping the plugin inside its own folder. Like so:

    config/
    modules/
    plugins/craft-cms-module-storychief-v3
    ...
    
To get Craft to see your plugin, you will need to install it as a Composer dependency of your Craft project.


To set it up, open your Craft project’s composer.json file and make the following changes:

 - Set minimum-stability to "dev"
 - Set prefer-stable to true
 - Add a new path repository record, pointed at your plugin’s root directory.

Hera are the modifications you should make on your **composer.json**


    {
    
      "minimum-stability": "dev",
      "prefer-stable": true,
      "repositories": [
        {
          "type": "path",
	      "url": "plugins/craft-cms-module-storychief-v3"
        }
      ]
    }


TIP: Set the url value to the absolute or relative path to your plugin’s source directory. 

In your terminal, go to your Craft project and tell Composer to require your plugin. 

`composer require storychief/storychief-v3`


## Activate
To activate the plugin you first need to set up a new Craft CMS channel on your StoryChief admin panel. As soon as you create one, it will give you an **encryption key** .

In your CRAFT CMS, go to your Settings/Plugins and activate your StoryChief plugin. Go to its Settings and fill the encryption key and website URL. 

Save it.

Finally, back to your StoryChief CRAFT CMS channel configuration, fill up your CRAFT CMS site URL and save

:)



Brought to you by [StoryChief](https://github.com/Story-Chief/)

