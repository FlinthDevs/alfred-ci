<?php

if (isset($variables['node'])) {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $variables['node'];

    if ($node->bundle() == 'information_folder') {
      $variables['page']['has_cover'] = FALSE;
      /** @var Drupal\vdg_core\Utility\EntityFieldHelper $entity_field_helper */
      $entity_field_helper = \Drupal::service('vdg_core.utility.entity_field_helper');

      $cover = $entity_field_helper->getReferencedEntity($node, 'field_cover');

      if (!empty($cover)) {
        $variables['page']['has_cover'] = TRUE;
      }
    }
  }

