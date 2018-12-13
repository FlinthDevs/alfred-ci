<?php

$recipes_nids = [];
if (!empty($ref)) {
  $recipes_nids = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->getQuery()
    ->condition('type', 'recipe')
    ->condition('field_recipe_code', $ref, 'IN')
    ->condition('status', 1)
    ->execute();
}
