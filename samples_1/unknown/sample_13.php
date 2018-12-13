<?php

/**
 * @file
 * Contains hook implementations for vdg_block_contact module.
 */

/**
 * Implements hook_theme().
 */
function vdg_block_contact_theme($existing, $type, $theme, $path) {
  return [
    'vdg_block_contact_title_name' => [
      'variables' => [
        'title_name' => '',
        'link_uri' => '',
      ],
    ],
  ];
}
