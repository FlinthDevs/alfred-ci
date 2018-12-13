<?php

/**
 * @file
 * Contains hook implementations for vdg_city_map module.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_preprocess().
 */
function vdg_city_map_preprocess(&$variables, $hook) {
  $variables['#attached']['library'][] = 'vdg_city_map/vdg_city_map';
}

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Add classes to form elements.
 */
function vdg_city_map_form_views_exposed_form_alter(&$form, FormStateInterface $form_state, $form_id) {
  if ($form['#id'] == 'views-exposed-form-city-map-list-page-1') {
    if (array_key_exists('field_district', $form)) {
      $form['field_district']['#options']['All'] = t('tous les quartiers');
    }

    $form_additional_attributes = [
      '#attributes' => [
        'class' => [
          'recherche-globale__formulaire',
        ],
      ],
    ];
    $form = array_merge_recursive($form, $form_additional_attributes);

    $search_additional_attributes = [
      '#attributes' => [
        'class' => [
          'controle__input',
        ],
      ],
    ];
    $form['search'] = array_merge_recursive($form['search'], $search_additional_attributes);

    $actions_additional_attributes = [
      '#attributes' => [
        'class' => [
          'primaire',
          'controle__bouton',
        ],
      ],
    ];
    $form['actions'] = array_merge_recursive($form['actions'], $actions_additional_attributes);
  }
}
