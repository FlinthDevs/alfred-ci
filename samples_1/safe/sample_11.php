
<?php

/**
 * @file
 * Contains hook implementations for vdg_voting module.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_alter().
 */
function vdg_voting_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if (strpos($form_id, 'fivestar_field_vote__vote_fivestar_votingapi_fivestar_form') !== FALSE) {
    $form['#attached']['library'][] = 'vdg_voting/vdg_voting';
    $form['#attributes']['class'][] = 'notes';
    $form['#attributes']['class'][] = 'classique';
  }
}
