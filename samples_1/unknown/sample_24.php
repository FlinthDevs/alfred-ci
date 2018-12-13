<?php

/**
 * @file
 * Contains hook implementations for vdg_epublications module.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;

/**
 * Implements hook_form_id_alter().
 */
function vdg_comment_form_comment_comment_form_alter(&$form, FormStateInterface &$form_state, $form_id) {
  $form['field_comment_body']['widget'][0]['value']['#title'] = t('Your comment');
  $form['field_comment_body']['widget'][0]['value']['#title_display'] = 'invisible';
  $form['field_comment_body']['widget'][0]['value']['#attributes']['class'][] = 'saisie';

  $form['actions']['submit']['#value'] = t('Comment', [], ['context' => 'verb']);
  $form['actions']['submit']['#attributes']['class'] = [
    'bouton',
    'primaire',
  ];

  $current_user = \Drupal::currentUser();
  if ($current_user->isAuthenticated()) {
    $form['author']['name']['#default_value'] = $current_user->getAccountName();
    $form['author']['name']['#disabled'] = TRUE;
    $form['author']['name']['#access'] = TRUE;
  }
  else {
    $form['author']['name']['#attributes']['placeholder'] = t('Your name (optional)');
  }
}

/**
 * Implements hook_preprocess_HOOK().
 */
function vdg_comment_preprocess_field__field_comment(&$variables) {
  /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
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
}

/**
 * Implements hook_preprocess_HOOK().
 */
function vdg_comment_preprocess_field__node__field_comment__commercial_spaces(&$variables) {
  vdg_comment_preprocess_field__field_comment($variables);
}

/**
 * Implements hook_preprocess_HOOK().
 */
function vdg_comment_preprocess_comment(&$variables) {
  $date = $variables['comment']->getCreatedTime();
  $variables['created'] = \Drupal::service('date.formatter')->format(intval($date), 'format_date_comment');
}
