<?php

  $entity = $variables['element']['#object'];

  $total = \Drupal::database()->select('comment_field_data', 'cfd')
    ->condition('cfd.status', NodeInterface::PUBLISHED)
    ->condition('cfd.comment_type', $variables['comment_type'])
    ->condition('cfd.entity_type', $variables['entity_type'])
    ->condition('cfd.field_name', $variables['field_name'])
    ->condition('cfd.entity_id', $entity->id())
    ->countQuery()
    ->execute()
    ->fetchField();
  $variables['comment_total'] = $total;
  $variables['#attached']['library'][] = 'vdg_comment/comments';

