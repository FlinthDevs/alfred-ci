<?php

/**
 * @file
 * Contains hook implementations for vdg_voting module.
 */

/**
 * Implements hook_form_alter().
 */
function vdg_voting_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  if (strpos($form_id, 'fivestar_field_vote__vote_fivestar_votingapi_fivestar_form') !== false) {
    $form['#attached']['library'][] = 'vdg_voting/vdg_voting';
    $form['#attributes']['class'][] = 'notes';
    $form['#attributes']['class'][] = 'classique';
  }
}
