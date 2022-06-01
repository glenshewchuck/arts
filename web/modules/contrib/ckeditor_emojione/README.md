# INTRODUCTION

This module Integrates the CKEditor Emojione plugin to CKEditor for Drupal 8.

Allows users to add various emojis using a CKEditor dialog.

# INSTALLATION

1. Install the [Composer  Merge Plugin](https://github.com/wikimedia/composer-merge-plugin):

```
composer require wikimedia/composer-merge-plugin
```
2. Edit the "composer.json" file of your website and under the `"extra": {` section add:

```
"merge-plugin": {
  "include": [
    "web/modules/contrib/ckeditor_emojione/composer.libraries.json"
  ]
},
```
 Note: the `web` represents the folder where drupal lives, eg. `docroot`. From now on, every time the `composer.json` file is updated, it will also read the content of `composer.libraries.json` file located at `web/modules/contrib/ckeditor_emojione/` and update accordingly.
3. Install required libraries:

```
composer update
```
4. Enable CKEditor Emojione in the Drupal admin.
5. Configure your WYSIWYG toolbar to include the button.

REQUIREMENTS
------------
CKEditor Module (Core)