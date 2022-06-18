<?php

/**
 * @file
 * Lists available colors and color schemes for the TaraPro theme.
 */

$info = [
  // Available colors and color labels used in theme.
  'fields' => [
    'top' => t('Primary Color'),
    'header_top' => t('Header top & breadcrumb background'),
    'bottom' => t('Header background'),
    'menu' => t('Header main menu Color'),
    'bg' => t('Main background'),
    'sidebar' => t('Light Color'),
    'footer' => t('Footer background'),
    'text' => t('Text color'),
    'heading' => t('Heading color'),
    'border'  => t('Border Color'),
    'social_icon' => t('Social icons color'),
  ],
  // Pre-defined color schemes.
  'schemes' => [
    'default' => [
      'title' => t('TaraPro (default)'),
      'colors' => [
        'top' => '#dc5431',
        'header_top' => '#e4e4dd',
        'bottom' => '#ffffff',
        'menu' => '#212121',
        'bg' => '#f4f4ef',
        'sidebar' => '#edede8',
        'footer' => '#e4dcd3',
        'text' => '#676767',
        'heading' => '#222222',
        'border'  => '#dedede',
        'social_icon' => '#c4bdb5',
      ],
    ],
  ],

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => [
    'css/colors.css',
  ],

  // Files to copy.
  'copy' => [
    'logo.svg',
  ],

  // Gradient definitions.
  'gradients' => [
    [
      // (x, y, width, height).
      'dimension' => [0, 0, 0, 0],
      // Direction of gradient ('vertical' or 'horizontal').
      'direction' => 'vertical',
      // Keys of colors to use for the gradient.
      'colors' => ['top', 'bottom'],
    ],
  ],

  // Preview files.
  'preview_library' => 'tarapro/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'tarapro'),
      ],
    ],
  ],
];
