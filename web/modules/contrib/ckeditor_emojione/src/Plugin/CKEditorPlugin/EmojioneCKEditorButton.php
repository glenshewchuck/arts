<?php

namespace Drupal\ckeditor_emojione\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "emojione" plugin.
 *
 * NOTE: The plugin ID ('id' key) corresponds to the CKEditor plugin name.
 * It is the first argument of the CKEDITOR.plugins.add() function in the
 * plugin.js file.
 *
 * @CKEditorPlugin(
 *   id = "emojione",
 *   label = @Translation("Emojione ckeditor button")
 * )
 */
class EmojioneCKEditorButton extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $path = $this->getLibraryPath();
    return [
      'Emojione' => [
        'label' => t('Emoji ckeditor button'),
        'image' => $path . '/icons/emojione.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    $path = $this->getLibraryPath();
    return $path . '/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

  /**
   * Get the CKEditor Emojione library path.
   *
   * @return string
   *   The library path with support for the Libraries API module.
   */
  protected function getLibraryPath() {
    // Support for "Libraries API" module.
    if (\Drupal::moduleHandler()->moduleExists('libraries')) {
      return \Drupal::service('library.libraries_directory_file_finder')->find('emojione');
    }

    return 'libraries/emojione';
  }


}
