# Magenerds_PageDesigner

This extension facilitates the cms editing process in your store.   
Instead of just a wysiwyg editor you now have a drag and drop editor to layout your blocks.
* Magento 2.3 compatibility version 4.0.0
* Magento 2.2 and lower version 3.x

## Extension installation
The easiest way to install the Magenerds module is via composer
```
# add to composer require
composer require magenerds/pagedesigner

# run magento setup to activate the module
bin/magento set:up
```

## Extension configuration
The extension can be configured if you go to ```Stores > Configuration``` and afterwards to ```Magenerds > Page Designer```.

![PageDesigner-Configuration](_images/pd_config.png?raw=true "PageDesigner Configuration")

* **CSS Classes (for columns)**:  
A comma separated list of pre defined css classes which the cms editor can choose from.  
The default class pd-highlight is just a dummy class and does nothing.  
It is important that a developer has to include the css classes into the theme before using them.
* **CSS Classes (for rows)**:  
A comma separated list of pre defined css classes which the cms editor can choose from.  
The default class pd-highlight is just a dummy class and does nothing.  
It is important that a developer has to include the css classes into the theme before using them.

## How to use
The page designer can be used for cms blocks and cms pages. Add a new block/page or edit an existing one.

![PageDesigner-Usage](_images/pd_usage.png?raw=true "PageDesigner Usage")

1. Select the responsive layout. You can define different layouts for smartphone, tablet, laptop and desktop
1. Add more rows
1. Click + in order to open up the editor. There you can choose from the widget list or just a wysiwyg editor
1. Drag and drop the row
1. Click + in order to add a column in the current row
1. Click to add pre defined css classes to the row
1. Delete the row
1. Drag and drop the column
1. Click to add pre defined css classes to the column
1. Delete the row

Here is an example how a layout can look like:

![PageDesigner-Example](_images/pd_usage2.png?raw=true "PageDesigner Example")

You can import cms blocks you already created into another cms block in order to build on already existing layouts.   
This helps you to not start from scratch. All cms blocks created with the page designer are visible in the dropdown Import Static Block.

##Video Tutorial
Watch a short video about the extension:

[![Magenerds Page Designer](https://img.youtube.com/vi/E0wZzVPFhM0/0.jpg)](https://www.youtube.com/watch?v=E0wZzVPFhM0 "Magenerds Page Designer")

* Magento 2.3 compatibility version 4.0.0
* Magento 2.2 and lower version 3.x
